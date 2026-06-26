<?php
/**
 * CRM — Reporting & Analytics
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_role(['super_admin','sales','finance','project']);
$pdo = db();

// ── REVENUE TREND (last 12 months, paid invoices by issue month) ───────────
$revRows = $pdo->query("
    SELECT strftime('%Y-%m', issue_date) AS ym, SUM(total) AS total, SUM(paid_amount) AS paid
    FROM invoices
    WHERE issue_date >= date('now','-12 months') AND status != 'cancelled'
    GROUP BY ym ORDER BY ym
")->fetchAll();
$revByMonth = [];
for ($i = 11; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $revByMonth[$ym] = ['total' => 0, 'paid' => 0];
}
foreach ($revRows as $r) {
    if (isset($revByMonth[$r['ym']])) $revByMonth[$r['ym']] = ['total' => (float)$r['total'], 'paid' => (float)$r['paid']];
}
$maxRev = max(1, max(array_column($revByMonth, 'total')));

// ── LEAD SOURCE ROI ──────────────────────────────────────────────────────────
$sourceRows = $pdo->query("
    SELECT l.source,
           COUNT(*) AS lead_count,
           SUM(CASE WHEN l.stage='won' THEN 1 ELSE 0 END) AS won_count,
           COALESCE(SUM(CASE WHEN l.stage='won' THEN q.total ELSE 0 END),0) AS won_value
    FROM leads l
    LEFT JOIN quotes q ON q.lead_id = l.id AND q.status = 'accepted'
    GROUP BY l.source ORDER BY won_value DESC
")->fetchAll();

// ── CONVERSION FUNNEL ────────────────────────────────────────────────────────
$funnelOrder = ['new','contacted','site_visit','quote_sent','negotiation','won'];
$funnelCounts = [];
foreach ($funnelOrder as $stage) {
    if ($stage === 'won') {
        $funnelCounts[$stage] = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE stage='won'")->fetchColumn();
    } else {
        // count leads that ever reached this stage or further (current stage at or beyond this point, or won)
        $idx = array_search($stage, $funnelOrder);
        $laterStages = array_slice($funnelOrder, $idx);
        $in = "'" . implode("','", $laterStages) . "','lost'";
        $funnelCounts[$stage] = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE stage IN ($in)")->fetchColumn();
    }
}
$funnelLabels = ['new'=>'Ny','contacted'=>'Kontaktad','site_visit'=>'Besök bokat','quote_sent'=>'Offert skickad','negotiation'=>'Förhandling','won'=>'Vunnen'];
$maxFunnel = max(1, $funnelCounts['new'] ?: 1);
$totalLeads = (int)$pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$wonLeads   = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE stage='won'")->fetchColumn();
$lostLeads  = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE stage='lost'")->fetchColumn();
$conversionRate = $totalLeads > 0 ? round($wonLeads / $totalLeads * 100, 1) : 0;

// ── SUPPLIER UTILIZATION ─────────────────────────────────────────────────────
$supplierStats = $pdo->query("
    SELECT s.id, s.company, s.rating,
           COUNT(DISTINCT ja.id) AS jobs,
           COALESCE(SUM(tr.hours),0) AS hours,
           COALESCE(SUM(tr.amount),0) AS earned
    FROM suppliers s
    LEFT JOIN job_assignments ja ON ja.supplier_id = s.id
    LEFT JOIN time_reports tr ON tr.supplier_id = s.id
    GROUP BY s.id HAVING jobs > 0
    ORDER BY hours DESC LIMIT 8
")->fetchAll();
$maxHours = max(1, max(array_column($supplierStats, 'hours') ?: [1]));

// ── INVOICE AGING ─────────────────────────────────────────────────────────────
$agingBuckets = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
$agingRows = $pdo->query("
    SELECT total - paid_amount AS remaining, julianday('now') - julianday(due_date) AS days_overdue
    FROM invoices WHERE status IN ('sent','partial','overdue') AND total > paid_amount
")->fetchAll();
foreach ($agingRows as $r) {
    $d = (float)$r['days_overdue'];
    $amt = (float)$r['remaining'];
    if ($d <= 30)      $agingBuckets['0-30']  += $amt;
    elseif ($d <= 60)  $agingBuckets['31-60'] += $amt;
    elseif ($d <= 90)  $agingBuckets['61-90'] += $amt;
    else               $agingBuckets['90+']   += $amt;
}
$totalOutstanding = array_sum($agingBuckets);

// ── KPI SUMMARY ────────────────────────────────────────────────────────────────
$revenueThisMonth = (float)$pdo->query("SELECT COALESCE(SUM(paid_amount),0) FROM invoices WHERE strftime('%Y-%m',issue_date)=strftime('%Y-%m','now')")->fetchColumn();
$revenueLastMonth = (float)$pdo->query("SELECT COALESCE(SUM(paid_amount),0) FROM invoices WHERE strftime('%Y-%m',issue_date)=strftime('%Y-%m','now','-1 month')")->fetchColumn();
$revenueChange = $revenueLastMonth > 0 ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100) : 0;
$avgQuoteValue = (float)$pdo->query("SELECT COALESCE(AVG(total),0) FROM quotes WHERE status='accepted'")->fetchColumn();

// ── CUSTOMER REVIEWS ─────────────────────────────────────────────────────────
$reviewCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$avgRating   = (float)$pdo->query("SELECT COALESCE(AVG(rating),0) FROM reviews")->fetchColumn();
$ratingDist  = ['5'=>0,'4'=>0,'3'=>0,'2'=>0,'1'=>0];
foreach ($pdo->query("SELECT rating, COUNT(*) AS c FROM reviews GROUP BY rating")->fetchAll() as $r) {
    $ratingDist[(string)(int)$r['rating']] = (int)$r['c'];
}
$maxRatingCount = max(1, max($ratingDist));

$crm_title = 'Rapporter & Analys';
$crm_page  = 'rapporter';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Rapporter & Analys</h1>
    <div class="topbar__sub">Intäkter, konverteringar och leverantörsutnyttjande</div>
  </div>
</div>

<!-- KPI ROW -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:20px">
  <div class="card card--pad">
    <div style="font-size:11.5px;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Intäkter denna månad</div>
    <div style="font-size:24px;font-weight:700"><?= money($revenueThisMonth) ?></div>
    <?php if ($revenueChange != 0): ?>
    <div style="font-size:12px;margin-top:4px;color:<?= $revenueChange >= 0 ? 'var(--green)' : 'var(--red)' ?>"><?= $revenueChange >= 0 ? '↑' : '↓' ?> <?= abs($revenueChange) ?>% mot föregående månad</div>
    <?php endif; ?>
  </div>
  <div class="card card--pad">
    <div style="font-size:11.5px;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Konverteringsgrad</div>
    <div style="font-size:24px;font-weight:700"><?= $conversionRate ?>%</div>
    <div style="font-size:12px;margin-top:4px;color:var(--gray)"><?= $wonLeads ?> vunna av <?= $totalLeads ?> leads</div>
  </div>
  <div class="card card--pad">
    <div style="font-size:11.5px;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Snittvärde offert</div>
    <div style="font-size:24px;font-weight:700"><?= money($avgQuoteValue) ?></div>
    <div style="font-size:12px;margin-top:4px;color:var(--gray)">Accepterade offerter</div>
  </div>
  <div class="card card--pad">
    <div style="font-size:11.5px;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Utestående fakturor</div>
    <div style="font-size:24px;font-weight:700;color:<?= $totalOutstanding > 0 ? 'var(--amber)' : 'var(--ink)' ?>"><?= money($totalOutstanding) ?></div>
    <div style="font-size:12px;margin-top:4px;color:var(--gray)"><?= count($agingRows) ?> obetalda fakturor</div>
  </div>
  <div class="card card--pad">
    <div style="font-size:11.5px;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Kundbetyg</div>
    <div style="font-size:24px;font-weight:700"><?= $reviewCount > 0 ? number_format($avgRating, 1, ',', '') : '—' ?></div>
    <div style="font-size:12px;margin-top:4px;color:var(--gray)"><?= $reviewCount ?> recensioner</div>
  </div>
</div>

<div class="detail-grid">
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- REVENUE TREND -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:18px">Intäktstrend (12 månader)</h3>
      <div style="display:flex;align-items:flex-end;gap:6px;height:160px">
        <?php foreach ($revByMonth as $ym => $v):
          $h = max(2, ($v['total'] / $maxRev) * 140);
          $hPaid = $v['total'] > 0 ? max(0, ($v['paid'] / $maxRev) * 140) : 0;
          $monthLabel = date('M', strtotime($ym . '-01'));
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px">
          <div style="position:relative;width:100%;height:140px;display:flex;align-items:flex-end">
            <div style="width:100%;height:<?= $h ?>px;background:var(--blue-lt);border-radius:4px 4px 0 0;position:relative">
              <div style="position:absolute;bottom:0;width:100%;height:<?= $hPaid ?>px;background:var(--blue);border-radius:4px 4px 0 0" title="<?= money($v['paid']) ?> betalt av <?= money($v['total']) ?>"></div>
            </div>
          </div>
          <span style="font-size:10px;color:var(--gray-lt)"><?= $monthLabel ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;gap:16px;margin-top:14px;font-size:11.5px;color:var(--gray)">
        <span><span style="display:inline-block;width:10px;height:10px;background:var(--blue);border-radius:2px;margin-right:5px;vertical-align:-1px"></span>Betalt</span>
        <span><span style="display:inline-block;width:10px;height:10px;background:var(--blue-lt);border-radius:2px;margin-right:5px;vertical-align:-1px"></span>Fakturerat (totalt)</span>
      </div>
    </div>

    <!-- CONVERSION FUNNEL -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:18px">Konverteringstratt</h3>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach ($funnelOrder as $stage):
          $count = $funnelCounts[$stage];
          $pct = max(4, ($count / $maxFunnel) * 100);
        ?>
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:110px;font-size:12.5px;color:var(--gray);flex-shrink:0"><?= e($funnelLabels[$stage]) ?></div>
          <div style="flex:1;background:#F3F4F6;border-radius:6px;height:26px;position:relative">
            <div style="height:100%;width:<?= $pct ?>%;background:<?= $stage === 'won' ? 'var(--green)' : 'var(--blue)' ?>;border-radius:6px;display:flex;align-items:center;padding-left:10px">
              <span style="color:#fff;font-size:11.5px;font-weight:600"><?= $count ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--gray);margin-top:6px;padding-top:10px;border-top:1px solid #F3F4F6">
          <span><?= $lostLeads ?> förlorade leads</span>
          <span><?= $conversionRate ?>% total konvertering</span>
        </div>
      </div>
    </div>

  </div>

  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- LEAD SOURCE ROI -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Lead-källor (ROI)</h3>
      <?php if ($sourceRows): ?>
      <table class="data" style="font-size:12.5px">
        <thead><tr><th>Källa</th><th>Leads</th><th>Vunna</th><th>Värde</th></tr></thead>
        <tbody>
        <?php foreach ($sourceRows as $s): ?>
        <tr style="cursor:default">
          <td style="font-weight:550"><?= e($s['source'] ?: 'Okänd') ?></td>
          <td><?= $s['lead_count'] ?></td>
          <td><?= $s['won_count'] ?></td>
          <td style="font-weight:600"><?= money((float)$s['won_value']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p style="font-size:13px;color:var(--gray)">Ingen lead-data ännu.</p>
      <?php endif; ?>
    </div>

    <!-- SUPPLIER UTILIZATION -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Leverantörsutnyttjande (timmar)</h3>
      <?php if ($supplierStats): ?>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach ($supplierStats as $s):
          $pct = max(4, ($s['hours'] / $maxHours) * 100);
        ?>
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:100px;font-size:12px;color:var(--gray);flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($s['company']) ?></div>
          <div style="flex:1;background:#F3F4F6;border-radius:6px;height:22px;position:relative">
            <div style="height:100%;width:<?= $pct ?>%;background:var(--blue);border-radius:6px"></div>
          </div>
          <div style="width:60px;text-align:right;font-size:11.5px;color:var(--gray)"><?= number_format($s['hours'],0,',','') ?>h</div>
          <div style="width:44px;text-align:right;font-size:11px;color:#D97706"><?= $s['rating'] > 0 ? number_format($s['rating'],1,',','') . '★' : '—' ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p style="font-size:13px;color:var(--gray)">Ingen leverantörsdata ännu.</p>
      <?php endif; ?>
    </div>

    <!-- CUSTOMER REVIEWS -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Kundnöjdhet (betygsfördelning)</h3>
      <?php if ($reviewCount > 0): ?>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php for ($star = 5; $star >= 1; $star--):
          $count = $ratingDist[(string)$star];
          $pct = $count > 0 ? max(4, ($count / $maxRatingCount) * 100) : 0;
        ?>
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:42px;font-size:12px;color:#D97706;flex-shrink:0;white-space:nowrap"><?= str_repeat('★', $star) ?></div>
          <div style="flex:1;background:#F3F4F6;border-radius:6px;height:20px">
            <div style="height:100%;width:<?= $pct ?>%;background:#D97706;border-radius:6px"></div>
          </div>
          <div style="width:24px;text-align:right;font-size:11.5px;color:var(--gray)"><?= $count ?></div>
        </div>
        <?php endfor; ?>
        <a href="recensioner.php" style="font-size:12px;margin-top:6px">Hantera recensioner →</a>
      </div>
      <?php else: ?>
      <p style="font-size:13px;color:var(--gray)">Inga recensioner ännu.</p>
      <?php endif; ?>
    </div>

    <!-- INVOICE AGING -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Förfallna fakturor (åldersfördelning)</h3>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php
        $agingColors = ['0-30'=>'var(--blue)','31-60'=>'var(--amber)','61-90'=>'#EA580C','90+'=>'var(--red)'];
        $maxAging = max(1, max($agingBuckets));
        foreach ($agingBuckets as $bucket => $amt):
          $pct = $amt > 0 ? max(4, ($amt / $maxAging) * 100) : 0;
        ?>
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:60px;font-size:12px;color:var(--gray);flex-shrink:0"><?= $bucket ?> dgr</div>
          <div style="flex:1;background:#F3F4F6;border-radius:6px;height:22px">
            <div style="height:100%;width:<?= $pct ?>%;background:<?= $agingColors[$bucket] ?>;border-radius:6px"></div>
          </div>
          <div style="width:90px;text-align:right;font-size:11.5px;font-weight:600;color:var(--ink)"><?= money($amt) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
