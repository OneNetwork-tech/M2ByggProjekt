<?php
/**
 * M2 Platform — View & Format Helpers
 */

function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function money(float $n): string { return number_format($n, 0, ',', ' ') . ' kr'; }

function dt(?string $s, string $fmt = 'j M Y'): string {
    if (!$s) return '–';
    $t = strtotime($s);
    $months = ['Jan','Feb','Mar','Apr','Maj','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
    if ($fmt === 'j M Y')   return date('j', $t) . ' ' . $months[date('n', $t)-1] . ' ' . date('Y', $t);
    if ($fmt === 'j M H:i') return date('j', $t) . ' ' . $months[date('n', $t)-1] . ' ' . date('H:i', $t);
    return date($fmt, $t);
}

function time_ago(?string $s): string {
    if (!$s) return '–';
    $diff = time() - strtotime($s);
    if ($diff < 60)      return 'nyss';
    if ($diff < 3600)    return floor($diff/60) . ' min sedan';
    if ($diff < 86400)   return floor($diff/3600) . ' tim sedan';
    if ($diff < 604800)  return floor($diff/86400) . ' dgr sedan';
    return dt($s);
}

function badge(string $key, array $map): string {
    $cfg = $map[$key] ?? ['label' => $key, 'color' => '#6B7280'];
    return '<span class="badge" style="background:' . $cfg['color'] . '14;color:' . $cfg['color'] . ';border:1px solid ' . $cfg['color'] . '33">' . e($cfg['label']) . '</span>';
}

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $out = mb_substr($parts[0] ?? '', 0, 1);
    if (count($parts) > 1) $out .= mb_substr(end($parts), 0, 1);
    return mb_strtoupper($out);
}

function flash(string $msg = null, string $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; return; }
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']);
        $color = $f['type'] === 'error' ? '#DC2626' : '#059669';
        echo '<div class="flash" style="border-color:' . $color . '33;background:' . $color . '0d;color:' . $color . '">' . e($f['msg']) . '</div>';
    }
}

function user_name(?int $id): string {
    if (!$id) return '–';
    static $cache = [];
    if (!isset($cache[$id])) {
        $s = db()->prepare("SELECT name FROM users WHERE id = ?"); $s->execute([$id]);
        $cache[$id] = $s->fetchColumn() ?: '–';
    }
    return $cache[$id];
}

/** Quote totals calculator (fastpris + ROT) */
function calc_quote_totals(array $items): array {
    $work = 0; $material = 0;
    foreach ($items as $it) {
        $line = $it['qty'] * $it['unit_price'];
        if (!empty($it['is_work'])) $work += $line; else $material += $line;
    }
    $subtotal = $work + $material;
    $vat = $subtotal * VAT_RATE;
    $rot = min($work * (1 + VAT_RATE) * ROT_RATE, 50000);
    $total = $subtotal + $vat - $rot;
    return ['work'=>$work, 'material'=>$material, 'subtotal'=>$subtotal, 'vat'=>$vat, 'rot'=>$rot, 'total'=>$total];
}

/** Update invoice status from paid amount */
function refresh_invoice_status(int $invoiceId): void {
    $inv = db()->prepare("SELECT * FROM invoices WHERE id = ?"); $inv->execute([$invoiceId]);
    $i = $inv->fetch(); if (!$i) return;
    if ($i['status'] === 'cancelled') return;
    $status = $i['status'];
    if ($i['paid_amount'] >= $i['total'] && $i['total'] > 0)       $status = 'paid';
    elseif ($i['paid_amount'] > 0)                                  $status = 'partial';
    elseif ($i['due_date'] && strtotime($i['due_date']) < time() && $i['status'] === 'sent') $status = 'overdue';
    db()->prepare("UPDATE invoices SET status = ? WHERE id = ?")->execute([$status, $invoiceId]);
}
