<?php
require_once 'config.php';
requireLogin();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($name)) {
        $error = 'Customer name is required';
    } elseif (empty($phone)) {
        $error = 'Phone number is required';
    } elseif (getCustomerByPhone($phone)) {
        $error = 'A customer with this phone number already exists';
    } else {
        $result = createCustomer($name, $phone, $address);
        if ($result) {
            $success = true;
            $customer_id = $result;
            // Clear form
            $name = $phone = $address = '';
        } else {
            $error = 'Failed to create customer. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Add Customer</title>
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
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-user-plus me-2"></i>Add New Customer
                            </h4>
                        </div>
                        <div class="card-body">
                            <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                Customer added successfully! 
                                <a href="customer_detail.php?id=<?php echo $customer_id; ?>" class="alert-link">View Customer</a>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="customerForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user me-1 text-primary"></i>Customer Name *
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                                               required autocomplete="off" placeholder="Enter customer name">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-1 text-success"></i>Phone Number *
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($phone ?? $_GET['phone'] ?? ''); ?>" 
                                               required autocomplete="off" placeholder="Enter phone number">
                                        <div class="form-text">Phone number must be unique</div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1 text-info"></i>Address (Optional)
                                    </label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              placeholder="Enter customer address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="customers.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Customers
                                    </a>
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="fas fa-eraser me-1"></i>Clear Form
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Add Customer
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Quick Tips -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-lightbulb me-2 text-warning"></i>Quick Tips</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Phone numbers must be unique</strong> - Each customer needs a different phone number
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Use the Quick Billing tab</strong> to instantly find customers by phone and add transactions
                                </li>
                                <li>
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Address is optional</strong> but helps identify customers with similar names
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on name field
        document.getElementById('name').focus();
        
        // Phone number validation
        document.getElementById('phone').addEventListener('input', function(e) {
            // Remove any non-numeric characters except + and -
            let value = e.target.value.replace(/[^\d\+\-]/g, '');
            e.target.value = value;
        });
        
        // Form validation
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            if (!name) {
                alert('Please enter customer name');
                document.getElementById('name').focus();
                e.preventDefault();
                return;
            }
            
            if (!phone) {
                alert('Please enter phone number');
                document.getElementById('phone').focus();
                e.preventDefault();
                return;
            }
            
            if (phone.length < 10) {
                alert('Please enter a valid phone number (at least 10 digits)');
                document.getElementById('phone').focus();
                e.preventDefault();
                return;
            }
        });
        
        // Auto-dismiss success message after 5 seconds
        setTimeout(function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                const alert = new bootstrap.Alert(successAlert);
                alert.close();
            }
        }, 5000);
    </script>
</body>
</html>