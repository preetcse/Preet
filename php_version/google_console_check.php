<?php
echo "<h1>🔍 Google Console Verification & Testing</h1>";
echo "<p><strong>Let's verify your Google Console setup and test systematically...</strong></p>";

echo "<h2>❌ Current Error Analysis</h2>";
echo "<div style='background: #ffebee; padding: 15px; border-left: 5px solid #f44336; margin: 20px 0;'>";
echo "<p><strong>Google is rejecting:</strong> <code>https://legendary-preet.ct.ws/google_callback_simple.php</code></p>";
echo "<p><strong>Client ID:</strong> <code>2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com</code></p>";
echo "<p><strong>This means the URI is not registered OR changes haven't propagated yet.</strong></p>";
echo "</div>";

echo "<h2>✅ Required Google Console Configuration</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>In your Google Cloud Console, you MUST have ALL these URIs:</h3>";
echo "<div style='background: #ffffff; padding: 15px; border: 2px solid #4caf50; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Copy these EXACT URLs (case-sensitive):</h4>";
echo "<pre style='background: #f5f5f5; padding: 10px; margin: 5px 0; font-family: monospace; font-size: 14px;'>";
echo "https://legendary-preet.ct.ws/google_callback.php";
echo "</pre>";
echo "<pre style='background: #f5f5f5; padding: 10px; margin: 5px 0; font-family: monospace; font-size: 14px;'>";
echo "https://legendary-preet.ct.ws/google_simple_callback.php";
echo "</pre>";
echo "<pre style='background: #f5f5f5; padding: 10px; margin: 5px 0; font-family: monospace; font-size: 14px;'>";
echo "https://legendary-preet.ct.ws/google_callback_simple.php";
echo "</pre>";
echo "</div>";
echo "</div>";

echo "<h2>🧪 Systematic Testing</h2>";
echo "<p>Let's test each callback URL to see which one works:</p>";

$test_callbacks = [
    'https://legendary-preet.ct.ws/google_callback.php' => 'Original Callback',
    'https://legendary-preet.ct.ws/google_simple_callback.php' => 'Manual Token Callback',
    'https://legendary-preet.ct.ws/google_callback_simple.php' => 'Simple Debug Callback',
];

foreach ($test_callbacks as $callback_uri => $description) {
    $oauth_params = [
        'client_id' => '2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com',
        'redirect_uri' => $callback_uri,
        'scope' => 'https://www.googleapis.com/auth/drive.file',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    $oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($oauth_params);
    
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>$description</h3>";
    echo "<p><strong>Callback:</strong> <code>$callback_uri</code></p>";
    echo "<p><a href='$oauth_url' target='_blank' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test This Callback</a></p>";
    echo "</div>";
}

echo "<h2>⏰ Propagation Test</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>If you just added the URI to Google Console:</h3>";
echo "<ol>";
echo "<li><strong>Wait 10 minutes</strong> - Google changes can take time</li>";
echo "<li><strong>Try incognito mode</strong> - Clear browser cache</li>";
echo "<li><strong>Test again</strong> - Use the buttons above</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔧 Alternative Solution: Create New OAuth Client</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>If propagation doesn't work, create a fresh OAuth client:</h3>";
echo "<ol>";
echo "<li>Go to <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console</a></li>";
echo "<li>Click <strong>'+ CREATE CREDENTIALS'</strong> → <strong>'OAuth 2.0 Client ID'</strong></li>";
echo "<li>Application type: <strong>'Web application'</strong></li>";
echo "<li>Name: 'Amarjit Store New'</li>";
echo "<li>Authorized redirect URIs: Add ALL three URLs above</li>";
echo "<li>Click <strong>Create</strong></li>";
echo "<li>Copy the new Client ID and Secret</li>";
echo "</ol>";
echo "</div>";

echo "<h2>📋 Double-Check Your Google Console</h2>";
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Verify these settings:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Correct Google Account:</strong> Make sure you're signed in with the right account</li>";
echo "<li>✅ <strong>Correct Project:</strong> Find the project containing Client ID 2633417852...</li>";
echo "<li>✅ <strong>Correct OAuth Client:</strong> Edit the right OAuth 2.0 Client ID</li>";
echo "<li>✅ <strong>Web Application:</strong> Make sure it's type 'Web application'</li>";
echo "<li>✅ <strong>Exact URLs:</strong> Copy-paste the URLs exactly as shown above</li>";
echo "<li>✅ <strong>Saved Changes:</strong> Click Save after adding URIs</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🆘 Emergency Bypass</h2>";
echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p>If nothing works, try this localhost bypass for testing:</p>";
$localhost_params = [
    'client_id' => '2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com',
    'redirect_uri' => 'http://localhost:8080/oauth/callback',
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
];
$localhost_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($localhost_params);
echo "<p><a href='$localhost_url' target='_blank' style='background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Test with Localhost</a></p>";
echo "<p><em>This tests if your OAuth client works at all (you'll get an error, but a different one)</em></p>";
echo "</div>";

echo "<hr>";
echo "<h3>📞 Next Steps</h3>";
echo "<ol>";
echo "<li>Test each callback button above</li>";
echo "<li>If all fail, wait 30 minutes and try again</li>";
echo "<li>If still failing, create a new OAuth client</li>";
echo "<li>Report which test (if any) works</li>";
echo "</ol>";
?>