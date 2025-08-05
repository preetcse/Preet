<?php
require_once 'config.php';

// Redirect if user already exists
if (userExists()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $result = createUser($username, $password);
        if ($result['success']) {
            $success = 'Account created successfully! You can now login.';
            // Auto-redirect after 3 seconds
            header('refresh:3;url=login.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Setup</title>
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
        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
        }
        .store-logo {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #e9ecef;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .feature-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="setup-card">
                    <div class="text-center mb-4">
                        <div class="store-logo">⚡</div>
                        <h2 class="h4 mb-2">Welcome to <?php echo APP_NAME; ?></h2>
                        <p class="text-muted">Customer Credit Management System</p>
                        <p class="small text-success">🌐 Running on Legendary-Preet.ct.ws</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <br><small>Redirecting to login page...</small>
                    </div>
                    <?php else: ?>

                    <div class="mb-4">
                        <h5 class="text-center mb-3">🚀 First Time Setup</h5>
                        <p class="text-center small text-muted">Create your admin account to get started</p>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-2"></i>Admin Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                   minlength="3" required autofocus>
                            <div class="form-text">Minimum 3 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" required>
                            <div class="form-text">Minimum 6 characters</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirm Password
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-rocket me-2"></i>Create Account & Start
                        </button>
                    </form>

                    <div class="feature-list">
                        <h6 class="mb-3 text-center">✨ What You'll Get:</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <small>
                                    <i class="fas fa-users text-primary"></i><br>
                                    <strong>Customer Management</strong><br>
                                    Store customer details & debt
                                </small>
                            </div>
                            <div class="col-6">
                                <small>
                                    <i class="fas fa-bolt text-warning"></i><br>
                                    <strong>Quick Billing</strong><br>
                                    Fast phone search & transactions
                                </small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <small>
                                    <i class="fas fa-images text-success"></i><br>
                                    <strong>Bill Photos</strong><br>
                                    Store receipts in Google Drive
                                </small>
                            </div>
                            <div class="col-6">
                                <small>
                                    <i class="fas fa-mobile-alt text-info"></i><br>
                                    <strong>Mobile Ready</strong><br>
                                    Perfect for shop use
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Secure • Free Forever • No Ads
                        </small>
                    </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>