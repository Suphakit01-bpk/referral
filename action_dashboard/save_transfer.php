<?php
session_start(); // Add session start
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db_connect.php';

// Debug log
error_log("Session data check - fullname: " . (isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'not set') . 
         ", hospital: " . (isset($_SESSION['hospital']) ? $_SESSION['hospital'] : 'not set'));

try {
    // Add debug logging
    error_log('Received approved_hospital: ' . ($data['approved_hospital'] ?? 'not set'));
    
    // Validate session data
    if (!isset($_SESSION['fullname']) || empty($_SESSION['fullname'])) {
        throw new Exception('ไม่พบข้อมูลผู้ใช้ กรุณาเข้าสู่ระบบใหม่');
    }

    if (!isset($_SESSION['hospital']) || empty($_SESSION['hospital'])) {
        throw new Exception('ไม่พบข้อมูลโรงพยาบาล กรุณาเข้าสู่ระบบใหม่');
    }

    // Get creator info from session
    $creator = $_SESSION['fullname'];
    $creator_hospital = $_SESSION['hospital'];

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null) {
        throw new Exception('Invalid JSON data');
    }

    // Get creator info from session
    $creator = $_SESSION['fullname'];
    $creator_hospital = $_SESSION['hospital'];

    // เตรียมคำสั่ง SQL with creator fields
    $sql = "INSERT INTO transfer_form (
        national_id, 
        full_name_tf, 
        hospital_tf, 
        transfer_date,
        company,
        address,
        phone,
        age,
        diagnosis,
        reason,
        status,
        billing_type,
        insurance_company,
        purpose,
        created_by,
        creator_hospital,
        approved_hospital
    ) VALUES (
        :national_id,
        :full_name_tf,
        :hospital_tf,
        :transfer_date,
        :company,
        :address,
        :phone,
        :age::integer,
        :diagnosis,
        :reason,
        'รอการอนุมัติ',
        :billing_type,
        :insurance_company,
        :purpose,
        :created_by,
        :creator_hospital,
        :approved_hospital
    )";

    $stmt = $conn->prepare($sql);
    
    // แปลง arrays เป็น string สำหรับ PostgreSQL
    $billing_type = isset($data['billing_type']) ? '{' . implode(',', $data['billing_type']) . '}' : null;
    $purpose = isset($data['purpose']) ? '{' . implode(',', $data['purpose']) . '}' : null;

    $params = [
        ':national_id' => $data['nationalId'],
        ':full_name_tf' => $data['fullName'],
        ':hospital_tf' => $data['hospital_tf'],
        ':transfer_date' => $data['transferDate'],
        ':company' => empty($data['company']) ? null : $data['company'],
        ':address' => empty($data['address']) ? null : $data['address'],
        ':phone' => empty($data['phone']) ? null : $data['phone'],
        ':age' => empty($data['age']) ? null : $data['age'],
        ':diagnosis' => empty($data['diagnosis']) ? null : $data['diagnosis'],
        ':reason' => empty($data['reason']) ? null : $data['reason'],
        ':billing_type' => $billing_type,
        ':insurance_company' => empty($data['insurance_company']) ? null : $data['insurance_company'],
        ':purpose' => $purpose,
        ':created_by' => $creator,
        ':creator_hospital' => $creator_hospital,
        ':approved_hospital' => !empty($data['approved_hospital']) ? $data['approved_hospital'] : null
    ];

    $result = $stmt->execute($params);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to insert data');
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