<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$customer_id = intval($_GET['id'] ?? 0);
if (!$customer_id) {
    header('Location: customers.php');
    exit;
}

$customer = getCustomerById($customer_id);
if (!$customer) {
    header('Location: customers.php');
    exit;
}

$transactions = getCustomerTransactions($customer_id);
$payments = getCustomerPayments($customer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo htmlspecialchars($customer['name']); ?></title>
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
        .debt-positive {
            color: #dc3545;
            font-weight: bold;
        }
        .debt-zero {
            color: #28a745;
            font-weight: bold;
        }
        .transaction-item, .payment-item {
            border-left: 4px solid #007bff;
            padding-left: 15px;
        }
        .payment-item {
            border-left-color: #28a745;
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
            <!-- Customer Header -->
            <div class="row mb-4">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>
                                <i class="fas fa-user me-2 text-primary"></i>
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </h2>
                            <p class="text-muted mb-0">Customer ID: <?php echo $customer['id']; ?></p>
                        </div>
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Customers
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Info Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-phone fa-2x mb-2"></i>
                            <h5><?php echo htmlspecialchars($customer['phone']); ?></h5>
                            <small>Phone Number</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card <?php echo $customer['total_debt'] > 0 ? 'bg-danger' : 'bg-success'; ?> text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-rupee-sign fa-2x mb-2"></i>
                            <h5>₹<?php echo number_format($customer['total_debt'], 2); ?></h5>
                            <small>Outstanding Debt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <h5><?php echo count($transactions); ?></h5>
                            <small>Total Purchases</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar fa-2x mb-2"></i>
                            <h5><?php echo formatDate($customer['created_date']); ?></h5>
                            <small>Customer Since</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Card -->
            <?php if ($customer['address']): ?>
            <div class="row mb-4">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h6><i class="fas fa-map-marker-alt me-2 text-info"></i>Address</h6>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <button class="btn btn-primary w-100" onclick="quickSale()">
                                        <i class="fas fa-shopping-cart me-2"></i>Record Sale
                                    </button>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <button class="btn btn-success w-100" onclick="quickPayment()">
                                        <i class="fas fa-money-bill me-2"></i>Record Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2 text-primary"></i>
                                Purchase History (<?php echo count($transactions); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($transactions)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No purchases yet</p>
                                <button class="btn btn-primary btn-sm" onclick="quickSale()">
                                    Record First Sale
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="timeline">
                                <?php foreach (array_reverse($transactions) as $transaction): ?>
                                <div class="transaction-item mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($transaction['description']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo formatDate($transaction['transaction_date']); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-primary">
                                            ₹<?php echo number_format($transaction['amount'], 2); ?>
                                        </span>
                                    </div>
                                    <?php if ($transaction['bill_image_url']): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo htmlspecialchars($transaction['bill_image_url']); ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-image me-1"></i>View Bill
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-hand-holding-usd me-2 text-success"></i>
                                Payment History (<?php echo count($payments); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($payments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No payments yet</p>
                                <button class="btn btn-success btn-sm" onclick="quickPayment()">
                                    Record First Payment
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="timeline">
                                <?php foreach (array_reverse($payments) as $payment): ?>
                                <div class="payment-item mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Payment Received</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo formatDate($payment['payment_date']); ?>
                                            </small>
                                            <?php if ($payment['notes']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($payment['notes']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-success">
                                            ₹<?php echo number_format($payment['amount'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function quickSale() {
            const amount = prompt('Enter sale amount:');
            if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
                const description = prompt('Enter sale description (optional):') || 'Quick Sale';
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'quick_billing.php';
                
                form.innerHTML = `
                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                    <input type="hidden" name="amount" value="${amount}">
                    <input type="hidden" name="description" value="${description}">
                    <input type="hidden" name="type" value="sale">
                    <input type="hidden" name="quick_action" value="1">
                    <input type="hidden" name="redirect" value="customer_detail.php?id=<?php echo $customer['id']; ?>">
                `;
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function quickPayment() {
            const amount = prompt('Enter payment amount:');
            if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
                const notes = prompt('Enter payment notes (optional):') || 'Quick Payment';
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'quick_billing.php';
                
                form.innerHTML = `
                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                    <input type="hidden" name="amount" value="${amount}">
                    <input type="hidden" name="notes" value="${notes}">
                    <input type="hidden" name="type" value="payment">
                    <input type="hidden" name="quick_action" value="1">
                    <input type="hidden" name="redirect" value="customer_detail.php?id=<?php echo $customer['id']; ?>">
                `;
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>