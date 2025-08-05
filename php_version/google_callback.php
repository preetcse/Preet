<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$error_message = '';
$success_message = '';

if (isset($_GET['code'])) {
    // Exchange authorization code for tokens
    $tokens = exchangeCodeForTokens($_GET['code']);
    
    if ($tokens) {
        // Save tokens to database
        if (saveGoogleTokens($_SESSION['user_id'], $tokens)) {
            $success_message = 'Google Drive connected successfully! You can now upload bill photos automatically.';
        } else {
            $error_message = 'Failed to save Google Drive tokens. Please try again.';
        }
    } else {
        $error_message = 'Failed to connect to Google Drive. Please try again.';
    }
} elseif (isset($_GET['error'])) {
    $error_message = 'Google Drive authorization was denied or failed: ' . htmlspecialchars($_GET['error']);
} else {
    $error_message = 'Invalid callback request.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Google Drive Connection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .callback-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="callback-card">
        <div class="card-body p-5 text-center">
            <?php if ($success_message): ?>
            <div class="mb-4">
                <i class="fab fa-google-drive fa-4x text-success mb-3"></i>
                <h2 class="text-success">Connected!</h2>
                <p class="text-muted"><?php echo htmlspecialchars($success_message); ?></p>
            </div>
            <div class="alert alert-success">
                <h6><i class="fas fa-check-circle me-2"></i>What's Next?</h6>
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-upload me-2"></i>Upload bill photos automatically</li>
                    <li><i class="fas fa-cloud me-2"></i>15GB free cloud storage</li>
                    <li><i class="fas fa-shield-alt me-2"></i>Secure backup in Google Drive</li>
                </ul>
            </div>
            <?php else: ?>
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                <h2 class="text-danger">Connection Failed</h2>
                <p class="text-muted"><?php echo htmlspecialchars($error_message); ?></p>
            </div>
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Need Help?</h6>
                <p class="mb-0">Go to Settings and try connecting again, or contact support if the problem persists.</p>
            </div>
            <?php endif; ?>
            
            <div class="d-flex gap-2 justify-content-center mt-4">
                <a href="settings.php" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
                <a href="quick_billing.php" class="btn btn-success">
                    <i class="fas fa-bolt me-2"></i>Quick Billing
                </a>
                <a href="index.php" class="btn btn-info">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-redirect to settings after 5 seconds if successful
        <?php if ($success_message): ?>
        setTimeout(function() {
            window.location.href = 'settings.php';
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>