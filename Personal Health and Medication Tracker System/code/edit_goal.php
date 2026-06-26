<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$goal_id = intval($_GET['id'] ?? 0);

if ($goal_id <= 0) {
    header('Location: health_goals.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM health_goals WHERE goal_id = ? AND user_id = ?");
$stmt->bind_param("ii", $goal_id, $user_id);
$stmt->execute();
$goal = $stmt->get_result()->fetch_assoc();

if (!$goal) {
    header('Location: health_goals.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal_type = $_POST['goal_type'];
    $target_value = trim($_POST['target_value']);
    $current_value = !empty($_POST['current_value']) ? trim($_POST['current_value']) : null;
    $start_date = $_POST['start_date'];
    $target_date = !empty($_POST['target_date']) ? $_POST['target_date'] : null;
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);
    
    $update = $conn->prepare("UPDATE health_goals SET 
        goal_type = ?, target_value = ?, current_value = ?, start_date = ?, 
        target_date = ?, status = ?, notes = ?
        WHERE goal_id = ? AND user_id = ?");
    $update->bind_param("sssssssii", $goal_type, $target_value, $current_value, $start_date, $target_date, $status, $notes, $goal_id, $user_id);
    
    if ($update->execute()) {
        header('Location: health_goals.php?updated=1');
        exit();
    } else {
        $error = 'Update failed: ' . $update->error;
    }
    $update->close();
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Goal - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
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
        <h1>Edit Health Goal</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="goal_type">Goal Type *</label>
                    <select id="goal_type" name="goal_type" required>
                        <option value="Weight" <?php echo $goal['goal_type'] == 'Weight' ? 'selected' : ''; ?>>Weight</option>
                        <option value="Blood Pressure" <?php echo $goal['goal_type'] == 'Blood Pressure' ? 'selected' : ''; ?>>Blood Pressure</option>
                        <option value="Blood Sugar" <?php echo $goal['goal_type'] == 'Blood Sugar' ? 'selected' : ''; ?>>Blood Sugar</option>
                        <option value="Exercise" <?php echo $goal['goal_type'] == 'Exercise' ? 'selected' : ''; ?>>Exercise</option>
                        <option value="Other" <?php echo $goal['goal_type'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="target_value">Target Value *</label>
                    <input type="text" id="target_value" name="target_value" value="<?php echo htmlspecialchars($goal['target_value']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="current_value">Current Value</label>
                    <input type="text" id="current_value" name="current_value" value="<?php echo htmlspecialchars($goal['current_value'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $goal['start_date']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="target_date">Target Date</label>
                    <input type="date" id="target_date" name="target_date" value="<?php echo $goal['target_date']; ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Not Started" <?php echo $goal['status'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                        <option value="In Progress" <?php echo $goal['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Achieved" <?php echo $goal['status'] == 'Achieved' ? 'selected' : ''; ?>>Achieved</option>
                        <option value="Abandoned" <?php echo $goal['status'] == 'Abandoned' ? 'selected' : ''; ?>>Abandoned</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2"><?php echo htmlspecialchars($goal['notes'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Goal</button>
                <a href="health_goals.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
