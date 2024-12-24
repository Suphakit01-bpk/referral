<?php
session_start();

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    // Redirect to main page or dashboard
    header("Location: form.php");
} else {
    // Redirect to login page
    header("Location: login.html");
}
exit();
?>