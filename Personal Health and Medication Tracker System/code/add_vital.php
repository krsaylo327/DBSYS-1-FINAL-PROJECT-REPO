<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $systolic = intval($_POST['systolic']);
    $diastolic = intval($_POST['diastolic']);
    $heart_rate = intval($_POST['heart_rate']);
    $weight = floatval($_POST['weight']);
    $blood_sugar = floatval($_POST['blood_sugar']);
    $temperature = floatval($_POST['temperature']);
    $notes = trim($_POST['notes']);
    
    // Use stored procedure
    $stmt = $conn->prepare("CALL LogVital(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiidddds", $user_id, $systolic, $diastolic, $heart_rate, $weight, $blood_sugar, $temperature, $notes);
    
    if ($stmt->execute()) {
        header('Location: vitals.php?success=1');
        exit();
    } else {
        $error = 'Failed to log vitals: ' . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vitals - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validate.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🏥 Health Tracker</div>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="vitals.php" class="active">Vitals</a></li>
            <li><a href="medications.php">Medications</a></li>
            <li><a href="medication_schedule.php">Schedule</a></li>
            <li><a href="health_goals.php">Goals</a></li>
            <li><a href="weekly_report.php">Weekly Report</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Log Vitals</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" onsubmit="return validateVitals()">
            <div class="form-grid">
                <div class="form-group">
                    <label for="systolic">Systolic BP (mmHg) *</label>
                    <input type="number" id="systolic" name="systolic" min="70" max="250" required>
                </div>
                <div class="form-group">
                    <label for="diastolic">Diastolic BP (mmHg) *</label>
                    <input type="number" id="diastolic" name="diastolic" min="40" max="160" required>
                </div>
                <div class="form-group">
                    <label for="heart_rate">Heart Rate (bpm)</label>
                    <input type="number" id="heart_rate" name="heart_rate" min="30" max="220">
                </div>
                <div class="form-group">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.1" min="20" max="300">
                </div>
                <div class="form-group">
                    <label for="blood_sugar">Blood Sugar (mg/dL)</label>
                    <input type="number" id="blood_sugar" name="blood_sugar" min="40" max="500">
                </div>
                <div class="form-group">
                    <label for="temperature">Temperature (°C)</label>
                    <input type="number" id="temperature" name="temperature" step="0.1" min="35" max="42">
                </div>
                <div class="form-group full-width">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Vitals</button>
                <a href="vitals.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
