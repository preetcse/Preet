<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$stats = getDashboardStats();
$recent_transactions = getRecentTransactions(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
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
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .debt-positive { color: #dc3545; font-weight: bold; }
        .debt-zero { color: #28a745; font-weight: bold; }
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
                <h1 class="h3 mb-0">Dashboard</h1>
                <p class="text-muted">Welcome back! Here's your store overview.</p>
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-3"></i>
                        <h3 class="mb-1"><?php echo number_format($stats['total_customers']); ?></h3>
                        <p class="mb-0">Total Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-rupee-sign fa-2x mb-3"></i>
                        <h3 class="mb-1"><?php echo formatCurrency($stats['total_debt']); ?></h3>
                        <p class="mb-0">Total Outstanding</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                        <h3 class="mb-1"><?php echo number_format($stats['monthly_transactions']); ?></h3>
                        <p class="mb-0">This Month's Sales</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-hand-holding-usd fa-2x mb-3"></i>
                        <h3 class="mb-1"><?php echo formatCurrency($stats['monthly_payments']); ?></h3>
                        <p class="mb-0">This Month's Payments</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt text-warning"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="quick_billing.php" class="btn btn-warning w-100">
                                    <i class="fas fa-bolt"></i> Quick Billing
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="add_customer.php" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus"></i> Add New Customer
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="customers.php" class="btn btn-info w-100">
                                    <i class="fas fa-users"></i> View All Customers
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="reports.php" class="btn btn-success w-100">
                                    <i class="fas fa-chart-bar"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock text-info"></i> Recent Transactions</h5>
                        <a href="transactions.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_transactions)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Transactions Yet</h5>
                            <p class="text-muted">Start by adding your first customer and recording a transaction.</p>
                            <a href="add_customer.php" class="btn btn-primary">Add First Customer</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                    <tr>
                                        <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                                        <td>
                                            <a href="customer_detail.php?id=<?php echo $transaction['customer_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($transaction['customer_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['customer_phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $transaction['transaction_type'] === 'purchase' ? 'primary' : 'warning'; ?>">
                                                <?php echo ucfirst($transaction['transaction_type']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?php echo formatCurrency($transaction['amount']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($transaction['description'] ?: 'No description'); ?>
                                            <?php if ($transaction['bill_image_url']): ?>
                                            <a href="<?php echo htmlspecialchars($transaction['bill_image_url']); ?>" target="_blank" class="ms-2">
                                                <i class="fas fa-image text-success" title="View Bill Photo"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>