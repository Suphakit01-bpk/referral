<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db_connect.php';

try {
    error_log("Starting save process"); // Debug log

    $json = file_get_contents('php://input');
    error_log("Received JSON: " . $json); // Debug log
    
    $data = json_decode($json, true);
    if ($data === null) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Validate required fields
    if (empty($data['nationalId']) || empty($data['fullName'])) {
        throw new Exception('Required fields are missing');
    }

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

    // Convert arrays to PostgreSQL array format
    $billing_type = isset($data['billing_type']) ? '{' . implode(',', $data['billing_type']) . '}' : null;
    $purpose = isset($data['purpose']) ? '{' . implode(',', $data['purpose']) . '}' : null;

    $sql = "INSERT INTO transfer_form (
        national_id, full_name_tf, hospital_tf, transfer_date, 
        company, address, phone, age, diagnosis, reason,
        status, billing_type, insurance_company, purpose,created_by,creator_hospital, approved_hospital
    ) VALUES (
        :national_id, :full_name_tf, :hospital_tf, :transfer_date,
        :company, :address, :phone, :age::integer, :diagnosis, :reason,
        'รอการอนุมัติ', :billing_type, :insurance_company, :purpose,:created_by,:creator_hospital, :approved_hospital
    ) RETURNING id";

    $stmt = $conn->prepare($sql);
    
    $params = [
        ':national_id' => $data['nationalId'],
        ':full_name_tf' => $data['fullName'],
        ':hospital_tf' => $data['hospital_tf'],
        ':transfer_date' => $data['transferDate'],
        ':company' => $data['company'] ?? null,
        ':address' => $data['address'] ?? null,
        ':phone' => $data['phone'] ?? null,
        ':age' => $data['age'] ?? null,
        ':diagnosis' => $data['diagnosis'] ?? null,
        ':reason' => $data['reason'] ?? null,
        ':billing_type' => $billing_type,
        ':insurance_company' => $data['insurance_company'] ?? null,
        ':purpose' => $purpose,
        ':created_by' => $creator,
        ':creator_hospital' => $creator_hospital,
        ':approved_hospital' => $data['approved_hospital'] ?? null
    ];


    
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            'success' => true,
            'id' => $result['id']
        ]);
    } else {
        throw new Exception('Failed to insert data');
    }

} catch (Exception $e) {
    error_log('Save error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

exit();
?>





