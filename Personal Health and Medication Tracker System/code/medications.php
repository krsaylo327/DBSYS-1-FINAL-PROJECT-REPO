<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM medications WHERE user_id = $user_id ORDER BY is_active DESC, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications - Health Tracker</title>
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
        <h1>Medications</h1>
        <div class="toolbar">
            <a href="add_medication.php" class="btn btn-primary">+ Add Medication</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Prescribed By</th>
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
                        <td><?php echo date('M d, Y', strtotime($row['start_date'])); ?></td>
                        <td><?php echo $row['end_date'] ? date('M d, Y', strtotime($row['end_date'])) : '—'; ?></td>
                        <td><?php echo htmlspecialchars($row['prescribed_by'] ?? '—'); ?></td>
                        <td><span class="badge <?php echo $row['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span></td>
                        <td>
                            <a href="edit_medication.php?id=<?php echo $row['medication_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="delete_medication.php?id=<?php echo $row['medication_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this medication?')">Delete</a>
                            <a href="mark_taken.php?med_id=<?php echo $row['medication_id']; ?>" class="btn btn-sm btn-primary">Mark Taken</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No medications added yet. <a href="add_medication.php">Add your first medication</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
