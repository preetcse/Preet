<?php
require_once 'config.php';

echo "<h1>🚨 Google Console Configuration Verification</h1>";
echo "<p><strong>Google is rejecting your redirect URI. Let's find out why!</strong></p>";

echo "<h2>❌ Current Error Analysis</h2>";
echo "<div style='background: #ffebee; padding: 15px; border-left: 5px solid #f44336; margin: 20px 0;'>";
echo "<p><strong>Google Error:</strong> 'register the redirect URI in the Google Cloud Console'</p>";
echo "<p><strong>Rejected URI:</strong> <code>https://legendary-preet.ct.ws/google_callback.php</code></p>";
echo "<p><strong>Your Client ID:</strong> <code>2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com</code></p>";
echo "</div>";

echo "<h2>🔍 Step-by-Step Verification</h2>";

echo "<h3>Step 1: Verify You're in the Correct Google Account</h3>";
echo "<ul>";
echo "<li>Make sure you're signed into Google with the account that created the OAuth credentials</li>";
echo "<li>If you have multiple Google accounts, switch to the correct one</li>";
echo "</ul>";

echo "<h3>Step 2: Find the Correct Google Cloud Project</h3>";
echo "<ol>";
echo "<li>Go to: <a href='https://console.cloud.google.com' target='_blank'>Google Cloud Console</a></li>";
echo "<li>Click the project dropdown (top left, next to 'Google Cloud')</li>";
echo "<li>Look for a project that contains your OAuth credentials</li>";
echo "<li>The project might be named something like:</li>";
echo "<ul>";
echo "<li>'Amarjit Electrical Store'</li>";
echo "<li>'My Project'</li>";
echo "<li>'Quickstart-...'</li>";
echo "<li>Or any custom name you gave it</li>";
echo "</ul>";
echo "</ol>";

echo "<h3>Step 3: Navigate to Credentials</h3>";
echo "<ol>";
echo "<li>In the correct project, go to: <strong>APIs & Services</strong> → <strong>Credentials</strong></li>";
echo "<li>Look for the <strong>OAuth 2.0 Client IDs</strong> section</li>";
echo "<li>Find the entry with Client ID: <code>2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7</code></li>";
echo "</ol>";

echo "<h3>Step 4: Edit the OAuth Client</h3>";
echo "<ol>";
echo "<li>Click the <strong>pencil/edit icon ✏️</strong> next to your OAuth client</li>";
echo "<li>Scroll down to find <strong>'Authorized redirect URIs'</strong></li>";
echo "<li>This section should show your current redirect URIs</li>";
echo "</ol>";

echo "<h2>📋 Required Configuration</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>In the 'Authorized redirect URIs' section, you MUST have:</h3>";
echo "<div style='background: #ffffff; padding: 15px; border: 2px solid #4caf50; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Copy these EXACT URLs (one per line):</h4>";
echo "<pre style='background: #f5f5f5; padding: 10px; margin: 5px 0; font-family: monospace; font-size: 14px;'>";
echo "https://legendary-preet.ct.ws/google_callback.php";
echo "</pre>";
echo "<pre style='background: #f5f5f5; padding: 10px; margin: 5px 0; font-family: monospace; font-size: 14px;'>";
echo "https://legendary-preet.ct.ws/google_simple_callback.php";
echo "</pre>";
echo "</div>";
echo "</div>";

echo "<h2>⚠️ Common Mistakes</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #ffeaa7;'><th>❌ Wrong</th><th>✅ Correct</th></tr>";
echo "<tr><td>http://legendary-preet.ct.ws/google_callback.php</td><td>https://legendary-preet.ct.ws/google_callback.php</td></tr>";
echo "<tr><td>https://legendary-preet.ct.ws/google_callback.php/</td><td>https://legendary-preet.ct.ws/google_callback.php</td></tr>";
echo "<tr><td>https://legendary-preet.ct.ws/Google_Callback.php</td><td>https://legendary-preet.ct.ws/google_callback.php</td></tr>";
echo "<tr><td>https://www.legendary-preet.ct.ws/google_callback.php</td><td>https://legendary-preet.ct.ws/google_callback.php</td></tr>";
echo "</table>";
echo "</div>";

echo "<h2>🔧 Troubleshooting Steps</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>If you can't find your OAuth client:</h3>";
echo "<ol>";
echo "<li><strong>Check all your Google Cloud projects</strong> - use the project switcher</li>";
echo "<li><strong>Search for the Client ID</strong> - use browser search (Ctrl+F) for '2633417852'</li>";
echo "<li><strong>Create a new OAuth client</strong> if you can't find the original one</li>";
echo "</ol>";

echo "<h3>If you found the OAuth client but changes don't work:</h3>";
echo "<ol>";
echo "<li><strong>Delete existing redirect URIs</strong> and add them fresh</li>";
echo "<li><strong>Wait 20-30 minutes</strong> for Google's changes to propagate</li>";
echo "<li><strong>Clear browser cache</strong> or use incognito mode</li>";
echo "<li><strong>Try different browser</strong> to rule out cache issues</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🆘 Nuclear Option: Create New OAuth Client</h2>";
echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<p>If nothing works, create a completely new OAuth 2.0 Client ID:</p>";
echo "<ol>";
echo "<li>In Google Cloud Console → APIs & Services → Credentials</li>";
echo "<li>Click <strong>'+ CREATE CREDENTIALS'</strong> → <strong>'OAuth 2.0 Client ID'</strong></li>";
echo "<li>Choose <strong>'Web application'</strong></li>";
echo "<li>Name: 'Amarjit Electrical Store'</li>";
echo "<li>Authorized redirect URIs: Add both URLs above</li>";
echo "<li>Click <strong>Create</strong></li>";
echo "<li>Update your config.php with the new Client ID and Secret</li>";
echo "</ol>";
echo "</div>";

echo "<h2>📞 Need Help?</h2>";
echo "<p>If you're still stuck, take a screenshot of:</p>";
echo "<ul>";
echo "<li>Your Google Cloud Console project list</li>";
echo "<li>The Credentials page showing OAuth 2.0 Client IDs</li>";
echo "<li>The edit page for your specific OAuth client (showing redirect URIs)</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🧪 Test When Ready</h3>";
echo "<p>After fixing your Google Console configuration, test here:</p>";
echo "<p><a href='google_oauth_simple.php' style='background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 Test Google OAuth</a></p>";
?>