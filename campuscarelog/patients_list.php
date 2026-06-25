<?php
include_once 'db_connect.php';

$sql = "SELECT patient_id, first_name, last_name, gender, birthdate, course, year_level, contact_number, address
        FROM patients
        ORDER BY patient_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients List</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
        a { text-decoration: none; }
        .btn { padding: 8px 12px; border-radius: 4px; color: white; display: inline-block; }
        .add { background: #28a745; }
        .edit { background: #17a2b8; }
        .delete { background: #dc3545; }
        .exit { background: #6c757d; }
        .top-actions { margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap; }
    </style>
</head>
<body>
<div class="container">
    <h1>Patients List</h1>

    <div class="top-actions">
        <a class="btn add" href="add_patient.php">Add New Patient</a>
        <a class="btn exit" href="index.php">Exit</a>
    </div>

    <p>Total Patients: <?php echo count($patients); ?></p>

    <table>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Gender</th>
            <th>Birthdate</th>
            <th>Course</th>
            <th>Year Level</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
        <?php if (count($patients) > 0): ?>
            <?php foreach ($patients as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['birthdate']); ?></td>
                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                    <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td>
                        <a class="btn edit" href="edit_patient.php?id=<?php echo $row['patient_id']; ?>">Edit</a>
                        <a class="btn delete" href="delete_patient.php?id=<?php echo $row['patient_id']; ?>" onclick="return confirm('Delete this record?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>