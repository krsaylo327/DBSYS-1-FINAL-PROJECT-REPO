<?php
// Start session at the very top
session_start();

// Check if user is logged in - protect the dashboard
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.html?error=not_logged_in");
    exit();
}

// Check for session timeout (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header("Location: login.html?error=session_timeout");
    exit();
}

// Get clinic info from session
$clinic_name = $_SESSION['clinic_name'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Include database connection
include_once 'db_connect.php';

$counts = [];
$tables = ['patients', 'nurses', 'medicines', 'clinic_visits', 'visit_medicines'];

foreach ($tables as $table) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM `$table`");
    $stmt->execute();
    $row = $stmt->fetch();
    $counts[$table] = $row['total'] ?? 0;
}

// Handle patient search
$search_results = [];
$search_query = '';

if (isset($_GET['search_patient']) && $_GET['search_patient'] !== '') {
    $search_query = $_GET['search_patient'];
    
    $stmt = $pdo->prepare("
        SELECT patient_id, first_name, last_name, birthdate, course, year_level, contact_number, address, gender 
        FROM patients 
        WHERE first_name LIKE :search
           OR last_name LIKE :search
           OR gender LIKE :search
           OR birthdate LIKE :search
           OR course LIKE :search
           OR year_level LIKE :search
           OR contact_number LIKE :search
           OR address LIKE :search
        LIMIT 50
    ");
    
    $search_param = "%$search_query%";
    $stmt->execute([':search' => $search_param]);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>campUs cAre Dashboard</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-image: url('no.-5-scaled.jpg');
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
            max-width: 1200px;
            margin: auto;
            padding: 8px;
            text-align: center;
        }

        .header {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.92);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            text-align: center;
        }

        .logo {
            width: 350px;
            height: 350px;
            object-fit: contain;
            display: block;
            margin: 0 auto 8px;
        }

        .header h1 {
            margin: 0;
            color: #2c5f2d;
            text-align: center;
            font-size: 35px;
        }

        .header p {
            margin: 6px 0 0;
            color: #666;
            font-weight: 900;
            text-align: center;
            font-size: 15px;
        }

        .user-info {
            margin: 10px 0;
            text-align: center;
            font-size: 13px;
            color: #333;
            line-height: 1.5;
        }

        .user-info strong {
            color: #28a745;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 12px;
            justify-content: center;
        }

        .card {
            background: rgba(255, 255, 255, 0.92);
            padding: 22px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            text-align: center;
        }

        .card h2 {
            margin: 0 0 10px;
            text-align: center;
            font-size: 23px;
        }

        .value {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
        }

        .links {
            background: rgba(255, 255, 255, 0.92);
            padding: 18px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            text-align: center;
            margin-bottom: 12px;
        }

        .links h2 {
            margin: 0 0 12px;
            text-align: center;
            color: #333;
            font-size: 24px;
            font-weight: 700;
        }

        .links-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }

        .links-buttons a {
            display: inline-block;
            padding: 12px 20px;
            background: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            text-align: center;
        }

        .links-buttons a:hover {
            background: #218838;
        }

        .search-section {
            background: rgba(255, 255, 255, 0.92);
            padding: 18px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            margin-bottom: 12px;
        }

        .search-section h2 {
            margin: 0 0 12px;
            text-align: center;
            color: #333;
            font-size: 24px;
            font-weight: 700;
        }

        .search-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .search-form input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            width: 300px;
        }

        .search-form button {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-form button:hover {
            background: #218838;
        }

        .search-table {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            border-radius: 6px;
            overflow: hidden;
        }

        .search-table th {
            background: #2c5f2d;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .search-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
        }

        .search-table tr:hover {
            background: #f5f5f5;
        }

        .search-table tr:last-child td {
            border-bottom: none;
        }

        .view-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        .no-results {
            text-align: center;
            color: #dc3545;
            padding: 30px;
            font-size: 16px;
            font-weight: 600;
            background: #f8d7da;
            border-radius: 6px;
            margin: 20px auto;
            max-width: 900px;
        }

        .logout-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 26px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        .security-note {
            background: rgba(240, 248, 240, 0.95);
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 13px;
            color: #666;
            text-align: center;
        }

        .security-note strong {
            color: #28a745;
        }

        @media (max-width: 768px) {
            .logo {
                width: 200px;
                height: 200px;
            }

            .header h1 {
                font-size: 24px;
            }

            .card h2 {
                font-size: 17px;
            }

            .value {
                font-size: 30px;
            }

            .card {
                padding: 18px;
            }

            .logout-btn {
                padding: 8px 22px;
                font-size: 14px;
            }

            .links-buttons a {
                padding: 10px 16px;
                font-size: 14px;
            }

            .search-form input {
                width: 100%;
                max-width: 300px;
            }

            .search-table {
                font-size: 12px;
            }

            .search-table th,
            .search-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="Use_AI_Image_Jun_10__2026__19_06_58-removebg-preview.png" alt="Logo" class="logo">
        <h1>campUs cAre Dashboard</h1>
        <p>Manage patients, nurses, medicines, clinic visits, and visit medicines - <?php echo htmlspecialchars($clinic_name); ?></p>
        
        <div class="user-info">
            <strong>Clinic:</strong> <?php echo htmlspecialchars($clinic_name); ?> | 
            <strong>Name:</strong> <?php echo htmlspecialchars($user_name); ?> | 
            <strong>Role:</strong> <?php echo htmlspecialchars($user_role); ?>
        </div>
    </div>

    <div class="grid">
        <div class="card"><h2>Patients</h2><div class="value"><?php echo $counts['patients']; ?></div></div>
        <div class="card"><h2>Nurses</h2><div class="value"><?php echo $counts['nurses']; ?></div></div>
        <div class="card"><h2>Medicines</h2><div class="value"><?php echo $counts['medicines']; ?></div></div>
        <div class="card"><h2>Clinic Visits</h2><div class="value"><?php echo $counts['clinic_visits']; ?></div></div>
        <div class="card"><h2>Visit Medicines</h2><div class="value"><?php echo $counts['visit_medicines']; ?></div></div>
    </div>

    <div class="search-section">
        <h2>Search Patients</h2>
        <form class="search-form" method="GET" action="index.php" id="searchForm">
            <input 
                type="text" 
                name="search_patient" 
                placeholder="Search by name, gender, birthdate, course, year level, contact, or address..." 
                value="<?php echo htmlspecialchars($search_query); ?>"
                id="searchInput"
            >
            <button type="submit">Search</button>
        </form>
        
        <div id="searchResults">
            <?php if (!empty($search_results)): ?>
                <table class="search-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Gender</th>
                            <th>Birthdate</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $patient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                                <td><?php echo htmlspecialchars($patient['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                                <td><?php echo htmlspecialchars($patient['birthdate']); ?></td>
                                <td><?php echo htmlspecialchars($patient['course']); ?></td>
                                <td><?php echo htmlspecialchars($patient['year_level']); ?></td>
                                <td><?php echo htmlspecialchars($patient['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($patient['address']); ?></td>
                                <td>
                                    <a href="view_patient.php?id=<?php echo $patient['patient_id']; ?>" class="view-link">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($_GET['search_patient'] ?? '' !== ''): ?>
                <div class="no-results">
                    No data shown
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="links">
        <h2>Open Modules</h2>
        <div class="links-buttons">
            <a href="patients_list.php">Patients</a>
            <a href="nurses_list.php">Nurses</a>
            <a href="medicines_list.php">Medicines</a>
            <a href="clinic_visits_list.php">Clinic Visits</a>
            <a href="visit_medicines_list.php">Visit Medicines</a>
        </div>
    </div>

    <div class="logout-section">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="security-note">
        <strong>Security Information:</strong> You are logged in as <?php echo htmlspecialchars($user_role); ?> to <?php echo htmlspecialchars($clinic_name); ?>. 
        All activities are logged for audit. Session auto-timeouts after 30 minutes.
    </div>
</div>

<script>
// Hide search results when input is cleared (without clicking Search)
document.getElementById('searchInput').addEventListener('input', function() {
    if (this.value.trim() === '') {
        document.getElementById('searchResults').innerHTML = '';
    }
});
</script>
</body>
</html>