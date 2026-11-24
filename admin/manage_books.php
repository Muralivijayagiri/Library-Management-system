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
<title>Manage Returns - Admin Dashboard</title>
<link rel="stylesheet" href="../assets/css/records-table.css">

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #f3f6ff 0%, #e8edff 100%);
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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .topbar h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        background: linear-gradient(90deg, #8fa0ff, #5d73ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
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

    tr:hover {
        background: #f3f6ff;
        transition: background 0.2s ease;
    }

    td {
        color: #1a1a2e;
        font-size: 15px;
        font-family: 'Poppins', sans-serif;
    }

    .date-column {
        width: 140px;
        text-align: center;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        border: none;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .borrow-btn {
        background: linear-gradient(90deg, #8c9eff, #536dfe);
        box-shadow: 0 4px 12px rgba(83, 109, 254, 0.4);
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .borrow-btn:hover {
        background: linear-gradient(90deg, #7c8dfc, #4e60e6);
        box-shadow: 0 6px 20px rgba(78, 96, 230, 0.5);
    }

    .return-btn {
        background: linear-gradient(135deg, #ff7043 0%, #ff5722 100%);
        box-shadow: 0 4px 12px rgba(255, 112, 67, 0.3);
    }

    .return-btn:hover {
        background: linear-gradient(135deg, #ff5722 0%, #e64a19 100%);
        box-shadow: 0 6px 20px rgba(255, 112, 67, 0.4);
    }

    .send-reminder-btn {
        background: linear-gradient(135deg, #ff7043 0%, #ff5722 100%);
        box-shadow: 0 4px 12px rgba(255, 112, 67, 0.3);
    }

    .send-reminder-btn:hover {
        background: linear-gradient(135deg, #ff5722 0%, #e64a19 100%);
        box-shadow: 0 6px 20px rgba(255, 112, 67, 0.4);
    }

    .delete-btn {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    .delete-btn:hover {
        background: linear-gradient(135deg, #ee5a5a 0%, #dd4949 100%);
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
    }

    .modal-bg {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
        backdrop-filter: blur(8px);
    }

    .modal-box {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        padding: 30px;
        border-radius: 16px;
        width: 380px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(179, 192, 237, 0.2);
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .modal-box h3 {
        margin-top: 0;
        color: #4a6cf7;
        font-size: 20px;
    }

    .modal-box label {
        display: block;
        margin: 15px 0 8px 0;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .modal-box input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5ff;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.25s ease;
    }

    .modal-box input:focus {
        outline: none;
        border-color: #4a6cf7;
        box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
    }

    .modal-box button {
        margin-top: 8px;
    }

    .search-filter-bar {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(179, 192, 237, 0.2);
        margin-bottom: 30px;
    }

    .returns-filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        width: 100%;
        align-items: center;
    }

    .returns-filter-form input {
        flex: 1 1 220px;
        min-width: 0;
    }

    .returns-filter-form select {
        flex: 1 1 140px;
        min-width: 0;
        max-width: 200px;
    }

    .returns-filter-form .filter-submit {
        flex: 0 0 auto;
        white-space: nowrap;
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

    .search-filter-bar input:focus,
    .search-filter-bar select:focus {
        outline: none;
        border-color: #b3c0ed;
        box-shadow: 0 0 0 4px rgba(179, 192, 237, 0.1);
        transform: translateY(-1px);
    }

</style>
</head>
<body>

<?php include "sidebar.php"; ?>

<div class="content">

    <div class="topbar">
        <h2>Manage Returns</h2>
    </div>

    <!-- Search and Filter Bar -->
    <div class="search-filter-bar">
        <form method="GET" action="" class="returns-filter-form">
            <input type="text" 
                   id="searchInput"
                   name="search" 
                   placeholder="Search by roll, name, book..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
            
            <select id="branchFilter" name="branch">
                <option value="all" <?= (isset($_GET['branch']) && $_GET['branch'] === 'all') || !isset($_GET['branch']) ? 'selected' : '' ?>>All Branches</option>
                <option value="CSE" <?= isset($_GET['branch']) && $_GET['branch'] === 'CSE' ? 'selected' : '' ?>>CSE</option>
                <option value="ECE" <?= isset($_GET['branch']) && $_GET['branch'] === 'ECE' ? 'selected' : '' ?>>ECE</option>
                <option value="EEE" <?= isset($_GET['branch']) && $_GET['branch'] === 'EEE' ? 'selected' : '' ?>>EEE</option>
                <option value="CSB" <?= isset($_GET['branch']) && $_GET['branch'] === 'CSB' ? 'selected' : '' ?>>CSB</option>
                <option value="Mechanical" <?= isset($_GET['branch']) && $_GET['branch'] === 'Mechanical' ? 'selected' : '' ?>>Mechanical</option>
                <option value="Civil" <?= isset($_GET['branch']) && $_GET['branch'] === 'Civil' ? 'selected' : '' ?>>Civil</option>
                <option value="IT" <?= isset($_GET['branch']) && $_GET['branch'] === 'IT' ? 'selected' : '' ?>>IT</option>
            </select>
            
            <select id="dueFilter" name="due_status">
                <option value="all" <?= (isset($_GET['due_status']) && $_GET['due_status'] === 'all') || !isset($_GET['due_status']) ? 'selected' : '' ?>>All</option>
                <option value="overdue" <?= isset($_GET['due_status']) && $_GET['due_status'] === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                <option value="due_today" <?= isset($_GET['due_status']) && $_GET['due_status'] === 'due_today' ? 'selected' : '' ?>>Due Today</option>
                <option value="due_tomorrow" <?= isset($_GET['due_status']) && $_GET['due_status'] === 'due_tomorrow' ? 'selected' : '' ?>>Due Tomorrow</option>
                <option value="due_3days" <?= isset($_GET['due_status']) && $_GET['due_status'] === 'due_3days' ? 'selected' : '' ?>>Due in 3 Days</option>
                <option value="normal" <?= isset($_GET['due_status']) && $_GET['due_status'] === 'normal' ? 'selected' : '' ?>>Normal</option>
            </select>
            
            <select id="returnedFilter" name="returned_status">
                <option value="all" <?= (isset($_GET['returned_status']) && $_GET['returned_status'] === 'all') || !isset($_GET['returned_status']) ? 'selected' : '' ?>>All</option>
                <option value="returned" <?= isset($_GET['returned_status']) && $_GET['returned_status'] === 'returned' ? 'selected' : '' ?>>Returned</option>
                <option value="not_returned" <?= isset($_GET['returned_status']) && $_GET['returned_status'] === 'not_returned' ? 'selected' : '' ?>>Not Returned</option>
            </select>
            
            <button type="submit" class="btn filter-submit" style="background: linear-gradient(90deg, #8fa0ff, #5d73ff); color: white; padding: 14px 18px; border-radius: 12px; font-weight: 600; cursor: pointer;">Search</button>
        </form>
    </div>

    <div class="table-wrap">
    <table class="records-table returns-table">
        <thead>
        <tr>
            <th class="col-roll">Student Roll</th>
            <th class="col-book">Book Name</th>
            <th class="col-category">Category</th>
            <th class="date-column col-date">Borrow Date</th>
            <th class="date-column col-date">Expected Return</th>
            <th class="date-column col-date">Actual Return</th>
            <th class="col-due">Due Status</th>
            <th class="col-reminder">Send Reminder</th>
            <th class="col-action">Action</th>
        </tr>
        </thead>

        <tbody id="returnsTableBody">
        <?php
        // Get filter values
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $branch = isset($_GET['branch']) ? trim($_GET['branch']) : 'all';
        $dueStatus = isset($_GET['due_status']) ? trim($_GET['due_status']) : 'all';
        $returnedStatus = isset($_GET['returned_status']) ? trim($_GET['returned_status']) : 'all';
        
        // Build query with filters
        $query = "SELECT 
            borrow_records.id AS borrow_id,
            borrow_records.student_roll,
            borrow_records.borrow_date,
            borrow_records.expected_return,
            borrow_records.actual_return_date,
            borrow_records.status,
            books.title,
            books.category,
            students.id AS student_id,
            students.branch AS student_branch
        FROM borrow_records
        JOIN books ON borrow_records.book_id = books.id
        JOIN students ON borrow_records.student_roll = students.roll_number
        WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Returned status filter (affects which records to show)
        if ($returnedStatus === 'returned') {
            $query .= " AND borrow_records.status = 'returned'";
        } else if ($returnedStatus === 'not_returned') {
            $query .= " AND borrow_records.status = 'borrowed'";
        } else {
            // "All" or default: show both returned and borrowed
            $query .= " AND (borrow_records.status = 'borrowed' OR borrow_records.status = 'returned')";
        }
        
        // Basic search filter
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query .= " AND (borrow_records.student_roll LIKE ? OR students.name LIKE ? OR books.title LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }
        
        // Branch filter
        if ($branch !== 'all' && !empty($branch)) {
            $query .= " AND students.branch = ?";
            $params[] = $branch;
            $types .= 's';
        }
        
        // Due status filter
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $threeDays = date('Y-m-d', strtotime('+3 days'));
        
        if ($dueStatus !== 'all' && !empty($dueStatus)) {
            switch ($dueStatus) {
                case 'overdue':
                    $query .= " AND borrow_records.expected_return < ? AND borrow_records.actual_return_date IS NULL";
                    $params[] = $today;
                    $types .= 's';
                    break;
                case 'due_today':
                    $query .= " AND borrow_records.expected_return = ? AND borrow_records.actual_return_date IS NULL";
                    $params[] = $today;
                    $types .= 's';
                    break;
                case 'due_tomorrow':
                    $query .= " AND borrow_records.expected_return = ? AND borrow_records.actual_return_date IS NULL";
                    $params[] = $tomorrow;
                    $types .= 's';
                    break;
                case 'due_3days':
                    $query .= " AND borrow_records.expected_return = ? AND borrow_records.actual_return_date IS NULL";
                    $params[] = $threeDays;
                    $types .= 's';
                    break;
                case 'normal':
                    // Normal = expected_return > today + 3 OR returned
                    $query .= " AND (borrow_records.expected_return > ? OR borrow_records.actual_return_date IS NOT NULL)";
                    $params[] = $threeDays;
                    $types .= 's';
                    break;
            }
        }
        
        $query .= " ORDER BY borrow_records.borrow_date DESC";
        
        // Execute query with prepared statement if params exist
        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }
        
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                // Determine due status
                $expected = $row['expected_return'];
                $actual = $row['actual_return_date'];
                
                $dueStatus = '';
                $dueStatusText = '';
                
                // Check if expected_return is valid
                if (empty($expected) || $expected == '-' || $expected == '0000-00-00') {
                    $dueStatus = '-';
                    $dueStatusText = '-';
                } else {
                    // Parse dates
                    $expectedDate = new DateTime(trim($expected));
                    
                    // Calculate signed difference in days (expected - today)
                    $todayTimestamp = $todayDate->getTimestamp();
                    $expectedTimestamp = $expectedDate->getTimestamp();
                    $diff = (int)(($expectedTimestamp - $todayTimestamp) / 86400);
                    
                    // Rule 1: If book is returned → Status = "-"
                    if (!empty($actual) && $actual != '0000-00-00' && $actual != '-' && $actual != '–') {
                        $dueStatus = '-';
                        $dueStatusText = '-';
                    }
                    // Rule 2: If diff < 0 (today > expected) → Status = "Overdue"
                    else if ($diff < 0) {
                        $dueStatus = 'Overdue';
                        $dueStatusText = 'Overdue';
                    }
                    // Rule 3: If diff == 0 → Status = "Due Today"
                    else if ($diff == 0) {
                        $dueStatus = 'Due';
                        $dueStatusText = 'Due Today';
                    }
                    // Rule 4: If diff == 1 → Status = "Due Tomorrow"
                    else if ($diff == 1) {
                        $dueStatus = 'Due';
                        $dueStatusText = 'Due Tomorrow';
                    }
                    // Rule 5: If diff <= 3 → Status = "Due in X days"
                    else if ($diff <= 3) {
                        $dueStatus = 'Due';
                        $dueStatusText = 'Due in ' . $diff . ' days';
                    }
                    // Rule 6: Else → Status = "Normal"
                    else {
                        $dueStatus = 'Normal';
                        $dueStatusText = 'Normal';
                    }
                }
                
                // Format actual return date
                $actualReturnDisplay = ($actual !== null && $actual !== '') ? htmlspecialchars($actual, ENT_QUOTES) : '—';
                
                // Override "Due Today" to display as "Overdue"
                if ($dueStatusText === "Due Today") {
                    $dueStatus = "Overdue";
                    $dueStatusText = "Overdue";
                }
        ?>
        <tr data-branch="<?= htmlspecialchars($row['student_branch'] ?? '', ENT_QUOTES) ?>">
            <td class="col-roll"><?= htmlspecialchars($row['student_roll'], ENT_QUOTES) ?></td>
            <td class="col-book"><?= htmlspecialchars($row['title'], ENT_QUOTES) ?></td>
            <td class="col-category"><?= htmlspecialchars($row['category'], ENT_QUOTES) ?></td>
            <td class="date-column col-date"><?= htmlspecialchars($row['borrow_date'], ENT_QUOTES) ?></td>
            <td class="date-column col-date"><?= htmlspecialchars($row['expected_return'], ENT_QUOTES) ?></td>
            <td class="date-column col-date"><?= $actualReturnDisplay ?></td>
            <td class="col-due">
                <?php if ($dueStatus === 'Overdue'): ?>
                    <span class="overdue-badge"><?= htmlspecialchars($dueStatusText, ENT_QUOTES) ?></span>
                <?php elseif ($dueStatus === 'Due'): ?>
                    <span class="due-badge"><?= htmlspecialchars($dueStatusText, ENT_QUOTES) ?></span>
                <?php elseif ($dueStatus === '-'): ?>
                    <?= htmlspecialchars($dueStatusText, ENT_QUOTES) ?>
                <?php else: ?>
                    <span class="normal-badge"><?= htmlspecialchars($dueStatusText, ENT_QUOTES) ?></span>
                <?php endif; ?>
            </td>
            <td class="col-reminder">
                <button class="btn-reminder"
                    onclick="sendReminder('<?php echo $row['student_id']; ?>',
                                          '<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>',
                                          '<?php echo htmlspecialchars($row['expected_return'], ENT_QUOTES); ?>')">
                    Send Reminder
                </button>
            </td>
            <td class="col-action">
                <?php if ($row['status'] === 'returned') : ?>
                    <button class="btn returned-btn" disabled>Returned</button>
                <?php else : ?>
                    <button class="btn return-btn" onclick="openReturnModal(<?= $row['borrow_id'] ?>)">Return</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php 
            endwhile;
        else:
        ?>
        <tr>
            <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                No borrowed books at the moment.
            </td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- RETURN MODAL -->
<div class="modal-bg" id="returnModal">
    <div class="modal-box">
        <h3 style="color:#4a6cf7;">Return Book</h3>

        <label>Enter Return Password</label>
        <input type="password" id="returnPass" style="
            width: 100%; padding: 10px; 
            border:1px solid #ccc; border-radius:8px;
            margin: 8px 0 15px 0;
        ">

        <button class="btn return-btn"
            onclick="confirmReturn()">
            Confirm Return
        </button>

        <button class="btn" style="background:#ccc; color:black;"
            onclick="closeReturnModal()">
            Cancel
        </button>
    </div>
</div>

<script>
let activeBorrowId = null;

// RETURN MODAL
function openReturnModal(id) {
    activeBorrowId = id;
    document.getElementById("returnModal").style.display = "flex";
}

function closeReturnModal() {
    activeBorrowId = null;
    document.getElementById("returnModal").style.display = "none";
}

function confirmReturn() {
    let pass = document.getElementById("returnPass").value;

    fetch("return_book.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            borrow_id: activeBorrowId,
            password: pass
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Book Returned ✔");
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert("Network error"));
}

function deleteBook(bookId) {
    if (!confirm("Are you sure you want to delete this book?")) return;

    fetch("delete_book.php?id=" + bookId)
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => alert("Network error"));
}

function openBorrowFromTable(bookId) {
    window.location.href = "my_library.php";
}

</script>

<script>
// Client-side filtering function
function applyFilters() {
    // Get filter values
    const searchValue = document.getElementById("searchInput").value.toLowerCase().trim();
    const branchValue = document.getElementById("branchFilter").value;
    const dueStatusValue = document.getElementById("dueFilter").value;
    const returnedValue = document.getElementById("returnedFilter").value;
    
    // Get all table rows in tbody
    const tbody = document.getElementById("returnsTableBody");
    const rows = tbody.querySelectorAll("tr");
    
    // Loop through each row
    rows.forEach(function(row) {
        // Get cell values
        const cells = row.querySelectorAll("td");
        
        // Extract data from cells (based on column order)
        const studentRoll = cells[0] ? cells[0].textContent.trim() : "";
        const bookName = cells[1] ? cells[1].textContent.trim() : "";
        const category = cells[2] ? cells[2].textContent.trim() : "";
        const actualReturn = cells[5] ? cells[5].textContent.trim() : "";
        const dueStatusBadge = cells[6] ? cells[6].querySelector("span") : null;
        const dueStatusText = dueStatusBadge ? dueStatusBadge.textContent.trim() : "";
        
        // Check if branch data is available (via data attribute)
        const studentBranch = row.getAttribute("data-branch") || "";
        
        // Apply filter 1: Basic Search
        let matchesSearch = true;
        if (searchValue !== "") {
            const searchableText = (studentRoll + " " + bookName + " " + category).toLowerCase();
            matchesSearch = searchableText.includes(searchValue);
        }
        
        // Apply filter 2: Branch Filter
        let matchesBranch = true;
        if (branchValue !== "all") {
            if (studentBranch !== "") {
                matchesBranch = studentBranch === branchValue;
            } else {
                // If branch data not available in DOM, skip this filter
                matchesBranch = true;
            }
        }
        
        // Apply filter 3: Due Status Filter
        let matchesDueStatus = true;
        if (dueStatusValue !== "all") {
            if (dueStatusValue === "normal") {
                matchesDueStatus = dueStatusText === "Normal";
            } else if (dueStatusValue === "overdue") {
                matchesDueStatus = dueStatusText === "Overdue";
            } else if (dueStatusValue === "due_today") {
                matchesDueStatus = dueStatusText === "Due Today";
            } else if (dueStatusValue === "due_tomorrow") {
                matchesDueStatus = dueStatusText === "Due Tomorrow";
            } else if (dueStatusValue === "due_3days") {
                // Match "Due in 2 days" or "Due in 3 days" (but not "Due Today" or "Due Tomorrow")
                matchesDueStatus = dueStatusText.includes("Due in") && 
                                  (dueStatusText.includes("2 days") || dueStatusText.includes("3 days"));
            }
        }
        
        // Apply filter 4: Returned Filter
        let matchesReturned = true;
        if (returnedValue !== "all") {
            if (returnedValue === "returned") {
                matchesReturned = actualReturn !== "—" && actualReturn !== "";
            } else if (returnedValue === "not_returned") {
                matchesReturned = actualReturn === "—" || actualReturn === "";
            }
        }
        
        // Show row if ALL filters match
        if (matchesSearch && matchesBranch && matchesDueStatus && matchesReturned) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

// Event listeners for search and filter inputs
document.getElementById("searchInput").addEventListener("keyup", function() {
    applyFilters();
});

document.getElementById("branchFilter").addEventListener("change", function() {
    applyFilters();
});

document.getElementById("dueFilter").addEventListener("change", function() {
    applyFilters();
});

document.getElementById("returnedFilter").addEventListener("change", function() {
    applyFilters();
});

// Apply filters on page load (in case there are initial filter values)
document.addEventListener("DOMContentLoaded", function() {
    applyFilters();
});

function sendReminder(studentId, bookName, expectedReturn) {
    if (!confirm("Send reminder to this student?")) {
        return;
    }

    const formData = new FormData();
    formData.append("student_id", studentId);
    formData.append("book_name", bookName);
    formData.append("expected_return", expectedReturn);

    fetch("send_reminder.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("Reminder sent successfully!");
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        alert("Network error. Please try again.");
        console.error(err);
    });
}
</script>

</body>
</html>
