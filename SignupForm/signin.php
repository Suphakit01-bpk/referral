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

// ถ้าไม่มี session จึงจะลบ session (สำหรับ logout)
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" type="image/x-icon" href="http://192.168.13.31/seedhelpdesk/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
</head>
<body>
    <div class="container">
        <h2><img src="http://192.168.13.31/seedhelpdesk/image/logo.jpg" alt="Logo"> Sign In</h2>
        <form id="loginForm" action="validate_login.php" method="POST">
            <div class="form-container">
                <div class="form">
                    <input type="text" id="username" name="username" class="form-input" autocomplete="off" placeholder="User/Id" required>
                    <label for="username" class="form-label">User/Id</label>
                </div>
                <div class="form">
                    <input type="password" id="password" name="password" class="form-input" autocomplete="off" placeholder="Password" required>
                    <label for="password" class="form-label">Password</label>
                </div>
                <button type="submit">เข้าสู่ระบบ</button>
                <a href="signup.php">ยังไม่มีบัญชีใช่ไหม? สมัครสมาชิก</a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: 'validate_login.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // เปลี่ยนเส้นทางตาม role
                            if(response.role === 'authorizer') {
                                window.location.href = '../dashboard/authorizer.php';
                            } else if(response.role === 'nurse') {
                                window.location.href = '../dashboard/user_nurse.php';
                            } else {
                                window.location.href = '../dashboard/user_register.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด!',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
