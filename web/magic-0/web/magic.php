<?php
$resizedImagePath = null;
$error = null;

function canUploadImage($file) {
    $maxFileSize = 500 * 1024;
    $forbidden = array(
        'exec', 'passthru', 'shell_exec', 'system', 'proc_open', 'popen', 'curl_exec',
        'curl_multi_exec', 'show_source', 'system', 'shell_exec', 'passthru', 'exec',
        'popen', 'proc_open', 'allow_url_fopen', 'allow_url_include', 'fopen_with_path',
        'file_put_contents', 'file_get_contents', 'readfile', 'move_uploaded_file', 
        'copy', 'rename', 'unlink', 'symlink', 'link', 'mkdir', 'rmdir', 'fopen', 'tmpfile', 
        '<?php', 'eval', 'create_function', 'include', 'include_once', 'require', 'require_once'
    );
    $content = file_get_contents($file['tmp_name']);
    foreach ($forbidden as $word) {
        if (stripos($content, $word) !== false) {
            return false;
        }
    }
    return ($file['size'] <= $maxFileSize &&
        strlen($file['name']) >= 30
    );
}

function resizeImage($fileName) {
    try {
        $imagick = new \Imagick('results/original-' . $fileName);
        $imagick->thumbnailImage(50, 50, true, true);
        $filePath = 'results/50x50-' . $fileName;
        $imagick->writeImage($filePath);
        chmod($filePath, 0444);
        return $filePath;
    } catch (Exception $e) {
        global $error;
        $error = 'Error when doing magic.';
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    if (canUploadImage($_FILES['image'])) {
        move_uploaded_file($_FILES['image']['tmp_name'], 'results/original-' . $_FILES['image']['name']);
        $resizedImagePath = resizeImage($_FILES['image']['name']);
    } else {
        $error = 'Please upload different file.';
    }
}
?>