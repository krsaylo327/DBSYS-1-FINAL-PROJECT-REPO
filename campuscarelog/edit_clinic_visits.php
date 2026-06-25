<?php
require_once 'db_connect.php';


$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: clinic_visits_list.php');
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM clinic_visits WHERE visit_id = :id");
$stmt->execute([':id' => $id]);
$visit = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$visit) {
    die("Clinic visit not found.");
}


$patients = $pdo->query("SELECT patient_id, first_name, last_name FROM patients ORDER BY first_name, last_name")->fetchAll(PDO::FETCH_ASSOC);
$nurses = $pdo->query("SELECT nurse_id, first_name, last_name FROM nurses ORDER BY first_name, last_name")->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE clinic_visits
            SET patient_id = :patient_id,
                nurse_id = :nurse_id,
                visit_date = :visit_date,
                visit_time = :visit_time,
                symptoms = :symptoms,
                diagnosis = :diagnosis,
                treatment = :treatment
            WHERE visit_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':patient_id' => $_POST['patient_id'],
        ':nurse_id' => $_POST['nurse_id'],
        ':visit_date' => $_POST['visit_date'],
        ':visit_time' => $_POST['visit_time'],
        ':symptoms' => $_POST['symptoms'],
        ':diagnosis' => $_POST['diagnosis'],
        ':treatment' => $_POST['treatment'],
        ':id' => $id,
    ]);


    header('Location: clinic_visits_list.php?success=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Clinic Visit</title>
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
    <h1>Edit Clinic Visit</h1>

    <form method="post">
        <div class="form-group">
            <label>Patient</label>
            <select name="patient_id" required>
                <option value="">Select Patient</option>
                <?php foreach ($patients as $patient): ?>
                    <option value="<?= htmlspecialchars($patient['patient_id']) ?>" <?= $visit['patient_id'] == $patient['patient_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Nurse</label>
            <select name="nurse_id" required>
                <option value="">Select Nurse</option>
                <?php foreach ($nurses as $nurse): ?>
                    <option value="<?= htmlspecialchars($nurse['nurse_id']) ?>" <?= $visit['nurse_id'] == $nurse['nurse_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($nurse['first_name'] . ' ' . $nurse['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Visit Date</label>
            <input type="date" name="visit_date" value="<?= htmlspecialchars($visit['visit_date']) ?>" required>
        </div>

        <div class="form-group">
            <label>Visit Time</label>
            <input type="time" name="visit_time" value="<?= htmlspecialchars($visit['visit_time']) ?>" required>
        </div>

        <div class="form-group">
            <label>Symptoms</label>
            <textarea name="symptoms" rows="3" required><?= htmlspecialchars($visit['symptoms']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Diagnosis</label>
            <textarea name="diagnosis" rows="3" required><?= htmlspecialchars($visit['diagnosis']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Treatment</label>
            <textarea name="treatment" rows="3" required><?= htmlspecialchars($visit['treatment']) ?></textarea>
        </div>

        <button type="submit" class="btn save">Save</button>
        <a href="clinic_visits_list.php" class="btn back">Back</a>
    </form>
</div>
</body>
</html>