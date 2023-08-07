<?php
function deleteFileRecursive($dir, $filename, &$deletedCount, &$failedCount) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($filePath)) {
            deleteFileRecursive($filePath, $filename, $deletedCount, $failedCount);
        } elseif ($file === $filename) {
            if (unlink($filePath)) {
                $deletedCount++;
                echo "File $filename berhasil dihapus.<br>";
            } else {
                $failedCount++;
                echo "Gagal menghapus file $filename.<br>";
            }
        }
    }
}

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$filenameToDelete = 'cok.html';
$deletedCount = 0;
$failedCount = 0;

deleteFileRecursive($documentRoot, $filenameToDelete, $deletedCount, $failedCount);

echo "Total file berhasil dihapus: $deletedCount<br>";
echo "Total file gagal dihapus: $failedCount<br>";
?>
