<?php
require_once 'db_connect.php';

// Search functionality
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = sanitize($_GET['search']);
}

// Build query with JOIN to households
$sql = "SELECT r.*, h.household_number, h.purok_zone, h.street_address,
        (SELECT COUNT(*) FROM certificate_request cr WHERE cr.resident_id = r.resident_id) AS request_count
        FROM barangay_residents r
        LEFT JOIN households h ON r.household_id = h.household_id";

if (!empty($search_query)) {
    $sql .= " WHERE r.first_name LIKE '%$search_query%' 
              OR r.last_name LIKE '%$search_query%' 
              OR r.contact_number LIKE '%$search_query%'";
}

$sql .= " ORDER BY r.last_name ASC";

$residents = $conn->query($sql);

// Subquery: Residents without any requests
$sql_no_requests = "SELECT CONCAT(first_name, ' ', last_name) AS full_name 
                     FROM barangay_residents 
                     WHERE resident_id NOT IN (SELECT DISTINCT resident_id FROM certificate_request)";
$no_requests_residents = $conn->query($sql_no_requests);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents - Barangay System</title>
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
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
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

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-people"></i> Residents</h1>
            <a href="residents_add.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Resident
            </a>
        </div>

        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="residents.php" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name or contact number..." 
                               value="<?php echo $search_query; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Residents with No Requests (Subquery Example) -->
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning">
                <h5><i class="bi bi-exclamation-triangle"></i> Residents WITHOUT Certificate Requests (Subquery Example)</h5>
            </div>
            <div class="card-body">
                <?php 
                $no_requests_list = [];
                while($row = $no_requests_residents->fetch_assoc()) {
                    $no_requests_list[] = $row['full_name'];
                }
                if (count($no_requests_list) > 0) {
                    echo '<span class="badge bg-danger me-2">' . count($no_requests_list) . '</span> ';
                    echo implode(', ', $no_requests_list);
                } else {
                    echo '<span class="text-success">All residents have submitted at least one request!</span>';
                }
                ?>
            </div>
        </div>

        <!-- Residents Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Household</th>
                            <th>Purok</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Requests</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($residents->num_rows > 0): ?>
                            <?php while($row = $residents->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['resident_id']; ?></td>
                                <td>
                                    <strong><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></strong>
                                    <br><small class="text-muted">Born: <?php echo date('M d, Y', strtotime($row['birth_date'])); ?></small>
                                </td>
                                <td><?php echo $row['household_number']; ?></td>
                                <td><?php echo $row['purok_zone']; ?></td>
                                <td><?php echo $row['gender']; ?></td>
                                <td><?php echo $row['contact_number']; ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $row['request_count']; ?></span>
                                </td>
                                <td>
                                    <a href="residents_edit.php?id=<?php echo $row['resident_id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="residents_delete.php?id=<?php echo $row['resident_id']; ?>" 
                                       class="btn btn-sm btn-danger delete-btn"
                                       onclick="return confirm('Delete this resident?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No residents found.</td></tr>
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