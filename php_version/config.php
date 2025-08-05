<?php
/**
 * Amarjit Electrical Store - Configuration File
 * PHP Version for InfinityFree Hosting
 * Supports MySQL database and Google Drive integration
 */

// Start session for user authentication
session_start();

// Database Configuration for InfinityFree
// Replace these with your actual InfinityFree database details
define('DB_HOST', 'sql200.infinityfree.com'); // InfinityFree MySQL host
define('DB_USER', 'if0_youruser'); // Your InfinityFree database username
define('DB_PASS', 'your_password'); // Your InfinityFree database password
define('DB_NAME', 'if0_youruser_electrical_store'); // Your database name

// Application Configuration
define('APP_NAME', 'Amarjit Electrical Store');
define('APP_VERSION', '1.0.0');
define('SITE_URL', 'https://legendary-preet.ct.ws'); // Your custom domain

// Security Configuration
define('SECRET_KEY', 'amarjit-electrical-store-secret-key-2024');
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);

// Google Drive API Configuration
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_CLIENT_ID', ''); // Your Google OAuth Client ID
define('GOOGLE_CLIENT_SECRET', ''); // Your Google OAuth Client Secret
define('GOOGLE_REDIRECT_URI', SITE_URL . '/google_callback.php');

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB max file size
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', 'uploads/');

// Timezone Configuration
date_default_timezone_set('Asia/Kolkata');

// Error Reporting (Set to 0 for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Database Connection Function
 * Returns mysqli connection object
 */
function getDBConnection() {
    static $connection = null;
    
    if ($connection === null) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }
        
        // Set charset to UTF-8
        $connection->set_charset("utf8");
    }
    
    return $connection;
}

/**
 * Initialize Database Tables
 * Creates all necessary tables if they don't exist
 */
function initializeDatabase() {
    $conn = getDBConnection();
    
    // Users table
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(80) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Customers table
    $sql_customers = "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) UNIQUE NOT NULL,
        address TEXT,
        total_debt DECIMAL(10,2) DEFAULT 0.00,
        created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Transactions table
    $sql_transactions = "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        transaction_date DATE NOT NULL,
        transaction_type VARCHAR(20) DEFAULT 'purchase',
        bill_image_url VARCHAR(500),
        bill_image_id VARCHAR(100),
        created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    )";
    
    // Payments table
    $sql_payments = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_date DATE NOT NULL,
        notes TEXT,
        created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    )";
    
    // Execute table creation
    $tables = [
        'users' => $sql_users,
        'customers' => $sql_customers,
        'transactions' => $sql_transactions,
        'payments' => $sql_payments
    ];
    
    foreach ($tables as $table => $sql) {
        if (!$conn->query($sql)) {
            die("Error creating table $table: " . $conn->error);
        }
    }
    
    return true;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Clean and validate input
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format currency for display
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Generate secure random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate phone number (Indian format)
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Google Drive Helper Functions
 */
function uploadToGoogleDrive($filePath, $fileName) {
    // This will be implemented with Google Drive API
    // For now, return dummy data
    if (GOOGLE_DRIVE_ENABLED) {
        // TODO: Implement Google Drive upload
        return [
            'id' => 'dummy_file_id_' . time(),
            'url' => 'https://drive.google.com/file/d/dummy_file_id_' . time() . '/view'
        ];
    }
    return null;
}

// Initialize database on first load
if (!isset($_SESSION['db_initialized'])) {
    initializeDatabase();
    $_SESSION['db_initialized'] = true;
}

// Include additional helper functions
require_once 'functions.php';
?>