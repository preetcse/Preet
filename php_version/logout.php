<?php
require_once 'config.php';

// Clear all session data
session_unset();
session_destroy();

// Start a new session for the flash message
session_start();
setFlashMessage('You have been logged out successfully.', 'info');

// Redirect to login page
header('Location: login.php');
exit();
?>