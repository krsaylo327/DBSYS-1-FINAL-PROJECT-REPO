<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $goal_type = $_POST['goal_type'];
    $target_value = trim($_POST['target_value']);
    $current_value = !empty($_POST['current_value']) ? trim($_POST['current_value']) : null;
    $start_date = $_POST['start_date'];
    $target_date = !empty($_POST['target_date']) ? $_POST['target_date'] : null;
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);
    
    $stmt = $conn->prepare("INSERT INTO health_goals (user_id, goal_type, target_value, current_value, start_date, target_date, status, notes) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $goal_type, $target_value, $current_value, $start_date, $target_date, $status, $notes);
    
    if ($stmt->execute()) {
        header('Location: health_goals.php?success=1');
        exit();
    } else {
        $error = 'Failed to add goal: ' . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Goal - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validate.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🏥 Health Tracker</div>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="vitals.php">Vitals</a></li>
            <li><a href="medications.php">Medications</a></li>
            <li><a href="medication_schedule.php">Schedule</a></li>
            <li><a href="health_goals.php" class="active">Goals</a></li>
            <li><a href="weekly_report.php">Weekly Report</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Set Health Goal</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" onsubmit="return validateGoal()">
            <div class="form-grid">
                <div class="form-group">
                    <label for="goal_type">Goal Type *</label>
                    <select id="goal_type" name="goal_type" required>
                        <option value="Weight">Weight</option>
                        <option value="Blood Pressure">Blood Pressure</option>
                        <option value="Blood Sugar">Blood Sugar</option>
                        <option value="Exercise">Exercise</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="target_value">Target Value *</label>
                    <input type="text" id="target_value" name="target_value" placeholder="e.g., 65kg, 120/80" required>
                </div>
                <div class="form-group">
                    <label for="current_value">Current Value</label>
                    <input type="text" id="current_value" name="current_value" placeholder="e.g., 70kg, 130/85">
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="target_date">Target Date</label>
                    <input type="date" id="target_date" name="target_date">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Not Started">Not Started</option>
                        <option value="In Progress" selected>In Progress</option>
                        <option value="Achieved">Achieved</option>
                        <option value="Abandoned">Abandoned</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Goal</button>
                <a href="health_goals.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
