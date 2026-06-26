<?php
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$med_id = intval($_GET['id'] ?? 0);

if ($med_id > 0) {
    $conn->query("DELETE FROM medication_schedule WHERE medication_id = $med_id");
    $stmt = $conn->prepare("DELETE FROM medications WHERE medication_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $med_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: medications.php?deleted=1');
exit();
?>
