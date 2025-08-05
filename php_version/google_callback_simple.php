<?php
// Simple Google OAuth callback with error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔗 Google OAuth Callback Test</h1>";

// Basic safety checks
try {
    echo "<h2>📋 Callback Information</h2>";
    echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
    echo "<p><strong>Current URL:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
    echo "</div>";
    
    echo "<h2>📥 GET Parameters</h2>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    if (!empty($_GET)) {
        echo "<pre>";
        foreach ($_GET as $key => $value) {
            echo htmlspecialchars($key) . " = " . htmlspecialchars($value) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p>No GET parameters received</p>";
    }
    echo "</div>";
    
    // Check for authorization code
    if (isset($_GET['code'])) {
        echo "<h2>✅ Success - Authorization Code Received!</h2>";
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>🎉 Google OAuth redirect is working!</strong></p>";
        echo "<p><strong>Authorization Code:</strong> " . htmlspecialchars(substr($_GET['code'], 0, 20)) . "...</p>";
        echo "<p><strong>Next Step:</strong> This code can be exchanged for access tokens</p>";
        echo "</div>";
        
        echo "<h3>🔧 Manual Token Exchange</h3>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p>Copy this cURL command and run it to get your access token:</p>";
        echo "<textarea style='width: 100%; height: 150px; font-family: monospace; font-size: 11px;'>";
        echo "curl -X POST https://oauth2.googleapis.com/token \\\n";
        echo "  -H \"Content-Type: application/x-www-form-urlencoded\" \\\n";
        echo "  -d \"client_id=2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com\" \\\n";
        echo "  -d \"client_secret=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\" \\\n";
        echo "  -d \"code=" . htmlspecialchars($_GET['code']) . "\" \\\n";
        echo "  -d \"grant_type=authorization_code\" \\\n";
        echo "  -d \"redirect_uri=https://legendary-preet.ct.ws/google_callback_simple.php\"";
        echo "</textarea>";
        echo "</div>";
        
    } elseif (isset($_GET['error'])) {
        echo "<h2>❌ OAuth Error</h2>";
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($_GET['error']) . "</p>";
        if (isset($_GET['error_description'])) {
            echo "<p><strong>Description:</strong> " . htmlspecialchars($_GET['error_description']) . "</p>";
        }
        echo "</div>";
    } else {
        echo "<h2>⚠️ No Authorization Code</h2>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<p>This page should be called by Google OAuth with an authorization code.</p>";
        echo "<p>If you're seeing this directly, that means the OAuth redirect is working!</p>";
        echo "</div>";
    }
    
    echo "<h2>🧪 Test OAuth Connection</h2>";
    $oauth_params = [
        'client_id' => '2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com',
        'redirect_uri' => 'https://legendary-preet.ct.ws/google_callback_simple.php',
        'scope' => 'https://www.googleapis.com/auth/drive.file',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    $oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($oauth_params);
    
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>This callback URL:</strong> <code>https://legendary-preet.ct.ws/google_callback_simple.php</code></p>";
    echo "<p><a href='$oauth_url' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test OAuth with This Callback</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h2>🚨 Error</h2>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>📞 Status Report</h3>";
echo "<p>If you see this page without errors, your callback file is working! ✅</p>";
echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px;'>← Back to Dashboard</a></p>";
?>