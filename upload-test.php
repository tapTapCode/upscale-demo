<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['file'])) {
        $error_code = $_FILES['file']['error'];
        if ($error_code === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['file']['tmp_name'];
            $name = basename($_FILES['file']['name']);
            $upload_dir = __DIR__ . '/uploads_test';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $dest = $upload_dir . '/' . $name;

            if (move_uploaded_file($tmp_name, $dest)) {
                echo "File uploaded successfully: $dest";
            } else {
                echo "Failed to move uploaded file.";
            }
        } else {
            echo "Upload error code: $error_code";
        }
    } else {
        echo "No file uploaded.";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head><title>Upload Test</title></head>
<body>
<form method="POST" enctype="multipart/form-data">
    <label>Select a file to upload:</label><br>
    <input type="file" name="file" /><br><br>
    <button type="submit">Upload</button>
</form>
</body>
</html>
