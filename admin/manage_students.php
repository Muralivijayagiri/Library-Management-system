<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
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
    <title>Manage Students - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/records-table.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: transparent;
            display: flex;
            color: #1a1a2e;
        }

        .content {
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }

        .topbar {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            padding: 20px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(179, 192, 237, 0.2);
        }

        .topbar h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(179, 192, 237, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #b3c0ed 0%, #d8e2ff 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card h3 {
            margin: 0 0 12px 0;
            font-size: 14px;
            font-weight: 600;
            color: #6b7fd7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a2e;
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-filter-bar {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(179, 192, 237, 0.2);
            margin-bottom: 30px;
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .search-filter-bar input,
        .search-filter-bar select {
            padding: 14px 18px;
            border: 2px solid rgba(179, 192, 237, 0.2);
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #1a1a2e;
        }

        .search-filter-bar input {
            flex: 1;
        }

        .search-filter-bar select {
            flex: 0 0 200px;
        }

        .search-filter-bar input:focus,
        .search-filter-bar select:focus {
            outline: none;
            border-color: #b3c0ed;
            box-shadow: 0 0 0 4px rgba(179, 192, 237, 0.1);
            transform: translateY(-1px);
        }

        .search-filter-bar label {
            font-weight: 600;
            color: #6b7fd7;
            font-size: 14px;
            white-space: nowrap;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(179, 192, 237, 0.2);
        }

        th, td {
            padding: 18px 24px;
            border-bottom: 1px solid rgba(179, 192, 237, 0.2);
            text-align: left;
        }

        th {
            background: linear-gradient(90deg, #8fa0ff, #5d73ff);
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(93, 115, 255, 0.3);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr.student-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        tbody tr.student-row:hover {
            background: #f3f6ff;
            transform: translateX(2px);
        }

        td {
            color: #1a1a2e;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        .stat-value {
            transition: all 0.3s ease;
        }

        .stat-value.loading {
            opacity: 0.6;
        }
    </style>
</head>
<body>

    <?php include "sidebar.php"; ?>

    <div class="content">

        <div class="ms-header-row">
            <h2 class="ms-title">Manage Students</h2>
            <a href="#" id="addStudentBtn" class="ms-add-btn">+ Add Student</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Students</h3>
                <div class="stat-value" id="totalStudents">...</div>
            </div>

            <div class="stat-card">
                <h3>Active Borrowers</h3>
                <div class="stat-value" id="activeBorrowers">...</div>
            </div>

            <div class="stat-card">
                <h3>Overdue Borrowers</h3>
                <div class="stat-value" id="overdueBorrowers">...</div>
            </div>
        </div>

        <div class="search-filter-bar">
            <input type="text" id="searchInput" placeholder="Search by name, roll, email..." style="flex: 1;">
            <label for="branchFilter">Branch:</label>
            <select id="branchFilter">
                <option value="all">All Branches</option>
                <option value="CSB">CSB</option>
                <option value="CSE">CSE</option>
                <option value="ECE">ECE</option>
                <option value="EEE">EEE</option>
                <option value="Mechanical">Mechanical</option>
                <option value="Civil">Civil</option>
                <option value="IT">IT</option>
            </select>
        </div>

        <div class="table-wrap">
        <table class="records-table students-table">
            <thead>
                <tr>
                    <th class="col-roll">Roll Number</th>
                    <th class="col-name">Name</th>
                    <th class="col-branch">Branch</th>
                    <th class="col-email">Email</th>
                    <th class="col-mobile">Mobile</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="studentsTableBody">
                <tr>
                    <td colspan="6" class="no-results">Loading students...</td>
                </tr>
            </tbody>
        </table>
        </div>

    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal-bg">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Add New Student</h3>
                <button class="modal-close" onclick="closeAddStudentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="addStudentMessage" class="error-message" style="display: none;"></div>
                <form id="addStudentForm">
                    <div class="form-group">
                        <label for="studentName">Name <span class="required">*</span></label>
                        <input type="text" id="studentName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="studentRoll">Roll Number <span class="required">*</span></label>
                        <input type="text" id="studentRoll" name="roll_number" required>
                    </div>
                    <div class="form-group">
                        <label for="studentBranch">Branch <span class="required">*</span></label>
                        <select id="studentBranch" name="branch" required>
                            <option value="">Select Branch</option>
                            <option value="CSB">CSB</option>
                            <option value="CSE">CSE</option>
                            <option value="ECE">ECE</option>
                            <option value="EEE">EEE</option>
                            <option value="Mechanical">Mechanical</option>
                            <option value="Civil">Civil</option>
                            <option value="IT">IT</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="studentEmail">Email <span class="required">*</span></label>
                        <input type="email" id="studentEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="studentMobile">Mobile <span class="required">*</span></label>
                        <input type="text" id="studentMobile" name="mobile" required>
                    </div>
                    <div class="form-group">
                        <label for="studentPassword">Password <span class="required">*</span></label>
                        <input type="password" id="studentPassword" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="studentProfileImage">Profile Image (Optional)</label>
                        <input type="file" id="studentProfileImage" name="profile_image" accept="image/*">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeAddStudentModal()">Cancel</button>
                        <button type="submit" class="btn-submit">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Student Modal -->
    <div id="deleteStudentModal" class="modal-bg">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Confirm Student Removal</h3>
                <button class="modal-close" onclick="closeDeleteStudentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="deleteStudentMessage" class="error-message" style="display: none;"></div>
                <p style="margin-bottom: 20px; color: #1a1a2e; font-size: 15px;">
                    Please enter your admin password to confirm removal of this student.
                </p>
                <form id="deleteStudentForm">
                    <input type="hidden" id="deleteStudentRoll" name="roll" value="">
                    <div class="form-group">
                        <label for="adminPassword">Admin Password <span class="required">*</span></label>
                        <input type="password" id="adminPassword" name="admin_password" required autocomplete="off">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeDeleteStudentModal()">Cancel</button>
                        <button type="submit" class="btn-delete">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/manage_students.js"></script>
    <script>
        // Load student statistics on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStudentStats();
            loadStudentsList();
        });

        let searchTimeout;
        let currentSearch = '';
        let currentBranch = 'all';

        function loadStudentsList(search = '', branch = 'all') {
            const tbody = document.getElementById('studentsTableBody');
            
            // Show loading state
            tbody.innerHTML = '<tr><td colspan="6" class="no-results">Loading students...</td></tr>';

            // Build query parameters
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (branch && branch !== 'all') params.append('branch', branch);

            // Fetch data from API
            fetch('api/get_students_list.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        renderStudentsTable(data.students);
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" class="no-results">Error loading students: ' + (data.message || 'Unknown error') + '</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    tbody.innerHTML = '<tr><td colspan="6" class="no-results">Network error. Please try again.</td></tr>';
                });
        }

        function renderStudentsTable(students) {
            const tbody = document.getElementById('studentsTableBody');
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="no-results">No students found.</td></tr>';
                return;
            }

            tbody.innerHTML = students.map(student => {
                const rollNumber = escapeHtml(student.roll_number || '');
                const name = escapeHtml(student.name || '');
                const branch = escapeHtml(student.branch || 'Not set');
                const email = escapeHtml(student.email || 'Not set');
                const mobile = escapeHtml(student.mobile || 'Not set');

                return `
                    <tr class="student-row">
                        <td class="col-roll" onclick="openStudentHistory('${rollNumber}')">${rollNumber}</td>
                        <td class="col-name" onclick="openStudentHistory('${rollNumber}')">${name}</td>
                        <td class="col-branch" onclick="openStudentHistory('${rollNumber}')">${branch}</td>
                        <td class="col-email" onclick="openStudentHistory('${rollNumber}')">${email}</td>
                        <td class="col-mobile" onclick="openStudentHistory('${rollNumber}')">${mobile}</td>
                        <td class="col-actions actions-cell">
                            <button class="delete-student-btn" onclick="event.stopPropagation(); openDeleteStudentModal('${rollNumber}')" title="Delete Student">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function openStudentHistory(roll) {
            window.location.href = 'student_history.php?roll=' + encodeURIComponent(roll);
        }

        // Search input event handler
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const query = this.value.trim();
            currentSearch = query;
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Load search results (debounced)
            searchTimeout = setTimeout(() => {
                loadStudentsList(currentSearch, currentBranch);
            }, 300);
        });

        // Branch filter event handler
        document.getElementById('branchFilter').addEventListener('change', function() {
            currentBranch = this.value;
            loadStudentsList(currentSearch, currentBranch);
        });

        function loadStudentStats() {
            // Show loading state
            const statElements = {
                totalStudents: document.getElementById('totalStudents'),
                activeBorrowers: document.getElementById('activeBorrowers'),
                overdueBorrowers: document.getElementById('overdueBorrowers')
            };

            // Add loading class
            Object.values(statElements).forEach(el => {
                if (el) {
                    el.classList.add('loading');
                    el.textContent = '...';
                }
            });

            // Fetch data from API
            fetch('api/get_student_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update stat values smoothly
                        if (statElements.totalStudents) {
                            statElements.totalStudents.textContent = data.total_students || 0;
                            statElements.totalStudents.classList.remove('loading');
                        }
                        
                        if (statElements.activeBorrowers) {
                            statElements.activeBorrowers.textContent = data.active_borrowers || 0;
                            statElements.activeBorrowers.classList.remove('loading');
                        }
                        
                        if (statElements.overdueBorrowers) {
                            statElements.overdueBorrowers.textContent = data.overdue_borrowers || 0;
                            statElements.overdueBorrowers.classList.remove('loading');
                        }
                    } else {
                        // Handle error
                        Object.values(statElements).forEach(el => {
                            if (el) {
                                el.textContent = '0';
                                el.classList.remove('loading');
                            }
                        });
                        console.error('Error loading stats:', data.message || 'Unknown error');
                    }
                })
                .catch(error => {
                    // Handle network error
                    console.error('Network error:', error);
                    Object.values(statElements).forEach(el => {
                        if (el) {
                            el.textContent = '0';
                            el.classList.remove('loading');
                        }
                    });
                });
        }
    </script>

</body>
</html>

