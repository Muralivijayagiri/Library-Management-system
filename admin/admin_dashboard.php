<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <!-- Content -->
    <div class="content">

        <!-- Topbar -->
        <div class="topbar">
            <h2>Admin dashboard</h2>
            <div class="profile">Welcome, <?= htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8') ?>!</div>
        </div>
        <main class="dashboard-content">
            <section class="dashboard-intro" aria-labelledby="admin-dashboard-title">
                <p class="dashboard-eyebrow">ADMIN WORKSPACE</p>
                <h1 id="admin-dashboard-title">Run your library with confidence</h1>
                <p>Manage books, student accounts, returns, and questions from one place.</p>
            </section>

            <section class="dashboard-actions" aria-labelledby="admin-actions-title">
                <div class="dashboard-section-heading">
                    <h2 id="admin-actions-title">Quick actions</h2>
                    <p>Go straight to a common library task.</p>
                </div>
                <div class="dashboard-action-grid">
                    <a class="dashboard-action-card" href="my_library.php">
                        <span class="dashboard-action-icon" aria-hidden="true">📚</span>
                        <span class="dashboard-action-copy"><strong>Book catalog</strong><span>Browse books and update available copies.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="manage_books.php">
                        <span class="dashboard-action-icon" aria-hidden="true">↩️</span>
                        <span class="dashboard-action-copy"><strong>Manage returns</strong><span>Review active loans and return status.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="manage_students.php">
                        <span class="dashboard-action-icon" aria-hidden="true">👥</span>
                        <span class="dashboard-action-copy"><strong>Students</strong><span>View and manage student accounts.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="add.php">
                        <span class="dashboard-action-icon" aria-hidden="true">➕</span>
                        <span class="dashboard-action-copy"><strong>Add a book</strong><span>Add a title to the library catalog.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="chat_inbox.php">
                        <span class="dashboard-action-icon" aria-hidden="true">💬</span>
                        <span class="dashboard-action-copy"><strong>Chat inbox</strong><span>Open conversations with students.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                </div>
            </section>
        </main>

    </div>

</body>
</html>

