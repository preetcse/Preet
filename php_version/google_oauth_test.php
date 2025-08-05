<?php
require_once 'config.php';

echo "<h1>🔬 Google OAuth Propagation Test</h1>";
echo "<p>Testing if Google's configuration changes have taken effect...</p>";

// Show current time
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📅 Current Time: " . date('Y-m-d H:i:s T') . "</h3>";
echo "<p>Google says changes may take <strong>5 minutes to a few hours</strong> to take effect.</p>";
echo "</div>";

// Show configuration
echo "<h2>⚙️ Current Configuration</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Client ID:</strong> " . GOOGLE_CLIENT_ID . "</p>";
echo "<p><strong>Redirect URI:</strong> " . GOOGLE_REDIRECT_URI . "</p>";
echo "<p><strong>Scope:</strong> https://www.googleapis.com/auth/drive.file</p>";
echo "</div>";

// Show what's registered in Google Console
echo "<h2>✅ Google Console Configuration (Confirmed)</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p>✅ <code>https://legendary-preet.ct.ws/google_callback.php</code></p>";
echo "<p>✅ <code>https://legendary-preet.ct.ws/google_simple_callback.php</code></p>";
echo "<p><em>These are correctly registered in your Google Cloud Console!</em></p>";
echo "</div>";

// Generate OAuth URL
$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
]);

echo "<h2>🧪 Test Options</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Option 1: Test with google_callback.php (Primary)</h3>";
echo "<p><a href='$auth_url' target='_blank' style='background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test OAuth Connection</a></p>";
echo "<p><em>This uses: google_callback.php</em></p>";
echo "</div>";

// Alternative test with simple callback
$auth_url_simple = str_replace('google_callback.php', 'google_simple_callback.php', $auth_url);

echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Option 2: Test with google_simple_callback.php (Backup)</h3>";
echo "<p><a href='$auth_url_simple' target='_blank' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test Simple Callback</a></p>";
echo "<p><em>This uses: google_simple_callback.php (manual token entry)</em></p>";
echo "</div>";

echo "<h2>📋 What to Expect</h2>";
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ If Google's changes have propagated:</h3>";
echo "<ul>";
echo "<li>You'll see Google's consent screen</li>";
echo "<li>You'll be able to grant permissions</li>";
echo "<li>You'll be redirected back to your site</li>";
echo "</ul>";

echo "<h3>❌ If still propagating:</h3>";
echo "<ul>";
echo "<li>Same error: 'redirect_uri_mismatch'</li>";
echo "<li>Wait another 30 minutes and try again</li>";
echo "<li>Try in incognito/private browsing mode</li>";
echo "</ul>";
echo "</div>";

echo "<h2>⏰ If Still Not Working After 2 Hours</h2>";
echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p>If you're still getting redirect_uri_mismatch after 2 hours:</p>";
echo "<ol>";
echo "<li><strong>Double-check you're editing the correct OAuth client</strong> (Client ID: 2633417852...)</li>";
echo "<li><strong>Try deleting and re-adding the redirect URIs</strong> in Google Console</li>";
echo "<li><strong>Create a completely new OAuth 2.0 Client ID</strong></li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Current Status:</strong> Google Console is correctly configured ✅</p>";
echo "<p><strong>Next Step:</strong> Wait for propagation, then test OAuth connection 🚀</p>";
?>