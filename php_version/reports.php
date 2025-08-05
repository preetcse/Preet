<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$stats = getDashboardStats();
$recent_transactions = getRecentTransactions(10);
$recent_payments = getRecentPayments(10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Reports</title>
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
        .report-card {
            transition: transform 0.2s ease;
        }
        .report-card:hover {
            transform: translateY(-5px);
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
            <h2 class="mb-4">
                <i class="fas fa-chart-bar me-2 text-primary"></i>Business Reports
            </h2>
            
            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card report-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h3><?php echo $stats['total_customers']; ?></h3>
                            <p class="mb-0">Total Customers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card report-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                            <h3><?php echo $stats['total_transactions']; ?></h3>
                            <p class="mb-0">Total Sales</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card report-card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-money-bill fa-3x mb-3"></i>
                            <h3><?php echo $stats['total_payments']; ?></h3>
                            <p class="mb-0">Total Payments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card report-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <h3>₹<?php echo number_format($stats['total_outstanding'], 2); ?></h3>
                            <p class="mb-0">Outstanding Debt</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Financial Summary -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Financial Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-success">₹<?php echo number_format($stats['total_sales'], 2); ?></h4>
                                    <p class="text-muted">Total Sales</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info">₹<?php echo number_format($stats['total_received'], 2); ?></h4>
                                    <p class="text-muted">Total Received</p>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <h5>Collection Rate</h5>
                                <?php 
                                $collection_rate = $stats['total_sales'] > 0 ? ($stats['total_received'] / $stats['total_sales']) * 100 : 0;
                                $rate_color = $collection_rate >= 80 ? 'success' : ($collection_rate >= 60 ? 'warning' : 'danger');
                                ?>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-<?php echo $rate_color; ?>" 
                                         style="width: <?php echo $collection_rate; ?>%"></div>
                                </div>
                                <span class="text-<?php echo $rate_color; ?> fw-bold">
                                    <?php echo number_format($collection_rate, 1); ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-circle me-2"></i>Outstanding Debts
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($stats['customers_with_debt'] > 0): ?>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="text-danger"><?php echo $stats['customers_with_debt']; ?></h4>
                                    <p class="text-muted">Customers with Debt</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success"><?php echo $stats['customers_cleared']; ?></h4>
                                    <p class="text-muted">Customers Cleared</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <h5>Average Debt per Customer</h5>
                                <h4 class="text-warning">
                                    ₹<?php echo number_format($stats['total_outstanding'] / max($stats['customers_with_debt'], 1), 2); ?>
                                </h4>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="text-success">All Clear!</h5>
                                <p class="text-muted">No outstanding debts</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Recent Sales (<?php echo count($recent_transactions); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_transactions)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No recent sales</p>
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_transactions as $transaction): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong><?php echo htmlspecialchars($transaction['customer_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($transaction['description']); ?></small><br>
                                        <small class="text-muted"><?php echo formatDate($transaction['transaction_date']); ?></small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">
                                        ₹<?php echo number_format($transaction['amount'], 2); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="transactions.php" class="btn btn-outline-primary btn-sm">View All Sales</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-money-bill me-2"></i>Recent Payments (<?php echo count($recent_payments); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_payments)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-money-bill fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No recent payments</p>
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_payments as $payment): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong><?php echo htmlspecialchars($payment['customer_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['notes'] ?: 'Payment received'); ?></small><br>
                                        <small class="text-muted"><?php echo formatDate($payment['payment_date']); ?></small>
                                    </div>
                                    <span class="badge bg-success rounded-pill">
                                        ₹<?php echo number_format($payment['amount'], 2); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="payments.php" class="btn btn-outline-success btn-sm">View All Payments</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>