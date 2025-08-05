<?php
require_once 'config.php';
require_once 'functions.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Google Drive Connection Test</h1>";
echo "<p>Testing Google Drive OAuth step by step...</p>";

echo "<h2>1. Configuration Check</h2>";

// Check if Google Drive is enabled
if (defined('GOOGLE_DRIVE_ENABLED') && GOOGLE_DRIVE_ENABLED) {
    echo "✅ Google Drive is enabled<br>";
} else {
    echo "❌ Google Drive is disabled<br>";
}

// Check credentials
if (defined('GOOGLE_CLIENT_ID') && !empty(GOOGLE_CLIENT_ID)) {
    echo "✅ Client ID configured: " . substr(GOOGLE_CLIENT_ID, 0, 20) . "...<br>";
} else {
    echo "❌ Client ID missing<br>";
}

if (defined('GOOGLE_CLIENT_SECRET') && !empty(GOOGLE_CLIENT_SECRET)) {
    echo "✅ Client Secret configured: " . substr(GOOGLE_CLIENT_SECRET, 0, 10) . "...<br>";
} else {
    echo "❌ Client Secret missing<br>";
}

if (defined('GOOGLE_REDIRECT_URI') && !empty(GOOGLE_REDIRECT_URI)) {
    echo "✅ Redirect URI configured: " . GOOGLE_REDIRECT_URI . "<br>";
} else {
    echo "❌ Redirect URI missing<br>";
}

echo "<h2>2. URL Generation Test</h2>";

try {
    $auth_url = getGoogleAuthUrl();
    if ($auth_url) {
        echo "✅ OAuth URL generated successfully<br>";
        echo "🔗 URL: <a href='" . htmlspecialchars($auth_url) . "' target='_blank'>Test OAuth Link</a><br>";
        echo "<small>Click the link above to test OAuth flow</small><br>";
    } else {
        echo "❌ Failed to generate OAuth URL<br>";
    }
} catch (Exception $e) {
    echo "❌ Error generating OAuth URL: " . $e->getMessage() . "<br>";
}

echo "<h2>3. cURL Test</h2>";

if (extension_loaded('curl')) {
    echo "✅ cURL extension is available<br>";
    
    // Test basic cURL to Google
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response && $httpCode == 200) {
        echo "✅ cURL can connect to Google APIs<br>";
    } else {
        echo "❌ cURL connection failed. HTTP Code: $httpCode<br>";
        if ($error) {
            echo "❌ cURL Error: $error<br>";
        }
    }
} else {
    echo "❌ cURL extension is not available<br>";
}

echo "<h2>4. Callback Test</h2>";

// Check if callback file exists
if (file_exists('google_callback.php')) {
    echo "✅ google_callback.php exists<br>";
} else {
    echo "❌ google_callback.php missing<br>";
}

echo "<h2>5. Database Test</h2>";

try {
    $conn = getDBConnection();
    if ($conn) {
        echo "✅ Database connection successful<br>";
        
        // Check if google_tokens table exists
        $result = $conn->query("SHOW TABLES LIKE 'google_tokens'");
        if ($result && $result->num_rows > 0) {
            echo "✅ google_tokens table exists<br>";
        } else {
            echo "⚠️ google_tokens table missing (will be created automatically)<br>";
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<h2>6. Manual OAuth Test</h2>";

if (isset($_GET['code'])) {
    echo "<h3>Processing OAuth code...</h3>";
    
    $code = $_GET['code'];
    echo "✅ Received authorization code: " . substr($code, 0, 20) . "...<br>";
    
    // Test token exchange
    echo "<h4>Testing token exchange...</h4>";
    
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode<br>";
    if ($curlError) {
        echo "❌ cURL Error: $curlError<br>";
    }
    
    if ($response) {
        echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";
        
        $tokens = json_decode($response, true);
        if ($tokens && isset($tokens['access_token'])) {
            echo "✅ Successfully received access token!<br>";
            echo "Access token: " . substr($tokens['access_token'], 0, 20) . "...<br>";
            
            if (isset($tokens['refresh_token'])) {
                echo "✅ Received refresh token<br>";
            } else {
                echo "⚠️ No refresh token (might need prompt=consent)<br>";
            }
        } else {
            echo "❌ Failed to get access token<br>";
            if (isset($tokens['error'])) {
                echo "Error: " . htmlspecialchars($tokens['error']) . "<br>";
                if (isset($tokens['error_description'])) {
                    echo "Description: " . htmlspecialchars($tokens['error_description']) . "<br>";
                }
            }
        }
    } else {
        echo "❌ No response from Google<br>";
    }
} elseif (isset($_GET['error'])) {
    echo "❌ OAuth Error: " . htmlspecialchars($_GET['error']) . "<br>";
    if (isset($_GET['error_description'])) {
        echo "Description: " . htmlspecialchars($_GET['error_description']) . "<br>";
    }
}

echo "<h2>7. Common Issues & Solutions</h2>";
echo "<ul>";
echo "<li><strong>Invalid redirect URI:</strong> Make sure the redirect URI in Google Console exactly matches: " . GOOGLE_REDIRECT_URI . "</li>";
echo "<li><strong>OAuth consent screen:</strong> Make sure your email is added as a test user</li>";
echo "<li><strong>Client credentials:</strong> Double-check Client ID and Secret from Google Console</li>";
echo "<li><strong>Scope permissions:</strong> Make sure your app requests 'drive.file' scope</li>";
echo "<li><strong>SSL issues:</strong> InfinityFree should support HTTPS for OAuth</li>";
echo "</ul>";

echo "<h2>8. Next Steps</h2>";
if (!isset($_GET['code']) && !isset($_GET['error'])) {
    $auth_url = getGoogleAuthUrl();
    if ($auth_url) {
        // Update the redirect URI for this test file
        $test_url = str_replace('google_callback.php', 'google_drive_test.php', $auth_url);
        echo "<p><strong>Click here to test OAuth flow:</strong></p>";
        echo "<p><a href='" . htmlspecialchars($test_url) . "' class='btn btn-primary' target='_blank'>🔗 Test Google Drive Connection</a></p>";
    }
}

echo "<hr>";
echo "<p><em>This test helps identify Google Drive connection issues. Once OAuth works here, it should work in the main application.</em></p>";
?>