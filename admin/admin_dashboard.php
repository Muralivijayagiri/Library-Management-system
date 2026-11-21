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


        </main>

    </div>

</body>
</html>

