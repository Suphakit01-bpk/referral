<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db_connect.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null) {
        throw new Exception('Invalid JSON data');
    }

    $sql = "UPDATE transfer_form SET 
            full_name_tf = :full_name_tf,
            hospital_tf = :hospital_tf,
            transfer_date = :transfer_date,
            company = :company,
            address = :address,
            phone = :phone,
            age = :age::integer,
            diagnosis = :diagnosis,
            reason = :reason,
            billing_type = :billing_type,
            insurance_company = :insurance_company,
            purpose = :purpose,
            approved_hospital = :approved_hospital
            WHERE national_id = :national_id";

    $stmt = $conn->prepare($sql);

    // แปลง arrays เป็น string
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
        ':approved_hospital' => !empty($data['approved_hospital']) ? $data['approved_hospital'] : null
    ];

    $result = $stmt->execute($params);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update data');
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