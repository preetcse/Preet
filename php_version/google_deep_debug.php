<?php
require_once 'config.php';

echo "<h1>🔍 Deep Google OAuth Debug Analysis</h1>";
echo "<p><strong>Since your URIs were added long ago, let's find the REAL issue...</strong></p>";

echo "<h2>🚨 Possible Root Causes</h2>";

echo "<h3>1. 🎯 Wrong OAuth Client Being Used</h3>";
echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Theory:</strong> You might have multiple OAuth clients, and we're using the wrong one.</p>";
echo "<p><strong>Your Current Client ID:</strong> <code>" . GOOGLE_CLIENT_ID . "</code></p>";
echo "<p><strong>Action:</strong> In Google Console, verify this EXACT Client ID has the redirect URIs.</p>";
echo "<ol>";
echo "<li>Go to Google Cloud Console → APIs & Services → Credentials</li>";
echo "<li>Search for: <code>2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7</code></li>";
echo "<li>Click edit on that SPECIFIC client</li>";
echo "<li>Verify it shows your redirect URIs</li>";
echo "</ol>";
echo "</div>";

echo "<h3>2. 📁 Wrong Google Cloud Project</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Theory:</strong> You added URIs to a different project than where your OAuth client exists.</p>";
echo "<p><strong>Action:</strong> Check ALL your Google Cloud projects:</p>";
echo "<ol>";
echo "<li>Go to <a href='https://console.cloud.google.com' target='_blank'>Google Cloud Console</a></li>";
echo "<li>Click project dropdown (top left)</li>";
echo "<li>Check EVERY project you have access to</li>";
echo "<li>Look for the client ID: <code>2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7</code></li>";
echo "</ol>";
echo "</div>";

echo "<h3>3. 🔧 OAuth Client Type Mismatch</h3>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Theory:</strong> Your OAuth client might not be configured as 'Web application'.</p>";
echo "<p><strong>Action:</strong> Verify the client type:</p>";
echo "<ol>";
echo "<li>Find your OAuth client in Google Console</li>";
echo "<li>Check that it says <strong>'Web client'</strong> or <strong>'Web application'</strong></li>";
echo "<li>If it says 'Desktop' or 'Mobile', that's the problem!</li>";
echo "</ol>";
echo "</div>";

echo "<h3>4. 🌐 Domain/URL Case Sensitivity</h3>";
echo "<div style='background: #f3e5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Theory:</strong> Tiny differences in the URL format.</p>";
echo "<p><strong>What Google expects:</strong> <code>https://legendary-preet.ct.ws/google_callback.php</code></p>";
echo "<p><strong>Common mistakes:</strong></p>";
echo "<ul>";
echo "<li>Extra 'www.' prefix</li>";
echo "<li>Trailing slash '/'</li>";
echo "<li>Wrong case (Google_Callback vs google_callback)</li>";
echo "<li>HTTP instead of HTTPS</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🔬 Debug Information</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>Current Configuration:</h4>";
echo "<pre>";
echo "GOOGLE_CLIENT_ID: " . GOOGLE_CLIENT_ID . "\n";
echo "GOOGLE_CLIENT_SECRET: " . substr(GOOGLE_CLIENT_SECRET, 0, 20) . "...\n";
echo "GOOGLE_REDIRECT_URI: " . GOOGLE_REDIRECT_URI . "\n";
echo "Expected callback file: " . __DIR__ . "/google_callback.php\n";
echo "File exists: " . (file_exists(__DIR__ . "/google_callback.php") ? "YES" : "NO") . "\n";
echo "</pre>";
echo "</div>";

// Generate the exact OAuth URL
$oauth_params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => 'https://www.googleapis.com/auth/drive.file',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
];

$oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($oauth_params);

echo "<h2>🧪 Generated OAuth URL</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>This is the EXACT URL being generated:</strong></p>";
echo "<textarea style='width: 100%; height: 100px; font-family: monospace; font-size: 12px;'>" . $oauth_url . "</textarea>";
echo "<p><strong>Pay attention to the redirect_uri parameter!</strong></p>";
echo "</div>";

echo "<h2>🎯 Systematic Debugging Steps</h2>";
echo "<div style='background: #fff; border: 2px solid #4caf50; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>Step 1: Verify the OAuth Client</h3>";
echo "<ol>";
echo "<li>Go to: <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Credentials</a></li>";
echo "<li>Use browser search (Ctrl+F) to find: <code>2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7</code></li>";
echo "<li>If NOT found, switch to different Google Cloud projects and search again</li>";
echo "<li>When found, click the edit pencil ✏️ icon</li>";
echo "<li>Take a screenshot of the 'Authorized redirect URIs' section</li>";
echo "</ol>";

echo "<h3>Step 2: Create a Test OAuth Client</h3>";
echo "<ol>";
echo "<li>In the same Google Cloud project, click <strong>'+ CREATE CREDENTIALS'</strong></li>";
echo "<li>Choose <strong>'OAuth 2.0 Client ID'</strong></li>";
echo "<li>Application type: <strong>'Web application'</strong></li>";
echo "<li>Name: 'Test Amarjit Store'</li>";
echo "<li>Authorized redirect URIs: <code>https://legendary-preet.ct.ws/google_callback.php</code></li>";
echo "<li>Click <strong>Create</strong></li>";
echo "<li>Copy the new Client ID and Secret</li>";
echo "<li>Test with the new credentials</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🚨 Emergency Test</h2>";
echo "<p>Let's test the OAuth URL right now:</p>";
echo "<p><a href='$oauth_url' target='_blank' style='background: #f44336; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🚨 TEST OAUTH NOW</a></p>";
echo "<p><em>This will either work or show you the exact error message with the exact redirect_uri Google is rejecting.</em></p>";

echo "<h2>📋 What to Report Back</h2>";
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p><strong>Please tell me:</strong></p>";
echo "<ol>";
echo "<li><strong>Did you find the Client ID</strong> (2633417852...) in your Google Console?</li>";
echo "<li><strong>Which Google Cloud project</strong> contains this Client ID?</li>";
echo "<li><strong>What type</strong> is the OAuth client? (Web application, Desktop, etc.)</li>";
echo "<li><strong>What exact error</strong> do you get when clicking 'TEST OAUTH NOW' above?</li>";
echo "<li><strong>Screenshot</strong> of the OAuth client edit page showing redirect URIs</li>";
echo "</ol>";
echo "</div>";
?>