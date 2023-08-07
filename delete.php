<?php
function deleteFileRecursive($dir, $filename) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($filePath)) {
            deleteFileRecursive($filePath, $filename);
        } elseif ($file === $filename) {
            unlink($filePath);
            echo "File $filename berhasil dihapus.<br>";
        }
    }
}

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$filenameToDelete = 'cok.html';

deleteFileRecursive($documentRoot, $filenameToDelete);
?>
