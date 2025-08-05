<?php
require_once 'config.php';
require_once 'functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🔗 Google Drive Connection (Using Original Callback)</h1>";

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<p>❌ You must be logged in to connect Google Drive.</p>";
    echo "<p><a href='login.php'>Login first</a></p>";
    exit;
}

echo "<h2>Connect Google Drive</h2>";
echo "<p>This uses your existing Google Cloud Console configuration.</p>";

// Generate OAuth URL that uses the original callback
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => 'https://legendary-preet.ct.ws/google_callback.php', // Use existing registered URI
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
];

$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);

echo "<p><a href='" . htmlspecialchars($auth_url) . "' style='background: #4285f4; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>
📂 Connect Google Drive (Original Method)
</a></p>";

echo "<h3>⚠️ Important Notes:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Since InfinityFree blocks external API calls:</strong></p>";
echo "<ul>";
echo "<li>The OAuth authorization will work (you'll get redirected back)</li>";
echo "<li>But the automatic token exchange might fail with HTTP 500</li>";
echo "<li>If it fails, you'll see an error page</li>";
echo "<li>In that case, use the manual method below</li>";
echo "</ul>";
echo "</div>";

echo "<h3>🔧 Manual Method (If Above Fails):</h3>";
echo "<p>If the automatic method fails due to InfinityFree limitations:</p>";
echo "<ol>";
echo "<li>Try the automatic method first (button above)</li>";
echo "<li>If it fails, update your Google Console to add: <code>google_simple_callback.php</code></li>";
echo "<li>Then use: <a href='google_oauth_start.php'>Manual Google Drive Setup</a></li>";
echo "</ol>";

echo "<h3>📋 Current Configuration:</h3>";
echo "<ul>";
echo "<li><strong>Client ID:</strong> " . substr(GOOGLE_CLIENT_ID, 0, 20) . "...</li>";
echo "<li><strong>Redirect URI:</strong> google_callback.php (currently registered)</li>";
echo "<li><strong>Scope:</strong> drive.file</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='settings.php'>← Back to Settings</a></p>";
?>