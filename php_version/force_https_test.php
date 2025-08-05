<?php
echo "<h1>🔒 HTTPS Force Test</h1>";
echo "<p><strong>Testing and forcing HTTPS for OAuth...</strong></p>";

echo "<h2>🔍 Current Connection Status</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<pre>";
echo "Current URL: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'Not set') . "\n";
echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'Not set') . "\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'Not set') . "\n";
echo "HTTP_X_FORWARDED_SSL: " . ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? 'Not set') . "\n";
echo "REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'Not set') . "\n";
echo "</pre>";
echo "</div>";

// Function to detect if we're on HTTPS
function isHTTPS() {
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        $_SERVER['SERVER_PORT'] == 443 ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')
    );
}

$is_https = isHTTPS();
echo "<h2>🔒 HTTPS Detection Result</h2>";
echo "<div style='background: " . ($is_https ? "#e8f5e8" : "#ffebee") . "; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>HTTPS Status: " . ($is_https ? "✅ DETECTED" : "❌ NOT DETECTED") . "</strong></p>";
if (!$is_https) {
    echo "<p><strong>🚨 PROBLEM:</strong> Your site is not serving HTTPS properly!</p>";
}
echo "</div>";

echo "<h2>🔧 InfinityFree HTTPS Solutions</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>1. Enable SSL in InfinityFree Control Panel</h3>";
echo "<ol>";
echo "<li>Log in to your InfinityFree control panel</li>";
echo "<li>Go to <strong>SSL/TLS</strong> section</li>";
echo "<li>Enable <strong>SSL Certificate</strong> for legendary-preet.ct.ws</li>";
echo "<li>Wait 15-30 minutes for activation</li>";
echo "</ol>";

echo "<h3>2. Force HTTPS Redirect (.htaccess)</h3>";
echo "<p>Add this to your .htaccess file:</p>";
echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
echo "RewriteEngine On\n";
echo "RewriteCond %{HTTPS} off\n";
echo "RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n";
echo "</pre>";
echo "</div>";

echo "<h2>🚨 Emergency Test Links</h2>";
echo "<p>Test these direct HTTPS links:</p>";
echo "<ul>";
echo "<li><a href='https://legendary-preet.ct.ws/' target='_blank'>🔗 https://legendary-preet.ct.ws/</a></li>";
echo "<li><a href='https://legendary-preet.ct.ws/google_callback.php' target='_blank'>🔗 https://legendary-preet.ct.ws/google_callback.php</a></li>";
echo "<li><a href='https://legendary-preet.ct.ws/index.php' target='_blank'>🔗 https://legendary-preet.ct.ws/index.php</a></li>";
echo "</ul>";

echo "<h2>🔄 Force HTTPS OAuth Test</h2>";
$forced_https_redirect = 'https://legendary-preet.ct.ws/google_callback.php';
$oauth_params = [
    'client_id' => '2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com',
    'redirect_uri' => $forced_https_redirect,
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
];
$oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($oauth_params);

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Forced HTTPS OAuth URL:</strong></p>";
echo "<p><strong>Redirect URI:</strong> <code>$forced_https_redirect</code></p>";
echo "<p><a href='$oauth_url' target='_blank' style='background: #4caf50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔒 Test HTTPS OAuth</a></p>";
echo "</div>";

echo "<h2>📋 What to Do Next</h2>";
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<ol>";
echo "<li><strong>Enable SSL in InfinityFree:</strong> Make sure HTTPS is properly configured</li>";
echo "<li><strong>Test the direct HTTPS links above</strong></li>";
echo "<li><strong>Try the forced HTTPS OAuth test</strong></li>";
echo "<li><strong>If still HTTP:</strong> Add .htaccess redirect rules</li>";
echo "</ol>";
echo "</div>";
?>