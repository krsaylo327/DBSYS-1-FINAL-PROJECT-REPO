<?php
include_once 'db_connect.php';

$sql = "SELECT nurse_id, first_name, last_name, contact_number, shift_schedule
        FROM nurses
        ORDER BY nurse_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$nurses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurses List</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.08); }
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
    <h1>Nurses List</h1>

    <div class="top-actions">
        <a class="btn add" href="add_nurse.php">Add New Nurse</a>
        <a class="btn exit" href="index.php">Exit</a>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Contact Number</th>
            <th>Shift Schedule</th>
            <th>Actions</th>
        </tr>

        <?php if (count($nurses) > 0): ?>
            <?php foreach ($nurses as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nurse_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['shift_schedule']); ?></td>
                    <td>
                        <a class="btn edit" href="edit_nurse.php?id=<?php echo $row['nurse_id']; ?>">Edit</a>
                        <a class="btn delete" href="delete_nurse.php?id=<?php echo $row['nurse_id']; ?>" onclick="return confirm('Delete this nurse?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No nurses found.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>