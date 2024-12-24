<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '..\db_connect.php';

try {
    $sql = "SELECT 
        national_id, 
        full_name_tf, 
        hospital_tf, 
        transfer_date::date,
        status,
        updated_at::date as cancelled_date
    FROM transfer_form 
    WHERE status = 'ยกเลิก'
    ORDER BY updated_at DESC";
    
    $stmt = $conn->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates
    foreach ($data as &$row) {
        if (isset($row['transfer_date'])) {
            $date = new DateTime($row['transfer_date']);
            $row['transfer_date'] = $date->format('m/d/Y');
        }
        if (isset($row['cancelled_date'])) {
            $date = new DateTime($row['cancelled_date']);
            $row['cancelled_date'] = $date->format('m/d/Y');
        }
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching data'
    ]);
}

$conn = null;
?>