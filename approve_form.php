<?php
session_start();
include 'Database\db_connect.php';
use Database\Database;

// ตรวจสอบว่ามีการ login หรือไม่
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// รับค่า JSON จาก request
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

try {
    $db = new Database();
    $pdo = $db->connect();

    // เพิ่ม error logging
    error_log("Attempting to update form ID: " . $id);
    error_log("User approving: " . $_SESSION['fullname']);

    // แก้ไข query และเพิ่ม error handling
    $query = "UPDATE transfer_form 
              SET status = 'อนุมัติ', 
                  approved_by = $1, 
                  approved_date = CURRENT_TIMESTAMP 
              WHERE id = $2 
              RETURNING id"; // เพิ่ม RETURNING เพื่อตรวจสอบการอัพเดท

    $result = pg_query_params($pdo, $query, array($_SESSION['fullname'], $id));

    if ($result) {
        $row = pg_fetch_assoc($result);
        if ($row) {
            error_log("Successfully updated form ID: " . $row['id']);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            error_log("No rows were updated for ID: " . $id);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No rows were updated']);
        }
    } else {
        $error = pg_last_error($pdo);
        error_log("Database error: " . $error);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $error]);
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ปิดการเชื่อมต่อ
if (isset($pdo)) {
    pg_close($pdo);
}
?> 