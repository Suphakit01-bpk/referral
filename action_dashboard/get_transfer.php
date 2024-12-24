<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '..\db_connect.php';

try {
    $national_id = $_GET['national_id'] ?? '';
    
    if (empty($national_id)) {
        throw new Exception('National ID is required');
    }

    $sql = "SELECT *, approved_hospital FROM transfer_form WHERE national_id = :national_id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':national_id' => $national_id]);
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        throw new Exception('Record not found');
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn = null;
?>
