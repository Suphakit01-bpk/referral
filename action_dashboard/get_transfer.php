<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../db_connect.php';

try {
    $nationalId = $_GET['national_id'] ?? null;
    $id = $_GET['id'] ?? null;

    if (!$nationalId || !$id) {
        throw new Exception('Missing required parameters');
    }

    $sql = "SELECT * FROM transfer_form WHERE national_id = :national_id AND id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':national_id' => $nationalId,
        ':id' => $id
    ]);

    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        echo json_encode([
            'success' => true,
            'data' => $record
        ]);
    } else {
        throw new Exception('Record not found');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn = null;
?>
