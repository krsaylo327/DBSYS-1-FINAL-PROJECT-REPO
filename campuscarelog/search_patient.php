<?php
include_once 'db_connect.php';

$search = $_GET['search'] ?? '';
$patients = [];

if ($search !== '') {
    $sql = "SELECT patient_id, first_name, last_name, birthdate, course, contact_number, address
            FROM patients
            WHERE first_name LIKE :search
               OR last_name LIKE :search
               OR course LIKE :search
               OR contact_number LIKE :search
               OR address LIKE :search
               OR DATE_FORMAT(birthdate, '%Y-%m-%d') LIKE :search
            ORDER BY patient_id ASC";
    $stmt = $pdo->prepare($sql);
    $like = '%' . $search . '%';
    $stmt->execute([
        ':search' => $like
    ]);
    $patients = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Patient</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
        }
        .search-box {
            width: 300px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
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
        .search-btn { background: #28a745; }
        .back-btn { background: #6c757d; margin-bottom: 15px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        a.view-link {
            color: #007bff;
            text-decoration: none;
        }
        a.view-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="btn back-btn">← Back to Dashboard</a>
    <h1>Search Patient</h1>

    <form method="GET">
        <input type="text" name="search" class="search-box" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, course, contact, or address">
        <button type="submit" class="btn search-btn">Search</button>
    </form>

    <?php if ($search !== ''): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Birthdate</th>
                <th>Course</th>
                <th>Contact Number</th>
                <th>Address</th>
                <th>Action</th>
            </tr>

            <?php if (count($patients) > 0): ?>
                <?php foreach ($patients as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['birthdate']); ?></td>
                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td>
                            <a href="view_patient.php?id=<?php echo $row['patient_id']; ?>" class="view-link">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No patients found.</td></tr>
            <?php endif; ?>
        </table>
    <?php else: ?>
        <p style="color: #666; margin-top: 20px;">Enter a search term to find patients.</p>
    <?php endif; ?>
</div>
</body>
</html>