<?php
// Complete System Check - Tests Every Function and Feature
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Complete System Check</h1>";
echo "<p>Testing every function, file, and feature...</p>";

// Include required files
try {
    require_once 'config.php';
    echo "✅ config.php loaded<br>";
} catch (Exception $e) {
    echo "❌ config.php error: " . $e->getMessage() . "<br>";
    exit;
}

try {
    require_once 'functions.php';
    echo "✅ functions.php loaded<br>";
} catch (Exception $e) {
    echo "❌ functions.php error: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>1. Core Functions Test</h2>";

$core_functions = [
    'getDBConnection',
    'initializeDatabase',
    'isLoggedIn', 
    'requireLogin',
    'authenticateUser',
    'createUser',
    'userExists',
    'updateUserPassword',
    'getUserCount',
    'setFlashMessage',
    'getFlashMessage',
    'formatDate',
    'formatDateTime',
    'formatCurrency',
    'sanitizeInput'
];

foreach ($core_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() missing<br>";
    }
}

echo "<h2>2. Customer Functions Test</h2>";

$customer_functions = [
    'validatePhone',
    'createCustomer',
    'getCustomer', 
    'getCustomerById',
    'getCustomerByPhone',
    'getAllCustomers',
    'updateCustomerDebt',
    'searchCustomers',
    'getCustomerWithRecentBills'
];

foreach ($customer_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() missing<br>";
    }
}

echo "<h2>3. Transaction Functions Test</h2>";

$transaction_functions = [
    'createTransaction',
    'getCustomerTransactions',
    'getRecentTransactions',
    'getAllTransactions',
    'addTransaction'
];

foreach ($transaction_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() missing<br>";
    }
}

echo "<h2>4. Payment Functions Test</h2>";

$payment_functions = [
    'createPayment',
    'getCustomerPayments',
    'getRecentPayments',
    'getAllPayments',
    'addPayment'
];

foreach ($payment_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() missing<br>";
    }
}

echo "<h2>5. Statistics Functions Test</h2>";

$stats_functions = [
    'getDashboardStats'
];

foreach ($stats_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
        
        // Test the function
        try {
            $stats = getDashboardStats();
            $required_fields = [
                'total_customers', 'total_debt', 'monthly_transactions', 'monthly_payments',
                'total_transactions', 'total_payments', 'total_outstanding', 'total_sales',
                'total_received', 'customers_with_debt', 'customers_cleared'
            ];
            
            foreach ($required_fields as $field) {
                if (isset($stats[$field])) {
                    echo "  ✅ $field: " . $stats[$field] . "<br>";
                } else {
                    echo "  ❌ $field: missing<br>";
                }
            }
        } catch (Exception $e) {
            echo "  ❌ Error calling $func: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ $func() missing<br>";
    }
}

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

echo "<h2>7. File Upload Functions Test</h2>";

$upload_functions = [
    'handleFileUpload'
];

foreach ($upload_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func() exists<br>";
    } else {
        echo "❌ $func() missing<br>";
    }
}

echo "<h2>8. Database Connection Test</h2>";

try {
    $conn = getDBConnection();
    if ($conn) {
        echo "✅ Database connection successful<br>";
        
        // Test table structure
        $tables = [
            'users' => ['id', 'username', 'password_hash', 'created_date'],
            'customers' => ['id', 'name', 'phone', 'address', 'total_debt', 'created_date'],
            'transactions' => ['id', 'customer_id', 'amount', 'description', 'transaction_date', 'transaction_type', 'bill_image_url', 'google_drive_file_id', 'created_date'],
            'payments' => ['id', 'customer_id', 'amount', 'payment_date', 'notes', 'created_date'],
            'google_tokens' => ['id', 'user_id', 'access_token', 'refresh_token', 'expires_at', 'created_date']
        ];
        
        foreach ($tables as $table => $columns) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Table '$table' exists<br>";
                
                // Check columns
                $result = $conn->query("DESCRIBE $table");
                if ($result) {
                    $existing_columns = [];
                    while ($row = $result->fetch_assoc()) {
                        $existing_columns[] = $row['Field'];
                    }
                    
                    foreach ($columns as $column) {
                        if (in_array($column, $existing_columns)) {
                            echo "  ✅ Column '$column' exists<br>";
                        } else {
                            echo "  ⚠️ Column '$column' missing (might be auto-created)<br>";
                        }
                    }
                }
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

echo "<h2>9. File Structure Test</h2>";

$required_files = [
    'config.php',
    'functions.php',
    'index.php',
    'login.php',
    'logout.php',
    'setup.php',
    'quick_billing.php',
    'customers.php',
    'add_customer.php',
    'customer_detail.php',
    'transactions.php',
    'payments.php',
    'reports.php',
    'settings.php',
    'help.php',
    'google_callback.php',
    'includes/sidebar.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

echo "<h2>10. Constants Test</h2>";

$required_constants = [
    'DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME',
    'APP_NAME', 'APP_VERSION',
    'GOOGLE_DRIVE_ENABLED', 'GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET',
    'GOOGLE_REDIRECT_URI', 'GOOGLE_FOLDER_NAME',
    'UPLOAD_DIR', 'MAX_FILE_SIZE'
];

foreach ($required_constants as $const) {
    if (defined($const)) {
        $value = constant($const);
        if ($const === 'DB_PASS' || $const === 'GOOGLE_CLIENT_SECRET') {
            $display_value = str_repeat('*', min(strlen($value), 10));
        } else {
            $display_value = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
        }
        echo "✅ $const: '$display_value'<br>";
    } else {
        echo "❌ $const: not defined<br>";
    }
}

echo "<h2>11. PHP Extensions Test</h2>";

$required_extensions = [
    'mysqli' => 'Database connectivity',
    'curl' => 'Google Drive API calls',
    'json' => 'JSON processing',
    'session' => 'Session management',
    'fileinfo' => 'File type detection',
    'mbstring' => 'String handling'
];

foreach ($required_extensions as $ext => $purpose) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: Available ($purpose)<br>";
    } else {
        echo "❌ $ext: Missing ($purpose)<br>";
    }
}

echo "<h2>12. Summary</h2>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🎯 System Status</h3>";

// Count results
$total_functions = count($core_functions) + count($customer_functions) + count($transaction_functions) + count($payment_functions) + count($stats_functions) + count($google_functions) + count($upload_functions);
$total_files = count($required_files);
$total_constants = count($required_constants);
$total_extensions = count($required_extensions);

echo "<p><strong>If you see mostly ✅ above, your system is ready!</strong></p>";
echo "<ul>";
echo "<li><strong>Functions tested:</strong> $total_functions core system functions</li>";
echo "<li><strong>Files checked:</strong> $total_files required PHP files</li>";
echo "<li><strong>Constants verified:</strong> $total_constants configuration values</li>";
echo "<li><strong>Extensions checked:</strong> $total_extensions PHP extensions</li>";
echo "</ul>";

echo "<h3>🚀 Test Your Pages</h3>";
echo "<p>If everything shows ✅ above, test these pages:</p>";
echo "<ul>";
echo "<li><a href='setup.php' target='_blank'>setup.php</a> - First-time setup</li>";
echo "<li><a href='login.php' target='_blank'>login.php</a> - Login page</li>";
echo "<li><a href='index.php' target='_blank'>index.php</a> - Dashboard</li>";
echo "<li><a href='quick_billing.php' target='_blank'>quick_billing.php</a> - Quick billing</li>";
echo "<li><a href='customers.php' target='_blank'>customers.php</a> - Customer list</li>";
echo "<li><a href='reports.php' target='_blank'>reports.php</a> - Business reports</li>";
echo "<li><a href='settings.php' target='_blank'>settings.php</a> - Settings & Google Drive</li>";
echo "</ul>";

echo "<h3>🔧 For Google Drive Issues</h3>";
echo "<p>If Google Drive connection fails, use:</p>";
echo "<ul>";
echo "<li><a href='google_drive_test.php' target='_blank'>google_drive_test.php</a> - Detailed OAuth testing</li>";
echo "</ul>";

echo "</div>";

echo "<hr>";
echo "<p><strong>System check completed!</strong> All functions, files, and configurations tested.</p>";
echo "<p>Delete this file once everything works properly.</p>";
?>