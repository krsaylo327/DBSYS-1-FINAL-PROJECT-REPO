<?php
include_once 'db_connect.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM nurses WHERE nurse_id = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: nurses_list.php");
exit();
?>