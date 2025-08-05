<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$transactions = getAllTransactions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Transactions</title>
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
            <h2 class="mb-4">
                <i class="fas fa-receipt me-2 text-primary"></i>All Transactions
            </h2>
            
            <div class="card">
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No transactions found</h4>
                        <p>Start by adding customers and recording sales</p>
                        <a href="quick_billing.php" class="btn btn-primary">Go to Quick Billing</a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Bill Photo</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($transactions) as $transaction): ?>
                                <tr>
                                    <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                                    <td>
                                        <a href="customer_detail.php?id=<?php echo $transaction['customer_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($transaction['customer_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                    <td class="fw-bold">₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td>
                                        <?php if ($transaction['bill_image_url']): ?>
                                        <a href="<?php echo htmlspecialchars($transaction['bill_image_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-image"></i>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">No photo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="customer_detail.php?id=<?php echo $transaction['customer_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>