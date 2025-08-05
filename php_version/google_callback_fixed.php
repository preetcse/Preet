<?php
// Fixed Google OAuth callback for InfinityFree hosting
// No login required, manual token exchange to bypass cURL restrictions

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error_message = '';
$success_message = '';
$auth_code = '';

if (isset($_GET['code'])) {
    $auth_code = $_GET['code'];
    $success_message = 'Authorization code received successfully!';
} elseif (isset($_GET['error'])) {
    $error_message = 'Google Drive authorization failed: ' . htmlspecialchars($_GET['error']);
    if (isset($_GET['error_description'])) {
        $error_message .= ' - ' . htmlspecialchars($_GET['error_description']);
    }
} else {
    $error_message = 'No authorization code received.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Drive Connection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .callback-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 800px;
            width: 90%;
        }
    </style>
</head>
<body>
    <div class="callback-container">
        <?php if ($success_message): ?>
            <div class="text-center mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <h2 class="text-success mt-3">🎉 Google OAuth Success!</h2>
                <p class="text-muted"><?php echo $success_message; ?></p>
            </div>

            <div class="alert alert-success">
                <h5><i class="fas fa-key"></i> Authorization Code Received</h5>
                <p>Your authorization code: <code><?php echo htmlspecialchars(substr($auth_code, 0, 30)); ?>...</code></p>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-terminal"></i> Step 1: Exchange Code for Tokens</h5>
                </div>
                <div class="card-body">
                    <p>Since InfinityFree blocks cURL, copy this command and run it elsewhere:</p>
                    <textarea class="form-control" rows="8" readonly style="font-family: monospace; font-size: 12px;">curl -X POST https://oauth2.googleapis.com/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "client_id=2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com" \
  -d "client_secret=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" \
  -d "code=<?php echo htmlspecialchars($auth_code); ?>" \
  -d "grant_type=authorization_code" \
  -d "redirect_uri=https://legendary-preet.ct.ws/google_callback_fixed.php"</textarea>
                    <small class="text-muted">Run this in terminal or online cURL tool</small>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-database"></i> Step 2: Save Tokens Manually</h5>
                </div>
                <div class="card-body">
                    <p>After running the cURL command, paste the response here:</p>
                    <form method="post" action="save_google_tokens.php">
                        <div class="mb-3">
                            <label for="token_response" class="form-label">Token Response (JSON):</label>
                            <textarea class="form-control" id="token_response" name="token_response" rows="4" placeholder='{"access_token":"...","refresh_token":"...","expires_in":3600}'></textarea>
                        </div>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save"></i> Save Tokens
                        </button>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center mb-4">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                <h2 class="text-danger mt-3">OAuth Error</h2>
            </div>

            <div class="alert alert-danger">
                <h5><i class="fas fa-times-circle"></i> Connection Failed</h5>
                <p><?php echo $error_message; ?></p>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5><i class="fas fa-redo"></i> Try Again</h5>
            </div>
            <div class="card-body">
                <p>Start a new Google Drive connection:</p>
                <?php
                $oauth_params = [
                    'client_id' => '2633417852-5ko03ljkcc8npsa23pg3im3lcmpf7ef7.apps.googleusercontent.com',
                    'redirect_uri' => 'https://legendary-preet.ct.ws/google_callback_fixed.php',
                    'scope' => 'https://www.googleapis.com/auth/drive.file',
                    'response_type' => 'code',
                    'access_type' => 'offline',
                    'prompt' => 'consent'
                ];
                $oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($oauth_params);
                ?>
                <a href="<?php echo $oauth_url; ?>" class="btn btn-primary">
                    <i class="fab fa-google-drive"></i> Connect Google Drive
                </a>
                <a href="index.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>