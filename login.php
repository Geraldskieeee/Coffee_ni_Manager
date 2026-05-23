<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize the email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // TODO: In the future, query your database here to check if the user exists 
    // and use password_verify() to check the password.
    
    // For now, if they enter any email, we log them in:
    if ($email) {
        $_SESSION['user_email'] = $email;
    }
    
    // Redirect back to the homepage
    header("Location: index.php");
    exit();
}
?>