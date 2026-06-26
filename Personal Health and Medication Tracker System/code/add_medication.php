<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $dosage = trim($_POST['dosage']);
    $frequency = trim($_POST['frequency']);
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $prescribed_by = trim($_POST['prescribed_by']);
    $instructions = trim($_POST['instructions']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $schedule_times = array_filter($_POST['schedule_times'] ?? []);
    
    $stmt = $conn->prepare("INSERT INTO medications (user_id, name, dosage, frequency, start_date, end_date, prescribed_by, instructions, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssi", $user_id, $name, $dosage, $frequency, $start_date, $end_date, $prescribed_by, $instructions, $is_active);
    
    if ($stmt->execute()) {
        $med_id = $stmt->insert_id;
        
        // Add schedule times
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
        
        header('Location: medications.php?success=1');
        exit();
    } else {
        $error = 'Failed to add medication: ' . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medication - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validate.js"></script>
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
        <h1>Add Medication</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" onsubmit="return validateMedication()">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Medication Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="dosage">Dosage *</label>
                    <input type="text" id="dosage" name="dosage" placeholder="e.g., 5mg, 500mg" required>
                </div>
                <div class="form-group">
                    <label for="frequency">Frequency *</label>
                    <input type="text" id="frequency" name="frequency" placeholder="e.g., Once daily, Twice daily" required>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date *</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date">
                </div>
                <div class="form-group">
                    <label for="prescribed_by">Prescribed By</label>
                    <input type="text" id="prescribed_by" name="prescribed_by" placeholder="Doctor's name">
                </div>
                <div class="form-group full-width">
                    <label for="instructions">Instructions</label>
                    <textarea id="instructions" name="instructions" rows="2"></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Schedule Times</label>
                    <div id="schedule-times">
                        <div class="time-input">
                            <input type="time" name="schedule_times[]" class="schedule-time">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="addScheduleTime()">+</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" checked> Active
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Medication</button>
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
