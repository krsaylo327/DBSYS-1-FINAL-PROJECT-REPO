<?php
require_once 'db_connect.php';

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($request_id <= 0) {
    header('Location: requests.php');
    exit;
}

$check = $conn->query("SELECT request_id FROM certificate_request WHERE request_id = $request_id");
if ($check->num_rows == 0) {
    header('Location: requests.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM certificate_request WHERE request_id = ?");
$stmt->bind_param("i", $request_id);

if ($stmt->execute()) {
    header('Location: requests.php?deleted=1');
} else {
    header('Location: requests.php?error=1');
}
$stmt->close();
$conn->close();
?>