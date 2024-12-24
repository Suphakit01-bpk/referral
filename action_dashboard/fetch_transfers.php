<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '..\db_connect.php';

try {
    // ตรวจสอบว่ามี hospital ใน session หรือไม่
    if (!isset($_SESSION['hospital']) || empty($_SESSION['hospital'])) {
        throw new Exception('ไม่พบข้อมูลโรงพยาบาล กรุณาเข้าสู่ระบบใหม่');
    }

    $userHospital = $_SESSION['hospital'];

    $sql = "SELECT 
        national_id, 
        full_name_tf, 
        hospital_tf,
        transfer_date::date,
        COALESCE(status, 'รอการอนุมัติ') as status,
        creator_hospital
    FROM transfer_form 
    WHERE creator_hospital = :creator_hospital 
    AND status != 'ยกเลิก'
    ORDER BY transfer_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':creator_hospital' => $userHospital]);
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates
    foreach ($data as &$row) {
        if (isset($row['transfer_date'])) {
            $date = new DateTime($row['transfer_date']);
            $row['transfer_date'] = $date->format('m/d/Y');
        }
    }

    echo json_encode([
        'success' => true, 
        'data' => $data,
        'hospital' => $userHospital // ส่งชื่อโรงพยาบาลกลับไปด้วย
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching data'
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn = null;
?>