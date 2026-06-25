<?php
include_once 'db_connect.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM medicines WHERE medicine_id = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: medicines_list.php");
exit();
?>