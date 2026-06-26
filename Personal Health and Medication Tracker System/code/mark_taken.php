<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Using aggregate function with GROUP BY
$sql = "SELECT goal_type, COUNT(*) AS total, 
        SUM(CASE WHEN status = 'Achieved' THEN 1 ELSE 0 END) AS achieved,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress
        FROM health_goals 
        WHERE user_id = $user_id 
        GROUP BY goal_type";
$stats_result = $conn->query($sql);

$result = $conn->query("SELECT * FROM health_goals WHERE user_id = $user_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Goals - Health Tracker</title>
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
        <h1>Health Goals</h1>
        <div class="toolbar">
            <a href="add_goal.php" class="btn btn-primary">+ Add Goal</a>
        </div>

        <?php if ($stats_result && $stats_result->num_rows > 0): ?>
        <div class="section">
            <h3>Goal Summary (Grouped by Type)</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Goal Type</th>
                        <th>Total</th>
                        <th>Achieved</th>
                        <th>In Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stat = $stats_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stat['goal_type']); ?></td>
                        <td><?php echo $stat['total']; ?></td>
                        <td><?php echo $stat['achieved']; ?></td>
                        <td><?php echo $stat['in_progress']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Current</th>
                    <th>Start Date</th>
                    <th>Target Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['goal_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['target_value']); ?></td>
                        <td><?php echo htmlspecialchars($row['current_value'] ?? '—'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['start_date'])); ?></td>
                        <td><?php echo $row['target_date'] ? date('M d, Y', strtotime($row['target_date'])) : '—'; ?></td>
                        <td>
                            <span class="badge <?php 
                                echo match($row['status']) {
                                    'Achieved' => 'badge-success',
                                    'In Progress' => 'badge-primary',
                                    'Not Started' => 'badge-warning',
                                    default => 'badge-danger'
                                };
                            ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                        <td>
                            <a href="edit_goal.php?id=<?php echo $row['goal_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="delete_goal.php?id=<?php echo $row['goal_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this goal?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No health goals set yet. <a href="add_goal.php">Set your first goal</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
