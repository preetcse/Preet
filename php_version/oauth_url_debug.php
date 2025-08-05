<?php
require_once 'config.php';

echo "<h1>🔍 OAuth URL Debug & Testing</h1>";
echo "<p><strong>Let's see the EXACT URL being generated and test variations...</strong></p>";

// Current OAuth parameters
$current_params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
];

echo "<h2>🧪 Current OAuth Configuration</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Parameter</th><th>Value</th></tr>";
foreach ($current_params as $key => $value) {
    echo "<tr><td><strong>$key</strong></td><td><code>$value</code></td></tr>";
}
echo "</table>";
echo "</div>";

$current_oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($current_params);

echo "<h2>📋 Generated OAuth URL</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Current URL:</strong></p>";
echo "<textarea style='width: 100%; height: 80px; font-family: monospace; font-size: 11px;'>$current_oauth_url</textarea>";
echo "<p><a href='$current_oauth_url' target='_blank' style='background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚨 Test Current URL</a></p>";
echo "</div>";

echo "<h2>🧪 Alternative Redirect URI Tests</h2>";
echo "<p>Let's test different variations to see if any work:</p>";

// Test different redirect URI variations
$redirect_variations = [
    'https://legendary-preet.ct.ws/google_callback.php',
    'http://legendary-preet.ct.ws/google_callback.php',
    'https://www.legendary-preet.ct.ws/google_callback.php',
    'https://legendary-preet.ct.ws/google_simple_callback.php',
];

// Check if domain resolves to something else
$detected_host = $_SERVER['HTTP_HOST'];
if ($detected_host !== 'legendary-preet.ct.ws') {
    $redirect_variations[] = 'https://' . $detected_host . '/google_callback.php';
    $redirect_variations[] = 'http://' . $detected_host . '/google_callback.php';
}

foreach ($redirect_variations as $index => $redirect_uri) {
    $test_params = $current_params;
    $test_params['redirect_uri'] = $redirect_uri;
    $test_oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($test_params);
    
    $variation_num = $index + 1;
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Variation $variation_num: $redirect_uri</h3>";
    echo "<p><a href='$test_oauth_url' target='_blank' style='background: #2196f3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px;'>🧪 Test Variation $variation_num</a></p>";
    echo "<details>";
    echo "<summary>View Full URL</summary>";
    echo "<textarea style='width: 100%; height: 60px; font-family: monospace; font-size: 10px;'>$test_oauth_url</textarea>";
    echo "</details>";
    echo "</div>";
}

echo "<h2>🔧 InfinityFree Specific Issues</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Common InfinityFree Problems:</h3>";
echo "<ol>";
echo "<li><strong>Internal Redirects:</strong> Your domain might internally redirect to a different URL</li>";
echo "<li><strong>Subdomain Issues:</strong> ct.ws domains sometimes have special handling</li>";
echo "<li><strong>HTTPS Certificate:</strong> Free SSL certificates might not be fully trusted by Google</li>";
echo "<li><strong>Blocked External Requests:</strong> Some free hosts block certain external connections</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🌐 Domain Resolution Check</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Current Server Information:</h3>";
echo "<pre>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "\n";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'Not set') . "\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'Not set') . "\n";
echo "HTTP_X_FORWARDED_HOST: " . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'Not set') . "\n";
echo "</pre>";

$ip = gethostbyname('legendary-preet.ct.ws');
echo "<p><strong>legendary-preet.ct.ws resolves to IP:</strong> $ip</p>";
echo "</div>";

echo "<h2>🚨 Manual Test Instructions</h2>";
echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>To identify the exact issue:</h3>";
echo "<ol>";
echo "<li><strong>Test each variation above</strong> - Try all the test buttons</li>";
echo "<li><strong>Note the exact error message</strong> for each one</li>";
echo "<li><strong>Check if any variation works</strong></li>";
echo "<li><strong>If none work:</strong> The issue might be with InfinityFree's domain setup</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔄 Alternative Solution</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>If all tests fail, try this:</h3>";
echo "<ol>";
echo "<li><strong>Create a completely new OAuth client</strong> in Google Console</li>";
echo "<li><strong>Use ONLY:</strong> <code>https://legendary-preet.ct.ws/google_simple_callback.php</code></li>";
echo "<li><strong>Test with the simple callback</strong> (manual token entry)</li>";
echo "<li><strong>This bypasses InfinityFree's cURL limitations</strong></li>";
echo "</ol>";
echo "<p><a href='google_simple_callback.php' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📱 Try Simple Callback Approach</a></p>";
echo "</div>";

echo "<h2>📞 Report Results</h2>";
echo "<p><strong>Please test the variations above and tell me:</strong></p>";
echo "<ul>";
echo "<li>Which variation (if any) works?</li>";
echo "<li>What exact error message do you get?</li>";
echo "<li>Does the domain/IP information look correct?</li>";
echo "</ul>";
?>