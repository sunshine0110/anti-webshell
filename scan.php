<?php

function scan_directory($directory)
{
    $files = glob($directory . '/*');
    $result = [];

    foreach ($files as $file) 
    {
            $allowed_extensions = ['php', 'shtml', 'phtm', 'php7', 'php4', 'php5', 'php3', 'cgi', 'inc', 'php.test', 'xxxjpg', 'html']; 
                $file_extension = pathinfo($file, PATHINFO_EXTENSION);
       if (is_file($file) && in_array($file_extension, $allowed_extensions))
        {
            $scan_result = check_file_content($file);
            if ($scan_result['dangerous']) {
                // Dapatkan tanggal modifikasi file
                $tanggal_modifikasi = date('Y-m-d H:i:s', filemtime($file));
                $result[] = [
                    'file' => $file,
                    'tanggal' => $tanggal_modifikasi,
                    'status' => 'Potensi Bahaya',
                    'pattern' => $scan_result['pattern'],
                ];
            }
        } elseif (is_dir($file)) {
            $subdirectory_result = scan_directory($file);
            $result = array_merge($result, $subdirectory_result);
        }
    }
    return $result;
}

function check_file_content($file)
{
    $dangerous_patterns = [
        '/(eval|gzinflate|str_rot13|exec|shell_exec|system|passthru|popen|proc_open|pcntl_exec|assert)\s*\(/i',
        '/(\\$_(GET|POST|REQUEST|COOKIE|FILES|SERVER|SESSION|GLOBALS|ENV)\\s*\\[.*?\\])\\s*\\(/i',
        '/<\\?php\\s+@?include\\s*\\(.*\\)\\s*;/i',
        '/\\$GLOBALS\\s*\\[\\$GLOBALS\\s*\\[(["\'])(.*?)\\1\\s*\\]\\s*\\]\\s*\\(\\);/',
        '/\\$[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*\\s*\\(\\s*\\$[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*\\s*(?:,|\\))/i',
        '/preg_replace\\s*\\(\\s*["\'][\\/\\\\\(\\[].*?[\\/\\\\\)\\]]*\\s*["\']\\s*,/i',
        '/\\b(chmod|chown|fopen|fwrite|symlink)\\s*\\(/i',
        '/<\\?php\\s*header\\("X-XSS-Protection:/i',
        '/mt5_A;k5chJ:\s*\$Code/i',
        '/define\("self",\s*"G\x65l\64y M\x69n\x69 Sh\x65ll"\)/i',
        '/\$_SESSION\[\'forbidden\'\]\s*=\s*\$pass;/i',
        '/\$a\s*=\s*"\x67\x7A\x75\x6E\x63\x6F\x6D\x70\x72\x65\x73\x73";/i',
        '/\/\*\* Adminer - Compact database management/i',
        '/\$LkzrdppIttlkxzS\s*=/i',
        '/safe_mode\s*\(\s*\);/i',
        '/goto\s+A7ZyE;\s+A7ZyE:\s*\$obfuscator\s*=/i',
        '/eval\(str_rot13\(gzinflate\(str_rot13\(base64_decode/i',
        '/\\b(while|for|foreach)\s*\\(.*\\s*\$(_POST|_GET|\\$_REQUEST|\\$_COOKIE|\\$_FILES|\\$_SESSION|\\$_ENV|\\$_SERVER)\\s*\\[.*?\\].*\\)/i',
        '/\$tmp\s*=\s*\\$_SERVER\[\'SERVER_NAME\'\].\\$_SERVER\[\'PHP_SELF\'\];/i',
        '/<title>IndoXploit<\/title>/i',
        '/{ IndoSec sHell }/i',
        '/base64_decode\s*\(\s*["\'][a-zA-Z0-9+\/=]+["\']\s*\)\s*;/i',
        '/eval\s*\(\s*\(base64_decode\s*\(\s*["\'][a-zA-Z0-9+\/=]+["\']\s*\)\s*\)\s*;/i',
        '/http_response_code\(404\);/i',
        '/base64_decode/i',
        '/base64_encode\s*\(\s*["\'][a-zA-Z0-9+\/=]+["\']\s*\)\s*;/i',
        '/alfa/i',
        '/indoXPloit/i',
        '/b374k/i',
        '/mini shell/i',
        '/solevisibel/i',
        '/WSO/i',
        '/shell/i',
        '/command/i',
        '/symlink/i',
        '/chmod/i',
        '/wget/i',
        '/gacor/i',
        '/menang/i',
        '/maxwin/i',
        '/deface/i',
        '/exploit/i',
        '/DDOS/i',
        '/uploader/i',
        '/ssi shell/i',
        '/R10T/i',
        '/index changer/i',
        '/decrypt/i',
       
    ];

    $file_content = file_get_contents($file);

    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $file_content, $matches)) {
            return [
                'dangerous' => true,
                'pattern' => $matches[0],
            ];
        }
    }

    return [
        'dangerous' => false,
        'pattern' => '',
    ];
}

function delete_file($file_path)
{
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function read_file($file_path)
{
    return file_get_contents($file_path);
}

function save_file($file_path, $content)
{
    return file_put_contents($file_path, $content);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $file_to_edit = $_POST['file'];

    if ($action === 'read') {
        $file_content = read_file($file_to_edit);
        echo json_encode(['status' => 'success', 'content' => $file_content]);
        exit;
    } elseif ($action === 'save') {
        $content = $_POST['content'];
        if (save_file($file_to_edit, $content)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file.']);
        }
        exit;
    } elseif ($action === 'delete') {
        if (delete_file($file_to_edit)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus file.']);
        }
        exit;
    }
}

$target_directory = isset($_POST['target_directory']) ? $_POST['target_directory'] : $_SERVER['DOCUMENT_ROOT'];
$scan_result = scan_directory($target_directory);


?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Scan File PHP Berbahaya</title>
    <style> 
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        table {width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #f44336;
        }

        .floating-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            z-index: 999;
            display: none;
            
        }

        .form-title {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .form-content {
            width: 100%;height: 300px;
        }

        .form-btn-container {
            text-align: right;
            margin-top: 10px;
        }

        .form-btn {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            text-decoration: none;
            cursor: pointer;
            margin-left: 5px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Floating form -->
    <div class="floating-form" id="editForm">
        <div class="form-title">Edit File</div>
        <form id="editFileForm">
            <input type="hidden" id="editFile" value="">
            <textarea class="form-content" id="fileContent"></textarea>
            <div class="form-btn-container">
                <button type="submit" class="form-btn">Simpan</button>
                <button type="button" id="closeForm" class="form-btn">Tutup</button>
            </div>
        </form>
    </div>
    <h1>Hasil Scan File PHP Berbahaya</h1>

    <?php if (!empty($scan_result)): ?><table>
            <tr>
                <th>File</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Pattern</th>
                <th>Edit File</th>
                <th>Hapus File</th>
            </tr>
            <?php foreach ($scan_result as $result): ?>
                <tr>
                    <td><?php echo $result['file']; ?></td>
                    <td><?php echo $result['tanggal']; ?></td>                   
                    <td><?php echo $result['status']; ?></td>
                    <td><?php echo $result['pattern']; ?></td>
                    <td><button class="btn btn-edit" data-file="<?php echo $result['file']; ?>">Edit</button></td>
                    <td><button class="btn btn-danger btn-delete" data-file="<?php echo $result['file']; ?>">Hapus</button></td>
                    
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Tidak ada file PHP berbahaya.</p>
    <?php endif; ?>

    <script>
        $(document).ready(function() 
        
        {
            // Tampilkan floating form saat tombol Edit diklik
            $('.btn-edit').click(function() {
                const fileToEdit = $(this).data('file');
                $.ajax({
                    method: 'POST',
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                    data: { action: 'read', file: fileToEdit },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editFile').val(fileToEdit);
                            $('#fileContent').val(response.content);
                            $('#editForm').show();
                        } else {
                            alert('Gagal membaca isi file.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menghubungi server.');
                    }
                });
            });
            
            

            // Simpan perubahan saat tombol Simpan diklik
            $('#editFileForm').submit(function(e) {
                e.preventDefault();
                const fileToSave = $('#editFile').val();
                const contentToSave = $('#fileContent').val();
                $.ajax({
                    method: 'POST',
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                    data: { action: 'save', file: fileToSave, content: contentToSave },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Perubahan berhasil disimpan.');
                            $('#editForm').hide();
                        } else {
                            alert(response.message || 'Gagal menyimpan perubahan.');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menghubungi server.');
                    }
                });
            });
            

            // Tutup floating form saat tombol Tutup diklik
            $('#closeForm').click(function() {
                $('#editForm').hide();
            });
        // Fungsi tombol Akses
        $('#accessForm').submit(function(e) {
            e.preventDefault();
            const targetDirectory = $('#target_directory').val();
            window.location.href = targetDirectory;
        });
            // Fungsi tombol Hapus
            $('.btn-delete').click(function() {
                const fileToDelete = $(this).data('file');
                if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
                    $.ajax({
                        method: 'POST',
                        url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                        data: { action: 'delete', file: fileToDelete },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('File berhasil dihapus.');
                                location.reload(); // Refresh halaman untuk menampilkan hasil terbaru
                            } else {
                                alert(response.message || 'Gagal menghapus file.');
                            }
                        },
                        error: function() {
                            alert('Terjadi kesalahan saat menghubungi server.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
