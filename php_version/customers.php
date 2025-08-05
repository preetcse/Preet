<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$customers = getAllCustomers($search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Customers</title>
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
        .customer-card {
            transition: transform 0.2s ease;
            cursor: pointer;
        }
        .customer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.15);
        }
        .debt-positive {
            color: #dc3545;
            font-weight: bold;
        }
        .debt-zero {
            color: #28a745;
            font-weight: bold;
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
            <div class="row mb-4">
                <div class="col">
                    <h2><i class="fas fa-users me-2 text-primary"></i>All Customers</h2>
                    <p class="text-muted">Manage your customer database</p>
                </div>
                <div class="col-auto">
                    <a href="add_customer.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Add New Customer
                    </a>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" 
                               placeholder="Search by name or phone..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($search): ?>
                        <a href="customers.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        <?php echo count($customers); ?> customer(s) found
                        <?php if ($search): ?>
                            for "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                    </small>
                </div>
            </div>

            <!-- Customers Grid -->
            <?php if (empty($customers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No customers found</h4>
                <?php if ($search): ?>
                    <p>Try adjusting your search terms</p>
                    <a href="customers.php" class="btn btn-primary">View All Customers</a>
                <?php else: ?>
                    <p>Start by adding your first customer</p>
                    <a href="add_customer.php" class="btn btn-primary">Add First Customer</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($customers as $customer): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card customer-card h-100" onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($customer['name']); ?>
                                </h5>
                                <span class="badge bg-light text-dark">
                                    ID: <?php echo $customer['id']; ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <i class="fas fa-phone me-2 text-success"></i>
                                <strong><?php echo htmlspecialchars($customer['phone']); ?></strong>
                            </div>
                            
                            <?php if ($customer['address']): ?>
                            <div class="mb-3">
                                <i class="fas fa-map-marker-alt me-2 text-info"></i>
                                <small><?php echo htmlspecialchars($customer['address']); ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted d-block">Outstanding Debt</small>
                                    <span class="<?php echo $customer['total_debt'] > 0 ? 'debt-positive' : 'debt-zero'; ?>">
                                        ₹<?php echo number_format($customer['total_debt'], 2); ?>
                                    </span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Since</small>
                                    <span class="fw-bold">
                                        <?php echo formatDate($customer['created_date']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-3 d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="event.stopPropagation(); quickSale(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')">
                                    <i class="fas fa-shopping-cart me-1"></i>Quick Sale
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="event.stopPropagation(); quickPayment(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')">
                                    <i class="fas fa-money-bill me-1"></i>Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewCustomer(customerId) {
            window.location.href = 'customer_detail.php?id=' + customerId;
        }
        
        function quickSale(customerId, customerName) {
            const amount = prompt(`Enter sale amount for ${customerName}:`);
            if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
                const description = prompt(`Enter sale description (optional):`) || 'Quick Sale';
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'quick_billing.php';
                
                form.innerHTML = `
                    <input type="hidden" name="customer_id" value="${customerId}">
                    <input type="hidden" name="amount" value="${amount}">
                    <input type="hidden" name="description" value="${description}">
                    <input type="hidden" name="type" value="sale">
                    <input type="hidden" name="quick_action" value="1">
                `;
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function quickPayment(customerId, customerName) {
            const amount = prompt(`Enter payment amount for ${customerName}:`);
            if (amount && !isNaN(amount) && parseFloat(amount) > 0) {
                const notes = prompt(`Enter payment notes (optional):`) || 'Quick Payment';
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'quick_billing.php';
                
                form.innerHTML = `
                    <input type="hidden" name="customer_id" value="${customerId}">
                    <input type="hidden" name="amount" value="${amount}">
                    <input type="hidden" name="notes" value="${notes}">
                    <input type="hidden" name="type" value="payment">
                    <input type="hidden" name="quick_action" value="1">
                `;
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>