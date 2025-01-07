<?php
session_start();

// ถ้ามี session อยู่แล้ว ให้ redirect ไปหน้าที่เหมาะสม
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'authorizer':
            header('Location: ../dashboard/authorizer.php');
            exit();
        case 'nurse':
            header('Location: ../dashboard/user_nurse.php');
            exit();
        case 'register':
            header('Location: ../dashboard/user_register.php');
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
</head>
<body>
    <div class="container">
        <h2><img src="http://192.168.13.31/seedhelpdesk/image/logo.jpg" alt="Logo" class="responsive-logo"> Register</h2>
        <form id="registerForm" action="save_user.php" method="POST">
        <div class="form-container">
            <div class="form">
                <input type="text" id="username" name="username" class="form-input" autocomplete="off" placeholder="User/Id" required>
                <label for="username" class="form-label">User/Id</label>
            </div>
            <div class="form">
                <input type="password" id="password" name="password" class="form-input" autocomplete="off" placeholder="Password" required>
                <label for="password" class="form-label">Password</label>
            </div>
            <div class="form">
                <input type="text" id="fullname" name="fullname" class="form-input" autocomplete="off" placeholder="Full Name" required>
                <label for="fullname" class="form-label">Full Name</label>
            </div>
            <div class="form">
                <select name="role" id="role" required>
                    <option value="" disabled selected>กรุณาเลือก บทบาท</option>
                    <option value="register">Register</option>
                    <option value="authorizer">Authorizer</option>
                    <option value="nurse">Nurse</option>
                </select>
            </div>
            <div class="form">
                <select name="hospital" id="hospital" required>
                    <option value="" disabled selected>กรุณาเลือก โรงพยาบาล</option>
                    <option value="โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล">โรงพยาบาลบางปะกอก 9 อินเตอร์เนชั่นแนล</option>
                    <option value="โรงพยาบาลบางปะกอก 1">โรงพยาบาลบางปะกอก 1</option>
                    <option value="โรงพยาบาลบางปะกอก 3">โรงพยาบาลบางปะกอก 3</option>
                    <option value="โรงพยาบาลบางปะกอก 8">โรงพยาบาลบางปะกอก 8</option>
                    <option value="โรงพยาบาลบางปะกอก 2 รังสิต">โรงพยาบาลบางปะกอก 2 รังสิต</option>
                    <option value="โรงพยาบาลบางปะกอกสมุทรปราการ">โรงพยาบาลบางปะกอกสมุทรปราการ</option>
                    <option value="โรงพยาบาลปิยะเวท">โรงพยาบาลปิยะเวท</option>
                    <option value="โรงพยาบาลบางปะกอกอายุรเวช">โรงพยาบาลบางปะกอกอายุรเวช</option>
                </select>
            </div>
            <button type="submit">ลงทะเบียน</button>
            <a href="signin.php">มีบัญชีอยู่แล้วใช่ไหม? เข้าสู่ระบบ</a>
        </form>
        </div>
    </div>


    <!-- เพิ่ม scripts ต่อไปนี้ก่อน closing body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- เพิ่ม JavaScript สำหรับจัดการฟอร์ม -->
  <script>
    $(document).ready(function() {
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: 'save_user.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: response.message,
                            confirmButtonText: 'ตกลง'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                
                                window.location.href = 'signin.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด!',
                            text: response.message,
                            confirmButtonText: 'ตกลง'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'กรุณาตรวจสอบข้อมูลอีกครั้ง',
                        confirmButtonText: 'ตกลง'
                    });
                    console.error(xhr, status, error);
                }
            });
        });
    });
    </script>

  
</body>
</html>


