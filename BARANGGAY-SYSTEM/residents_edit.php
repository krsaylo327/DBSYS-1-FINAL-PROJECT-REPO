<?php
require_once 'db_connect.php';

$resident_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($resident_id <= 0) {
    header('Location: residents.php');
    exit;
}

$resident = $conn->query("SELECT * FROM barangay_residents WHERE resident_id = $resident_id");
if ($resident->num_rows == 0) {
    header('Location: residents.php');
    exit;
}
$resident_data = $resident->fetch_assoc();

$households = $conn->query("SELECT household_id, household_number, street_address FROM households ORDER BY household_number");

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $household_id = intval($_POST['household_id']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $birth_date = sanitize($_POST['birth_date']);
    $gender = sanitize($_POST['gender']);
    $contact_number = sanitize($_POST['contact_number']);
    
    $errors = [];
    if ($household_id <= 0) $errors[] = "Household is required.";
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($birth_date)) $errors[] = "Birth date is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE barangay_residents SET 
                               household_id = ?, first_name = ?, last_name = ?, 
                               birth_date = ?, gender = ?, contact_number = ? 
                               WHERE resident_id = ?");
        $stmt->bind_param("isssssi", $household_id, $first_name, $last_name, $birth_date, $gender, $contact_number, $resident_id);
        
        if ($stmt->execute()) {
            $message = showMessage('Resident updated successfully!', 'success');
            $resident = $conn->query("SELECT * FROM barangay_residents WHERE resident_id = $resident_id");
            $resident_data = $resident->fetch_assoc();
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
    <title>Edit Resident - Barangay System</title>
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
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-pencil-square"></i> Edit Resident</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" action="residents_edit.php?id=<?php echo $resident_id; ?>" onsubmit="return validateResidentForm()">
                            <div class="mb-3">
                                <label class="form-label">Household <span class="text-danger">*</span></label>
                                <select name="household_id" class="form-select" required>
                                    <?php 
                                    $households = $conn->query("SELECT household_id, household_number, street_address FROM households ORDER BY household_number");
                                    while($row = $households->fetch_assoc()): 
                                    ?>
                                    <option value="<?php echo $row['household_id']; ?>" 
                                        <?php echo ($row['household_id'] == $resident_data['household_id']) ? 'selected' : ''; ?>>
                                        <?php echo $row['household_number'] . ' - ' . $row['street_address']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" 
                                           value="<?php echo $resident_data['first_name']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control" 
                                           value="<?php echo $resident_data['last_name']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Birth Date <span class="text-danger">*</span></label>
                                    <input type="date" name="birth_date" class="form-control" 
                                           value="<?php echo $resident_data['birth_date']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" <?php echo ($resident_data['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($resident_data['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($resident_data['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" 
                                       value="<?php echo $resident_data['contact_number']; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Resident
                            </button>
                            <a href="residents.php" class="btn btn-secondary">Cancel</a>
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