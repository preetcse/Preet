<?php
/**
 * Helper Functions for Amarjit Electrical Store
 * PHP Version for InfinityFree Hosting
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
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Customer with this phone number already exists'];
    }
    
    // Insert new customer
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $address);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Customer added successfully', 'customer_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error adding customer: ' . $conn->error];
    }
}

function getCustomer($id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getCustomerByPhone($phone) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM customers WHERE phone = ?");
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
    
    if ($search) {
        $search = '%' . $search . '%';
        $stmt = $conn->prepare("SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY total_debt DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ssii", $search, $search, $limit, $offset);
    } else {
        $stmt = $conn->prepare("SELECT * FROM customers ORDER BY total_debt DESC LIMIT ? OFFSET ?");
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

// updateCustomerDebt function is defined later in the file (around line 244)
// This duplicate declaration has been removed to prevent redeclaration error

/**
 * Transaction Management Functions
 */

function createTransaction($customer_id, $amount, $description, $transaction_date, $transaction_type = 'purchase', $bill_image_url = null, $bill_image_id = null) {
    $conn = getDBConnection();
    
    // Clean inputs
    $description = sanitizeInput($description);
    $transaction_type = sanitizeInput($transaction_type);
    
    // Insert transaction
    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, amount, description, transaction_date, transaction_type, bill_image_url, bill_image_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsssss", $customer_id, $amount, $description, $transaction_date, $transaction_type, $bill_image_url, $bill_image_id);
    
    if ($stmt->execute()) {
        // Update customer debt
        if ($transaction_type === 'purchase') {
            updateCustomerDebt($customer_id, $amount, 'add');
        } else {
            updateCustomerDebt($customer_id, $amount, 'subtract');
        }
        
        return ['success' => true, 'message' => 'Transaction added successfully', 'transaction_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error adding transaction: ' . $conn->error];
    }
}

function getCustomerTransactions($customer_id, $limit = 50) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY transaction_date DESC LIMIT ?");
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
    
    $stmt = $conn->prepare("SELECT t.*, c.name as customer_name, c.phone as customer_phone FROM transactions t JOIN customers c ON t.customer_id = c.id ORDER BY t.created_date DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

/**
 * Payment Management Functions
 */

function createPayment($customer_id, $amount, $payment_date, $notes = '') {
    $conn = getDBConnection();
    
    // Clean inputs
    $notes = sanitizeInput($notes);
    
    // Insert payment
    $stmt = $conn->prepare("INSERT INTO payments (customer_id, amount, payment_date, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $customer_id, $amount, $payment_date, $notes);
    
    if ($stmt->execute()) {
        // Update customer debt
        updateCustomerDebt($customer_id, $amount, 'subtract');
        
        return ['success' => true, 'message' => 'Payment recorded successfully', 'payment_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error recording payment: ' . $conn->error];
    }
}

function getCustomerPayments($customer_id, $limit = 50) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM payments WHERE customer_id = ? ORDER BY payment_date DESC LIMIT ?");
    $stmt->bind_param("ii", $customer_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
    return $payments;
}

/**
 * Transaction Functions
 */

function addTransaction($customer_id, $amount, $description, $bill_image_url = null, $google_drive_file_id = null) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, amount, description, bill_image_url, google_drive_file_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $customer_id, $amount, $description, $bill_image_url, $google_drive_file_id);
    
    if ($stmt->execute()) {
        // Update customer's total debt
        updateCustomerDebt($customer_id);
        return $conn->insert_id;
    }
    
    return false;
}

function addPayment($customer_id, $amount, $notes = null) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO payments (customer_id, amount, notes) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $customer_id, $amount, $notes);
    
    if ($stmt->execute()) {
        // Update customer's total debt
        updateCustomerDebt($customer_id);
        return $conn->insert_id;
    }
    
    return false;
}

function updateCustomerDebt($customer_id) {
    $conn = getDBConnection();
    
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
    $stmt->bind_param("iii", $customer_id, $customer_id, $customer_id);
    return $stmt->execute();
}

/**
 * Statistics Functions
 */

function getDashboardStats() {
    $conn = getDBConnection();
    
    // Total customers
    $result = $conn->query("SELECT COUNT(*) as count FROM customers");
    $total_customers = $result->fetch_assoc()['count'];
    
    // Total debt
    $result = $conn->query("SELECT SUM(total_debt) as total FROM customers");
    $total_debt = $result->fetch_assoc()['total'] ?? 0;
    
    // Total transactions this month
    $current_month = date('Y-m');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM transactions WHERE DATE_FORMAT(transaction_date, '%Y-%m') = ?");
    $stmt->bind_param("s", $current_month);
    $stmt->execute();
    $monthly_transactions = $stmt->get_result()->fetch_assoc()['count'];
    
    // Total payments this month
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?");
    $stmt->bind_param("s", $current_month);
    $stmt->execute();
    $monthly_payments = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    
    return [
        'total_customers' => $total_customers,
        'total_debt' => $total_debt,
        'monthly_transactions' => $monthly_transactions,
        'monthly_payments' => $monthly_payments
    ];
}

/**
 * User Authentication Functions
 */

// authenticateUser function is defined in config.php (line 175)
// This duplicate declaration has been removed to prevent redeclaration error

function createUser($username, $password) {
    $conn = getDBConnection();
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Username already exists'];
    }
    
    // Hash password and create user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password_hash);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'User created successfully', 'user_id' => $conn->insert_id];
    } else {
        return ['success' => false, 'message' => 'Error creating user: ' . $conn->error];
    }
}

function userExists() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    return $result->fetch_assoc()['count'] > 0;
}

/**
 * File Upload Functions
 */

function handleFileUpload($file, $customer_phone) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validate file type
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, and GIF are allowed.');
    }
    
    // Validate file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new Exception('File size too large. Maximum size is 10MB.');
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $customer_phone . '_' . date('Ymd_His') . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Upload to Google Drive if enabled
        if (GOOGLE_DRIVE_ENABLED) {
            $drive_result = uploadToGoogleDrive($filepath, $filename);
            if ($drive_result) {
                // Delete local file after successful upload
                unlink($filepath);
                return $drive_result;
            }
        }
        
        // Return local file info if Google Drive is disabled
        return [
            'id' => null,
            'url' => SITE_URL . '/' . $filepath
        ];
    }
    
    throw new Exception('Failed to upload file.');
}

/**
 * Search Functions
 */

function searchCustomers($query) {
    $conn = getDBConnection();
    
    $search = '%' . $query . '%';
    $stmt = $conn->prepare("SELECT id, name, phone, total_debt FROM customers WHERE name LIKE ? OR phone LIKE ? ORDER BY total_debt DESC LIMIT 10");
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
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? AND bill_image_url IS NOT NULL ORDER BY transaction_date DESC LIMIT 5");
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
    $stmt->bind_param("i", $customer['id']);
    $stmt->execute();
    $total_transactions = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM payments WHERE customer_id = ?");
    $stmt->bind_param("i", $customer['id']);
    $stmt->execute();
    $total_payments = $stmt->get_result()->fetch_assoc()['count'];
    
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
?>