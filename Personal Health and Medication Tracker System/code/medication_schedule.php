<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query using INNER JOIN to show medication details with schedule
$sql = "SELECT ms.*, m.name, m.dosage, m.frequency 
        FROM medication_schedule ms
        INNER JOIN medications m ON ms.medication_id = m.medication_id
        WHERE m.user_id = $user_id AND m.is_active = TRUE
        ORDER BY ms.scheduled_time";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication Schedule - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🏥 Health Tracker</div>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="vitals.php">Vitals</a></li>
            <li><a href="medications.php">Medications</a></li>
            <li><a href="medication_schedule.php" class="active">Schedule</a></li>
            <li><a href="health_goals.php">Goals</a></li>
            <li><a href="weekly_report.php">Weekly Report</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Medication Schedule</h1>
        <div class="toolbar">
            <a href="add_medication.php" class="btn btn-primary">+ Add Medication</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Medication</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Time</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['dosage']); ?></td>
                        <td><?php echo htmlspecialchars($row['frequency']); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['scheduled_time'])); ?></td>
                        <td><?php echo htmlspecialchars($row['days_of_week']); ?></td>
                        <td>
                            <span class="badge <?php echo $row['is_taken'] ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo $row['is_taken'] ? 'Taken' : 'Pending'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="mark_taken.php?schedule_id=<?php echo $row['schedule_id']; ?>&med_id=<?php echo $row['medication_id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <?php echo $row['is_taken'] ? 'Reset' : 'Mark Taken'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No medications scheduled. <a href="add_medication.php">Add your first medication</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
