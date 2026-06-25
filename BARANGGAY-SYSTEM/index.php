<?php
require_once 'db_connect.php';

// Dashboard Statistics
$stats = [];

// Total Residents
$result = $conn->query("SELECT COUNT(*) AS total FROM barangay_residents");
$stats['total_residents'] = $result->fetch_assoc()['total'];

// Total Households
$result = $conn->query("SELECT COUNT(*) AS total FROM households");
$stats['total_households'] = $result->fetch_assoc()['total'];

// Total Certificate Requests
$result = $conn->query("SELECT COUNT(*) AS total FROM certificate_request");
$stats['total_requests'] = $result->fetch_assoc()['total'];

// Pending Requests
$result = $conn->query("SELECT COUNT(*) AS total FROM certificate_request WHERE status = 'Pending'");
$stats['pending_requests'] = $result->fetch_assoc()['total'];

// Get recent requests using VIEW
$recent_requests = $conn->query("SELECT * FROM certificate_request_summary LIMIT 5");

// Resident summary from VIEW
$resident_summary = $conn->query("SELECT * FROM resident_dashboard_summary LIMIT 10");

// Status distribution (GROUP BY)
$status_stats = $conn->query("
    SELECT status, COUNT(*) AS count 
    FROM certificate_request 
    GROUP BY status
");

// Top requested certificates (JOIN + GROUP BY)
$top_certificates = $conn->query("
    SELECT c.certificate_name, COUNT(cr.request_id) AS request_count
    FROM certificates c
    INNER JOIN certificate_request cr ON c.certificate_id = cr.certificate_id
    GROUP BY c.certificate_id
    ORDER BY request_count DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Resident Information System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
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
                        <a class="nav-link active" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
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
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
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

    <!-- Main Content -->
    <div class="container mt-4">
        <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people"></i> Total Residents</h5>
                        <h2 class="display-4"><?php echo $stats['total_residents']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-building"></i> Households</h5>
                        <h2 class="display-4"><?php echo $stats['total_households']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-file-text"></i> Total Requests</h5>
                        <h2 class="display-4"><?php echo $stats['total_requests']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-clock"></i> Pending Requests</h5>
                        <h2 class="display-4"><?php echo $stats['pending_requests']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Distribution & Top Certificates -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-pie-chart"></i> Request Status (GROUP BY)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while($row = $status_stats->fetch_assoc()): ?>
                            <div class="col-6 mb-2">
                                <span class="badge <?php echo ($row['status'] == 'Pending') ? 'bg-warning' : (($row['status'] == 'Approved') ? 'bg-success' : (($row['status'] == 'Rejected') ? 'bg-danger' : 'bg-info')); ?> p-2 w-100">
                                    <?php echo $row['status']; ?>: <?php echo $row['count']; ?>
                                </span>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-trophy"></i> Top Requested Certificates (INNER JOIN + GROUP BY)</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php while($row = $top_certificates->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $row['certificate_name']; ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $row['request_count']; ?></span>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Requests (VIEW) -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="bi bi-clock-history"></i> Recent Certificate Requests (VIEW)</h5>
                    </div>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_requests->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['request_id']; ?></td>
                                    <td><?php echo $row['resident_name']; ?></td>
                                    <td><?php echo $row['certificate_name']; ?></td>
                                    <td><?php echo substr($row['purpose'], 0, 30) . '...'; ?></td>
                                    <td><?php echo getStatusBadge($row['status']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['requested_at'])); ?></td>
                                    <td><?php echo $row['processed_by'] ?: 'Not processed'; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resident Summary (VIEW) -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="bi bi-table"></i> Resident Dashboard Summary (VIEW)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Resident</th>
                                    <th>Household</th>
                                    <th>Purok</th>
                                    <th>Total Requests</th>
                                    <th>Pending</th>
                                    <th>Approved</th>
                                    <th>Released</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $resident_summary->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['household_number']; ?></td>
                                    <td><?php echo $row['purok_zone']; ?></td>
                                    <td><?php echo $row['total_requests']; ?></td>
                                    <td><?php echo $row['pending_requests']; ?></td>
                                    <td><?php echo $row['approved_requests']; ?></td>
                                    <td><?php echo $row['released_requests']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>