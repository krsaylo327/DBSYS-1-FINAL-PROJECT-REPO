<?php
// 1. INITIALIZE SESSION MANAGER BEFORE ANY HTML IS RENDERED
session_start();

// Redirect home instantly if the user is already logged in
if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$error_message = "";

// 2. INTERCEPT INCOMING HTTP POST ROUTING DATA
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim values safely to remove accidental copy-pasted trailing spaces
    $input_user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $input_pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    // =========================================================================
    // EMERGENCY MASTER BYPASS GATEWAY (PRESENTATION INSURANCE)
    // If you type these exact credentials, you pass straight through instantly!
    // =========================================================================
    if ($input_user === 'admin_league' && $input_pass === 'admin123') {
        $_SESSION['user_id'] = 999;
        $_SESSION['username'] = 'admin_league';
        $_SESSION['role'] = 'admin';
        header("Location: index.php");
        exit();
    }

    // 3. STANDARD RELATIONAL DATABASE SECURITY FALLBACK
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "esports_tournament";

    // Establish connection handshake
    $conn = new mysqli($servername, $username, $password, $dbname);

    if (!$conn->connect_error) {
        // Secure query lookup using a prepared parameter statement
        $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $input_user);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                // Evaluates the encrypted database string profile
                if (password_verify($input_pass, $row['password'])) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['username'] = $input_user;
                    $_SESSION['role'] = $row['role'];

                    header("Location: index.php");
                    exit();
                }
            }
            $stmt->close();
        }
        $conn->close();
    }

    // Set error message if both database check and fallback fail
    $error_message = "Invalid password credentials.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Gate — Authorization Portal</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Center the box perfectly layout-wise on screen */
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
        }
        .alert-error {
            background: rgba(255, 76, 76, 0.1);
            border: 1px solid #ff4c4c;
            color: #ff4c4c;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        .guest-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #4facfe;
            text-decoration: none;
            font-size: 14px;
        }
        .guest-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <header style="text-align: center; margin-bottom: 20px;">
        <h1>NEXUS GATE</h1>
        <p>Enter administrative authentication clearances</p>
    </header>

    <div class="login-box panel">
        <h2>Secure Sign-In</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">Username / Handle</label>
                <input type="text" name="username" required placeholder="e.g., admin_league" style="margin-bottom: 15px;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-muted);">Secure Key Matrix (Password)</label>
                <input type="password" name="password" required placeholder="••••••••" style="margin-bottom: 20px;">
            </div>
            <button type="submit" style="width: 100%; padding: 12px; font-weight: bold;">Authenticate</button>
        </form>

        <a href="index.php" class="guest-link">Proceed to Dashboard as Viewer</a>
    </div>

</body>
</html>