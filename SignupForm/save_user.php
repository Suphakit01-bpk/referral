<?php

require_once __DIR__ . '/../Database/db_connect.php';

use Database\Database;

try {
    // สร้าง instance ของ Database
    $database = new Database();
    
    // เชื่อมต่อ database
    $conn = $database->connect();

    // รับค่าและทำความสะอาดข้อมูล
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $fullname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
    $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);
    $hospital = filter_var($_POST['hospital'], FILTER_SANITIZE_STRING);

    // เข้ารหัสรหัสผ่าน
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ตรวจสอบค่าว่าง
    if (empty($username) || empty($password) || empty($fullname) || empty($role) || empty($hospital)) {
        throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
    }

    // ตรวจสอบว่า username ซ้ำหรือไม่
    $checkUser = $database->query("SELECT username FROM users WHERE username = $1", array($username));
    if (pg_num_rows($checkUser) > 0) {
        throw new Exception("ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว");
    }

    // เตรียม SQL query
    $query = "INSERT INTO users (username, password, fullname, role, hospital) 
              VALUES ($1, $2, $3, $4, $5)";
    
    // ทำการ execute query
    $result = $database->query($query, array(
        $username,
        $hashedPassword,
        $fullname,
        $role,
        $hospital
    ));

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'ลงทะเบียนสำเร็จ'
        ]);
    } else {
        throw new Exception("ไม่สามารถบันทึกข้อมูลได้");
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($database)) {
        $database->close();
    }
}
?>