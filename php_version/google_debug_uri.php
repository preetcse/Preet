<?php
require_once 'config.php';

echo "<h1>🔧 Google OAuth URI Debug</h1>";
echo "<p>This shows exactly what URIs your system is using vs what should be in Google Console.</p>";

echo "<h2>📋 Current System Configuration</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><td><strong>Setting</strong></td><td><strong>Value</strong></td></tr>";
echo "<tr><td>GOOGLE_CLIENT_ID</td><td>" . GOOGLE_CLIENT_ID . "</td></tr>";
echo "<tr><td>GOOGLE_REDIRECT_URI</td><td>" . GOOGLE_REDIRECT_URI . "</td></tr>";
echo "<tr><td>Domain</td><td>legendary-preet.ct.ws</td></tr>";
echo "</table>";

echo "<h2>🎯 Required Google Console Settings</h2>";
echo "<p><strong>In your Google Cloud Console, you MUST have exactly these URIs:</strong></p>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Authorized redirect URIs (copy exactly):</h3>";
echo "<pre style='background: #fff; padding: 10px; border: 1px solid #ccc;'>";
echo "https://legendary-preet.ct.ws/google_callback.php\n";
echo "https://legendary-preet.ct.ws/google_simple_callback.php";
echo "</pre>";
echo "</div>";

echo "<h2>🔍 Google Console Verification Steps</h2>";
echo "<ol>";
echo "<li><strong>Go to:</strong> <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console - Credentials</a></li>";
echo "<li><strong>Find:</strong> OAuth 2.0 Client ID with ID: <code>2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7</code></li>";
echo "<li><strong>Click:</strong> The edit/pencil icon ✏️</li>";
echo "<li><strong>Scroll to:</strong> 'Authorized redirect URIs' section</li>";
echo "<li><strong>Verify you have BOTH:</strong>";
echo "<ul>";
echo "<li><code>https://legendary-preet.ct.ws/google_callback.php</code></li>";
echo "<li><code>https://legendary-preet.ct.ws/google_simple_callback.php</code></li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Click:</strong> Save</li>";
echo "<li><strong>Wait:</strong> 5-10 minutes for changes to take effect</li>";
echo "</ol>";

echo "<h2>⚠️ Common Issues</h2>";
echo "<ul>";
echo "<li><strong>Case sensitive:</strong> URLs must match exactly (https, not http)</li>";
echo "<li><strong>No trailing slash:</strong> Don't add / at the end</li>";
echo "<li><strong>Wrong project:</strong> Make sure you're editing the right Google project</li>";
echo "<li><strong>Wrong client:</strong> Make sure the Client ID matches exactly</li>";
echo "<li><strong>Propagation delay:</strong> Changes can take up to 15 minutes</li>";
echo "</ul>";

echo "<h2>🧪 Test Current OAuth URL</h2>";
$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
]);

echo "<p><strong>Current OAuth URL being generated:</strong></p>";
echo "<div style='background: #fff; padding: 10px; border: 1px solid #ccc; word-break: break-all; font-size: 12px;'>";
echo htmlspecialchars($auth_url);
echo "</div>";

echo "<p><a href='" . htmlspecialchars($auth_url) . "' target='_blank' style='background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test This OAuth URL</a></p>";

echo "<h2>🎯 Quick Fix Suggestions</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h3>If error persists:</h3>";
echo "<ol>";
echo "<li><strong>Double-check Google Console:</strong> Screenshot the redirect URIs section and verify both URLs are there</li>";
echo "<li><strong>Try incognito/private browsing:</strong> Sometimes browser cache causes issues</li>";
echo "<li><strong>Wait longer:</strong> Google says 5 minutes, but sometimes takes 15-20 minutes</li>";
echo "<li><strong>Contact me with screenshot:</strong> Show me what's currently in your Google Console</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>If you see this page working, PHP is fine. The issue is purely Google Console configuration.</strong></p>";
?>