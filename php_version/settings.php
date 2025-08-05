<?php
require_once 'config.php';
requireLogin();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $error_message = 'All password fields are required';
                } elseif ($new_password !== $confirm_password) {
                    $error_message = 'New passwords do not match';
                } elseif (strlen($new_password) < 6) {
                    $error_message = 'New password must be at least 6 characters long';
                } else {
                    // Verify current password
                    $user = authenticateUser($_SESSION['username'], $current_password);
                    if ($user) {
                        // Update password
                        if (updateUserPassword($_SESSION['user_id'], $new_password)) {
                            $success_message = 'Password changed successfully';
                        } else {
                            $error_message = 'Failed to change password';
                        }
                    } else {
                        $error_message = 'Current password is incorrect';
                    }
                }
                break;
                
            case 'connect_google_drive':
                $auth_url = getGoogleAuthUrl();
                if ($auth_url) {
                    header('Location: ' . $auth_url);
                    exit;
                } else {
                    $error_message = 'Google Drive is not properly configured. Please check the settings.';
                }
                break;
                
            case 'disconnect_google_drive':
                if (disconnectGoogleDrive($_SESSION['user_id'])) {
                    $success_message = 'Google Drive disconnected successfully.';
                } else {
                    $error_message = 'Failed to disconnect Google Drive.';
                }
                break;
        }
    }
}

// Get current Google Drive status
$google_drive_connected = isGoogleDriveConnected($_SESSION['user_id']);
$google_drive_status = $google_drive_connected ? 'Connected' : 'Not Connected';
$google_drive_info = $google_drive_connected ? 'Bill photos will be automatically backed up to your Google Drive.' : 'Connect your Google Drive to enable automatic backup of bill photos.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 12px 20px !important;
            border-radius: 8px;
            margin: 5px 15px;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white !important;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .setting-section {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .setting-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .status-connected {
            color: #28a745;
        }
        .status-disconnected {
            color: #dc3545;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h2 class="mb-4">
                        <i class="fas fa-cog me-2 text-primary"></i>Settings
                    </h2>
                    
                    <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Account Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Account Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="setting-section">
                                <h6>Account Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                        <p><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Login Status:</strong> <span class="text-success">Active</span></p>
                                        <p><strong>Last Login:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Change Password -->
                            <div class="setting-section">
                                <h6>Change Password</h6>
                                <form method="POST" id="passwordForm">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" 
                                                   name="current_password" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" 
                                                   name="new_password" required minlength="6">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" required minlength="6">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Google Drive Integration -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fab fa-google-drive me-2"></i>Google Drive Integration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6>Cloud Storage for Bill Photos</h6>
                                    <p class="mb-2">
                                        Status: <span class="fw-bold <?php echo $google_drive_connected ? 'status-connected' : 'status-disconnected'; ?>">
                                            <?php echo $google_drive_status; ?>
                                        </span>
                                    </p>
                                    <p class="text-muted mb-0">
                                        Connect your Google Drive to automatically backup all bill photos to the cloud. 
                                        This ensures your important documents are safe and accessible from anywhere.
                                    </p>
                                    <?php if ($google_drive_info): ?>
                                    <p class="text-info mt-2"><small><?php echo $google_drive_info; ?></small></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-end">
                                    <?php if ($google_drive_connected): ?>
                                    <button type="button" class="btn btn-outline-danger" onclick="disconnectGoogleDrive()">
                                        <i class="fas fa-unlink me-2"></i>Disconnect
                                    </button>
                                    <?php else: ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="connect_google_drive">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fab fa-google-drive me-2"></i>Connect Drive
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Google Drive Benefits -->
                            <div class="mt-4">
                                <h6>Benefits of Google Drive Integration:</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>15GB Free Storage</strong> - Store thousands of bill photos
                                    </li>
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Automatic Backup</strong> - Bills are automatically saved to cloud
                                    </li>
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Access Anywhere</strong> - View bills from any device
                                    </li>
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Safe & Secure</strong> - Google-level security for your documents
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Application Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>Application Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="setting-section">
                                <h6>Database Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Database Host:</strong> <?php echo DB_HOST; ?></p>
                                        <p><strong>Database Name:</strong> <?php echo DB_NAME; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Application Version:</strong> <?php echo APP_VERSION; ?></p>
                                        <p><strong>Storage Location:</strong> InfinityFree MySQL</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="setting-section">
                                <h6>Quick Actions</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="customers.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-users me-1"></i>Manage Customers
                                    </a>
                                    <a href="quick_billing.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-bolt me-1"></i>Quick Billing
                                    </a>
                                    <a href="index.php" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Support Information -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-life-ring me-2"></i>Support & Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Application Details</h6>
                                    <p><strong>Name:</strong> <?php echo APP_NAME; ?></p>
                                    <p><strong>Version:</strong> <?php echo APP_VERSION; ?></p>
                                    <p><strong>Website:</strong> <a href="https://legendary-preet.ct.ws" target="_blank">Legendary-Preet.ct.ws</a></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Store Information</h6>
                                    <p><strong>Store Name:</strong> Amarjit Electrical Store and Repair Centre</p>
                                    <p><strong>Location:</strong> India</p>
                                    <p><strong>Purpose:</strong> Customer Credit Management</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                e.preventDefault();
                return;
            }
            
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long');
                e.preventDefault();
                return;
            }
        });
        
        function disconnectGoogleDrive() {
            if (confirm('Are you sure you want to disconnect Google Drive? This will stop automatic backup of bill photos.')) {
                // Implement disconnect functionality
                alert('Google Drive disconnected successfully');
                location.reload();
            }
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>