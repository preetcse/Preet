<?php
require_once 'config.php';

echo "<h1>🌐 Domain & Callback Accessibility Test</h1>";
echo "<p><strong>Testing if Google can actually reach your callback URL...</strong></p>";

echo "<h2>🔍 Current Callback URL Analysis</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Expected Callback URL:</strong> <code>" . GOOGLE_REDIRECT_URI . "</code></p>";
echo "<p><strong>This should resolve to:</strong> <code>" . $_SERVER['HTTP_HOST'] . "/google_callback.php</code></p>";
echo "</div>";

echo "<h2>🧪 File Accessibility Tests</h2>";

// Test 1: Check if callback file exists
$callback_file = __DIR__ . '/google_callback.php';
echo "<div style='background: " . (file_exists($callback_file) ? "#e8f5e8" : "#ffebee") . "; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Test 1: Callback File Exists</h3>";
echo "<p><strong>File:</strong> google_callback.php</p>";
echo "<p><strong>Result:</strong> " . (file_exists($callback_file) ? "✅ EXISTS" : "❌ NOT FOUND") . "</p>";
if (!file_exists($callback_file)) {
    echo "<p><strong>🚨 CRITICAL ERROR:</strong> google_callback.php file is missing!</p>";
}
echo "</div>";

// Test 2: Check current domain
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Test 2: Current Domain Detection</h3>";
echo "<p><strong>Detected Domain:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>Expected Domain:</strong> legendary-preet.ct.ws</p>";
echo "<p><strong>Protocol:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP') . "</p>";
echo "<p><strong>Full Current URL:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
echo "</div>";

// Test 3: DNS Resolution
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>Test 3: Domain Resolution</h3>";
$ip = gethostbyname('legendary-preet.ct.ws');
echo "<p><strong>legendary-preet.ct.ws resolves to:</strong> $ip</p>";
if ($ip === 'legendary-preet.ct.ws') {
    echo "<p><strong>🚨 DNS ERROR:</strong> Domain doesn't resolve!</p>";
} else {
    echo "<p><strong>✅ DNS OK:</strong> Domain resolves correctly</p>";
}
echo "</div>";

// Test 4: Check if we can simulate the callback
echo "<h2>🔗 Callback URL Tests</h2>";

$callback_urls = [
    'https://legendary-preet.ct.ws/google_callback.php',
    'http://legendary-preet.ct.ws/google_callback.php',
    'https://www.legendary-preet.ct.ws/google_callback.php',
    'https://legendary-preet.ct.ws/google_callback.php/',
];

foreach ($callback_urls as $test_url) {
    echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
    echo "<h4>Testing: <code>$test_url</code></h4>";
    
    // Test with cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $test_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'GoogleBot/1.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "<p>❌ cURL Error: $error</p>";
        } else {
            echo "<p>HTTP Status: $http_code</p>";
            if ($http_code == 200) {
                echo "<p>✅ URL is accessible</p>";
            } else {
                echo "<p>❌ URL returned error code: $http_code</p>";
            }
        }
    } else {
        echo "<p>⚠️ cURL not available for testing</p>";
    }
    echo "</div>";
}

echo "<h2>🤔 Common Issues & Solutions</h2>";
echo "<div style='background: #fff; border: 2px solid #ff9800; padding: 20px; border-radius: 5px; margin: 20px 0;'>";

echo "<h3>🚨 Issue 1: Missing google_callback.php</h3>";
echo "<p>If the callback file doesn't exist, Google can't redirect to it.</p>";
echo "<p><strong>Solution:</strong> Make sure google_callback.php is uploaded to your website.</p>";

echo "<h3>🚨 Issue 2: Wrong Domain in config.php</h3>";
echo "<p>If GOOGLE_REDIRECT_URI doesn't match your actual domain.</p>";
echo "<p><strong>Current:</strong> " . GOOGLE_REDIRECT_URI . "</p>";
echo "<p><strong>Should be:</strong> https://" . $_SERVER['HTTP_HOST'] . "/google_callback.php</p>";

echo "<h3>🚨 Issue 3: InfinityFree Subdomain Issues</h3>";
echo "<p>Some free hosts have subdomain redirects that confuse OAuth.</p>";
echo "<p><strong>Check if:</strong> legendary-preet.ct.ws actually redirects to a different URL</p>";

echo "<h3>🚨 Issue 4: HTTPS Certificate Issues</h3>";
echo "<p>Google requires HTTPS for OAuth, but the certificate might be invalid.</p>";
echo "<p><strong>Test:</strong> Visit <a href='https://legendary-preet.ct.ws/google_callback.php' target='_blank'>https://legendary-preet.ct.ws/google_callback.php</a> directly</p>";

echo "</div>";

echo "<h2>🔧 Quick Fix Test</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p>Let's test if the callback URL works manually:</p>";
echo "<p><a href='google_callback.php?code=test123' target='_blank' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Callback Manually</a></p>";
echo "<p><em>This should take you to google_callback.php and show some response (even if it's an error about the test code)</em></p>";
echo "</div>";

echo "<h2>📋 Diagnostic Report</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>Domain Information:</h4>";
echo "<pre>";
echo "Current Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Current Protocol: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP') . "\n";
echo "Config Redirect URI: " . GOOGLE_REDIRECT_URI . "\n";
echo "Callback File Exists: " . (file_exists($callback_file) ? "YES" : "NO") . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "</pre>";
echo "</div>";
?>