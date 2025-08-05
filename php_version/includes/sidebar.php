<div class="sidebar">
    <div class="text-center mb-4">
        <h4 class="text-white">⚡ <?php echo APP_NAME; ?></h4>
        <small class="text-white-50">v<?php echo APP_VERSION; ?></small>
    </div>
    
    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'quick_billing.php' ? 'active' : ''; ?>" href="quick_billing.php">
            <i class="fas fa-bolt me-2"></i> Quick Billing
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'customers.php' ? 'active' : ''; ?>" href="customers.php">
            <i class="fas fa-users me-2"></i> Customers
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'add_customer.php' ? 'active' : ''; ?>" href="add_customer.php">
            <i class="fas fa-user-plus me-2"></i> Add Customer
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'transactions.php' ? 'active' : ''; ?>" href="transactions.php">
            <i class="fas fa-receipt me-2"></i> Transactions
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>" href="payments.php">
            <i class="fas fa-hand-holding-usd me-2"></i> Payments
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
            <i class="fas fa-chart-bar me-2"></i> Reports
        </a>
        
        <hr class="border-white-50 mx-3">
        
        <a class="nav-link" href="settings.php">
            <i class="fas fa-cog me-2"></i> Settings
        </a>
        <a class="nav-link" href="help.php">
            <i class="fas fa-question-circle me-2"></i> Help
        </a>
        <a class="nav-link" href="logout.php" onclick="return confirm('Are you sure you want to logout?')">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </nav>
    
    <div class="mt-auto px-3 pb-3">
        <div class="text-center">
            <small class="text-white-50">
                <i class="fas fa-store me-1"></i>
                Legendary-Preet.ct.ws
            </small>
        </div>
    </div>
</div>