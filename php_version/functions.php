<?php
/**
 * Helper Functions for Amarjit Electrical Store
 * PHP Version for InfinityFree Hosting
 * CLEAN VERSION - No Duplicates
 */

/**
 * Utility Functions
 */

function validatePhone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if phone number is between 10-15 digits (international format)
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Customer Management Functions
 */

function createCustomer($name, $phone, $address = '') {
    $conn = getDBConnection();
    if (!$conn) return ['success' => false, 'message' => 'Database connection failed'];
    
    // Clean inputs
    $name = sanitizeInput($name);
    $phone = sanitizeInput($phone);
    $address = sanitizeInput($address);
    
    // Validate phone number
    if (!validatePhone($phone)) {
        return ['success' => false, 'message' => 'Invalid phone number format'];
    }
    
    // Check if customer already exists
    $stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
    if (!$stmt) return ['success' => false, 'message' => 'Database error'];
    
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Customer with this phone number already exists'];
    }
    
    // Insert new customer
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)");
    if (!$stmt) return ['success' => false, 'message' => 'Database error'];
    
    $stmt->bind_param("sss", $name, $phone, $address);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Customer added successfully', 'customer_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error adding customer: ' . $conn->error];
    }
}

function getCustomer($id) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    if (!$stmt) return null;
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getCustomerById($id) {
    // Alias for getCustomer for consistency
    return getCustomer($id);
}

function getCustomerByPhone($phone) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
    if (!$stmt) return null;
    
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getAllCustomers($search = '', $limit = 20, $offset = 0) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    if ($search) {
        $search = '%' . $search . '%';
        $stmt = $conn->prepare("SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY total_debt DESC LIMIT ? OFFSET ?");
        if (!$stmt) return [];
        $stmt->bind_param("ssii", $search, $search, $limit, $offset);
    } else {
        $stmt = $conn->prepare("SELECT * FROM customers ORDER BY total_debt DESC LIMIT ? OFFSET ?");
        if (!$stmt) return [];
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    return $customers;
}

function updateCustomerDebt($customer_id) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    // Calculate total debt = total transactions - total payments
    $stmt = $conn->prepare("
        UPDATE customers 
        SET total_debt = (
            SELECT COALESCE(SUM(t.amount), 0) - COALESCE(SUM(p.amount), 0)
            FROM (SELECT amount FROM transactions WHERE customer_id = ?) t
            LEFT JOIN (SELECT amount FROM payments WHERE customer_id = ?) p ON 1=1
        )
        WHERE id = ?
    ");
    if (!$stmt) return false;
    
    $stmt->bind_param("iii", $customer_id, $customer_id, $customer_id);
    return $stmt->execute();
}

/**
 * Transaction Management Functions
 */

function createTransaction($customer_id, $amount, $description, $transaction_date, $transaction_type = 'purchase', $bill_image_url = null, $bill_image_id = null) {
    $conn = getDBConnection();
    if (!$conn) return ['success' => false, 'message' => 'Database connection failed'];
    
    // Clean inputs
    $description = sanitizeInput($description);
    $transaction_type = sanitizeInput($transaction_type);
    
    // Insert transaction
    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, amount, description, transaction_date, transaction_type, bill_image_url, google_drive_file_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) return ['success' => false, 'message' => 'Database error'];
    
    $stmt->bind_param("idsssss", $customer_id, $amount, $description, $transaction_date, $transaction_type, $bill_image_url, $bill_image_id);
    
    if ($stmt->execute()) {
        // Update customer debt
        updateCustomerDebt($customer_id);
        
        return ['success' => true, 'message' => 'Transaction added successfully', 'transaction_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error adding transaction: ' . $conn->error];
    }
}

function getCustomerTransactions($customer_id, $limit = 50) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY transaction_date DESC LIMIT ?");
    if (!$stmt) return [];
    
    $stmt->bind_param("ii", $customer_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

function getRecentTransactions($limit = 10) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $stmt = $conn->prepare("SELECT t.*, c.name as customer_name, c.phone as customer_phone FROM transactions t LEFT JOIN customers c ON t.customer_id = c.id ORDER BY t.created_date DESC LIMIT ?");
    if (!$stmt) return [];
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

function getAllTransactions($limit = 100) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $stmt = $conn->prepare("
        SELECT t.*, c.name as customer_name, c.phone as customer_phone 
        FROM transactions t 
        LEFT JOIN customers c ON t.customer_id = c.id 
        ORDER BY t.transaction_date DESC 
        LIMIT ?
    ");
    if (!$stmt) return [];
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

function addTransaction($customer_id, $amount, $description, $bill_image_url = null, $google_drive_file_id = null) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $transaction_date = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, amount, description, transaction_date, bill_image_url, google_drive_file_id) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) return false;
    
    $stmt->bind_param("idssss", $customer_id, $amount, $description, $transaction_date, $bill_image_url, $google_drive_file_id);
    
    if ($stmt->execute()) {
        // Update customer's total debt
        updateCustomerDebt($customer_id);
        return $conn->insert_id;
    }
    
    return false;
}

/**
 * Payment Management Functions
 */

function createPayment($customer_id, $amount, $payment_date, $notes = '') {
    $conn = getDBConnection();
    if (!$conn) return ['success' => false, 'message' => 'Database connection failed'];
    
    // Clean inputs
    $notes = sanitizeInput($notes);
    
    // Insert payment
    $stmt = $conn->prepare("INSERT INTO payments (customer_id, amount, payment_date, notes) VALUES (?, ?, ?, ?)");
    if (!$stmt) return ['success' => false, 'message' => 'Database error'];
    
    $stmt->bind_param("idss", $customer_id, $amount, $payment_date, $notes);
    
    if ($stmt->execute()) {
        // Update customer debt
        updateCustomerDebt($customer_id);
        
        return ['success' => true, 'message' => 'Payment recorded successfully', 'payment_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error recording payment: ' . $conn->error];
    }
}

function getCustomerPayments($customer_id, $limit = 50) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $stmt = $conn->prepare("SELECT * FROM payments WHERE customer_id = ? ORDER BY payment_date DESC LIMIT ?");
    if (!$stmt) return [];
    
    $stmt->bind_param("ii", $customer_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
    return $payments;
}

function getAllPayments($limit = 100) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as customer_name, c.phone as customer_phone 
        FROM payments p 
        LEFT JOIN customers c ON p.customer_id = c.id 
        ORDER BY p.payment_date DESC 
        LIMIT ?
    ");
    if (!$stmt) return [];
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
    return $payments;
}

function getRecentPayments($limit = 10) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $stmt = $conn->prepare("
        SELECT p.*, c.name as customer_name, c.phone as customer_phone 
        FROM payments p 
        LEFT JOIN customers c ON p.customer_id = c.id 
        ORDER BY p.payment_date DESC 
        LIMIT ?
    ");
    if (!$stmt) return [];
    
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
    return $payments;
}

function addPayment($customer_id, $amount, $notes = null) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $payment_date = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO payments (customer_id, amount, payment_date, notes) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;
    
    $stmt->bind_param("idss", $customer_id, $amount, $payment_date, $notes);
    
    if ($stmt->execute()) {
        // Update customer's total debt
        updateCustomerDebt($customer_id);
        return $conn->insert_id;
    }
    
    return false;
}

/**
 * Statistics Functions
 */

function getDashboardStats() {
    $conn = getDBConnection();
    if (!$conn) return [
        'total_customers' => 0, 'total_debt' => 0, 'monthly_transactions' => 0, 'monthly_payments' => 0,
        'total_transactions' => 0, 'total_payments' => 0, 'total_outstanding' => 0, 'total_sales' => 0,
        'total_received' => 0, 'customers_with_debt' => 0, 'customers_cleared' => 0
    ];
    
    // Total customers
    $result = $conn->query("SELECT COUNT(*) as count FROM customers");
    $total_customers = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Total debt (outstanding)
    $result = $conn->query("SELECT SUM(total_debt) as total FROM customers WHERE total_debt > 0");
    $total_debt = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
    
    // All time transactions
    $result = $conn->query("SELECT COUNT(*) as count, SUM(amount) as total FROM transactions");
    if ($result) {
        $trans_data = $result->fetch_assoc();
        $total_transactions = $trans_data['count'] ?? 0;
        $total_sales = $trans_data['total'] ?? 0;
    } else {
        $total_transactions = 0;
        $total_sales = 0;
    }
    
    // All time payments
    $result = $conn->query("SELECT COUNT(*) as count, SUM(amount) as total FROM payments");
    if ($result) {
        $payment_data = $result->fetch_assoc();
        $total_payments = $payment_data['count'] ?? 0;
        $total_received = $payment_data['total'] ?? 0;
    } else {
        $total_payments = 0;
        $total_received = 0;
    }
    
    // Customers with debt vs cleared
    $result = $conn->query("SELECT COUNT(*) as count FROM customers WHERE total_debt > 0");
    $customers_with_debt = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM customers WHERE total_debt = 0");
    $customers_cleared = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Monthly transactions
    $current_month = date('Y-m');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transactions WHERE DATE_FORMAT(transaction_date, '%Y-%m') = ?");
    if ($stmt) {
        $stmt->bind_param("s", $current_month);
        $stmt->execute();
        $monthly_transactions = $stmt->get_result()->fetch_assoc()['count'];
    } else {
        $monthly_transactions = 0;
    }
    
    // Monthly payments
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?");
    if ($stmt) {
        $stmt->bind_param("s", $current_month);
        $stmt->execute();
        $monthly_payments = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    } else {
        $monthly_payments = 0;
    }
    
    return [
        'total_customers' => $total_customers,
        'total_debt' => $total_debt,
        'monthly_transactions' => $monthly_transactions,
        'monthly_payments' => $monthly_payments,
        'total_transactions' => $total_transactions,
        'total_payments' => $total_payments,
        'total_outstanding' => $total_debt, // Alias for total_debt
        'total_sales' => $total_sales,
        'total_received' => $total_received,
        'customers_with_debt' => $customers_with_debt,
        'customers_cleared' => $customers_cleared
    ];
}

/**
 * User Authentication Functions
 */

function createUser($username, $password) {
    $conn = getDBConnection();
    if (!$conn) return ['success' => false, 'message' => 'Database connection failed'];
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$stmt) return ['success' => false, 'message' => 'Database error'];
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Username already exists'];
    }
    
    // Hash password and create user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    if (!$stmt) return ['success' => false, 'message' => 'Database error'];
    
    $stmt->bind_param("ss", $username, $password_hash);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'User created successfully', 'user_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error creating user: ' . $conn->error];
    }
}

function userExists() {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if (!$result) return false;
    
    return $result->fetch_assoc()['count'] > 0;
}

/**
 * Search Functions
 */

function searchCustomers($query) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $search = '%' . $query . '%';
    $stmt = $conn->prepare("SELECT id, name, phone, total_debt FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY total_debt DESC LIMIT 10");
    if (!$stmt) return [];
    
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    
    return $customers;
}

function getCustomerWithRecentBills($phone) {
    $customer = getCustomerByPhone($phone);
    if (!$customer) {
        return null;
    }
    
    // Get recent transactions with bill photos
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? AND bill_image_url IS NOT NULL ORDER BY transaction_date DESC LIMIT 5");
    if (!$stmt) return null;
    
    $stmt->bind_param("i", $customer['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recent_bills = [];
    while ($row = $result->fetch_assoc()) {
        $recent_bills[] = [
            'date' => formatDate($row['transaction_date']),
            'amount' => $row['amount'],
            'description' => $row['description'] ?: 'Purchase',
            'bill_url' => $row['bill_image_url']
        ];
    }
    
    // Get total counts
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transactions WHERE customer_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $customer['id']);
        $stmt->execute();
        $total_transactions = $stmt->get_result()->fetch_assoc()['count'];
    } else {
        $total_transactions = 0;
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM payments WHERE customer_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $customer['id']);
        $stmt->execute();
        $total_payments = $stmt->get_result()->fetch_assoc()['count'];
    } else {
        $total_payments = 0;
    }
    
    return [
        'found' => true,
        'id' => $customer['id'],
        'name' => $customer['name'],
        'phone' => $customer['phone'],
        'address' => $customer['address'],
        'total_debt' => $customer['total_debt'],
        'created_date' => formatDate($customer['created_date']),
        'recent_bills' => $recent_bills,
        'total_transactions' => $total_transactions,
        'total_payments' => $total_payments
    ];
}

/**
 * File Upload Functions
 */

function handleFileUpload($file, $customer_phone) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validate file type (basic check)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, and GIF are allowed.');
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum size is 5MB.');
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $customer_phone . '_' . date('Ymd_His') . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Try to upload to Google Drive if enabled
        if (defined('GOOGLE_DRIVE_ENABLED') && GOOGLE_DRIVE_ENABLED) {
            $drive_result = uploadToGoogleDrive($filepath, $filename, $_SESSION['user_id'] ?? 1);
            if ($drive_result) {
                // Delete local file after successful upload
                unlink($filepath);
                return $drive_result;
            }
        }
        
        // Return local file info if Google Drive failed or disabled
        return [
            'id' => null,
            'url' => $filepath
        ];
    }
    
    throw new Exception('Failed to upload file.');
}
?>