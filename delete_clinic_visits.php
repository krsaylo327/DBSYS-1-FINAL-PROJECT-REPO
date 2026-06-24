<?php
require_once 'db_connect.php';

$id = $_GET['id'] ?? null;
if ($id) {
    // First, delete all visit medicines for this clinic visit
    $stmt_medicines = $pdo->prepare("DELETE FROM visit_medicines WHERE visit_id = :id");
    $stmt_medicines->execute([':id' => $id]);
    
    // Then, delete the clinic visit
    $stmt_visit = $pdo->prepare("DELETE FROM clinic_visits WHERE visit_id = :id");
    $stmt_visit->execute([':id' => $id]);
}

header('Location: clinic_visits_list.php');
exit();
?>