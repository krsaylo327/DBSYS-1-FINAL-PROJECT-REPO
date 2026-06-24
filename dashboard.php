<?php
include_once 'db_connect.php';



// Total patients
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_patients FROM patients");
$stmt->execute();
$total_patients = $stmt->fetch()['total_patients'];



// Total nurses
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_nurses FROM nurses");
$stmt->execute();
$total_nurses = $stmt->fetch()['total_nurses'];



// Total clinic visits
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_visits FROM clinic_visits");
$stmt->execute();
$total_visits = $stmt->fetch()['total_visits'];



// Total medicines
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_medicines FROM medicines");
$stmt->execute();
$total_medicines = $stmt->fetch()['total_medicines'];



// Total medicine quantity in stock
$stmt = $pdo->prepare("SELECT SUM(stock_quantity) AS total_stock FROM medicines");
$stmt->execute();
$total_stock = $stmt->fetch()['total_stock'];
$total_stock = $total_stock ? $total_stock : 0;



// Average medicine stock
$stmt = $pdo->prepare("SELECT AVG(stock_quantity) AS avg_stock FROM medicines");
$stmt->execute();
$avg_stock = $stmt->fetch()['avg_stock'];
$avg_stock = $avg_stock ? number_format($avg_stock, 2) : '0.00';



// Total visit medicines records
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_visit_medicines FROM visit_medicines");
$stmt->execute();
$total_visit_medicines = $stmt->fetch()['total_visit_medicines'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>campUs cAre</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/no.-5-scaled.jpg');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            position: relative;
            min-height: 100vh;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 0;
            pointer-events: none;
        }
        .container {
            position: relative;
            z-index: 1;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.92);
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .header-text h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .header-text p {
            margin: 5px 0 0;
            color: #666;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.92);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #333;
        }

        .card .value {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
        }

        .nav {
            margin-bottom: 20px;
        }

        .nav a {
            text-decoration: none;
            margin-right: 10px;
            color: white;
            background: #007bff;
            padding: 10px 14px;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-text">
            <h1>campUs cAre</h1>
            <p>Summary of records from your tables</p>
        </div>
    </div>

    <div class="nav">
        <a href="patients_list.php">Patients</a>
        <a href="search_patient.php">Search Patients</a>
        <a href="add_patient.php">Add Patient</a>
    </div>

    <div class="grid">
        <div class="card">
            <h2>Total Patients</h2>
            <div class="value"><?php echo $total_patients; ?></div>
        </div>

        <div class="card">
            <h2>Total Nurses</h2>
            <div class="value"><?php echo $total_nurses; ?></div>
        </div>

        <div class="card">
            <h2>Total Clinic Visits</h2>
            <div class="value"><?php echo $total_visits; ?></div>
        </div>

        <div class="card">
            <h2>Total Medicines</h2>
            <div class="value"><?php echo $total_medicines; ?></div>
        </div>

        <div class="card">
            <h2>Total Medicine Stock</h2>
            <div class="value"><?php echo $total_stock; ?></div>
        </div>

        <div class="card">
            <h2>Average Medicine Stock</h2>
            <div class="value"><?php echo $avg_stock; ?></div>
        </div>

        <div class="card">
            <h2>Visit Medicines Records</h2>
            <div class="value"><?php echo $total_visit_medicines; ?></div>
        </div>
    </div>
</div>
</body>
</html>