<?php
function deleteFileRecursive($dir, $filename, &$deletedDirs, &$failedDirs) {
    $deleted = false;

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $filePath = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_dir($filePath)) {
            if (deleteFileRecursive($filePath, $filename, $deletedDirs, $failedDirs)) {
                $deleted = true;
            }
        } elseif ($file === $filename) {
            if (unlink($filePath)) {
                $deleted = true;
            }
        }
    }

    if ($deleted) {
        $deletedDirs[] = $dir;
    } else {
        $failedDirs[] = $dir;
    }

    return $deleted;
}

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$filenameToDelete = 'cok.html';
$deletedDirs = [];
$failedDirs = [];

deleteFileRecursive($documentRoot, $filenameToDelete, $deletedDirs, $failedDirs);

if (!empty($deletedDirs)) {
    echo "File $filenameToDelete berhasil dihapus dari direktori:<br>";
    foreach ($deletedDirs as $deletedDir) {
        echo "- $deletedDir<br>";
    }
} else {
    echo "Gagal menghapus file $filenameToDelete dari semua direktori.<br>";
}

if (!empty($failedDirs)) {
    echo "Gagal menghapus file $filenameToDelete dari direktori berikut:<br>";
    foreach ($failedDirs as $failedDir) {
        echo "- $failedDir<br>";
    }
}
?>
