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


        </main>

    </div>

</body>
</html>

