<?php
require_once 'db_connect.php';

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$is_edit = ($edit_id > 0);
$data = ['household_number' => '', 'purok_zone' => '', 'street_address' => '', 'source' => 'Barangay Census'];

if ($is_edit) {
    $result = $conn->query("SELECT * FROM households WHERE household_id = $edit_id");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        $is_edit = false;
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $household_number = sanitize($_POST['household_number']);
    $purok_zone = sanitize($_POST['purok_zone']);
    $street_address = sanitize($_POST['street_address']);
    $source = sanitize($_POST['source']);
    
    $errors = [];
    if (empty($household_number)) $errors[] = "Household number is required.";
    if (empty($purok_zone)) $errors[] = "Purok zone is required.";
    if (empty($street_address)) $errors[] = "Street address is required.";
    
    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $conn->prepare("UPDATE households SET household_number = ?, purok_zone = ?, street_address = ?, source = ? WHERE household_id = ?");
            $stmt->bind_param("ssssi", $household_number, $purok_zone, $street_address, $source, $edit_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO households (household_number, purok_zone, street_address, source) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $household_number, $purok_zone, $street_address, $source);
        }
        
        if ($stmt->execute()) {
            $message = showMessage(($is_edit ? 'Household updated' : 'Household added') . ' successfully!', 'success');
            if (!$is_edit) {
                $_POST = [];
                $data = ['household_number' => '', 'purok_zone' => '', 'street_address' => '', 'source' => 'Barangay Census'];
            } else {
                $result = $conn->query("SELECT * FROM households WHERE household_id = $edit_id");
                $data = $result->fetch_assoc();
            }
        } else {
            $message = showMessage('Error: ' . $stmt->error, 'danger');
        }
        $stmt->close();
    } else {
        $message = showMessage(implode('<br>', $errors), 'danger');
        $data = ['household_number' => $household_number, 'purok_zone' => $purok_zone, 'street_address' => $street_address, 'source' => $source];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Household - Barangay System</title>
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
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-building"></i> <?php echo $is_edit ? 'Edit' : 'Add'; ?> Household</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" action="households_add.php<?php echo $is_edit ? '?edit=' . $edit_id : ''; ?>">
                            <div class="mb-3">
                                <label class="form-label">Household Number <span class="text-danger">*</span></label>
                                <input type="text" name="household_number" class="form-control" 
                                       value="<?php echo $data['household_number']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Purok Zone <span class="text-danger">*</span></label>
                                <input type="text" name="purok_zone" class="form-control" 
                                       value="<?php echo $data['purok_zone']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Street Address <span class="text-danger">*</span></label>
                                <input type="text" name="street_address" class="form-control" 
                                       value="<?php echo $data['street_address']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <input type="text" name="source" class="form-control" 
                                       value="<?php echo $data['source']; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> <?php echo $is_edit ? 'Update' : 'Save'; ?>
                            </button>
                            <a href="households.php" class="btn btn-secondary">Cancel</a>
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