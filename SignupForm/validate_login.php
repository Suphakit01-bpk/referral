<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../Database/db_connect.php';

use Database\Database;

try {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        throw new Exception("กรุณากรอกข้อมูลให้ครบ");
    }

    $db = new Database();
    if (!$db->connect()) {
        throw new Exception($db->getMessage());
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $result = $db->query(
        "SELECT id, username, password, role, fullname, hospital FROM users WHERE username = $1",
        array($username)
    );

    if (!$result) {
        throw new Exception("เกิดข้อผิดพลาดในการค้นหาข้อมูล");
    }

    $user = pg_fetch_assoc($result);
    if (!$user) {
        throw new Exception("ไม่พบชื่อผู้ใช้นี้");
    }

    if (!password_verify($password, $user['password'])) {
        throw new Exception("รหัสผ่านไม่ถูกต้อง");
    }

    // Validate that hospital is not empty
    if (empty($user['hospital'])) {
        throw new Exception("ไม่พบข้อมูลโรงพยาบาลของผู้ใช้");
    }

    // ตรวจสอบข้อมูลโรงพยาบาลก่อนตั้งค่า session
    if (empty($user['hospital'])) {
        throw new Exception("กรุณาระบุโรงพยาบาลต้นสังกัดของผู้ใช้");
    }

    if (empty($user['fullname'])) {
        throw new Exception("กรุณาระบุชื่อ-นามสกุลของผู้ใช้");
    }

    // Normalize role to lowercase for consistent comparison
    $userRole = strtolower($user['role']);

    // Define authorized roles with correct spelling
    $authorized_roles = ['admin', 'editor', 'register', 'authorizer', 'nurse'];
    
    if (!in_array($userRole, array_map('strtolower', $authorized_roles))) {
        throw new Exception("บัญชีของคุณไม่มีสิทธิ์เข้าถึง ({$userRole})");
    }

    // Set session with additional info
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role']; // Keep original case
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['hospital'] = $user['hospital']; // Add hospital to session

    // Log session data for debugging
    error_log("Session data set - fullname: {$user['fullname']}, hospital: {$user['hospital']}");

    echo json_encode([
        'status' => 'success',
        'message' => 'เข้าสู่ระบบสำเร็จ',
        'role' => $user['role'],
        'hospital' => $user['hospital'],
        'fullname' => $user['fullname']
    ]);

} catch (Exception $e) {
    error_log("Login error for user {$username}: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?>