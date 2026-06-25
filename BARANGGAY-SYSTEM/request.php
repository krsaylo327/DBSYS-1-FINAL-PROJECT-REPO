<?php
require_once 'db_connect.php';

// Filter by status
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$where_clause = '';
if (!empty($status_filter)) {
    $where_clause = "WHERE cr.status = '$status_filter'";
}

// Fetch requests with JOINs
$requests = $conn->query("
    SELECT cr.*, 
           CONCAT(r.first_name, ' ', r.last_name) AS resident_name,
           c.certificate_name,
           c.base_fee,
           CONCAT(s.first_name, ' ', s.last_name) AS staff_name
    FROM certificate_request cr
    INNER JOIN barangay_residents r ON cr.resident_id = r.resident_id
    INNER JOIN certificates c ON cr.certificate_id = c.certificate_id
    LEFT JOIN barangay_staff s ON cr.staff_id = s.staff_id
    $where_clause
    ORDER BY cr.requested_at DESC
");

// Certificate types for dropdown
$certificates = $conn->query("SELECT certificate_id, certificate_name FROM certificates");
$staff = $conn->query("SELECT staff_id, first_name, last_name FROM barangay_staff");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Requests - Barangay System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door"></i> Barangay System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people"></i> Residents
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="residents.php">View All</a></li>
                            <li><a class="dropdown-item" href="residents_add.php">Add Resident</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-text"></i> Certificates
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="requests.php">View Requests</a></li>
                            <li><a class="dropdown-item" href="request_add.php">New Request</a></li>
                            <li><a class="dropdown-item" href="certificates.php">Certificate Types</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="households.php"><i class="bi bi-building"></i> Households</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="staff.php"><i class="bi bi-person-badge"></i> Staff</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-file-text"></i> Certificate Requests</h1>
            <a href="request_add.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> New Request
            </a>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="requests.php" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo ($status_filter == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo ($status_filter == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                            <option value="Released" <?php echo ($status_filter == 'Released') ? 'selected' : ''; ?>>Released</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="requests.php" class="btn btn-secondary w-100">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Resident</th>
                            <th>Certificate</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Processed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($requests->num_rows > 0): ?>
                            <?php while($row = $requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['request_id']; ?></td>
                                <td><?php echo $row['resident_name']; ?></td>
                                <td><?php echo $row['certificate_name']; ?></td>
                                <td><?php echo substr($row['purpose'], 0, 30) . '...'; ?></td>
                                <td><?php echo getStatusBadge($row['status']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['requested_at'])); ?></td>
                                <td><?php echo $row['staff_name'] ?: 'Not processed'; ?></td>
                                <td>
                                    <a href="request_edit.php?id=<?php echo $row['request_id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="request_delete.php?id=<?php echo $row['request_id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this request?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>