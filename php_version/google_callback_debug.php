<?php
// Debug version of Google Drive callback - shows detailed error information
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'functions.php';

echo "<h1>🔧 Google Drive Callback Debug</h1>";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>1. Session Information</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "❌ No user_id in session<br>";
}

if (isset($_SESSION['username'])) {
    echo "Username: " . $_SESSION['username'] . "<br>";
} else {
    echo "❌ No username in session<br>";
}

echo "<h2>2. Login Status</h2>";
if (isLoggedIn()) {
    echo "✅ User is logged in<br>";
} else {
    echo "❌ User is NOT logged in<br>";
}

echo "<h2>3. GET Parameters</h2>";
if (isset($_GET['code'])) {
    echo "✅ Authorization code received: " . substr($_GET['code'], 0, 20) . "...<br>";
    $code = $_GET['code'];
} else {
    echo "❌ No authorization code<br>";
}

if (isset($_GET['scope'])) {
    echo "✅ Scope: " . htmlspecialchars($_GET['scope']) . "<br>";
} else {
    echo "❌ No scope parameter<br>";
}

if (isset($_GET['error'])) {
    echo "❌ OAuth Error: " . htmlspecialchars($_GET['error']) . "<br>";
    if (isset($_GET['error_description'])) {
        echo "Error Description: " . htmlspecialchars($_GET['error_description']) . "<br>";
    }
}

if (isset($_GET['code'])) {
    echo "<h2>4. Token Exchange Test</h2>";
    
    echo "<h3>Configuration Check:</h3>";
    echo "GOOGLE_DRIVE_ENABLED: " . (GOOGLE_DRIVE_ENABLED ? 'true' : 'false') . "<br>";
    echo "GOOGLE_CLIENT_ID: " . substr(GOOGLE_CLIENT_ID, 0, 20) . "...<br>";
    echo "GOOGLE_CLIENT_SECRET: " . substr(GOOGLE_CLIENT_SECRET, 0, 10) . "...<br>";
    echo "GOOGLE_REDIRECT_URI: " . GOOGLE_REDIRECT_URI . "<br>";
    
    echo "<h3>Making Token Request:</h3>";
    
    $data = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'code' => $code
    ];
    
    echo "Request data prepared<br>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    echo "cURL request configured<br>";
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode<br>";
    
    if ($curlError) {
        echo "❌ cURL Error: $curlError<br>";
    } else {
        echo "✅ cURL request successful<br>";
    }
    
    if ($response) {
        echo "<h3>Response from Google:</h3>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        $tokens = json_decode($response, true);
        if ($tokens && isset($tokens['access_token'])) {
            echo "✅ Access token received!<br>";
            
            echo "<h3>Saving to Database:</h3>";
            
            $user_id = $_SESSION['user_id'] ?? 1;
            echo "Using user ID: $user_id<br>";
            
            try {
                if (saveGoogleTokens($user_id, $tokens)) {
                    echo "✅ Tokens saved successfully!<br>";
                    echo "<p><strong>SUCCESS!</strong> Google Drive is now connected.</p>";
                    echo "<p><a href='settings.php'>Go to Settings</a> to verify the connection.</p>";
                } else {
                    echo "❌ Failed to save tokens to database<br>";
                }
            } catch (Exception $e) {
                echo "❌ Exception saving tokens: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ No access token in response<br>";
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
} else {
    echo "<h2>4. No Authorization Code</h2>";
    echo "<p>This page should be called with an authorization code from Google.</p>";
    echo "<p><a href='settings.php'>Go to Settings</a> to start the Google Drive connection process.</p>";
}

echo "<hr>";
echo "<p><strong>Debug completed!</strong> This shows exactly what's happening during the OAuth callback.</p>";
echo "<p>Once this works, the regular google_callback.php should work too.</p>";
?>