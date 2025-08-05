<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Help</title>
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
        .help-section {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 2rem;
            margin-bottom: 2rem;
        }
        .help-section:last-child {
            border-bottom: none;
        }
        .step-number {
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
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
                <i class="fas fa-question-circle me-2 text-primary"></i>Help & Documentation
            </h2>
            
            <!-- Quick Start Guide -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>Quick Start Guide
                    </h4>
                </div>
                <div class="card-body">
                    <div class="help-section">
                        <h5>🎯 Getting Started</h5>
                        <div class="d-flex align-items-start mb-3">
                            <span class="step-number">1</span>
                            <div>
                                <strong>Add Your First Customer</strong><br>
                                Go to <a href="add_customer.php">Add Customer</a> and enter their name, phone number, and address.
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <span class="step-number">2</span>
                            <div>
                                <strong>Record a Sale</strong><br>
                                Use <a href="quick_billing.php">Quick Billing</a> to search by phone number and add a sale.
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <span class="step-number">3</span>
                            <div>
                                <strong>Record Payments</strong><br>
                                When customers pay, use Quick Billing to record payments and reduce their debt.
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <span class="step-number">4</span>
                            <div>
                                <strong>Monitor Business</strong><br>
                                Check the <a href="index.php">Dashboard</a> and <a href="reports.php">Reports</a> to track your business.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Feature Guide -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Feature Guide
                    </h4>
                </div>
                <div class="card-body">
                    <div class="help-section">
                        <h5>⚡ Quick Billing (Recommended for Daily Use)</h5>
                        <p><strong>Purpose:</strong> Fastest way to process sales and payments</p>
                        <ul>
                            <li>Search customers instantly by phone number</li>
                            <li>Record sales with amount and description</li>
                            <li>Record payments with notes</li>
                            <li>Upload bill photos (coming soon)</li>
                            <li>View customer's purchase history and debt</li>
                        </ul>
                        <p><strong>💡 Tip:</strong> Bookmark this page on your phone for shop use!</p>
                    </div>
                    
                    <div class="help-section">
                        <h5>👥 Customer Management</h5>
                        <p><strong>Add Customer:</strong> Create new customer profiles with unique phone numbers</p>
                        <p><strong>View Customers:</strong> Browse all customers, search by name/phone, see outstanding debts</p>
                        <p><strong>Customer Details:</strong> View complete history of purchases and payments</p>
                    </div>
                    
                    <div class="help-section">
                        <h5>📊 Reports & Analytics</h5>
                        <ul>
                            <li><strong>Dashboard:</strong> Quick overview of total customers, sales, and outstanding debts</li>
                            <li><strong>Reports:</strong> Detailed business analytics with collection rates and trends</li>
                            <li><strong>Transactions:</strong> Complete list of all sales</li>
                            <li><strong>Payments:</strong> Complete list of all payments received</li>
                        </ul>
                    </div>
                    
                    <div class="help-section">
                        <h5>⚙️ Settings & Configuration</h5>
                        <ul>
                            <li><strong>Change Password:</strong> Update your login password</li>
                            <li><strong>Google Drive:</strong> Connect cloud storage for bill photos (coming soon)</li>
                            <li><strong>Account Info:</strong> View your account details and application info</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Usage -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-mobile-alt me-2"></i>Mobile Usage Tips
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-bookmark me-2 text-primary"></i>Bookmark for Quick Access</h6>
                            <p>Add Legendary-Preet.ct.ws to your phone's home screen for instant access during shop operations.</p>
                            
                            <h6><i class="fas fa-search me-2 text-success"></i>Quick Customer Search</h6>
                            <p>In Quick Billing, type just the last 4 digits of a phone number to find customers quickly.</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-bolt me-2 text-warning"></i>Fast Transactions</h6>
                            <p>The Quick Billing page is optimized for rapid sales and payment processing on mobile devices.</p>
                            
                            <h6><i class="fas fa-wifi me-2 text-info"></i>Works Offline</h6>
                            <p>The interface remains accessible even with slow internet connections.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- FAQ -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-question me-2"></i>Frequently Asked Questions
                    </h4>
                </div>
                <div class="card-body">
                    <div class="help-section">
                        <h6>Q: Can I add the same phone number for multiple customers?</h6>
                        <p>A: No, each customer must have a unique phone number. This ensures accurate identification during quick searches.</p>
                    </div>
                    
                    <div class="help-section">
                        <h6>Q: Where is my data stored?</h6>
                        <p>A: All customer data, transactions, and payments are stored securely in your MySQL database on InfinityFree hosting. Nothing is stored locally on your device.</p>
                    </div>
                    
                    <div class="help-section">
                        <h6>Q: How do I backup my data?</h6>
                        <p>A: Your hosting provider (InfinityFree) handles database backups. For additional security, you can export your data from the MySQL control panel.</p>
                    </div>
                    
                    <div class="help-section">
                        <h6>Q: Can multiple people use this system?</h6>
                        <p>A: Currently, the system supports one admin user. All transactions are logged with timestamps for accountability.</p>
                    </div>
                    
                    <div class="help-section">
                        <h6>Q: What about bill photo storage?</h6>
                        <p>A: Google Drive integration for automatic bill photo backup is coming soon. This will provide 15GB of free cloud storage for your bill images.</p>
                    </div>
                </div>
            </div>
            
            <!-- Contact & Support -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-life-ring me-2"></i>Contact & Support
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📧 Technical Support</h6>
                            <p>For technical issues or questions about the system, refer to the documentation or contact your system administrator.</p>
                            
                            <h6>🏪 Store Information</h6>
                            <p><strong>Store:</strong> Amarjit Electrical Store and Repair Centre<br>
                            <strong>Location:</strong> India<br>
                            <strong>Website:</strong> <a href="https://legendary-preet.ct.ws" target="_blank">Legendary-Preet.ct.ws</a></p>
                        </div>
                        <div class="col-md-6">
                            <h6>🔧 System Information</h6>
                            <p><strong>Application:</strong> <?php echo APP_NAME; ?><br>
                            <strong>Version:</strong> <?php echo APP_VERSION; ?><br>
                            <strong>Database:</strong> MySQL (InfinityFree)<br>
                            <strong>Hosting:</strong> InfinityFree.com</p>
                            
                            <h6>📋 Quick Actions</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="quick_billing.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-bolt me-1"></i>Quick Billing
                                </a>
                                <a href="add_customer.php" class="btn btn-success btn-sm">
                                    <i class="fas fa-user-plus me-1"></i>Add Customer
                                </a>
                                <a href="index.php" class="btn btn-info btn-sm">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>