<?php
require_once 'config.php';
require_once 'functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🔗 Google Drive Connection</h1>";

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<p>❌ You must be logged in to connect Google Drive.</p>";
    echo "<p><a href='login.php'>Login first</a></p>";
    exit;
}

echo "<h2>Start Google Drive Connection</h2>";
echo "<p>Click the button below to connect your Google Drive account:</p>";

// Generate the correct OAuth URL
$auth_url = getGoogleAuthUrl();

if ($auth_url) {
    echo "<p><a href='" . htmlspecialchars($auth_url) . "' class='btn btn-primary' style='background: #4285f4; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>
    📂 Connect Google Drive
    </a></p>";
    
    echo "<h3>ℹ️ What happens next:</h3>";
    echo "<ol>";
    echo "<li>You'll be taken to Google's authorization page</li>";
    echo "<li>Login with your Google account</li>";
    echo "<li>Grant permission to access Google Drive</li>";
    echo "<li>You'll be redirected back to: <code>google_callback.php</code></li>";
    echo "<li>Your Google Drive will be connected!</li>";
    echo "</ol>";
    
    echo "<h3>🔧 Current Configuration:</h3>";
    echo "<ul>";
    echo "<li><strong>Client ID:</strong> " . substr(GOOGLE_CLIENT_ID, 0, 20) . "...</li>";
    echo "<li><strong>Redirect URI:</strong> " . GOOGLE_REDIRECT_URI . "</li>";
    echo "<li><strong>Scope:</strong> https://www.googleapis.com/auth/drive.file</li>";
    echo "</ul>";
    
} else {
    echo "<p>❌ Could not generate OAuth URL. Check your Google Drive configuration.</p>";
}

echo "<hr>";
echo "<p><a href='settings.php'>← Back to Settings</a></p>";
?>