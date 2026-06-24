<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $medicine_name = trim($_POST['medicine_name'] ?? '');
    $stock_quantity = $_POST['stock_quantity'] ?? '';
    $expiration_date = $_POST['expiration_date'] ?? '';
    $dosage = trim($_POST['dosage'] ?? '');
    
    if (empty($medicine_name) || empty($stock_quantity) || empty($expiration_date) || empty($dosage)) {
        die("All fields are required");
    }
    
    try {
        $sql = "INSERT INTO medicines (medicine_name, stock_quantity, expiration_date, dosage)
                VALUES (:medicine_name, :stock_quantity, :expiration_date, :dosage)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':medicine_name' => $medicine_name,
            ':stock_quantity' => $stock_quantity,
            ':expiration_date' => $expiration_date,
            ':dosage' => $dosage
        ]);
        
        header("Location: medicines_list.php?success=1");
        exit();
    } catch (PDOException $e) {
        error_log("Medicine insert error: " . $e->getMessage());
        die("Error saving medicine. Please try again.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine</title>
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
        input, select {
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
    <h1>Add Medicine</h1>

    <form method="POST">
        <div class="form-group">
            <label>Medicine Name</label>
            <input type="text" name="medicine_name" required>
        </div>

        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" required>
        </div>

        <div class="form-group">
            <label>Expiration Date</label>
            <input type="date" name="expiration_date" required>
        </div>

        <div class="form-group">
            <label>Dosage</label>
            <input type="text" name="dosage" required>
        </div>

        <button type="submit" class="btn save">Save</button>
        <a href="medicines_list.php" class="btn back">Back</a>
    </form>
</div>
</body>
</html>