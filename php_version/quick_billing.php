<?php
require_once 'config.php';
requireLogin();

$customer = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search_phone'])) {
        // Search customer by phone
        $phone = cleanInput($_POST['phone']);
        if ($phone) {
            $customer = getCustomerWithRecentBills($phone);
            if (!$customer) {
                $message = "Customer not found with phone number: $phone";
            }
        }
    } elseif (isset($_POST['quick_action'])) {
        // Process quick sale or payment
        $phone = cleanInput($_POST['phone']);
        $action = cleanInput($_POST['action']);
        $amount = floatval($_POST['amount']);
        $description = cleanInput($_POST['description'] ?? '');
        
        $customer_data = getCustomerByPhone($phone);
        if ($customer_data) {
            if ($action === 'sale') {
                $result = createTransaction($customer_data['id'], $amount, $description, date('Y-m-d'), 'purchase');
                if ($result['success']) {
                    setFlashMessage("Sale of " . formatCurrency($amount) . " added for {$customer_data['name']}. New debt: " . formatCurrency($customer_data['total_debt'] + $amount), 'success');
                } else {
                    setFlashMessage($result['message'], 'error');
                }
            } elseif ($action === 'payment') {
                $result = createPayment($customer_data['id'], $amount, date('Y-m-d'), $description);
                if ($result['success']) {
                    $new_debt = max(0, $customer_data['total_debt'] - $amount);
                    setFlashMessage("Payment of " . formatCurrency($amount) . " recorded for {$customer_data['name']}. Remaining debt: " . formatCurrency($new_debt), 'success');
                } else {
                    setFlashMessage($result['message'], 'error');
                }
            }
            // Refresh customer data
            $customer = getCustomerWithRecentBills($phone);
        } else {
            setFlashMessage('Customer not found!', 'error');
        }
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
        .debt-positive { color: #dc3545; font-weight: bold; }
        .debt-zero { color: #28a745; font-weight: bold; }
        .quick-action-btn {
            border-radius: 10px;
            padding: 15px;
            font-size: 1.1rem;
            margin: 5px 0;
        }
        .customer-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                transform: translateX(0);
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">⚡ Quick Billing</h1>
                <p class="text-muted">Fast customer search and instant transactions</p>
            </div>
            <button class="btn btn-primary d-md-none" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($message): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Search Section -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search text-primary"></i> Customer Search</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" name="phone" placeholder="Enter customer phone number" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                       pattern="[0-9]{10}" title="Please enter a 10-digit phone number" required autofocus>
                                <button type="submit" name="search_phone" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>

                        <?php if ($customer): ?>
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Customer Found!</h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <strong><?php echo htmlspecialchars($customer['name']); ?></strong> (<?php echo htmlspecialchars($customer['phone']); ?>)<br>
                                    Current Debt: <span class="<?php echo $customer['total_debt'] > 0 ? 'debt-positive' : 'debt-zero'; ?>">
                                        <?php echo formatCurrency($customer['total_debt']); ?>
                                    </span><br>
                                    Customer since: <?php echo $customer['created_date']; ?>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="customer_detail.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-bolt text-warning"></i> Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="quickActionForm">
                                    <input type="hidden" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Action Type</label>
                                            <select class="form-select" name="action" id="actionType" required>
                                                <option value="">Select action...</option>
                                                <option value="sale">Record Sale (Add to Debt)</option>
                                                <option value="payment">Record Payment (Reduce Debt)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Amount (₹)</label>
                                            <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Description (Optional)</label>
                                        <input type="text" class="form-control" name="description" placeholder="Item description or payment note">
                                    </div>
                                    
                                    <button type="submit" name="quick_action" class="btn btn-success btn-lg w-100" id="submitBtn" disabled>
                                        <i class="fas fa-plus-circle"></i> Process Transaction
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Customer Info Sidebar -->
            <div class="col-lg-4">
                <?php if ($customer): ?>
                <div class="card customer-info-card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-user text-info"></i> Customer Information</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['address'] ?: 'Not provided'); ?></p>
                        <p><strong>Current Debt:</strong> 
                            <span class="<?php echo $customer['total_debt'] > 0 ? 'debt-positive' : 'debt-zero'; ?>">
                                <?php echo formatCurrency($customer['total_debt']); ?>
                            </span>
                        </p>
                        <p><strong>Customer Since:</strong> <?php echo $customer['created_date']; ?></p>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <small><strong><?php echo $customer['total_transactions']; ?></strong><br>Purchases</small>
                            </div>
                            <div class="col-4">
                                <small><strong><?php echo $customer['total_payments']; ?></strong><br>Payments</small>
                            </div>
                            <div class="col-4">
                                <small><strong><?php echo count($customer['recent_bills']); ?></strong><br>Bills</small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($customer['recent_bills'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-images text-success"></i> Recent Bill Photos</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($customer['recent_bills'] as $bill): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <small>
                                <strong><?php echo $bill['date']; ?></strong> - <?php echo formatCurrency($bill['amount']); ?><br>
                                <?php echo htmlspecialchars($bill['description']); ?><br>
                                <a href="<?php echo htmlspecialchars($bill['bill_url']); ?>" target="_blank" class="btn btn-xs btn-success">
                                    <i class="fas fa-image"></i> View Photo
                                </a>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <!-- Help Card -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-question-circle text-info"></i> Quick Help</h6>
                    </div>
                    <div class="card-body">
                        <h6>How to use Quick Billing:</h6>
                        <ol class="small">
                            <li><strong>Search:</strong> Enter customer's phone number</li>
                            <li><strong>Found:</strong> Customer details will appear</li>
                            <li><strong>Action:</strong> Choose sale or payment</li>
                            <li><strong>Amount:</strong> Enter the amount</li>
                            <li><strong>Process:</strong> Click to complete</li>
                        </ol>
                        
                        <hr>
                        
                        <h6>Perfect for:</h6>
                        <ul class="small">
                            <li>📱 Mobile shop use</li>
                            <li>⚡ Quick transactions</li>
                            <li>💰 Instant debt updates</li>
                            <li>📸 Bill photo access</li>
                        </ul>
                        
                        <div class="text-center mt-3">
                            <a href="add_customer.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-user-plus"></i> Add New Customer
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        // Enable submit button when action is selected
        document.getElementById('actionType').addEventListener('change', function() {
            const submitBtn = document.getElementById('submitBtn');
            const actionType = this.value;
            
            if (actionType) {
                submitBtn.disabled = false;
                if (actionType === 'sale') {
                    submitBtn.className = 'btn btn-warning btn-lg w-100';
                    submitBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Record Sale';
                } else if (actionType === 'payment') {
                    submitBtn.className = 'btn btn-success btn-lg w-100';
                    submitBtn.innerHTML = '<i class="fas fa-hand-holding-usd"></i> Record Payment';
                }
            } else {
                submitBtn.disabled = true;
                submitBtn.className = 'btn btn-secondary btn-lg w-100';
                submitBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Process Transaction';
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Auto-focus phone input
        document.querySelector('input[name="phone"]').focus();
    </script>
</body>
</html>