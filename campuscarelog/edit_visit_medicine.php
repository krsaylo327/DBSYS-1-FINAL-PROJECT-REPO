<?php
include_once 'db_connect.php';


$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: visit_medicines_list.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE visit_medicines
            SET visit_id = :visit_id,
                medicine_id = :medicine_id,
                quantity = :quantity
            WHERE visit_medicines_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':visit_id' => $_POST['visit_id'],
        ':medicine_id' => $_POST['medicine_id'],
        ':quantity' => $_POST['quantity'],
        ':id' => $id
    ]);


    header("Location: visit_medicines_list.php?success=1");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM visit_medicines WHERE visit_medicines_id = :id");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch();


if (!$item) {
    die("Record not found.");
}


$visits = $pdo->query("SELECT visit_id FROM clinic_visits ORDER BY visit_id ASC")->fetchAll();
$medicines = $pdo->query("SELECT medicine_id, medicine_name FROM medicines ORDER BY medicine_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Visit Medicine</title>
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
    <h1>Edit Visit Medicine</h1>

    <form method="POST">
        <div class="form-group">
            <label>Clinic Visit</label>
            <select name="visit_id" required>
                <option value="">Select Visit</option>
                <?php foreach ($visits as $visit): ?>
                    <option value="<?php echo $visit['visit_id']; ?>" <?php if ($visit['visit_id'] == $item['visit_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($visit['visit_id']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Medicine</label>
            <select name="medicine_id" required>
                <option value="">Select Medicine</option>
                <?php foreach ($medicines as $medicine): ?>
                    <option value="<?php echo $medicine['medicine_id']; ?>" <?php if ($medicine['medicine_id'] == $item['medicine_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($medicine['medicine_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
        </div>

        <button type="submit" class="btn save">Save</button>
        <a href="visit_medicines_list.php" class="btn back">Back</a>
    </form>
</div>
</body>
</html>