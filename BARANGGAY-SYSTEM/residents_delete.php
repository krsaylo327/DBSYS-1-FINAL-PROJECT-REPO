<?php
require_once 'db_connect.php';

$resident_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($resident_id <= 0) {
    header('Location: residents.php');
    exit;
}

$check = $conn->query("SELECT resident_id FROM barangay_residents WHERE resident_id = $resident_id");
if ($check->num_rows == 0) {
    header('Location: residents.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM barangay_residents WHERE resident_id = ?");
$stmt->bind_param("i", $resident_id);

if ($stmt->execute()) {
    header('Location: residents.php?deleted=1');
} else {
    header('Location: residents.php?error=1');
}
$stmt->close();
$conn->close();
?>