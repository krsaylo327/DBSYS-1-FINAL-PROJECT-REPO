<?php
require_once 'db_connect.php';

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$is_edit = ($edit_id > 0);
$data = ['first_name' => '', 'last_name' => '', 'position' => '', 'username' => '', 'contact_number' => '', 'is_active' => 1];

if ($is_edit) {
    $result = $conn->query("SELECT * FROM barangay_staff WHERE staff_id = $edit_id");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        $is_edit = false;
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $position = sanitize($_POST['position']);
    $username = sanitize($_POST['username']);
    $password = isset($_POST['password']) && !empty($_POST['password']) ? hash('sha256', $_POST['password']) : '';
    $contact_number = sanitize($_POST['contact_number']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($position)) $errors[] = "Position is required.";
    if (empty($username)) $errors[] = "Username is required.";
    
    // Check if username exists
    $check = $conn->query("SELECT staff_id FROM barangay_staff WHERE username = '$username'");
    if (!$is_edit && $check->num_rows > 0) {
        $errors[] = "Username already exists.";
    } elseif ($is_edit) {
        $check = $conn->query("SELECT staff_id FROM barangay_staff WHERE username = '$username' AND staff_id != $edit_id");
        if ($check->num_rows > 0) {
            $errors[] = "Username already exists.";
        }
    }
    
    if (empty($errors)) {
        if ($is_edit) {
            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE barangay_staff SET first_name = ?, last_name = ?, position = ?, username = ?, password_hash = ?, contact_number = ?, is_active = ? WHERE staff_id = ?");
                $stmt->bind_param("ssssssi", $first_name, $last_name, $position, $username, $password, $contact_number, $is_active, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE barangay_staff SET first_name = ?, last_name = ?, position = ?, username = ?, contact_number = ?, is_active = ? WHERE staff_id = ?");
                $stmt->bind_param("sssssii", $first_name, $last_name, $position, $username, $contact_number, $is_active, $edit_id);
            }
        } else {
            if (empty($password)) {
                $errors[] = "Password is required for new staff.";
            } else {
                $stmt = $conn->prepare("INSERT INTO barangay_staff (first_name, last_name, position, username, password_hash, contact_number, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $first_name, $last_name, $position, $username, $password, $contact_number, $is_active);
            }
        }
        
        if (empty($errors) && $stmt->execute()) {
            $message = showMessage(($is_edit ? 'Staff updated' : 'Staff added') . ' successfully!', 'success');
            if (!$is_edit) {
                $_POST = [];
                $data = ['first_name' => '', 'last_name' => '', 'position' => '', 'username' => '', 'contact_number' => '', 'is_active' => 1];
            } else {
                $result = $conn->query("SELECT * FROM barangay_staff WHERE staff_id = $edit_id");
                $data = $result->fetch_assoc();
            }
        } else {
            $message = showMessage('Error: ' . $stmt->error, 'danger');
        }
        if (isset($stmt)) $stmt->close();
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
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Staff - Barangay System</title>
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
                        <a class="nav-link" href="households.php"><i class="bi bi-building"></i> Households</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="staff.php"><i class="bi bi-person-badge"></i> Staff</a>
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
                        <h4><i class="bi bi-person-plus"></i> <?php echo $is_edit ? 'Edit' : 'Add'; ?> Staff</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" action="staff_add.php<?php echo $is_edit ? '?edit=' . $edit_id : ''; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" 
                                           value="<?php echo $data['first_name']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control" 
                                           value="<?php echo $data['last_name']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" name="position" class="form-control" 
                                       value="<?php echo $data['position']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo $data['username']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password <?php echo $is_edit ? '(leave blank to keep current)' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" name="password" class="form-control" <?php echo !$is_edit ? 'required' : ''; ?>>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" 
                                       value="<?php echo $data['contact_number']; ?>">
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" 
                                       <?php echo ($data['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> <?php echo $is_edit ? 'Update' : 'Save'; ?>
                            </button>
                            <a href="staff.php" class="btn btn-secondary">Cancel</a>
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