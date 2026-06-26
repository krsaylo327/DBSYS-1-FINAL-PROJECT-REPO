<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$log_id = intval($_GET['id'] ?? 0);

if ($log_id > 0) {
    $stmt = $conn->prepare("DELETE FROM vitals_log WHERE log_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $log_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: vitals.php?deleted=1');
exit();
?>
