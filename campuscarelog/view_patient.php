<?php
include_once 'db_connect.php';

$patient_id = $_GET['id'] ?? 0;
$patient = [];
$clinic_visits = [];

// Get patient details
if ($patient_id > 0) {
    $stmt = $pdo->prepare("
        SELECT patient_id, first_name, last_name, birthdate, course, year_level, contact_number, address, gender
        FROM patients
        WHERE patient_id = :id
    ");
    $stmt->execute([':id' => $patient_id]);
    $patient = $stmt->fetch();
    
    // Get clinic visits for this patient (removed 'notes' column)
    $stmt = $pdo->prepare("
        SELECT cv.visit_id, cv.visit_date, cv.symptoms, cv.diagnosis, cv.treatment
        FROM clinic_visits cv
        WHERE cv.patient_id = :patient_id
        ORDER BY cv.visit_date DESC
    ");
    $stmt->execute([':patient_id' => $patient_id]);
    $clinic_visits = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient - <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            text-align: center;
        }
        .btn {
            padding: 10px 14px;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn { background: #6c757d; margin-bottom: 15px; }
        .patient-info {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: inline-block;
            text-align: left;
        }
        .patient-info h2 {
            margin: 0 0 15px;
            color: #007bff;
            font-size: 24px;
            text-align: center;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .info-item {
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 120px;
        }
        .visits-section {
            margin-top: 20px;
            text-align: center;
        }
        .visits-section h2 {
            margin: 0 0 15px;
            color: #2c5f2d;
            font-size: 22px;
        }
        table {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            border-radius: 6px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #2c5f2d;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .no-visits {
            text-align: center;
            color: #666;
            padding: 30px;
            background: #f8d7da;
            border-radius: 6px;
            font-weight: 600;
            max-width: 900px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if ($patient): ?>
        <div class="patient-info">
            <h2><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">ID:</span>
                    <?php echo htmlspecialchars($patient['patient_id']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Gender:</span>
                    <?php echo htmlspecialchars($patient['gender']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Birthdate:</span>
                    <?php echo htmlspecialchars($patient['birthdate']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Course:</span>
                    <?php echo htmlspecialchars($patient['course']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Year Level:</span>
                    <?php echo htmlspecialchars($patient['year_level']); ?>
                </div>
                <div class="info-item">
                    <span class="info-label">Contact:</span>
                    <?php echo htmlspecialchars($patient['contact_number']); ?>
                </div>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <span class="info-label">Address:</span>
                    <?php echo htmlspecialchars($patient['address']); ?>
                </div>
            </div>
        </div>

        <div class="visits-section">
            <a href="index.php" class="btn back-btn">← Back to Dashboard</a>
            <h2>Clinic Visits</h2>
            
            <?php if (count($clinic_visits) > 0): ?>
                <table>
                    <tr>
                        <th>Visit ID</th>
                        <th>Visit Date</th>
                        <th>Symptoms</th>
                        <th>Diagnosis</th>
                        <th>Treatment</th>
                    </tr>
                    <?php foreach ($clinic_visits as $visit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($visit['visit_id']); ?></td>
                            <td><?php echo htmlspecialchars($visit['visit_date']); ?></td>
                            <td><?php echo htmlspecialchars($visit['symptoms']); ?></td>
                            <td><?php echo htmlspecialchars($visit['diagnosis']); ?></td>
                            <td><?php echo htmlspecialchars($visit['treatment']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <div class="no-visits">
                    No clinic visits recorded for this patient.
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <a href="index.php" class="btn back-btn">← Back to Dashboard</a>
        <p style="color: #dc3545; text-align: center; font-weight: 600;">Patient not found.</p>
    <?php endif; ?>
</div>
</body>
</html>