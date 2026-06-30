<?php
/**
 * M2 Platform — Minimal IMAP client for the CRM's built-in email inbox.
 * Wraps PHP's ext/imap. Requires the imap PHP extension to be enabled on the host
 * (cPanel: MultiPHP Manager → PHP Extensions). Every function fails soft (returns
 * null/[]/false) so the UI can show a clear message instead of a fatal error when
 * the extension is missing or the mailbox can't be reached.
 */

function imap_ext_available(): bool {
    return extension_loaded('imap');
}

/** @return resource|\IMAP\Connection|null */
function imap_open_account(array $acc, string $folder = 'INBOX') {
    if (!imap_ext_available()) return null;
    $host = $acc['imap_host'] ?: $acc['host'];
    $port = (int)($acc['imap_port'] ?: 993);
    $enc  = $acc['imap_encryption'] ?: 'ssl';
    $flag = $enc === 'tls' ? '/imap/tls' : '/imap/ssl';
    $mailbox = '{' . $host . ':' . $port . $flag . '}' . $folder;
    $conn = @imap_open($mailbox, $acc['username'], $acc['password'], OP_READONLY);
    return $conn ?: null;
}

/** Wrapper around imap_errors()/imap_last_error() — named to avoid colliding with the native function. */
function imap_account_last_error(): string {
    $err = imap_errors();
    return $err ? implode('; ', $err) : (imap_last_error() ?: 'Okänt IMAP-fel');
}

/**
 * List the most recent messages in the inbox (newest first).
 * Returns [] on any failure — check imap_ext_available()/imap_last_error() separately to explain why.
 */
function imap_fetch_inbox(array $acc, int $limit = 50): array {
    $conn = imap_open_account($acc);
    if (!$conn) return [];

    $total = imap_num_msg($conn);
    if ($total < 1) { imap_close($conn); return []; }

    $start = max(1, $total - $limit + 1);
    $overview = imap_fetch_overview($conn, "$start:$total", 0);
    $msgs = [];
    foreach ($overview as $o) {
        $msgs[] = [
            'msgno'   => (int)$o->msgno,
            'uid'     => (int)($o->uid ?? $o->msgno),
            'subject' => isset($o->subject) ? imap_safe_decode($o->subject) : '(inget ämne)',
            'from'    => isset($o->from) ? imap_safe_decode($o->from) : '(okänd avsändare)',
            'date'    => $o->date ?? '',
            'ts'      => isset($o->date) ? strtotime($o->date) : 0,
            'seen'    => !empty($o->seen),
        ];
    }
    usort($msgs, fn($a, $b) => $b['ts'] <=> $a['ts']);
    imap_close($conn);
    return $msgs;
}

/** Fetch one message's full body (HTML preferred, falls back to plain text wrapped in <pre>). */
function imap_fetch_message(array $acc, int $msgno): ?array {
    $conn = imap_open_account($acc);
    if (!$conn) return null;

    $header = @imap_headerinfo($conn, $msgno);
    if (!$header) { imap_close($conn); return null; }

    $structure = imap_fetchstructure($conn, $msgno);
    [$html, $text] = imap_extract_bodies($conn, $msgno, $structure);

    $result = [
        'subject' => isset($header->subject) ? imap_safe_decode($header->subject) : '(inget ämne)',
        'from'    => isset($header->from[0]) ? imap_addr_to_string($header->from[0]) : '',
        'to'      => isset($header->to[0]) ? imap_addr_to_string($header->to[0]) : '',
        'date'    => $header->date ?? '',
        'body_html' => $html,
        'body_text' => $text,
    ];
    imap_close($conn);
    return $result;
}

function imap_addr_to_string(object $a): string {
    $email = ($a->mailbox ?? '') . '@' . ($a->host ?? '');
    $name = isset($a->personal) ? imap_safe_decode($a->personal) : '';
    return $name ? "$name <$email>" : $email;
}

function imap_safe_decode(string $s): string {
    $decoded = @imap_utf8($s);
    return $decoded !== false && $decoded !== '' ? $decoded : $s;
}

/** Walk a message structure and pull out the text/html and text/plain parts. */
function imap_extract_bodies($conn, int $msgno, $structure): array {
    $html = ''; $text = '';

    if (!isset($structure->parts) || !$structure->parts) {
        // Single-part message
        $body = imap_fetchbody($conn, $msgno, '1');
        $body = imap_decode_part($body, $structure->encoding ?? 0);
        if (($structure->subtype ?? '') === 'HTML') $html = $body; else $text = $body;
        return [$html, $text];
    }

    foreach ($structure->parts as $i => $part) {
        $partNo = (string)($i + 1);
        if (($part->subtype ?? '') === 'HTML' && $html === '') {
            $body = imap_fetchbody($conn, $msgno, $partNo);
            $html = imap_decode_part($body, $part->encoding ?? 0);
        } elseif (($part->subtype ?? '') === 'PLAIN' && $text === '') {
            $body = imap_fetchbody($conn, $msgno, $partNo);
            $text = imap_decode_part($body, $part->encoding ?? 0);
        } elseif (!empty($part->parts)) {
            // nested multipart (e.g. multipart/alternative inside multipart/mixed)
            foreach ($part->parts as $j => $sub) {
                $subPartNo = $partNo . '.' . ($j + 1);
                if (($sub->subtype ?? '') === 'HTML' && $html === '') {
                    $body = imap_fetchbody($conn, $msgno, $subPartNo);
                    $html = imap_decode_part($body, $sub->encoding ?? 0);
                } elseif (($sub->subtype ?? '') === 'PLAIN' && $text === '') {
                    $body = imap_fetchbody($conn, $msgno, $subPartNo);
                    $text = imap_decode_part($body, $sub->encoding ?? 0);
                }
            }
        }
    }
    return [$html, $text];
}

function imap_decode_part(string $body, int $encoding): string {
    switch ($encoding) {
        case 3: return imap_base64($body);   // ENCBASE64
        case 4: return imap_qprint($body);   // ENCQUOTEDPRINTABLE
        default: return $body;
    }
}
