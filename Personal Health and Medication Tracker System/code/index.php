<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Dashboard Stats
$stats = [];

// Total vitals records
$result = $conn->query("SELECT COUNT(*) AS total FROM vitals_log WHERE user_id = $user_id");
$stats['total_vitals'] = $result->fetch_assoc()['total'] ?? 0;

// Active medications
$result = $conn->query("SELECT COUNT(*) AS total FROM medications WHERE user_id = $user_id AND is_active = TRUE");
$stats['active_meds'] = $result->fetch_assoc()['total'] ?? 0;

// Health goals
$result = $conn->query("SELECT COUNT(*) AS total FROM health_goals WHERE user_id = $user_id AND status != 'Achieved'");
$stats['active_goals'] = $result->fetch_assoc()['total'] ?? 0;

// Latest vitals
$latest = $conn->query("SELECT * FROM vitals_log WHERE user_id = $user_id ORDER BY logged_at DESC LIMIT 1");
$latest_vitals = $latest->fetch_assoc();

// Weekly stats
$weekly = $conn->query("SELECT 
    COUNT(*) AS entries,
    AVG(blood_pressure_systolic) AS avg_sys,
    AVG(blood_pressure_diastolic) AS avg_dia,
    AVG(weight) AS avg_weight
    FROM vitals_log 
    WHERE user_id = $user_id 
    AND logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$weekly_stats = $weekly->fetch_assoc();

// Upcoming medications
$upcoming = $conn->query("SELECT m.name, m.dosage, ms.scheduled_time 
    FROM medications m 
    JOIN medication_schedule ms ON m.medication_id = ms.medication_id 
    WHERE m.user_id = $user_id AND m.is_active = TRUE 
    ORDER BY ms.scheduled_time LIMIT 5");

// Recent vitals
$recent = $conn->query("SELECT * FROM vitals_log WHERE user_id = $user_id ORDER BY logged_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Health Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🏥 Health Tracker</div>
        <div class="nav-user">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="vitals.php">Vitals</a></li>
            <li><a href="medications.php">Medications</a></li>
            <li><a href="medication_schedule.php">Schedule</a></li>
            <li><a href="health_goals.php">Goals</a></li>
            <li><a href="weekly_report.php">Weekly Report</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo $stats['total_vitals']; ?></div>
                <div class="stat-label">Total Vitals Entries</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💊</div>
                <div class="stat-number"><?php echo $stats['active_meds']; ?></div>
                <div class="stat-label">Active Medications</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-number"><?php echo $stats['active_goals']; ?></div>
                <div class="stat-label">Active Goals</div>
            </div>
        </div>

        <?php if ($weekly_stats && $weekly_stats['entries'] > 0): ?>
        <div class="section">
            <h2>Weekly Summary</h2>
            <div class="weekly-stats">
                <div class="weekly-item">
                    <span class="label">Entries:</span>
                    <span class="value"><?php echo $weekly_stats['entries']; ?></span>
                </div>
                <div class="weekly-item">
                    <span class="label">Avg Systolic:</span>
                    <span class="value"><?php echo round($weekly_stats['avg_sys'] ?? 0); ?></span>
                </div>
                <div class="weekly-item">
                    <span class="label">Avg Diastolic:</span>
                    <span class="value"><?php echo round($weekly_stats['avg_dia'] ?? 0); ?></span>
                </div>
                <div class="weekly-item">
                    <span class="label">Avg Weight:</span>
                    <span class="value"><?php echo round($weekly_stats['avg_weight'] ?? 0, 1); ?> kg</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($latest_vitals): ?>
        <div class="section">
            <h2>Latest Vitals</h2>
            <div class="latest-vitals">
                <div><strong>BP:</strong> <?php echo $latest_vitals['blood_pressure_systolic']; ?>/<?php echo $latest_vitals['blood_pressure_diastolic']; ?></div>
                <div><strong>HR:</strong> <?php echo $latest_vitals['heart_rate']; ?> bpm</div>
                <div><strong>Weight:</strong> <?php echo $latest_vitals['weight']; ?> kg</div>
                <div><strong>Blood Sugar:</strong> <?php echo $latest_vitals['blood_sugar']; ?> mg/dL</div>
                <div><strong>Temperature:</strong> <?php echo $latest_vitals['temperature']; ?> °C</div>
                <div><strong>Logged:</strong> <?php echo date('M d, Y h:i A', strtotime($latest_vitals['logged_at'])); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($upcoming && $upcoming->num_rows > 0): ?>
        <div class="section">
            <h2>Upcoming Medications</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $upcoming->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['dosage']); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['scheduled_time'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($recent && $recent->num_rows > 0): ?>
        <div class="section">
            <h2>Recent Vitals</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>BP</th>
                        <th>HR</th>
                        <th>Weight</th>
                        <th>Blood Sugar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['logged_at'])); ?></td>
                        <td><?php echo $row['blood_pressure_systolic']; ?>/<?php echo $row['blood_pressure_diastolic']; ?></td>
                        <td><?php echo $row['heart_rate']; ?></td>
                        <td><?php echo $row['weight']; ?></td>
                        <td><?php echo $row['blood_sugar']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
