<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['student_logged_in'])) {
    http_response_code(403);
    die("Unauthorized");
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(404);
    echo "File not found";
    exit;
}

$file = basename($_GET['file']);

$filePath = $DOCS_PATH . $file;

if (!file_exists($filePath)) {
    http_response_code(404);
    echo "File not found";
    exit;
}

$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

// Build public URL for PDF.js
$fileUrl = $BASE_URL . "uploads/documents/" . rawurlencode($file);

if ($extension === "pdf") {
    $viewerUrl = $BASE_URL . "pdfjs/web/viewer.html?file=" . urlencode($fileUrl);
    echo "<!DOCTYPE html>
<html lang='en' style='width:100%; height:100%; margin:0; padding:0; overflow:hidden;'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        html, body {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .pdf-frame {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
            display: block !important;
        }
    </style>
</head>
<body style='width:100%; height:100%; margin:0; padding:0;'>
    <iframe 
        class='pdf-frame'
        src='" . htmlspecialchars($viewerUrl, ENT_QUOTES) . "'>
    </iframe>
</body>
</html>";
    exit;
}

// Handle images
if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
    $mimeType = 'image/' . ($extension === 'jpg' ? 'jpeg' : ($extension === 'svg' ? 'svg+xml' : $extension));
    header("Content-Type: " . $mimeType);
    header("Content-Disposition: inline; filename=\"" . basename($file) . "\"");
    readfile($filePath);
    exit;
}

// Handle text files
if ($extension === "txt") {
    header("Content-Type: text/plain; charset=utf-8");
    header("Content-Disposition: inline; filename=\"" . basename($file) . "\"");
    readfile($filePath);
    exit;
}

// Non-supported files fallback
echo "
    <div style='padding:20px; color:white;'>
        <h3>Preview not supported for this file type.</h3>
        <p>Please click <b>Open in New Tab</b> or <b>Download</b> to view this file.</p>
    </div>
";

exit;
?>

