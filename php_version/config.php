<?php
/**
 * Configuration file for Amarjit Electrical Store Management System
 * Replace the database credentials below with your actual InfinityFree details
 */

// Prevent direct access
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// Database configuration for InfinityFree
// IMPORTANT: Replace these with your actual InfinityFree database details!
// You can find these in your InfinityFree control panel under "MySQL Databases"

define('DB_HOST', 'sql200.infinityfree.com'); // Your InfinityFree MySQL host (usually sqlXXX.infinityfree.com)
define('DB_USER', 'if0_37114663'); // Your InfinityFree database username (format: if0_XXXXXXXX)
define('DB_PASS', 'YourDatabasePassword'); // Your InfinityFree database password
define('DB_NAME', 'if0_37114663_electrical_store'); // Your database name (format: if0_XXXXXXXX_electrical_store)

// Application settings
define('APP_NAME', 'Amarjit Electrical Store');
define('APP_VERSION', '2.0.0');

// Google Drive settings
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_CLIENT_ID', ''); // Your Google OAuth Client ID (from Google Cloud Console)
define('GOOGLE_CLIENT_SECRET', ''); // Your Google OAuth Client Secret (from Google Cloud Console)
define('GOOGLE_REDIRECT_URI', 'https://legendary-preet.ct.ws/google_callback.php'); // OAuth redirect URI
define('GOOGLE_FOLDER_NAME', 'Amarjit Electrical Store Bills'); // Folder name in Google Drive

// Upload settings
define('UPLOAD_DIR', 'uploads/'); // Local temporary upload directory
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB max file size

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}

// Database connection function
function getDBConnection() {
    static $connection = null;
    
    if ($connection === null) {
        try {
            $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($connection->connect_error) {
                throw new Exception("Connection failed: " . $connection->connect_error);
            }
            
            // Set charset to handle special characters properly
            $connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            // Don't die immediately - let the application handle it gracefully
            error_log("Database connection error: " . $e->getMessage());
            return false;
        }
    }
    
    return $connection;
}

// Initialize database tables
function initializeDatabase() {
    $conn = getDBConnection();
    
    if (!$conn) {
        return false;
    }
    
    try {
        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error creating users table: " . $conn->error);
        }
        
        // Customers table
        $sql = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) UNIQUE NOT NULL,
            address TEXT,
            total_debt DECIMAL(10,2) DEFAULT 0.00,
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error creating customers table: " . $conn->error);
        }
        
        // Transactions table
        $sql = "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            description TEXT,
            bill_image_url TEXT,
            google_drive_file_id VARCHAR(255),
            transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error creating transactions table: " . $conn->error);
        }
        
        // Payments table
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            notes TEXT,
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error creating payments table: " . $conn->error);
        }
        
        // Google Drive tokens table
        $sql = "CREATE TABLE IF NOT EXISTS google_drive_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            access_token TEXT NOT NULL,
            refresh_token TEXT,
            expires_at TIMESTAMP NULL,
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error creating google_drive_tokens table: " . $conn->error);
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function authenticateUser($username, $password) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    if (!$stmt) return false;
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password_hash'])) {
            return $user;
        }
    }
    
    return false;
}

function createUser($username, $password) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    if (!$stmt) return false;
    
    $stmt->bind_param("ss", $username, $password_hash);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    
    return false;
}

function updateUserPassword($user_id, $new_password) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    if (!$stmt) return false;
    
    $stmt->bind_param("si", $password_hash, $user_id);
    
    return $stmt->execute();
}

function getUserCount() {
    $conn = getDBConnection();
    if (!$conn) return 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if (!$result) return 0;
    
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Flash message functions
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Utility functions
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Google Drive functions
function getGoogleAuthUrl() {
    if (!GOOGLE_DRIVE_ENABLED || !GOOGLE_CLIENT_ID) {
        return null;
    }
    
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'scope' => 'https://www.googleapis.com/auth/drive.file',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
}

function exchangeCodeForTokens($code) {
    if (!GOOGLE_DRIVE_ENABLED || !GOOGLE_CLIENT_ID || !GOOGLE_CLIENT_SECRET) {
        return false;
    }
    
    $data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'code' => $code
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

function saveGoogleTokens($user_id, $tokens) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $expires_at = null;
    if (isset($tokens['expires_in'])) {
        $expires_at = date('Y-m-d H:i:s', time() + $tokens['expires_in']);
    }
    
    // Delete existing tokens
    $stmt = $conn->prepare("DELETE FROM google_drive_tokens WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    // Insert new tokens
    $stmt = $conn->prepare("INSERT INTO google_drive_tokens (user_id, access_token, refresh_token, expires_at) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;
    
    $stmt->bind_param("isss", $user_id, $tokens['access_token'], $tokens['refresh_token'] ?? null, $expires_at);
    
    return $stmt->execute();
}

function getGoogleTokens($user_id) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stmt = $conn->prepare("SELECT * FROM google_drive_tokens WHERE user_id = ? ORDER BY created_date DESC LIMIT 1");
    if (!$stmt) return null;
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function refreshGoogleToken($refresh_token) {
    if (!GOOGLE_DRIVE_ENABLED || !GOOGLE_CLIENT_ID || !GOOGLE_CLIENT_SECRET) {
        return false;
    }
    
    $data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'refresh_token' => $refresh_token,
        'grant_type' => 'refresh_token'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

function getValidAccessToken($user_id) {
    $tokens = getGoogleTokens($user_id);
    if (!$tokens) {
        return null;
    }
    
    // Check if token is expired
    if ($tokens['expires_at'] && strtotime($tokens['expires_at']) <= time()) {
        // Try to refresh the token
        if ($tokens['refresh_token']) {
            $new_tokens = refreshGoogleToken($tokens['refresh_token']);
            if ($new_tokens) {
                // Update the access token
                $new_tokens['refresh_token'] = $tokens['refresh_token']; // Keep the existing refresh token
                saveGoogleTokens($user_id, $new_tokens);
                return $new_tokens['access_token'];
            }
        }
        return null;
    }
    
    return $tokens['access_token'];
}

function createGoogleDriveFolder($access_token, $folder_name) {
    $metadata = [
        'name' => $folder_name,
        'mimeType' => 'application/vnd.google-apps.folder'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/drive/v3/files');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($metadata));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

function findGoogleDriveFolder($access_token, $folder_name) {
    $query = "name='" . addslashes($folder_name) . "' and mimeType='application/vnd.google-apps.folder' and trashed=false";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/drive/v3/files?q=' . urlencode($query));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (!empty($data['files'])) {
            return $data['files'][0];
        }
    }
    
    return null;
}

function uploadToGoogleDrive($filePath, $fileName, $user_id) {
    if (!GOOGLE_DRIVE_ENABLED) {
        return null;
    }
    
    $access_token = getValidAccessToken($user_id);
    if (!$access_token) {
        return null;
    }
    
    // Find or create the folder
    $folder = findGoogleDriveFolder($access_token, GOOGLE_FOLDER_NAME);
    if (!$folder) {
        $folder = createGoogleDriveFolder($access_token, GOOGLE_FOLDER_NAME);
        if (!$folder) {
            return null;
        }
    }
    
    // Upload the file
    $metadata = [
        'name' => $fileName,
        'parents' => [$folder['id']]
    ];
    
    $boundary = uniqid();
    $delimiter = '-------' . $boundary;
    $close_delim = "\r\n--{$delimiter}--\r\n";
    
    $post_data = "--{$delimiter}\r\n";
    $post_data .= "Content-Type: application/json\r\n\r\n";
    $post_data .= json_encode($metadata) . "\r\n";
    $post_data .= "--{$delimiter}\r\n";
    $post_data .= "Content-Type: " . mime_content_type($filePath) . "\r\n\r\n";
    $post_data .= file_get_contents($filePath);
    $post_data .= $close_delim;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: multipart/related; boundary="' . $delimiter . '"'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $file_data = json_decode($response, true);
        return [
            'id' => $file_data['id'],
            'url' => 'https://drive.google.com/file/d/' . $file_data['id'] . '/view'
        ];
    }
    
    return null;
}

function isGoogleDriveConnected($user_id) {
    $tokens = getGoogleTokens($user_id);
    return $tokens !== null;
}

function disconnectGoogleDrive($user_id) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $stmt = $conn->prepare("DELETE FROM google_drive_tokens WHERE user_id = ?");
    if (!$stmt) return false;
    
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// Only try to initialize database if we can connect
if (getDBConnection()) {
    initializeDatabase();
}

?>