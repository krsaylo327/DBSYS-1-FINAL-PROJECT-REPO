<?php
require_once 'db_connect.php';

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($request_id <= 0) {
    header('Location: requests.php');
    exit;
}

$request = $conn->query("SELECT * FROM certificate_request WHERE request_id = $request_id");
if ($request->num_rows == 0) {
    header('Location: requests.php');
    exit;
}
$request_data = $request->fetch_assoc();

$residents = $conn->query("SELECT resident_id, first_name, last_name FROM barangay_residents ORDER BY last_name");
$certificates = $conn->query("SELECT certificate_id, certificate_name FROM certificates");
$staff = $conn->query("SELECT staff_id, first_name, last_name FROM barangay_staff");

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resident_id = intval($_POST['resident_id']);
    $certificate_id = intval($_POST['certificate_id']);
    $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : null;
    $purpose = sanitize($_POST['purpose']);
    $status = sanitize($_POST['status']);
    
    $errors = [];
    if ($resident_id <= 0) $errors[] = "Resident is required.";
    if ($certificate_id <= 0) $errors[] = "Certificate type is required.";
    if (empty($purpose)) $errors[] = "Purpose is required.";
    
    if (empty($errors)) {
        $resolved_at = ($status != 'Pending') ? 'NOW()' : 'NULL';
        $stmt = $conn->prepare("UPDATE certificate_request SET 
                               resident_id = ?, certificate_id = ?, staff_id = ?, 
                               purpose = ?, status = ?, resolved_at = " . ($status != 'Pending' ? "NOW()" : "NULL") . "
                               WHERE request_id = ?");
        $stmt->bind_param("iiissi", $resident_id, $certificate_id, $staff_id, $purpose, $status, $request_id);
        
        if ($stmt->execute()) {
            $message = showMessage('Request updated successfully!', 'success');
            $request = $conn->query("SELECT * FROM certificate_request WHERE request_id = $request_id");
            $request_data = $request->fetch_assoc();
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
    <title>Edit Request - Barangay System</title>
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
                        <h4><i class="bi bi-pencil-square"></i> Edit Certificate Request</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" action="request_edit.php?id=<?php echo $request_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Resident <span class="text-danger">*</span></label>
                                <select name="resident_id" class="form-select" required>
                                    <?php 
                                    $residents = $conn->query("SELECT resident_id, first_name, last_name FROM barangay_residents ORDER BY last_name");
                                    while($row = $residents->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $row['resident_id']; ?>" 
                                        <?php echo ($row['resident_id'] == $request_data['resident_id']) ? 'selected' : ''; ?>>
                                        <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Certificate Type <span class="text-danger">*</span></label>
                                <select name="certificate_id" class="form-select" required>
                                    <?php 
                                    $certificates = $conn->query("SELECT certificate_id, certificate_name FROM certificates");
                                    while($row = $certificates->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $row['certificate_id']; ?>" 
                                        <?php echo ($row['certificate_id'] == $request_data['certificate_id']) ? 'selected' : ''; ?>>
                                        <?php echo $row['certificate_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Staff (Processor)</label>
                                <select name="staff_id" class="form-select">
                                    <option value="">Not assigned</option>
                                    <?php 
                                    $staff = $conn->query("SELECT staff_id, first_name, last_name FROM barangay_staff");
                                    while($row = $staff->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $row['staff_id']; ?>" 
                                        <?php echo ($row['staff_id'] == $request_data['staff_id']) ? 'selected' : ''; ?>>
                                        <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="3" required><?php echo $request_data['purpose']; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Pending" <?php echo ($request_data['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo ($request_data['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo ($request_data['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="Released" <?php echo ($request_data['status'] == 'Released') ? 'selected' : ''; ?>>Released</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Request
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