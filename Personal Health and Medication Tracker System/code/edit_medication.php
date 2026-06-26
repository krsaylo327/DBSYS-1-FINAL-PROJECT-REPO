<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$med_id = intval($_GET['id'] ?? 0);

if ($med_id <= 0) {
    header('Location: medications.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM medications WHERE medication_id = ? AND user_id = ?");
$stmt->bind_param("ii", $med_id, $user_id);
$stmt->execute();
$med = $stmt->get_result()->fetch_assoc();

if (!$med) {
    header('Location: medications.php');
    exit();
}

// Get schedule
$schedule_result = $conn->query("SELECT * FROM medication_schedule WHERE medication_id = $med_id");
$schedule = [];
while ($row = $schedule_result->fetch_assoc()) {
    $schedule[] = $row['scheduled_time'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $dosage = trim($_POST['dosage']);
    $frequency = trim($_POST['frequency']);
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $prescribed_by = trim($_POST['prescribed_by']);
    $instructions = trim($_POST['instructions']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $schedule_times = array_filter($_POST['schedule_times'] ?? []);
    
    $update = $conn->prepare("UPDATE medications SET 
        name = ?, dosage = ?, frequency = ?, start_date = ?, end_date = ?, 
        prescribed_by = ?, instructions = ?, is_active = ?
        WHERE medication_id = ? AND user_id = ?");
    $update->bind_param("sssssssiii", $name, $dosage, $frequency, $start_date, $end_date, $prescribed_by, $instructions, $is_active, $med_id, $user_id);
    
    if ($update->execute()) {
        // Update schedule
        $conn->query("DELETE FROM medication_schedule WHERE medication_id = $med_id");
        if (!empty($schedule_times)) {
            $schedule_stmt = $conn->prepare("INSERT INTO medication_schedule (medication_id, scheduled_time, days_of_week) VALUES (?, ?, 'Daily')");
            foreach ($schedule_times as $time) {
                if (!empty($time)) {
                    $schedule_stmt->bind_param("is", $med_id, $time);
                    $schedule_stmt->execute();
                }
            }
            $schedule_stmt->close();
        }
        
        header('Location: medications.php?updated=1');
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
    <title>Edit Medication - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🏥 Health Tracker</div>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="vitals.php">Vitals</a></li>
            <li><a href="medications.php" class="active">Medications</a></li>
            <li><a href="medication_schedule.php">Schedule</a></li>
            <li><a href="health_goals.php">Goals</a></li>
            <li><a href="weekly_report.php">Weekly Report</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Edit Medication</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Medication Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($med['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dosage">Dosage *</label>
                    <input type="text" id="dosage" name="dosage" value="<?php echo htmlspecialchars($med['dosage']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="frequency">Frequency *</label>
                    <input type="text" id="frequency" name="frequency" value="<?php echo htmlspecialchars($med['frequency']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $med['start_date']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $med['end_date']; ?>">
                </div>
                <div class="form-group">
                    <label for="prescribed_by">Prescribed By</label>
                    <input type="text" id="prescribed_by" name="prescribed_by" value="<?php echo htmlspecialchars($med['prescribed_by'] ?? ''); ?>">
                </div>
                <div class="form-group full-width">
                    <label for="instructions">Instructions</label>
                    <textarea id="instructions" name="instructions" rows="2"><?php echo htmlspecialchars($med['instructions'] ?? ''); ?></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Schedule Times</label>
                    <div id="schedule-times">
                        <?php if (empty($schedule)): ?>
                            <div class="time-input">
                                <input type="time" name="schedule_times[]" class="schedule-time">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="addScheduleTime()">+</button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($schedule as $time): ?>
                                <div class="time-input">
                                    <input type="time" name="schedule_times[]" class="schedule-time" value="<?php echo $time; ?>">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">×</button>
                                </div>
                            <?php endforeach; ?>
                            <div class="time-input">
                                <input type="time" name="schedule_times[]" class="schedule-time">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="addScheduleTime()">+</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" <?php echo $med['is_active'] ? 'checked' : ''; ?>> Active
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Medication</button>
                <a href="medications.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function addScheduleTime() {
            const container = document.getElementById('schedule-times');
            const div = document.createElement('div');
            div.className = 'time-input';
            div.innerHTML = `
                <input type="time" name="schedule_times[]" class="schedule-time">
                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
