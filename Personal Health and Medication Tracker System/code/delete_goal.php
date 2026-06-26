<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get weekly vitals summary using the view and aggregate functions
$weekly_summary = $conn->query("
    SELECT 
        DATE(logged_at) AS log_date,
        COUNT(*) AS entries,
        AVG(blood_pressure_systolic) AS avg_sys,
        AVG(blood_pressure_diastolic) AS avg_dia,
        AVG(heart_rate) AS avg_hr,
        AVG(weight) AS avg_weight,
        AVG(blood_sugar) AS avg_sugar,
        MIN(blood_pressure_systolic) AS min_sys,
        MAX(blood_pressure_systolic) AS max_sys
    FROM vitals_log 
    WHERE user_id = $user_id 
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(logged_at)
    ORDER BY log_date DESC
");

// Overall weekly stats (using aggregate functions)
$overall = $conn->query("
    SELECT 
        COUNT(*) AS total_entries,
        AVG(blood_pressure_systolic) AS overall_avg_sys,
        AVG(blood_pressure_diastolic) AS overall_avg_dia,
        AVG(weight) AS overall_avg_weight,
        AVG(blood_sugar) AS overall_avg_sugar
    FROM vitals_log 
    WHERE user_id = $user_id 
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
")->fetch_assoc();

// Subquery: Get entries with above average systolic BP
$above_avg = $conn->query("
    SELECT v.*, u.full_name
    FROM vitals_log v
    JOIN users u ON v.user_id = u.user_id
    WHERE v.user_id = $user_id 
    AND v.blood_pressure_systolic > (
        SELECT AVG(blood_pressure_systolic) 
        FROM vitals_log 
        WHERE user_id = $user_id
    )
    AND v.logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY v.logged_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Report - Health Tracker</title>
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
            <li><a href="health_goals.php">Goals</a></li>
            <li><a href="weekly_report.php" class="active">Weekly Report</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Weekly Health Report</h1>
        <p><em>Last 7 days summary</em></p>

        <?php if ($overall && $overall['total_entries'] > 0): ?>
        <div class="section">
            <h2>Overall Weekly Averages</h2>
            <div class="weekly-stats">
                <div class="weekly-item">
                    <span class="label">Total Entries:</span>
                    <span class="value"><?php echo $overall['total_entries']; ?></span>
                </div>
                <div class="weekly-item">
                    <span class="label">Avg Systolic BP:</span>
                    <span class="value"><?php echo round($overall['overall_avg_sys'] ?? 0); ?> mmHg</span>
                </div>
                <div class="weekly-item">
                    <span class="label">Avg Diastolic BP:</span>
                    <span class="value"><?php echo round($overall['overall_avg_dia'] ?? 0); ?> mmHg</span>
                </div>
                <div class="weekly-item">
                    <span class="label">Avg Weight:</span>
                    <span class="value"><?php echo round($overall['overall_avg_weight'] ?? 0, 1); ?> kg</span>
                </div>
                <div class="weekly-item">
                    <span class="label">Avg Blood Sugar:</span>
                    <span class="value"><?php echo round($overall['overall_avg_sugar'] ?? 0); ?> mg/dL</span>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Daily Breakdown</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Entries</th>
                        <th>Avg BP</th>
                        <th>Avg HR</th>
                        <th>Avg Weight</th>
                        <th>Avg Sugar</th>
                        <th>Min/Max Systolic</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $weekly_summary->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['log_date'])); ?></td>
                        <td><?php echo $row['entries']; ?></td>
                        <td><?php echo round($row['avg_sys'] ?? 0); ?>/<?php echo round($row['avg_dia'] ?? 0); ?></td>
                        <td><?php echo round($row['avg_hr'] ?? 0); ?> bpm</td>
                        <td><?php echo round($row['avg_weight'] ?? 0, 1); ?> kg</td>
                        <td><?php echo round($row['avg_sugar'] ?? 0); ?> mg/dL</td>
                        <td><?php echo $row['min_sys'] ?? 0; ?> / <?php echo $row['max_sys'] ?? 0; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($above_avg && $above_avg->num_rows > 0): ?>
        <div class="section">
            <h2>Above Average Systolic BP Readings (Subquery)</h2>
            <p><small>Showing readings where systolic BP is above your personal average</small></p>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Systolic</th>
                        <th>Diastolic</th>
                        <th>HR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $above_avg->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y h:i A', strtotime($row['logged_at'])); ?></td>
                        <td class="text-danger"><?php echo $row['blood_pressure_systolic']; ?></td>
                        <td><?php echo $row['blood_pressure_diastolic']; ?></td>
                        <td><?php echo $row['heart_rate']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-info">No vitals logged in the past 7 days. Start logging your vitals to see your weekly report.</div>
        <?php endif; ?>
    </div>
</body>
</html>
