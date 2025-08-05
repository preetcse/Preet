<?php
// Simple error check file to diagnose HTTP 500 errors
// Upload this file to your website and visit it to see what's wrong

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>🔧 Error Diagnostic Tool</h1>";
echo "<p>This tool will help identify what's causing the HTTP 500 error.</p>";

echo "<h2>1. PHP Version Check</h2>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h2>2. Required Extensions</h2>";
$required_extensions = ['mysqli', 'curl', 'json'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ Available" : "❌ Missing";
    echo "$ext: $status<br>";
}

echo "<h2>3. File Permissions Check</h2>";
$files_to_check = ['config.php', 'functions.php', 'index.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "$file: ✅ Exists (Permissions: $perms)<br>";
    } else {
        echo "$file: ❌ Missing<br>";
    }
}

echo "<h2>4. Directory Check</h2>";
if (!file_exists('uploads')) {
    if (mkdir('uploads', 0755, true)) {
        echo "uploads/: ✅ Created successfully<br>";
    } else {
        echo "uploads/: ❌ Failed to create<br>";
    }
} else {
    echo "uploads/: ✅ Already exists<br>";
}

echo "<h2>5. Config File Test</h2>";
try {
    if (file_exists('config.php')) {
        echo "config.php: ✅ File exists<br>";
        
        // Try to include config file
        ob_start();
        include_once 'config.php';
        $config_output = ob_get_clean();
        
        if (empty($config_output)) {
            echo "config.php: ✅ Loads without errors<br>";
        } else {
            echo "config.php: ⚠️ Generated output: " . htmlspecialchars($config_output) . "<br>";
        }
        
        // Check if constants are defined
        $constants = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
        foreach ($constants as $const) {
            if (defined($const)) {
                $value = constant($const);
                $display_value = ($const === 'DB_PASS') ? str_repeat('*', strlen($value)) : $value;
                echo "$const: ✅ Defined as '$display_value'<br>";
            } else {
                echo "$const: ❌ Not defined<br>";
            }
        }
        
    } else {
        echo "config.php: ❌ File missing<br>";
    }
} catch (Exception $e) {
    echo "config.php: ❌ Error loading: " . $e->getMessage() . "<br>";
} catch (ParseError $e) {
    echo "config.php: ❌ Syntax error: " . $e->getMessage() . "<br>";
}

echo "<h2>6. Database Connection Test</h2>";
try {
    if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
        $test_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($test_connection->connect_error) {
            echo "Database: ❌ Connection failed: " . $test_connection->connect_error . "<br>";
            echo "<strong>Action needed:</strong> Update database credentials in config.php<br>";
        } else {
            echo "Database: ✅ Connection successful<br>";
            $test_connection->close();
        }
    } else {
        echo "Database: ❌ Configuration incomplete<br>";
    }
} catch (Exception $e) {
    echo "Database: ❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>7. Functions File Test</h2>";
try {
    if (file_exists('functions.php')) {
        echo "functions.php: ✅ File exists<br>";
        
        ob_start();
        include_once 'functions.php';
        $functions_output = ob_get_clean();
        
        if (empty($functions_output)) {
            echo "functions.php: ✅ Loads without errors<br>";
        } else {
            echo "functions.php: ⚠️ Generated output: " . htmlspecialchars($functions_output) . "<br>";
        }
    } else {
        echo "functions.php: ❌ File missing<br>";
    }
} catch (Exception $e) {
    echo "functions.php: ❌ Error loading: " . $e->getMessage() . "<br>";
} catch (ParseError $e) {
    echo "functions.php: ❌ Syntax error: " . $e->getMessage() . "<br>";
}

echo "<h2>8. Session Test</h2>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "Sessions: ✅ Working<br>";
} catch (Exception $e) {
    echo "Sessions: ❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>9. Common Issues & Solutions</h2>";
echo "<ul>";
echo "<li><strong>Missing files:</strong> Make sure all PHP files are uploaded to the correct directory</li>";
echo "<li><strong>Database errors:</strong> Update config.php with your actual InfinityFree database credentials</li>";
echo "<li><strong>Permission errors:</strong> Files should have 644 permissions, directories 755</li>";
echo "<li><strong>Syntax errors:</strong> Check for missing semicolons, brackets, or quotes in PHP files</li>";
echo "<li><strong>Extension errors:</strong> Contact InfinityFree if required extensions are missing</li>";
echo "</ul>";

echo "<h2>10. Next Steps</h2>";
echo "<p>If all checks pass above, try visiting:</p>";
echo "<ul>";
echo "<li><a href='setup.php'>setup.php</a> - First-time setup</li>";
echo "<li><a href='login.php'>login.php</a> - Login page</li>";
echo "<li><a href='index.php'>index.php</a> - Main dashboard</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Diagnostic completed!</strong> Review the results above to identify and fix issues.</p>";
echo "<p>Once issues are resolved, you can delete this error_check.php file.</p>";
?>