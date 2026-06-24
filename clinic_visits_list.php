<?php
require_once 'db_connect.php';

$sql = "SELECT cv.visit_id, cv.patient_id, cv.nurse_id, cv.visit_date, cv.visit_time, cv.symptoms, cv.diagnosis, cv.treatment,
               p.first_name AS patient_first_name, p.last_name AS patient_last_name,
               n.first_name AS nurse_first_name, n.last_name AS nurse_last_name
        FROM clinic_visits cv
        LEFT JOIN patients p ON cv.patient_id = p.patient_id
        LEFT JOIN nurses n ON cv.nurse_id = n.nurse_id
        ORDER BY cv.visit_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Visits List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 30px;
        }
        .container {
            max-width: 1600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        h2 {
            margin: 0 0 12px 0;
            font-size: 30px;
            font-weight: 700;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            font-size: 18px;
            border: none;
            cursor: pointer;
        }
        .add { background: #28a745; }
        .edit { background: #17a2b8; }
        .delete { background: #dc3545; }
        .exit { background: #6c757d; }
        .top-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 15px;
            font-size: 16px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 13px;
            text-align: left;
            vertical-align: top;
            font-size: 16px;
        }
        th:nth-child(9),
        td:nth-child(9) {
            width: 170px;
            white-space: nowrap;
        }
        .actions {
            white-space: nowrap;
        }
        th {
            background: #007bff;
            color: #fff;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Clinic Visits List</h2>

    <div class="top-actions">
        <a class="btn add" href="add_clinic_visits.php">Add Visit</a>
        <a class="btn exit" href="index.php">Exit</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Nurse</th>
            <th>Visit Date</th>
            <th>Visit Time</th>
            <th>Symptoms</th>
            <th>Diagnosis</th>
            <th>Treatment</th>
            <th>Actions</th>
        </tr>
        <?php if ($visits): ?>
            <?php foreach ($visits as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['visit_id']) ?></td>
                    <td><?= htmlspecialchars(trim(($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars(trim(($row['nurse_first_name'] ?? '') . ' ' . ($row['nurse_last_name'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($row['visit_date']) ?></td>
                    <td><?= htmlspecialchars($row['visit_time']) ?></td>
                    <td><?= htmlspecialchars($row['symptoms']) ?></td>
                    <td><?= htmlspecialchars($row['diagnosis']) ?></td>
                    <td><?= htmlspecialchars($row['treatment']) ?></td>
                    <td class="actions">
                        <a class="btn edit" href="edit_clinic_visits.php?id=<?= urlencode($row['visit_id']) ?>">Edit</a>
                        <a class="btn delete" href="delete_clinic_visits.php?id=<?= urlencode($row['visit_id']) ?>" onclick="return confirm('Delete this visit?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No clinic visits found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>