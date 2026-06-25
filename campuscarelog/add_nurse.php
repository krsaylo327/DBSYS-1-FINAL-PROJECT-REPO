<?php
include_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO nurses (first_name, last_name, contact_number, shift_schedule)
            VALUES (:first_name, :last_name, :contact_number, :shift_schedule)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $_POST['first_name'],
        ':last_name' => $_POST['last_name'],
        ':contact_number' => $_POST['contact_number'],
        ':shift_schedule' => $_POST['shift_schedule']
    ]);

    header("Location: nurses_list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Nurse</title>
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
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input {
            width: 100%;
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
        .save { background: #28a745; }
        .back { background: #6c757d; margin-left: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Add Nurse</h1>

    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" required>
        </div>

        <div class="form-group">
            <label>Shift Schedule</label>
            <input type="text" name="shift_schedule" required>
        </div>

        <button type="submit" class="btn save">Save</button>
        <a href="nurses_list.php" class="btn back">Back</a>
    </form>
</div>
</body>
</html>