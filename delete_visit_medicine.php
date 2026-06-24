<?php
include_once 'db_connect.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM visit_medicines WHERE visit_medicines_id = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: visit_medicines_list.php");
exit();
?>