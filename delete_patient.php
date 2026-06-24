<?php
include_once 'db_connect.php';

$id = $_GET['id'] ?? null;
if ($id) {
    // First, delete all clinic visits for this patient
    $stmt_visits = $pdo->prepare("DELETE FROM clinic_visits WHERE patient_id = :id");
    $stmt_visits->execute([':id' => $id]);
    
    // Then, delete the patient
    $stmt_patient = $pdo->prepare("DELETE FROM patients WHERE patient_id = :id");
    $stmt_patient->execute([':id' => $id]);
}

header("Location: patients_list.php");
exit();
?>