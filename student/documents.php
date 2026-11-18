<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documents</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/document_viewer_modal.css">

<style>
    /* Shared layout (.content, .topbar, .grid, .card) comes from dashboard.css.
       Page-specific rules are scoped to .documents-page so they cannot leak. */
    body.documents-page {
        display: flex;
    }

    .documents-page .card h3 {
        margin: 0 0 12px 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }

    .documents-page .card p {
        color: #666;
        font-size: 14px;
        margin: 8px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .documents-page .download-btn {
        margin-top: 15px;
    }

    .documents-page .view-btn {
        margin-top: 15px;
        margin-right: 10px;
    }
</style>
</head>

<body class="documents-page">

<?php include "sidebar.php"; ?>

<div class="content">

    <div class="topbar">
        <h2>Documents</h2>
    </div>

    <div class="grid">
        <?php
        $docs = $conn->query("SELECT * FROM documents ORDER BY id DESC");

        while ($doc = $docs->fetch_assoc()):

        ?>
        <div class="card">
            <h3><?= htmlspecialchars($doc['name'], ENT_QUOTES) ?></h3>

            <p style="color:#666;font-size:14px;margin:8px 0;">
                <?php 
                // Extract just filename (handle both old full paths and new filenames)
                $displayPath = basename($doc['file_path']);
                echo htmlspecialchars($displayPath, ENT_QUOTES); 
                ?>
            </p>

            <?php 
            // Extract just filename for view_document.php (handle both old full paths and new filenames)
            $fileName = basename($doc['file_path']);
            // Extract just filename for download (handle both old full paths and new filenames)
            $downloadFileName = basename($doc['file_path']);
            ?>

            <button class="view-btn"
                    onclick="openDocumentViewer('<?= htmlspecialchars($doc['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($fileName, ENT_QUOTES) ?>')">
                View
            </button>

            <a class="download-btn" 
               href="../uploads/documents/<?= htmlspecialchars($downloadFileName, ENT_QUOTES) ?>" 
               download>
                Download
            </a>
        </div>
        <?php endwhile; ?>
    </div>

</div>

<!-- Document Viewer Modal -->
<div id="documentViewerModal" class="document-viewer-modal">
    <div class="document-viewer-overlay" onclick="closeDocumentViewer()"></div>
    <div class="document-viewer-container">
        <div class="document-viewer-header">
            <h3 class="document-viewer-title" id="documentViewerTitle">Document Preview</h3>
            <button class="document-viewer-close" onclick="closeDocumentViewer()" aria-label="Close">×</button>
        </div>
        <div class="document-viewer-body">
            <div class="document-viewer-content" id="documentViewerContent">
                <div class="document-viewer-loading">Loading document...</div>
            </div>
        </div>
        <div class="document-viewer-footer">
            <button class="document-viewer-btn document-viewer-btn-secondary" onclick="closeDocumentViewer()">Close</button>
            <button class="document-viewer-btn document-viewer-btn-primary" id="documentViewerOpenTab" onclick="openDocumentInNewTab()">Open in New Tab</button>
        </div>
    </div>
</div>

<script>
// Document Viewer Modal Functions
let currentDocumentUrl = '';

function openDocumentViewer(title, fileName) {
    // Build view_document.php URL
    const viewUrl = 'view_document.php?file=' + encodeURIComponent(fileName);
    currentDocumentUrl = viewUrl;
    
    const modal = document.getElementById('documentViewerModal');
    const titleEl = document.getElementById('documentViewerTitle');
    const contentEl = document.getElementById('documentViewerContent');
    
    titleEl.textContent = title;
    contentEl.innerHTML = '<div class="document-viewer-loading">Loading document...</div>';
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Detect file type and render accordingly
    const fileExtension = fileName.split('.').pop().toLowerCase();
    
    if (fileExtension === 'pdf') {
        // PDF - use iframe with view_document.php wrapped in pdf-container
        contentEl.innerHTML = `<div class="pdf-container"><iframe class="pdf-frame" src="${escapeHtml(viewUrl)}" type="application/pdf" allow="fullscreen"></iframe></div>`;
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExtension)) {
        // Image - use img tag with view_document.php
        contentEl.innerHTML = `<img src="${escapeHtml(viewUrl)}" alt="${escapeHtml(title)}">`;
    } else if (fileExtension === 'txt') {
        // Text file - load via fetch from view_document.php
        fetch(viewUrl)
            .then(response => {
                if (!response.ok) throw new Error('Failed to load text file');
                return response.text();
            })
            .then(text => {
                contentEl.innerHTML = `<pre>${escapeHtml(text)}</pre>`;
            })
            .catch(err => {
                contentEl.innerHTML = `<div class="document-viewer-loading" style="color: #ff6b6b;">Error loading file: ${escapeHtml(err.message)}</div>`;
            });
    } else {
        // Default: try iframe with view_document.php wrapped in pdf-container
        contentEl.innerHTML = `<div class="pdf-container"><iframe class="pdf-frame" src="${escapeHtml(viewUrl)}" allow="fullscreen"></iframe></div>`;
    }
}

function closeDocumentViewer() {
    const modal = document.getElementById('documentViewerModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    currentDocumentUrl = '';
}

function openDocumentInNewTab() {
    if (currentDocumentUrl) {
        window.open(currentDocumentUrl, '_blank');
    }
}

// ESC key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('documentViewerModal');
        if (modal.classList.contains('active')) {
            closeDocumentViewer();
        }
    }
});

// Escape HTML helper
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>

