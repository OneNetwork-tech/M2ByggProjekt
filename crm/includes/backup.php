<?php
/**
 * Backup helpers — bundles the SQLite database + portal-uploads directory into a
 * backup. Three-tier fallback depending on what the host environment allows:
 *   1. ZipArchive (typical on cPanel shared hosting) → single .zip file
 *   2. PharData (built into PHP core, but blocked if php.ini has phar.readonly=1) → .tar.gz
 *   3. Plain recursive directory copy (zero dependencies, always works) → timestamped folder
 *
 * MySQL note: if DB_DRIVER is 'mysql', the database itself is NOT included here — back it
 * up via your hosting panel's MySQL backup tool (e.g. cPanel > Backup Wizard) or mysqldump.
 * This only covers the SQLite file + uploaded files in all three modes.
 */

require_once __DIR__ . '/db.php';

function backup_dir(): string {
    $dir = dirname(__DIR__, 2) . '/data/backups';
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    return $dir;
}

/** Recursively add a directory's files to a ZipArchive under the given prefix. */
function backup_zip_add_dir(ZipArchive $zip, string $sourceDir, string $prefix): void {
    if (!is_dir($sourceDir)) return;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($files as $file) {
        if ($file->isDir()) continue;
        $localPath = $prefix . '/' . substr($file->getPathname(), strlen($sourceDir) + 1);
        $zip->addFile($file->getPathname(), str_replace('\\', '/', $localPath));
    }
}

function backup_copy_dir(string $src, string $dst): void {
    if (!is_dir($src)) return;
    if (!is_dir($dst)) mkdir($dst, 0750, true);
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($files as $file) {
        $target = $dst . '/' . substr($file->getPathname(), strlen($src) + 1);
        if ($file->isDir()) {
            if (!is_dir($target)) mkdir($target, 0750, true);
        } else {
            copy($file->getPathname(), $target);
        }
    }
}

/** Create a new backup. Returns the backup name (file or folder) on success, or null on failure. */
function backup_create(): ?string {
    $dataDir = dirname(__DIR__, 2) . '/data';
    $timestamp = date('Y-m-d_His');
    $uploadsDir = $dataDir . '/portal-uploads';
    $includeDb = DB_DRIVER !== 'mysql' && is_file(DB_SQLITE_PATH);

    // Tier 1: ZipArchive
    if (class_exists('ZipArchive')) {
        $filename = "m2-backup-{$timestamp}.zip";
        $zip = new ZipArchive();
        if ($zip->open(backup_dir() . '/' . $filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            if ($includeDb) $zip->addFile(DB_SQLITE_PATH, 'm2platform.sqlite');
            backup_zip_add_dir($zip, $uploadsDir, 'portal-uploads');
            $zip->close();
            return $filename;
        }
    }

    // Tier 2: PharData (tar.gz) — fails if phar.readonly=1 in php.ini
    if (class_exists('PharData') && !ini_get('phar.readonly')) {
        $filename = "m2-backup-{$timestamp}.tar.gz";
        $tarPath = backup_dir() . "/m2-backup-{$timestamp}.tar";
        try {
            $phar = new PharData($tarPath);
            if ($includeDb) $phar->addFile(DB_SQLITE_PATH, 'm2platform.sqlite');
            if (is_dir($uploadsDir)) $phar->buildFromDirectory($uploadsDir, '//');
            $phar->compress(Phar::GZ);
            unset($phar);
            unlink($tarPath);
            rename($tarPath . '.gz', backup_dir() . '/' . $filename);
            return $filename;
        } catch (Throwable $e) {
            error_log('backup_create (PharData) failed: ' . $e->getMessage());
            if (is_file($tarPath)) @unlink($tarPath);
        }
    }

    // Tier 3: plain directory copy — zero dependencies, always works
    $folderName = "m2-backup-{$timestamp}";
    $target = backup_dir() . '/' . $folderName;
    mkdir($target, 0750, true);
    if ($includeDb) copy(DB_SQLITE_PATH, $target . '/m2platform.sqlite');
    backup_copy_dir($uploadsDir, $target . '/portal-uploads');
    return $folderName;
}

function backup_path(string $name): string {
    return backup_dir() . '/' . basename($name);
}

function backup_is_folder(string $name): bool {
    return is_dir(backup_path($name));
}

function backup_size(string $path): int {
    if (!is_dir($path)) return filesize($path);
    $total = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->isFile()) $total += $f->getSize();
    }
    return $total;
}

/** List existing backups, newest first. */
function backup_list(): array {
    $dir = backup_dir();
    $entries = scandir($dir) ?: [];
    $out = [];
    foreach ($entries as $name) {
        if (!preg_match('/^m2-backup-/', $name)) continue;
        $path = $dir . '/' . $name;
        $out[] = ['name' => $name, 'size' => backup_size($path), 'mtime' => filemtime($path), 'is_folder' => is_dir($path)];
    }
    usort($out, fn($a, $b) => $b['mtime'] - $a['mtime']);
    return $out;
}

function backup_delete(string $filename): bool {
    $path = backup_path($filename);
    if (!file_exists($path)) return false;
    if (is_dir($path)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $f) { $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname()); }
        return rmdir($path);
    }
    return unlink($path);
}

/** Delete backups beyond the most recent $keep. */
function backup_apply_retention(int $keep = 14): int {
    $toDelete = array_slice(backup_list(), $keep);
    $deleted = 0;
    foreach ($toDelete as $b) {
        if (backup_delete($b['name'])) $deleted++;
    }
    return $deleted;
}
