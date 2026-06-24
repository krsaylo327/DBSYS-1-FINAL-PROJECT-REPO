<?php
include_once 'db_connect.php';


$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: patients_list.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];


    $sql = "UPDATE patients
            SET first_name = :first_name,
                last_name = :last_name,
                gender = :gender,
                birthdate = :birthdate,
                course = :course,
                year_level = :year_level,
                contact_number = :contact_number,
                address = :address
            WHERE patient_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':gender' => $gender,
        ':birthdate' => $birthdate,
        ':course' => $course,
        ':year_level' => $year_level,
        ':contact_number' => $contact_number,
        ':address' => $address,
        ':id' => $id
    ]);


    header("Location: patients_list.php?success=1");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = :id");
$stmt->execute([':id' => $id]);
$patient = $stmt->fetch();


if (!$patient) {
    die("Patient not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient</title>
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
    <h1>Edit Patient</h1>

    <form method="POST">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Gender</label>
            <select name="gender" required>
                <option value="Male" <?php echo $patient['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $patient['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>

        <div class="form-group">
            <label>Birthdate</label>
            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($patient['birthdate']); ?>" required>
        </div>

        <div class="form-group">
            <label>Course</label>
            <input type="text" name="course" value="<?php echo htmlspecialchars($patient['course']); ?>" required>
        </div>

        <div class="form-group">
            <label>Year Level</label>
            <input type="number" name="year_level" value="<?php echo htmlspecialchars($patient['year_level']); ?>" required>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" value="<?php echo htmlspecialchars($patient['contact_number']); ?>" required>
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="3" required><?php echo htmlspecialchars($patient['address']); ?></textarea>
        </div>

        <button type="submit" class="btn save">Save</button>
        <a href="patients_list.php" class="btn back">Back</a>
    </form>
</div>
</body>
</html>