<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM vitals_log WHERE user_id = $user_id";
if ($search) {
    $search_escaped = $conn->real_escape_string($search);
    $sql .= " AND (notes LIKE '%$search_escaped%' OR blood_pressure_systolic LIKE '%$search_escaped%')";
}
$sql .= " ORDER BY logged_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitals Log - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
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
        <h1>Vitals Log</h1>
        
        <div class="toolbar">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search vitals..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">Search</button>
                <?php if ($search): ?>
                    <a href="vitals.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
            <a href="add_vital.php" class="btn btn-primary">+ Add Vitals</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>BP (Systolic/Diastolic)</th>
                    <th>Heart Rate</th>
                    <th>Weight (kg)</th>
                    <th>Blood Sugar (mg/dL)</th>
                    <th>Temperature (°C)</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y h:i A', strtotime($row['logged_at'])); ?></td>
                        <td><?php echo $row['blood_pressure_systolic']; ?>/<?php echo $row['blood_pressure_diastolic']; ?></td>
                        <td><?php echo $row['heart_rate']; ?></td>
                        <td><?php echo $row['weight']; ?></td>
                        <td><?php echo $row['blood_sugar']; ?></td>
                        <td><?php echo $row['temperature']; ?></td>
                        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                        <td>
                            <a href="edit_vital.php?id=<?php echo $row['log_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="delete_vital.php?id=<?php echo $row['log_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this entry?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No vitals logged yet. <a href="add_vital.php">Add your first entry</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
