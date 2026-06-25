<?php
include_once 'db_connect.php';


$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: nurses_list.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE nurses
            SET first_name = :first_name,
                last_name = :last_name,
                contact_number = :contact_number,
                shift_schedule = :shift_schedule
            WHERE nurse_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $_POST['first_name'],
        ':last_name' => $_POST['last_name'],
        ':contact_number' => $_POST['contact_number'],
        ':shift_schedule' => $_POST['shift_schedule'],
        ':id' => $id
    ]);


    header("Location: nurses_list.php?success=1");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM nurses WHERE nurse_id = :id");
$stmt->execute([':id' => $id]);
$nurse = $stmt->fetch();


if (!$nurse) {
    die("Nurse not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nurse</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
        }
        h1 {
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 32px;
        }
        .form-group { margin-bottom: 15px; }
        label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: bold; 
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 14px;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .save { background: #28a745; }
        .back { background: #6c757d; }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Nurse</h1>

    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($nurse['first_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($nurse['last_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?php echo htmlspecialchars($nurse['contact_number']); ?>" required>
        </div>

        <div class="form-group">
            <label>Shift Schedule</label>
            <input type="text" name="shift_schedule" value="<?php echo htmlspecialchars($nurse['shift_schedule']); ?>" required>
        </div>

        <button type="submit" class="btn save">Save</button>
        <a href="nurses_list.php" class="btn back">Back</a>
    </form>
</div>
</body>
</html>