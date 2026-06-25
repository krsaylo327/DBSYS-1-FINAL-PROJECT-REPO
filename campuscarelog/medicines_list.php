<?php
include_once 'db_connect.php';

$sql = "SELECT medicine_id, medicine_name, stock_quantity, expiration_date, dosage
        FROM medicines
        ORDER BY medicine_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$medicines = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicines List</title>
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
    <h1>Medicines List</h1>

    <div class="top-actions">
        <a class="btn add" href="add_medicine.php">Add New Medicine</a>
        <a class="btn exit" href="index.php">Exit</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Medicine Name</th>
            <th>Stock Quantity</th>
            <th>Expiration Date</th>
            <th>Dosage</th>
            <th>Actions</th>
        </tr>

        <?php if (count($medicines) > 0): ?>
            <?php foreach ($medicines as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['medicine_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['medicine_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['expiration_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['dosage']); ?></td>
                    <td>
                        <a class="btn edit" href="edit_medicine.php?id=<?php echo $row['medicine_id']; ?>">Edit</a>
                        <a class="btn delete" href="delete_medicine.php?id=<?php echo $row['medicine_id']; ?>" onclick="return confirm('Delete this medicine?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No medicines found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>