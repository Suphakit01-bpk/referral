<?php
session_start();

// Check if user is already logged in
if(isset($_SESSION['role'])) {
    // Redirect to main page or dashboard
    header("Location: SignupForm/signin.php");
} else {
    // Redirect to login page
    header("Location: SignupForm/signin.php");
}
exit();
?>