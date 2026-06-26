<?php
/**
 * M2 Platform — Quote Builder & Detail
 * AUTOMATION (per blueprint): Quote Accepted → Create Project + Generate Invoice
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$me = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$quote = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM quotes WHERE id=?"); $stmt->execute([$id]);
    $quote = $stmt->fetch();
    if (!$quote) { header('Location: offerter.php'); exit; }
}

// Prefill from lead or customer
$prefillLead = null; $prefillCust = null;
if (!empty($_GET['lead'])) {
    $s = $pdo->prepare("SELECT * FROM leads WHERE id=?"); $s->execute([(int)$_GET['lead']]);
    $prefillLead = $s->fetch();
}
if (!empty($_GET['customer'])) {
    $s = $pdo->prepare("SELECT * FROM customers WHERE id=?"); $s->execute([(int)$_GET['customer']]);
    $prefillCust = $s->fetch();
}

$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$leads     = $pdo->query("SELECT id, name, lead_no FROM leads WHERE stage NOT IN ('won','lost') ORDER BY created_at DESC")->fetchAll();

/* ── ACTIONS ─────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    // SAVE (create or update)
    if ($action === 'save') {
        $items = array_values($_POST['items'] ?? []);
        $items = array_filter($items, fn($i) => trim($i['description'] ?? '') !== '');
        foreach ($items as &$it) {
            $it['qty'] = (float)($it['qty'] ?? 1);
            $it['unit_price'] = (float)($it['unit_price'] ?? 0);
            $it['is_work'] = !empty($it['is_work']) ? 1 : 0;
            $it['total'] = $it['qty'] * $it['unit_price'];
        }
        unset($it);
        $t = calc_quote_totals($items);

        if ($quote) {
            $pdo->prepare("UPDATE quotes SET title=?, customer_id=?, lead_id=?, valid_until=?, notes=?, work_cost=?, material_cost=?, subtotal=?, vat=?, rot_deduction=?, total=? WHERE id=?")
                ->execute([trim($_POST['title']), $_POST['customer_id'] ?: null, $_POST['lead_id'] ?: null,
                           $_POST['valid_until'] ?: null, trim($_POST['notes'] ?? ''),
                           $t['work'], $t['material'], $t['subtotal'], $t['vat'], $t['rot'], $t['total'], $id]);
            $pdo->prepare("DELETE FROM quote_items WHERE quote_id=?")->execute([$id]);
            $qid = $id;
            log_timeline('quote', $qid, 'system', 'Offert uppdaterad', '', $me['id']);
        } else {
            $no = next_number('O', 'quotes', 'quote_no');
            $pdo->prepare("INSERT INTO quotes (quote_no, title, customer_id, lead_id, valid_until, notes, work_cost, material_cost, subtotal, vat, rot_deduction, total, created_by, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'draft')")
                ->execute([$no, trim($_POST['title']), $_POST['customer_id'] ?: null, $_POST['lead_id'] ?: null,
                           $_POST['valid_until'] ?: null, trim($_POST['notes'] ?? ''),
                           $t['work'], $t['material'], $t['subtotal'], $t['vat'], $t['rot'], $t['total'], $me['id']]);
            $qid = $pdo->lastInsertId();
            log_timeline('quote', $qid, 'system', 'Offert skapad', '', $me['id']);
            if (!empty($_POST['lead_id'])) {
                log_timeline('lead', (int)$_POST['lead_id'], 'system', "Offert $no skapad", '', $me['id']);
            }
            audit('quote_create', 'quote', $qid);
        }

        $ins = $pdo->prepare("INSERT INTO quote_items (quote_id, description, qty, unit, unit_price, is_work, total, sort_order) VALUES (?,?,?,?,?,?,?,?)");
        foreach (array_values($items) as $i => $it) {
            $ins->execute([$qid, trim($it['description']), $it['qty'], trim($it['unit'] ?? 'st'), $it['unit_price'], $it['is_work'], $it['total'], $i]);
        }
        flash('Offert sparad.');
        header("Location: offert.php?id=$qid"); exit;
    }

    // STATUS CHANGE — with automation
    if ($action === 'status' && $quote) {
        $status = $_POST['status'];
        if (!isset(QUOTE_STATUSES[$status])) { header("Location: offert.php?id=$id"); exit; }

        $extra = '';
        if ($status === 'sent')     $extra = ", sent_at = datetime('now','localtime')";
        if ($status === 'viewed')   $extra = ", viewed_at = datetime('now','localtime')";
        if ($status === 'accepted') $extra = ", accepted_at = datetime('now','localtime')";
        $pdo->prepare("UPDATE quotes SET status=? $extra WHERE id=?")->execute([$status, $id]);
        log_timeline('quote', $id, 'status', 'Status: ' . QUOTE_STATUSES[$status]['label'], '', $me['id']);
        audit('quote_status', 'quote', $id, $status);

        // Notify customer by email when quote is sent
        if ($status === 'sent' && $quote['customer_id']) {
            $cs = $pdo->prepare("SELECT * FROM customers WHERE id=?"); $cs->execute([$quote['customer_id']]);
            if ($cust = $cs->fetch()) {
                if (!empty($cust['email'])) {
                    crm_send_mail(
                        $cust['email'], $cust['name'],
                        'Din offert från M2 Bygg Team — ' . $quote['quote_no'],
                        '<p>Hej ' . htmlspecialchars($cust['name'], ENT_QUOTES, 'UTF-8') . '!</p><p>Vi har skickat en offert till dig: <strong>' . htmlspecialchars($quote['title'], ENT_QUOTES, 'UTF-8') . '</strong> (' . htmlspecialchars($quote['quote_no'], ENT_QUOTES, 'UTF-8') . ').</p><p>Logga in på din kundportal för att granska och godkänna offerten.</p>',
                        'quote', $id,
                        (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/portal/offerter.php',
                        'Visa offert i portalen'
                    );
                }
            }
        }

        /* ════ AUTOMATION ENGINE (per blueprint) ════
           Quote Approved → Create Project + Generate Invoice
           Lead → mark won */
        if ($status === 'accepted') {

            // 1. Ensure customer exists (convert from lead if needed)
            $custId = $quote['customer_id'];
            if (!$custId && $quote['lead_id']) {
                $l = $pdo->prepare("SELECT * FROM leads WHERE id=?"); $l->execute([$quote['lead_id']]);
                if ($lead = $l->fetch()) {
                    if ($lead['customer_id']) {
                        $custId = $lead['customer_id'];
                    } else {
                        $pdo->prepare("INSERT INTO customers (name,email,phone,address,city) VALUES (?,?,?,?,?)")
                            ->execute([$lead['name'],$lead['email'],$lead['phone'],$lead['address'],$lead['city']]);
                        $custId = $pdo->lastInsertId();
                        $pdo->prepare("UPDATE leads SET customer_id=? WHERE id=?")->execute([$custId, $lead['id']]);
                        log_timeline('customer', $custId, 'system', 'Kund skapad automatiskt vid offertacceptans', '', $me['id']);
                    }
                }
                $pdo->prepare("UPDATE quotes SET customer_id=? WHERE id=?")->execute([$custId, $id]);
            }

            // 2. Mark lead as won
            if ($quote['lead_id']) {
                $pdo->prepare("UPDATE leads SET stage='won' WHERE id=?")->execute([$quote['lead_id']]);
                log_timeline('lead', $quote['lead_id'], 'system', 'Lead vunnen – offert ' . $quote['quote_no'] . ' accepterad', '', $me['id']);
            }

            // 3. Create project
            $existing = $pdo->prepare("SELECT id FROM projects WHERE quote_id=?"); $existing->execute([$id]);
            if (!$existing->fetchColumn()) {
                $pno = next_number('P', 'projects', 'project_no');
                $custRow = null;
                if ($custId) { $cs = $pdo->prepare("SELECT * FROM customers WHERE id=?"); $cs->execute([$custId]); $custRow = $cs->fetch(); }
                $pdo->prepare("INSERT INTO projects (project_no, customer_id, quote_id, title, address, city, status, budget, next_step) VALUES (?,?,?,?,?,?,'planning',?,?)")
                    ->execute([$pno, $custId, $id, $quote['title'], $custRow['address'] ?? '', $custRow['city'] ?? '', $quote['total'], 'Boka startdatum med kund']);
                $projId = $pdo->lastInsertId();
                log_timeline('project', $projId, 'system', "Projekt skapat automatiskt från offert {$quote['quote_no']}", '', $me['id']);
                log_timeline('quote', $id, 'system', "Projekt $pno genererat", '', $me['id']);
                notify_role('project', 'Nytt projekt: ' . $quote['title'], "Projekt $pno skapades automatiskt från accepterad offert.", "projekt-detalj.php?id=$projId");
            }

            // 4. Generate draft invoice from quote
            $existingInv = $pdo->prepare("SELECT id FROM invoices WHERE quote_id=?"); $existingInv->execute([$id]);
            if (!$existingInv->fetchColumn()) {
                $fno = next_number('F', 'invoices', 'invoice_no');
                $pdo->prepare("INSERT INTO invoices (invoice_no, customer_id, quote_id, status, issue_date, due_date, subtotal, vat, rot_deduction, total) VALUES (?,?,?,'draft',date('now'),date('now','+30 days'),?,?,?,?)")
                    ->execute([$fno, $custId, $id, $quote['subtotal'], $quote['vat'], $quote['rot_deduction'], $quote['total']]);
                $invId = $pdo->lastInsertId();
                // copy items
                $qi = $pdo->prepare("SELECT * FROM quote_items WHERE quote_id=?"); $qi->execute([$id]);
                $ii = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, qty, unit, unit_price, total) VALUES (?,?,?,?,?,?)");
                foreach ($qi->fetchAll() as $item) {
                    $ii->execute([$invId, $item['description'], $item['qty'], $item['unit'], $item['unit_price'], $item['total']]);
                }
                log_timeline('quote', $id, 'system', "Fakturautkast $fno genererat", '', $me['id']);
                notify_role('finance', 'Nytt fakturautkast: ' . $fno, 'Genererat automatiskt från accepterad offert.', "faktura.php?id=$invId");
            }

            flash('Offert accepterad! Projekt och fakturautkast har skapats automatiskt.');
        } else {
            flash('Status uppdaterad.');
        }
        header("Location: offert.php?id=$id"); exit;
    }
}

// Load items
$items = [];
if ($quote) {
    $s = $pdo->prepare("SELECT * FROM quote_items WHERE quote_id=? ORDER BY sort_order"); $s->execute([$id]);
    $items = $s->fetchAll();
}
$tlEvents = [];
if ($quote) {
    $s = $pdo->prepare("SELECT * FROM timeline WHERE entity_type='quote' AND entity_id=? ORDER BY created_at DESC"); $s->execute([$id]);
    $tlEvents = $s->fetchAll();
}
$signature = null;
if ($quote) {
    $s = $pdo->prepare("SELECT * FROM quote_signatures WHERE quote_id=?"); $s->execute([$id]);
    $signature = $s->fetch() ?: null;
}

$crm_title = $quote ? $quote['quote_no'] : 'Ny offert';
$crm_page  = 'offerter';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <a href="offerter.php" style="color:var(--gray-lt);display:flex"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
      <h1><?= $quote ? e($quote['quote_no']) : 'Ny offert' ?></h1>
      <?php if ($quote): ?><?= badge($quote['status'], QUOTE_STATUSES) ?><?php endif; ?>
    </div>
    <?php if ($quote): ?><div class="topbar__sub">Skapad <?= dt($quote['created_at'], 'j M H:i') ?> av <?= e(user_name($quote['created_by'])) ?></div><?php endif; ?>
  </div>
  <?php if ($quote): ?>
  <div class="topbar__actions">
    <a href="offert-pdf.php?id=<?= $id ?>" target="_blank" class="btn btn--ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Visa / Skriv ut
    </a>
    <?php if (!in_array($quote['status'], ['accepted','rejected'])): ?>
    <form method="POST" style="display:inline">
      <?= csrf_field() ?><input type="hidden" name="action" value="status">
      <?php if ($quote['status'] === 'draft'): ?>
      <input type="hidden" name="status" value="sent">
      <button class="btn btn--primary">Markera som skickad</button>
      <?php else: ?>
      <input type="hidden" name="status" value="accepted">
      <button class="btn btn--green" onclick="return confirm('Acceptera offerten? Projekt + faktura skapas automatiskt.')">✓ Acceptera</button>
      <?php endif; ?>
    </form>
    <?php if ($quote['status'] !== 'draft'): ?>
    <form method="POST" style="display:inline">
      <?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="status" value="rejected">
      <button class="btn btn--danger" onclick="return confirm('Markera som avvisad?')">Avvisa</button>
    </form>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php flash(); ?>

<form method="POST">
<?= csrf_field() ?>
<input type="hidden" name="action" value="save">

<div class="detail-grid">
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- HEADER FIELDS -->
    <div class="card card--pad">
      <div class="fg"><label>Titel *</label>
        <input class="fi" name="title" required placeholder="T.ex. Takbyte – Villagatan 12, Mölndal"
               value="<?= e($quote['title'] ?? ($prefillLead ? ($prefillLead['service'] . ' – ' . $prefillLead['name']) : '')) ?>">
      </div>
      <div class="frow-3">
        <div class="fg"><label>Kund</label>
          <select class="fs" name="customer_id">
            <option value="">– Välj kund –</option>
            <?php foreach ($customers as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($quote['customer_id'] ?? ($prefillCust['id'] ?? 0)) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Lead</label>
          <select class="fs" name="lead_id">
            <option value="">– Koppla lead –</option>
            <?php foreach ($leads as $l): ?>
            <option value="<?= $l['id'] ?>" <?= ($quote['lead_id'] ?? ($prefillLead['id'] ?? 0)) == $l['id'] ? 'selected' : '' ?>><?= e($l['name']) ?> (<?= e($l['lead_no']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Giltig till</label>
          <input class="fi" type="date" name="valid_until" value="<?= e($quote['valid_until'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
        </div>
      </div>
    </div>

    <!-- LINE ITEMS -->
    <div class="card card--pad">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
        <h3 style="font-size:14.5px">Offertrader</h3>
        <button type="button" class="btn btn--ghost btn--sm" onclick="addQuoteItem()">+ Lägg till rad</button>
      </div>
      <div class="table-wrap">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
          <thead>
            <tr style="text-align:left;color:var(--gray-lt);font-size:11px;text-transform:uppercase;letter-spacing:.06em">
              <th style="padding:6px 8px">Beskrivning</th>
              <th style="padding:6px 8px">Antal</th>
              <th style="padding:6px 8px">Enhet</th>
              <th style="padding:6px 8px">À-pris</th>
              <th style="padding:6px 8px;text-align:center" title="Arbetskostnad ger ROT-avdrag">Arbete</th>
              <th style="padding:6px 8px;text-align:right">Summa</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="qi-body">
            <?php
            $rows = $items ?: [['description'=>'','qty'=>1,'unit'=>'st','unit_price'=>0,'is_work'=>1]];
            foreach ($rows as $n => $it): ?>
            <tr class="qi-row">
              <td style="padding:4px"><input class="fi" name="items[<?= $n ?>][description]" value="<?= e($it['description']) ?>" placeholder="Beskrivning" required></td>
              <td style="padding:4px"><input class="fi qi-qty" name="items[<?= $n ?>][qty]" type="number" step="0.1" value="<?= (float)$it['qty'] ?>" style="width:70px"></td>
              <td style="padding:4px"><input class="fi" name="items[<?= $n ?>][unit]" value="<?= e($it['unit']) ?>" style="width:64px"></td>
              <td style="padding:4px"><input class="fi qi-price" name="items[<?= $n ?>][unit_price]" type="number" step="1" value="<?= (float)$it['unit_price'] ?>" style="width:110px"></td>
              <td style="padding:4px;text-align:center"><input type="checkbox" class="qi-work" name="items[<?= $n ?>][is_work]" value="1" <?= !empty($it['is_work']) ? 'checked' : '' ?> style="accent-color:var(--blue)"></td>
              <td class="qi-line" style="padding:4px 8px;text-align:right;font-weight:600;white-space:nowrap">0 kr</td>
              <td style="padding:4px"><button type="button" class="btn btn--danger btn--sm" onclick="this.closest('tr').remove();recalcQuote()">✕</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- TOTALS -->
      <div style="margin-top:18px;border-top:1px solid var(--border);padding-top:14px;display:flex;flex-direction:column;gap:6px;align-items:flex-end;font-size:13.5px">
        <div style="display:flex;gap:32px"><span style="color:var(--gray)">Delsumma (exkl. moms)</span><strong id="q-subtotal" style="min-width:110px;text-align:right">0 kr</strong></div>
        <div style="display:flex;gap:32px"><span style="color:var(--gray)">Moms 25%</span><strong id="q-vat" style="min-width:110px;text-align:right">0 kr</strong></div>
        <div style="display:flex;gap:32px"><span style="color:var(--green)">ROT-avdrag (30% av arbete, max 50 000)</span><strong id="q-rot" style="min-width:110px;text-align:right;color:var(--green)">0 kr</strong></div>
        <div style="display:flex;gap:32px;font-size:16px;border-top:2px solid var(--ink);padding-top:8px;margin-top:4px"><span style="font-weight:700">Att betala (fastpris)</span><strong id="q-total" style="min-width:110px;text-align:right">0 kr</strong></div>
      </div>
    </div>

    <div class="card card--pad">
      <div class="fg"><label>Villkor / Anteckningar</label>
        <textarea class="fta" name="notes" placeholder="T.ex. Fast pris. 5 år garanti ingår. Vi hanterar ROT-ansökan."><?= e($quote['notes'] ?? "Fast pris – prisgaranti ingår.\n5 år garanti på allt arbete.\nVi hanterar ROT-ansökan till Skatteverket.") ?></textarea>
      </div>
      <button class="btn btn--primary" style="width:100%;justify-content:center">
        <?= $quote ? 'Spara ändringar' : 'Skapa offert' ?>
      </button>
    </div>
  </div>

  <!-- RIGHT: status + timeline -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <?php if ($quote): ?>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Sammanfattning</h3>
      <div style="display:flex;flex-direction:column;gap:9px;font-size:13.5px">
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Arbetskostnad</span><strong><?= money($quote['work_cost']) ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Material</span><strong><?= money($quote['material_cost']) ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Moms</span><strong><?= money($quote['vat']) ?></strong></div>
        <div style="display:flex;justify-content:space-between;color:var(--green)"><span>ROT-avdrag</span><strong>−<?= money($quote['rot_deduction']) ?></strong></div>
        <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:9px;font-size:15px"><span style="font-weight:700">Fastpris</span><strong><?= money($quote['total']) ?></strong></div>
      </div>
    </div>

    <?php if ($signature): ?>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">✓ Elektronisk signatur</h3>
      <img src="<?= e($signature['signature_data']) ?>" alt="Signatur" style="border:1px solid var(--border);border-radius:8px;max-width:100%;background:#fff;margin-bottom:10px">
      <div style="font-size:12.5px;color:var(--gray);line-height:1.7">
        <div><strong style="color:var(--ink)"><?= e($signature['signer_name']) ?></strong><?= $signature['signer_email'] ? ' · '.e($signature['signer_email']) : '' ?></div>
        <div>Signerad: <?= dt($signature['signed_at'], 'j M H:i') ?></div>
        <div>IP-adress: <?= e($signature['ip_address'] ?: '—') ?></div>
      </div>
    </div>
    <?php endif; ?>

    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Aktivitet</h3>
      <div class="timeline">
        <?php foreach ($tlEvents as $ev): ?>
        <div class="tl-item">
          <div class="tl-dot <?= $ev['type'] === 'system' ? 'gray' : 'amber' ?>"></div>
          <div class="tl-title"><?= e($ev['title']) ?></div>
          <div class="tl-meta"><?= e(user_name($ev['created_by'])) ?> · <?= time_ago($ev['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="card card--pad" style="background:var(--blue-lt);border-color:#BFDBFE">
      <h3 style="font-size:14px;margin-bottom:8px;color:var(--blue)">💡 Automation</h3>
      <p style="font-size:13px;color:var(--ink-soft);line-height:1.6">När offerten <strong>accepteras</strong> skapas automatiskt:</p>
      <ul style="font-size:13px;color:var(--ink-soft);padding-left:18px;margin-top:8px;line-height:1.8">
        <li>Kund (om lead inte redan konverterats)</li>
        <li>Projekt med budget från offerten</li>
        <li>Fakturautkast med alla rader</li>
        <li>Lead markeras som <strong>Vunnen</strong></li>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</div>
</form>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
