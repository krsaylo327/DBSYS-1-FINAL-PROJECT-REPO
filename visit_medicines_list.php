<?php
include_once 'db_connect.php';

$sql = "SELECT vm.visit_medicines_id, vm.visit_id, vm.medicine_id, vm.quantity,
               cv.visit_date, m.medicine_name
        FROM visit_medicines vm
        LEFT JOIN clinic_visits cv ON vm.visit_id = cv.visit_id
        LEFT JOIN medicines m ON vm.medicine_id = m.medicine_id
        ORDER BY vm.visit_medicines_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Medicines List</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 8px 12px; border-radius: 4px; color: white; text-decoration: none; display: inline-block; margin-right: 5px; }
        .add { background: #28a745; }
        .edit { background: #17a2b8; }
        .delete { background: #dc3545; }
        .exit { background: #6c757d; }
        .top-actions { margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap; }
    </style>
</head>
<body>
<div class="container">
    <h1>Visit Medicines</h1>

    <div class="top-actions">
        <a class="btn add" href="add_visit_medicine.php">Add New Record</a>
        <a class="btn exit" href="index.php">Exit</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Visit ID</th>
            <th>Medicine ID</th>
            <th>Medicine Name</th>
            <th>Visit Date</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>

        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['visit_medicines_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['visit_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['medicine_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['medicine_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['visit_date'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                    <td>
                        <a class="btn edit" href="edit_visit_medicine.php?id=<?php echo $row['visit_medicines_id']; ?>">Edit</a>
                        <a class="btn delete" href="delete_visit_medicine.php?id=<?php echo $row['visit_medicines_id']; ?>" onclick="return confirm('Delete this record?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>