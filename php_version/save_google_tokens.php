<?php
// Save Google Drive tokens manually (for InfinityFree hosting)
require_once 'config.php';
require_once 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token_response'])) {
    $token_response = trim($_POST['token_response']);
    
    if (empty($token_response)) {
        $message = 'Please paste the token response from the cURL command.';
    } else {
        // Try to decode JSON response
        $tokens = json_decode($token_response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $message = 'Invalid JSON format. Please copy the exact response from the cURL command.';
        } elseif (!isset($tokens['access_token'])) {
            $message = 'Invalid token response. Make sure it contains access_token.';
        } else {
            // Get user ID (fallback to 1 if not logged in)
            $user_id = $_SESSION['user_id'] ?? 1;
            
            // Save tokens to database
            if (saveGoogleTokens($user_id, $tokens)) {
                $success = true;
                $message = 'Google Drive tokens saved successfully! You can now upload bill photos.';
            } else {
                $message = 'Failed to save tokens to database. Please check database connection.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Google Drive Tokens</title>
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
        .token-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 90%;
        }
    </style>
</head>
<body>
    <div class="token-container">
        <div class="text-center mb-4">
            <i class="fas fa-database text-primary" style="font-size: 3rem;"></i>
            <h2 class="text-primary mt-3">Save Google Drive Tokens</h2>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $success ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="text-center">
                <h4 class="text-success">🎉 Google Drive Connected!</h4>
                <p>You can now upload bill photos automatically to Google Drive.</p>
                <a href="index.php" class="btn btn-success">
                    <i class="fas fa-home"></i> Go to Dashboard
                </a>
                <a href="quick_billing.php" class="btn btn-primary ms-2">
                    <i class="fas fa-receipt"></i> Try Quick Billing
                </a>
            </div>
        <?php else: ?>
            <form method="post">
                <div class="mb-3">
                    <label for="token_response" class="form-label">
                        <i class="fas fa-code"></i> Paste Token Response (JSON)
                    </label>
                    <textarea class="form-control" id="token_response" name="token_response" rows="6" 
                              placeholder='{"access_token":"ya29.a0AfH6SMC...","refresh_token":"1//04...","expires_in":3600,"token_type":"Bearer"}' 
                              required><?php echo isset($_POST['token_response']) ? htmlspecialchars($_POST['token_response']) : ''; ?></textarea>
                    <small class="text-muted">
                        Copy the entire JSON response from the cURL command and paste it here.
                    </small>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Tokens
                    </button>
                    <a href="google_callback_fixed.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to OAuth
                    </a>
                </div>
            </form>

            <hr>
            
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6><i class="fas fa-info-circle"></i> Instructions</h6>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Run the cURL command from the previous page</li>
                        <li>Copy the entire JSON response</li>
                        <li>Paste it in the text area above</li>
                        <li>Click "Save Tokens"</li>
                    </ol>
                    <p class="mb-0"><strong>Expected format:</strong></p>
                    <code>{"access_token":"...", "refresh_token":"...", "expires_in":3600}</code>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>