<?php
require_once 'db_connect.php';

// Fetch residents and certificates for dropdowns
$residents = $conn->query("SELECT resident_id, first_name, last_name FROM barangay_residents ORDER BY last_name");
$certificates = $conn->query("SELECT certificate_id, certificate_name, base_fee FROM certificates");

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resident_id = intval($_POST['resident_id']);
    $certificate_id = intval($_POST['certificate_id']);
    $purpose = sanitize($_POST['purpose']);
    
    $errors = [];
    if ($resident_id <= 0) $errors[] = "Resident is required.";
    if ($certificate_id <= 0) $errors[] = "Certificate type is required.";
    if (empty($purpose)) $errors[] = "Purpose is required.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO certificate_request (resident_id, certificate_id, purpose) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $resident_id, $certificate_id, $purpose);
        
        if ($stmt->execute()) {
            $message = showMessage('Certificate request submitted successfully!', 'success');
            $_POST = [];
        } else {
            $message = showMessage('Error: ' . $stmt->error, 'danger');
        }
        $stmt->close();
    } else {
        $message = showMessage(implode('<br>', $errors), 'danger');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Certificate Request - Barangay System</title>
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
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-file-earmark-plus"></i> New Certificate Request</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" action="request_add.php" onsubmit="return validateRequestForm()">
                            <div class="mb-3">
                                <label class="form-label">Resident <span class="text-danger">*</span></label>
                                <select name="resident_id" class="form-select" required>
                                    <option value="">Select Resident</option>
                                    <?php 
                                    $residents = $conn->query("SELECT resident_id, first_name, last_name FROM barangay_residents ORDER BY last_name");
                                    while($row = $residents->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $row['resident_id']; ?>">
                                        <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Certificate Type <span class="text-danger">*</span></label>
                                <select name="certificate_id" class="form-select" required>
                                    <option value="">Select Certificate</option>
                                    <?php 
                                    $certificates = $conn->query("SELECT certificate_id, certificate_name, base_fee FROM certificates");
                                    while($row = $certificates->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $row['certificate_id']; ?>">
                                        <?php echo $row['certificate_name']; ?> (₱<?php echo number_format($row['base_fee'], 2); ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send"></i> Submit Request
                            </button>
                            <a href="requests.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>