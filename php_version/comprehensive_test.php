<?php
// Comprehensive test file to check all critical functions and includes
// Upload this file and visit it to test everything works

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Comprehensive Function Test</h1>";
echo "<p>Testing all critical functions and includes...</p>";

// Test 1: Config file
echo "<h2>1. Config File Test</h2>";
try {
    include_once 'config.php';
    echo "✅ config.php loaded successfully<br>";
    
    if (defined('DB_HOST')) {
        echo "✅ Database constants defined<br>";
    } else {
        echo "❌ Database constants missing<br>";
    }
    
    if (defined('GOOGLE_CLIENT_ID')) {
        echo "✅ Google Drive constants defined<br>";
    } else {
        echo "❌ Google Drive constants missing<br>";
    }
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}

// Test 2: Functions file
echo "<h2>2. Functions File Test</h2>";
try {
    include_once 'functions.php';
    echo "✅ functions.php loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Functions error: " . $e->getMessage() . "<br>";
}

// Test 3: Critical Functions
echo "<h2>3. Critical Functions Test</h2>";
$functions_to_test = [
    'getDBConnection',
    'createCustomer', 
    'getAllCustomers',
    'getDashboardStats',
    'getRecentTransactions',
    'getAllTransactions',
    'getAllPayments',
    'userExists',
    'authenticateUser',
    'createUser',
    'validatePhone',
    'sanitizeInput',
    'isLoggedIn',
    'requireLogin',
    'setFlashMessage',
    'getFlashMessage'
];

foreach ($functions_to_test as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() missing<br>";
    }
}

// Test 4: Database Connection
echo "<h2>4. Database Connection Test</h2>";
try {
    $conn = getDBConnection();
    if ($conn) {
        echo "✅ Database connection successful<br>";
        
        // Test table existence
        $tables = ['users', 'customers', 'transactions', 'payments'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Table '$table' exists<br>";
            } else {
                echo "⚠️ Table '$table' missing (will be created on first use)<br>";
            }
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 5: Page Include Test
echo "<h2>5. Page Include Test</h2>";
$pages_to_test = [
    'setup.php' => 'Setup Page',
    'login.php' => 'Login Page', 
    'index.php' => 'Dashboard'
];

foreach ($pages_to_test as $page => $name) {
    echo "<h3>Testing $name ($page):</h3>";
    
    // Check if file exists
    if (file_exists($page)) {
        echo "✅ File exists<br>";
        
        // Try to include without executing (capture output)
        ob_start();
        $error = false;
        try {
            // Read file content to check for basic issues
            $content = file_get_contents($page);
            if (strpos($content, "require_once 'config.php'") !== false) {
                echo "✅ Includes config.php<br>";
            } else {
                echo "❌ Missing config.php include<br>";
            }
            
            if (strpos($content, "require_once 'functions.php'") !== false) {
                echo "✅ Includes functions.php<br>";
            } else {
                echo "❌ Missing functions.php include<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Error testing $page: " . $e->getMessage() . "<br>";
            $error = true;
        }
        ob_end_clean();
        
        if (!$error) {
            echo "✅ $name should work<br>";
        }
    } else {
        echo "❌ File missing<br>";
    }
    echo "<br>";
}

// Test 6: Google Drive Functions
echo "<h2>6. Google Drive Functions Test</h2>";
$google_functions = [
    'getGoogleAuthUrl',
    'exchangeCodeForTokens', 
    'saveGoogleTokens',
    'getGoogleTokens',
    'refreshGoogleToken',
    'getValidAccessToken',
    'uploadToGoogleDrive',
    'isGoogleDriveConnected',
    'disconnectGoogleDrive'
];

foreach ($google_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() missing<br>";
    }
}

echo "<h2>🎯 Summary</h2>";
echo "<p><strong>If you see mostly ✅ above, your website should work!</strong></p>";
echo "<p>Try visiting:</p>";
echo "<ul>";
echo "<li><a href='setup.php' target='_blank'>setup.php</a> - First time setup</li>";
echo "<li><a href='login.php' target='_blank'>login.php</a> - Login page</li>";
echo "<li><a href='index.php' target='_blank'>index.php</a> - Dashboard</li>";
echo "</ul>";

echo "<h2>🔧 If Still Getting HTTP 500 Errors:</h2>";
echo "<ol>";
echo "<li><strong>Check file uploads:</strong> Make sure ALL PHP files are uploaded</li>";
echo "<li><strong>Check file permissions:</strong> PHP files should be 644</li>";
echo "<li><strong>Check database setup:</strong> Make sure your InfinityFree database is created</li>";
echo "<li><strong>Contact support:</strong> If everything looks good, contact InfinityFree support</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>Test completed! You can delete this file once everything works.</em></p>";
?>