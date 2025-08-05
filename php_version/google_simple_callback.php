<?php
// Simplified Google Drive callback for InfinityFree (no cURL required)
require_once 'config.php';
require_once 'functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🔗 Google Drive Connection (Simple Mode)</h1>";

$error_message = '';
$success_message = '';

// Check if user is logged in
if (!isLoggedIn()) {
    $error_message = 'You must be logged in to connect Google Drive. <a href="login.php">Login here</a>';
} elseif (isset($_GET['code'])) {
    echo "<h2>✅ Authorization Code Received!</h2>";
    echo "<p>Google has provided an authorization code. However, due to InfinityFree hosting limitations, we cannot automatically exchange this for access tokens.</p>";
    
    $code = $_GET['code'];
    echo "<p><strong>Authorization Code:</strong> <code>" . htmlspecialchars(substr($code, 0, 50)) . "...</code></p>";
    
    echo "<h3>🔧 Manual Token Exchange Instructions</h3>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>To complete the Google Drive connection, follow these steps:</strong></p>";
    echo "<ol>";
    echo "<li>Copy the authorization code above</li>";
    echo "<li>Use a tool like Postman or run this cURL command on your local computer:</li>";
    echo "</ol>";
    
    echo "<h4>cURL Command:</h4>";
    echo "<pre style='background: #333; color: #fff; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo "curl -X POST https://oauth2.googleapis.com/token \\\n";
    echo "  -H \"Content-Type: application/x-www-form-urlencoded\" \\\n";
    echo "  -d \"client_id=" . GOOGLE_CLIENT_ID . "\" \\\n";
    echo "  -d \"client_secret=" . GOOGLE_CLIENT_SECRET . "\" \\\n";
    echo "  -d \"redirect_uri=" . GOOGLE_REDIRECT_URI . "\" \\\n";
    echo "  -d \"grant_type=authorization_code\" \\\n";
    echo "  -d \"code=" . htmlspecialchars($code) . "\"";
    echo "</pre>";
    
    echo "<p><strong>This will return JSON with access_token and refresh_token.</strong></p>";
    
    echo "<h4>Manual Token Entry:</h4>";
    echo "<p>Once you get the tokens from the cURL command, enter them below:</p>";
    
    echo "<form method='post' style='background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<input type='hidden' name='action' value='save_tokens'>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label><strong>Access Token:</strong></label><br>";
    echo "<textarea name='access_token' style='width: 100%; height: 60px;' placeholder='ya29.a0...'></textarea>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label><strong>Refresh Token:</strong></label><br>";
    echo "<textarea name='refresh_token' style='width: 100%; height: 60px;' placeholder='1//04...'></textarea>";
    echo "</div>";
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label><strong>Expires In (seconds):</strong></label><br>";
    echo "<input type='number' name='expires_in' value='3600' style='width: 100px;'>";
    echo "</div>";
    echo "<button type='submit' style='background: #4285f4; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>💾 Save Tokens</button>";
    echo "</form>";
    echo "</div>";
    
} elseif (isset($_POST['action']) && $_POST['action'] === 'save_tokens') {
    echo "<h2>💾 Saving Tokens...</h2>";
    
    $access_token = trim($_POST['access_token'] ?? '');
    $refresh_token = trim($_POST['refresh_token'] ?? '');
    $expires_in = intval($_POST['expires_in'] ?? 3600);
    
    if (empty($access_token)) {
        $error_message = 'Access token is required.';
    } else {
        // Create tokens array
        $tokens = [
            'access_token' => $access_token,
            'expires_in' => $expires_in,
            'token_type' => 'Bearer'
        ];
        
        if (!empty($refresh_token)) {
            $tokens['refresh_token'] = $refresh_token;
        }
        
        // Save tokens to database
        $user_id = $_SESSION['user_id'] ?? 1;
        
        try {
            if (saveGoogleTokens($user_id, $tokens)) {
                $success_message = 'Google Drive tokens saved successfully! Your Google Drive is now connected.';
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3>✅ Success!</h3>";
                echo "<p>$success_message</p>";
                echo "<p><a href='settings.php'>Go to Settings</a> to verify the connection.</p>";
                echo "<p><a href='quick_billing.php'>Try Quick Billing</a> to upload bill photos.</p>";
                echo "</div>";
            } else {
                $error_message = 'Failed to save tokens to database.';
            }
        } catch (Exception $e) {
            $error_message = 'Error saving tokens: ' . $e->getMessage();
        }
    }
    
} elseif (isset($_GET['error'])) {
    $error_message = 'Google Drive authorization failed: ' . htmlspecialchars($_GET['error']);
    if (isset($_GET['error_description'])) {
        $error_message .= ' - ' . htmlspecialchars($_GET['error_description']);
    }
} else {
    echo "<h2>🔗 Start Google Drive Connection</h2>";
    echo "<p>This page handles Google Drive OAuth when your hosting provider blocks external API calls.</p>";
    echo "<p><a href='google_oauth_start.php'>Click here to start the connection process</a></p>";
}

if ($error_message) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>$error_message</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔧 Alternative: Local Testing</h3>";
echo "<p>Since InfinityFree blocks external API calls, you can:</p>";
echo "<ul>";
echo "<li><strong>Test locally:</strong> Run this on XAMPP/WAMP where cURL works</li>";
echo "<li><strong>Use a different host:</strong> Try PythonAnywhere, Heroku, or paid hosting</li>";
echo "<li><strong>Manual process:</strong> Use the manual token entry above</li>";
echo "</ul>";

echo "<p><a href='settings.php'>← Back to Settings</a></p>";
?>