<?php
session_start();
if (!isset($_SESSION['student_logged_in'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <!-- Content -->
    <div class="content">

        <!-- Topbar -->
        <div class="topbar">
            <h2>Student dashboard</h2>
            <div class="profile">Welcome, <?= htmlspecialchars($_SESSION['student_name'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <main class="dashboard-content">
            <section class="dashboard-intro" aria-labelledby="student-dashboard-title">
                <p class="dashboard-eyebrow">YOUR STUDENT LIBRARY</p>
                <h1 id="student-dashboard-title">Make the most of your library</h1>
                <p>Browse books, open learning resources, and keep track of your borrowings.</p>
            </section>

            <section class="dashboard-actions" aria-labelledby="student-actions-title">
                <div class="dashboard-section-heading">
                    <h2 id="student-actions-title">Explore your library</h2>
                    <p>Pick up where you left off or find something new.</p>
                </div>
                <div class="dashboard-action-grid">
                    <a class="dashboard-action-card" href="my_library.php">
                        <span class="dashboard-action-icon" aria-hidden="true">📚</span>
                        <span class="dashboard-action-copy"><strong>Browse books</strong><span>Explore titles in the library catalog.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="my_borrowings.php">
                        <span class="dashboard-action-icon" aria-hidden="true">📖</span>
                        <span class="dashboard-action-copy"><strong>My borrowings</strong><span>Check your current and past loans.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="documents.php">
                        <span class="dashboard-action-icon" aria-hidden="true">📁</span>
                        <span class="dashboard-action-copy"><strong>Documents</strong><span>Open study documents shared by the library.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="audio.php">
                        <span class="dashboard-action-icon" aria-hidden="true">🎧</span>
                        <span class="dashboard-action-copy"><strong>Audio books</strong><span>Listen to available audio titles.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                    <a class="dashboard-action-card" href="student_chat.php">
                        <span class="dashboard-action-icon" aria-hidden="true">💬</span>
                        <span class="dashboard-action-copy"><strong>Chat with admin</strong><span>Ask the library team a question.</span></span>
                        <span class="dashboard-action-arrow" aria-hidden="true">→</span>
                    </a>
                </div>
            </section>
        </main>

    </div>

</body>
</html>

