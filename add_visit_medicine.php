<?php
include_once 'db_connect.php';

// Fetch visits and medicines BEFORE the POST check
$visits = $pdo->query("
    SELECT cv.visit_id, p.first_name, p.last_name
    FROM clinic_visits cv
    INNER JOIN patients p ON cv.patient_id = p.patient_id
    ORDER BY cv.visit_id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$medicines = $pdo->query("SELECT medicine_id, medicine_name FROM medicines ORDER BY medicine_name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $visit_id = $_POST['visit_id'] ?? '';
    $medicine_id = $_POST['medicine_id'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    
    if (empty($visit_id) || empty($medicine_id) || empty($quantity)) {
        die("All fields are required");
    }
    
    if ($quantity < 1) {
        die("Quantity must be at least 1");
    }
    
    try {
        $sql = "INSERT INTO visit_medicines (visit_id, medicine_id, quantity)
                VALUES (:visit_id, :medicine_id, :quantity)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':visit_id' => $visit_id,
            ':medicine_id' => $medicine_id,
            ':quantity' => $quantity
        ]);
        
        header("Location: visit_medicines_list.php?success=1");
        exit();
    } catch (PDOException $e) {
        error_log("Visit medicine insert error: " . $e->getMessage());
        die("Error saving visit medicine. Please try again.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Visit Medicine</title>
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
        select, input {
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
    <h1>Add Visit Medicine</h1>

    <form method="POST">
        <div class="form-group">
            <label>Visit</label>
            <select name="visit_id" required>
                <option value="">Select Visit</option>
                <?php foreach ($visits as $visit): ?>
                    <option value="<?php echo htmlspecialchars($visit['visit_id']); ?>">
                        <?php echo htmlspecialchars($visit['visit_id'] . ' - ' . $visit['first_name'] . ' ' . $visit['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Medicine</label>
            <select name="medicine_id" required>
                <option value="">Select Medicine</option>
                <?php foreach ($medicines as $medicine): ?>
                    <option value="<?php echo htmlspecialchars($medicine['medicine_id']); ?>">
                        <?php echo htmlspecialchars($medicine['medicine_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="quantity" min="1" required>
        </div>

        <button type="submit" class="btn save">Save</button>
        <a href="visit_medicines_list.php" class="btn back">Back</a>
    </form>
</div>
</body>
</html>