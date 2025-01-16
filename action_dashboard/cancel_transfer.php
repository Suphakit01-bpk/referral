<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '..\db_connect.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null) {
        throw new Exception('Invalid JSON data');
    }

    $sql = "UPDATE transfer_form SET status = 'ยกเลิก' WHERE national_id = :national_id AND id = :id";
    $stmt = $conn->prepare($sql);
    
    $result = $stmt->execute([
        ':national_id' => $data['nationalId'],
        ':id' => $data['id']
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to cancel transfer');
    }

} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn = null;
?>