<?php
session_start();
// Remove all session variables
session_unset();
// Destroy the session
session_destroy();
// Redirect back to the homepage
header("Location: index.php");
exit();
?>