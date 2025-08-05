<?php
require_once 'config.php';
requireLogin();

$message = '';
$message_type = '';
$customer_data = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search_phone'])) {
        // Search customer by phone
        $phone = trim($_POST['phone']);
        if (!empty($phone)) {
            $customer_data = getCustomerWithRecentBills($phone);
        } else {
            $message = 'Please enter a phone number';
            $message_type = 'warning';
        }
    } elseif (isset($_POST['add_sale']) || isset($_POST['add_payment']) || isset($_POST['quick_action'])) {
        // Process transaction or payment
        $customer_id = intval($_POST['customer_id']);
        $amount = floatval($_POST['amount']);
        
        if (isset($_POST['add_sale']) || (isset($_POST['type']) && $_POST['type'] === 'sale')) {
            // Handle sale transaction
            $description = trim($_POST['description'] ?? 'Sale');
            $bill_image_url = null;
            $google_drive_file_id = null;
            
            // Handle bill photo upload
            if (isset($_FILES['bill_photo']) && $_FILES['bill_photo']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleBillPhotoUpload($_FILES['bill_photo'], $customer_id);
                if ($upload_result['success']) {
                    $bill_image_url = $upload_result['url'];
                    $google_drive_file_id = $upload_result['file_id'];
                } else {
                    $message = 'Sale recorded but photo upload failed: ' . $upload_result['error'];
                    $message_type = 'warning';
                }
            }
            
            if (addTransaction($customer_id, $amount, $description, $bill_image_url, $google_drive_file_id)) {
                $message = 'Sale recorded successfully' . ($bill_image_url ? ' with bill photo' : '');
                $message_type = 'success';
                
                // Refresh customer data
                $customer = getCustomerById($customer_id);
                if ($customer) {
                    $customer_data = getCustomerWithRecentBills($customer['phone']);
                }
            } else {
                $message = 'Failed to record sale';
                $message_type = 'danger';
            }
        } elseif (isset($_POST['add_payment']) || (isset($_POST['type']) && $_POST['type'] === 'payment')) {
            // Handle payment
            $notes = trim($_POST['notes'] ?? 'Payment');
            
            if (addPayment($customer_id, $amount, $notes)) {
                $message = 'Payment recorded successfully';
                $message_type = 'success';
                
                // Refresh customer data
                $customer = getCustomerById($customer_id);
                if ($customer) {
                    $customer_data = getCustomerWithRecentBills($customer['phone']);
                }
            } else {
                $message = 'Failed to record payment';
                $message_type = 'danger';
            }
        }
        
        // Handle redirect if specified
        if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
            header('Location: ' . $_POST['redirect']);
            exit;
        }
    }
}

// Handle bill photo upload
function handleBillPhotoUpload($file, $customer_id) {
    // Validate file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Only JPG, PNG, and GIF images are allowed'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size must be less than 5MB'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'bill_' . $customer_id . '_' . time() . '.' . $extension;
    $upload_path = UPLOAD_DIR . $filename;
    
    // Move uploaded file to temporary location
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file'];
    }
    
    // Try to upload to Google Drive
    $google_result = uploadToGoogleDrive($upload_path, $filename, $_SESSION['user_id']);
    
    if ($google_result) {
        // Success - delete local file and return Google Drive URL
        unlink($upload_path);
        return [
            'success' => true,
            'url' => $google_result['url'],
            'file_id' => $google_result['id']
        ];
    } else {
        // Google Drive failed - keep local file
        return [
            'success' => true,
            'url' => 'uploads/' . $filename,
            'file_id' => null
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Quick Billing</title>
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
        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .customer-found {
            border: 2px solid #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        .debt-positive {
            color: #dc3545;
            font-weight: bold;
        }
        .debt-zero {
            color: #28a745;
            font-weight: bold;
        }
        .recent-bill {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .bill-photo-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
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
            .search-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-bolt me-2 text-warning"></i>Quick Billing</h2>
                    <p class="text-muted mb-0">Search customers and process transactions instantly</p>
                </div>
                <div>
                    <a href="customers.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-users me-1"></i>All Customers
                    </a>
                    <a href="add_customer.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>New Customer
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Search Section -->
            <div class="search-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4><i class="fas fa-search me-2"></i>Find Customer</h4>
                        <form method="POST" class="d-flex mt-3">
                            <input type="text" name="phone" class="form-control form-control-lg me-3" 
                                   placeholder="Enter phone number..." 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                   autocomplete="off" autofocus>
                            <button type="submit" name="search_phone" class="btn btn-light btn-lg">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-mobile-alt fa-3x mb-2 opacity-50"></i>
                        <p class="mb-0 small">Optimized for mobile use</p>
                    </div>
                </div>
            </div>

            <?php if ($customer_data): ?>
            <?php if ($customer_data['found']): ?>
            <!-- Customer Found -->
            <div class="card customer-found mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2"></i>Customer Found
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><?php echo htmlspecialchars($customer_data['name']); ?></h4>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($customer_data['phone']); ?></p>
                            <p class="mb-1"><strong>Customer since:</strong> <?php echo $customer_data['created_date']; ?></p>
                            <?php if ($customer_data['address']): ?>
                            <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($customer_data['address']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="mb-3">
                                <h3 class="<?php echo $customer_data['total_debt'] > 0 ? 'debt-positive' : 'debt-zero'; ?>">
                                    ₹<?php echo number_format($customer_data['total_debt'], 2); ?>
                                </h3>
                                <small class="text-muted">Outstanding Debt</small>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <strong><?php echo $customer_data['total_transactions']; ?></strong><br>
                                    <small class="text-muted">Total Sales</small>
                                </div>
                                <div class="col-6">
                                    <strong><?php echo $customer_data['total_payments']; ?></strong><br>
                                    <small class="text-muted">Total Payments</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bills -->
            <?php if (!empty($customer_data['recent_bills'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Recent Purchase History</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($customer_data['recent_bills'] as $bill): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="recent-bill">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($bill['description']); ?></h6>
                                        <p class="mb-1 fw-bold text-primary">₹<?php echo number_format($bill['amount'], 2); ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo formatDate($bill['transaction_date']); ?>
                                        </small>
                                    </div>
                                    <?php if ($bill['bill_image_url']): ?>
                                    <div class="ms-2">
                                        <img src="<?php echo htmlspecialchars($bill['bill_image_url']); ?>" 
                                             class="bill-photo-preview" 
                                             alt="Bill Photo"
                                             onclick="viewBillPhoto('<?php echo htmlspecialchars($bill['bill_image_url']); ?>')">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Record New Sale</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="customer_id" value="<?php echo $customer_data['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (₹) *</label>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="description" name="description" 
                                           placeholder="Items purchased...">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bill_photo" class="form-label">
                                        <i class="fas fa-camera me-1"></i>Bill Photo (Optional)
                                    </label>
                                    <input type="file" class="form-control" id="bill_photo" name="bill_photo" 
                                           accept="image/*" capture="environment">
                                    <div class="form-text">
                                        <?php if (isGoogleDriveConnected($_SESSION['user_id'])): ?>
                                        <i class="fab fa-google-drive text-success me-1"></i>Will be automatically uploaded to Google Drive
                                        <?php else: ?>
                                        <i class="fas fa-info-circle text-info me-1"></i>Connect Google Drive in Settings for automatic backup
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <button type="submit" name="add_sale" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Record Sale
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i>Record Payment</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="customer_id" value="<?php echo $customer_data['id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="payment_amount" class="form-label">Payment Amount (₹) *</label>
                                    <input type="number" step="0.01" class="form-control" id="payment_amount" 
                                           name="amount" required>
                                    <?php if ($customer_data['total_debt'] > 0): ?>
                                    <div class="form-text">
                                        Outstanding debt: ₹<?php echo number_format($customer_data['total_debt'], 2); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Payment Notes</label>
                                    <input type="text" class="form-control" id="notes" name="notes" 
                                           placeholder="Cash payment, online transfer, etc.">
                                </div>
                                
                                <button type="submit" name="add_payment" class="btn btn-success w-100">
                                    <i class="fas fa-hand-holding-usd me-2"></i>Record Payment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Row -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-3">Quick Actions</h6>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="customer_detail.php?id=<?php echo $customer_data['id']; ?>" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-1"></i>View Full History
                                </a>
                                <a href="quick_billing.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-search me-1"></i>Search Another Customer
                                </a>
                                <a href="index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Customer Not Found -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-times me-2"></i>Customer Not Found
                    </h5>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>No customer found with phone number: <?php echo htmlspecialchars($_POST['phone']); ?></h5>
                    <p class="text-muted">Would you like to add this as a new customer?</p>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="add_customer.php?phone=<?php echo urlencode($_POST['phone']); ?>" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Add New Customer
                        </a>
                        <a href="quick_billing.php" class="btn btn-secondary">
                            <i class="fas fa-search me-2"></i>Search Again
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Help Section -->
            <?php if (!$customer_data): ?>
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>How to Use Quick Billing</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <i class="fas fa-search fa-2x text-primary mb-2"></i>
                                <h6>1. Search Customer</h6>
                                <p class="small text-muted">Enter phone number to find existing customer</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                                <h6>2. Record Transaction</h6>
                                <p class="small text-muted">Add sale with bill photo or record payment</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <i class="fas fa-cloud-upload-alt fa-2x text-info mb-2"></i>
                                <h6>3. Auto Backup</h6>
                                <p class="small text-muted">Photos automatically saved to Google Drive</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bill Photo Modal -->
    <div class="modal fade" id="billPhotoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bill Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="billPhotoImage" src="" class="img-fluid" alt="Bill Photo">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewBillPhoto(url) {
            document.getElementById('billPhotoImage').src = url;
            new bootstrap.Modal(document.getElementById('billPhotoModal')).show();
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Focus on amount field when customer is found
        <?php if ($customer_data && $customer_data['found']): ?>
        document.getElementById('amount').focus();
        <?php endif; ?>
    </script>
</body>
</html>