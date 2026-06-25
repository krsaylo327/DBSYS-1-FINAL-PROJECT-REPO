<?php
require_once 'db_connect.php';

// Households with resident count (HAVING example)
$households = $conn->query("
    SELECT h.*, COUNT(r.resident_id) AS resident_count
    FROM households h
    LEFT JOIN barangay_residents r ON h.household_id = r.household_id
    GROUP BY h.household_id
    ORDER BY h.household_number
");

// Large households (HAVING clause)
$large_households = $conn->query("
    SELECT h.household_number, h.purok_zone, COUNT(r.resident_id) AS resident_count
    FROM households h
    LEFT JOIN barangay_residents r ON h.household_id = r.household_id
    GROUP BY h.household_id
    HAVING COUNT(r.resident_id) >= 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Households - Barangay System</title>
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
                        <a class="nav-link active" href="households.php"><i class="bi bi-building"></i> Households</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="staff.php"><i class="bi bi-person-badge"></i> Staff</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1><i class="bi bi-building"></i> Households</h1>

        <!-- Large Households (HAVING example) -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-people-fill"></i> Large Households (HAVING clause example)</h5>
                <small>Households with 3 or more residents</small>
            </div>
            <div class="card-body">
                <?php if ($large_households->num_rows > 0): ?>
                    <div class="row">
                        <?php while($row = $large_households->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <strong><?php echo $row['household_number']; ?></strong><br>
                                <?php echo $row['purok_zone']; ?><br>
                                <span class="badge bg-primary"><?php echo $row['resident_count']; ?> residents</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No households with 3 or more residents.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- All Households -->
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Household #</th>
                            <th>Purok Zone</th>
                            <th>Street Address</th>
                            <th>Source</th>
                            <th>Residents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $households->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['household_number']; ?></strong></td>
                            <td><?php echo $row['purok_zone']; ?></td>
                            <td><?php echo $row['street_address']; ?></td>
                            <td><?php echo $row['source']; ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $row['resident_count']; ?></span>
                            </td>
                            <td>
                                <a href="households_add.php?edit=<?php echo $row['household_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>