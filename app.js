// Amarjit Electrical Store - Firebase with Photo Storage
// Real-time database + bill photo storage + Enhanced UI

class ElectricalStoreApp {
    constructor() {
        // Your password (change this!)
        this.PASSWORD = 'amarjit123';
        
        // Firebase configuration
        this.firebaseConfig = {
            apiKey: "AIzaSyBHNr3fI-kV2VgNOiOgchXgSa9wns7ClSs",
            authDomain: "amarjit-electrical-store-90d1c.firebaseapp.com",
            projectId: "amarjit-electrical-store-90d1c",
            storageBucket: "amarjit-electrical-store-90d1c.firebasestorage.app",
            messagingSenderId: "63912761857",
            appId: "1:63912761857:web:e601bc73b569f82d247e00",
            measurementId: "G-LEWP7F5MZK"
        };
        
        // App state
        this.isLoggedIn = false;
        this.isFirebaseConnected = false;
        this.customers = [];
        this.transactions = [];
        this.db = null;
        this.storage = null;
        
        this.init();
    }

    async init() {
        const sessionLogin = sessionStorage.getItem('currentSession');
        if (sessionLogin === 'active') {
            this.isLoggedIn = true;
            this.showMainApp();
            await this.initializeFirebase();
        }

        this.setupEventListeners();
    }

    setupEventListeners() {
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        document.getElementById('googleDriveBtn').addEventListener('click', () => {
            this.handleFirebaseSync();
        });

        // Customer search
        const searchInput = document.getElementById('customerSearch');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.searchCustomers());
        }

        // Add customer form
        const addCustomerForm = document.getElementById('addCustomerForm');
        if (addCustomerForm) {
            addCustomerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addCustomer();
            });
        }
    }

    async handleLogin() {
        const password = document.getElementById('password').value;
        const errorDiv = document.getElementById('loginError');

        if (password === this.PASSWORD) {
            sessionStorage.setItem('currentSession', 'active');
            this.isLoggedIn = true;
            this.showMainApp();
            await this.initializeFirebase();
        } else {
            errorDiv.classList.remove('hidden');
            document.getElementById('password').value = '';
            setTimeout(() => errorDiv.classList.add('hidden'), 3000);
        }
    }

    async initializeFirebase() {
        this.showLoading();
        
        try {
            // Load Firebase SDK
            await this.loadFirebaseSDK();
            
            // Initialize Firebase
            firebase.initializeApp(this.firebaseConfig);
            this.db = firebase.firestore();
            this.storage = firebase.storage();
            
            // Load data from Firebase
            await this.loadDataFromFirebase();
            
            this.isFirebaseConnected = true;
            this.updateCloudStatus(true);
            this.showAlert('Connected to Firebase (Database + Storage)!', 'success');
            
        } catch (error) {
            console.error('Firebase initialization failed:', error);
            this.loadFromLocal();
            this.updateCloudStatus(false);
            this.showAlert('Using offline mode. Data saved locally.', 'info');
        }
        
        this.hideLoading();
    }

    async loadFirebaseSDK() {
        return new Promise((resolve, reject) => {
            if (typeof firebase !== 'undefined') {
                resolve();
                return;
            }

            // Load Firebase SDK with Storage
            const scripts = [
                'https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js',
                'https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js',
                'https://www.gstatic.com/firebasejs/9.23.0/firebase-storage-compat.js'
            ];

            let loadedCount = 0;
            
            scripts.forEach((src) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => {
                    loadedCount++;
                    if (loadedCount === scripts.length) {
                        resolve();
                    }
                };
                script.onerror = () => reject(`Failed to load ${src}`);
                document.head.appendChild(script);
            });
        });
    }

    async loadDataFromFirebase() {
        try {
            // Load customers
            const customersSnapshot = await this.db.collection('customers').orderBy('createdDate', 'desc').get();
            this.customers = [];
            
            customersSnapshot.forEach((doc) => {
                this.customers.push({
                    id: doc.id,
                    ...doc.data()
                });
            });

            // Load transactions
            const transactionsSnapshot = await this.db.collection('transactions').orderBy('createdDate', 'desc').get();
            this.transactions = [];
            
            transactionsSnapshot.forEach((doc) => {
                this.transactions.push({
                    id: doc.id,
                    ...doc.data()
                });
            });
            
            // Also save to local storage as backup
            this.saveToLocal();
            
            this.updateDashboard();
            this.displayCustomers();
            
        } catch (error) {
            console.error('Error loading from Firebase:', error);
            throw error;
        }
    }

    async uploadBillPhoto(file, customerName, transactionId) {
        if (!this.storage || !file) return null;

        try {
            // Create unique filename
            const timestamp = new Date().toISOString().slice(0, 10);
            const filename = `bills/${customerName.replace(/[^a-zA-Z0-9]/g, '_')}_${timestamp}_${transactionId}.${file.name.split('.').pop()}`;
            
            // Upload file
            const storageRef = this.storage.ref().child(filename);
            const snapshot = await storageRef.put(file);
            
            // Get download URL
            const downloadURL = await snapshot.ref.getDownloadURL();
            
            return {
                filename: filename,
                downloadURL: downloadURL,
                size: file.size,
                type: file.type,
                uploadDate: new Date().toISOString()
            };
        } catch (error) {
            console.error('Error uploading photo:', error);
            return null;
        }
    }

    async addTransaction(customerId, amount, description, billPhoto = null) {
        const customer = this.customers.find(c => c.id === customerId);
        if (!customer) return false;

        const transactionId = Date.now().toString();
        let photoData = null;

        // Upload bill photo if provided
        if (billPhoto) {
            this.showAlert('Uploading bill photo...', 'info');
            photoData = await this.uploadBillPhoto(billPhoto, customer.name, transactionId);
            
            if (photoData) {
                this.showAlert('Bill photo uploaded successfully!', 'success');
            } else {
                this.showAlert('Photo upload failed, but transaction will be saved.', 'warning');
            }
        }

        const transaction = {
            id: transactionId,
            customerId: customerId,
            customerName: customer.name,
            amount: parseFloat(amount),
            description: description,
            billPhoto: photoData,
            createdDate: firebase.firestore.FieldValue.serverTimestamp(),
            type: 'sale'
        };

        try {
            // Save transaction to Firebase
            await this.db.collection('transactions').doc(transactionId).set(transaction);
            
            // Update customer debt
            const newDebt = customer.totalDebt + parseFloat(amount);
            await this.db.collection('customers').doc(customerId).update({
                totalDebt: newDebt,
                lastTransaction: firebase.firestore.FieldValue.serverTimestamp()
            });

            // Update local data
            customer.totalDebt = newDebt;
            this.transactions.unshift({...transaction, createdDate: new Date()});
            this.saveToLocal();
            
            this.updateDashboard();
            this.displayCustomers();
            
            return true;
        } catch (error) {
            console.error('Error adding transaction:', error);
            return false;
        }
    }

    async addPayment(customerId, amount, notes = '') {
        const customer = this.customers.find(c => c.id === customerId);
        if (!customer) return false;

        const paymentId = Date.now().toString();
        const payment = {
            id: paymentId,
            customerId: customerId,
            customerName: customer.name,
            amount: parseFloat(amount),
            notes: notes,
            createdDate: firebase.firestore.FieldValue.serverTimestamp(),
            type: 'payment'
        };

        try {
            // Save payment to Firebase
            await this.db.collection('transactions').doc(paymentId).set(payment);
            
            // Update customer debt
            const newDebt = Math.max(0, customer.totalDebt - parseFloat(amount));
            await this.db.collection('customers').doc(customerId).update({
                totalDebt: newDebt,
                lastPayment: firebase.firestore.FieldValue.serverTimestamp()
            });

            // Update local data
            customer.totalDebt = newDebt;
            this.transactions.unshift({...payment, createdDate: new Date()});
            this.saveToLocal();
            
            this.updateDashboard();
            this.displayCustomers();
            
            return true;
        } catch (error) {
            console.error('Error adding payment:', error);
            return false;
        }
    }

    async saveToFirebase(customer) {
        if (!this.isFirebaseConnected || !this.db) return false;
        
        try {
            if (customer.firestoreId) {
                // Update existing customer
                await this.db.collection('customers').doc(customer.firestoreId).update({
                    name: customer.name,
                    phone: customer.phone,
                    address: customer.address,
                    totalDebt: customer.totalDebt,
                    lastUpdated: firebase.firestore.FieldValue.serverTimestamp()
                });
            } else {
                // Create new customer
                const docRef = await this.db.collection('customers').add({
                    name: customer.name,
                    phone: customer.phone,
                    address: customer.address,
                    totalDebt: customer.totalDebt,
                    createdDate: firebase.firestore.FieldValue.serverTimestamp(),
                    lastUpdated: firebase.firestore.FieldValue.serverTimestamp()
                });
                customer.firestoreId = docRef.id;
                customer.id = docRef.id;
            }
            return true;
        } catch (error) {
            console.error('Error saving to Firebase:', error);
            return false;
        }
    }

    async handleFirebaseSync() {
        if (!this.isFirebaseConnected) {
            await this.initializeFirebase();
            return;
        }
        
        this.showAlert('Data syncs automatically in real-time!', 'info');
    }

    async addCustomer() {
        const name = document.getElementById('customerName').value.trim();
        const phone = document.getElementById('customerPhone').value.trim();
        const address = document.getElementById('customerAddress').value.trim();

        if (!name || !phone) {
            this.showAlert('Please fill in customer name and phone number.', 'danger');
            return;
        }

        const existingCustomer = this.customers.find(c => c.phone === phone);
        if (existingCustomer) {
            this.showAlert('Customer with this phone number already exists.', 'warning');
            return;
        }

        const customer = {
            id: Date.now(),
            name: name,
            phone: phone,
            address: address,
            totalDebt: 0,
            createdDate: new Date().toISOString(),
            lastUpdated: new Date().toISOString()
        };

        // Add to local array first for immediate UI update
        this.customers.unshift(customer);
        this.saveToLocal();
        this.displayCustomers();
        this.updateDashboard();

        // Clear form and close modal
        document.getElementById('addCustomerForm').reset();
        const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
        modal.hide();

        // Save to Firebase in background
        const saved = await this.saveToFirebase(customer);
        
        if (saved) {
            this.showAlert('Customer added and synced to cloud!', 'success');
        } else {
            this.showAlert('Customer added locally. Will sync when online.', 'warning');
        }
    }

    loadFromLocal() {
        this.customers = JSON.parse(localStorage.getItem('customers') || '[]');
        this.transactions = JSON.parse(localStorage.getItem('transactions') || '[]');
        this.updateDashboard();
        this.displayCustomers();
    }

    saveToLocal() {
        localStorage.setItem('customers', JSON.stringify(this.customers));
        localStorage.setItem('transactions', JSON.stringify(this.transactions));
    }

    displayCustomers() {
        const customerList = document.getElementById('customerList');
        
        if (this.customers.length === 0) {
            customerList.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-4">
                        <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No customers yet</h5>
                        <p class="text-muted">Add your first customer to get started.</p>
                        <button class="btn btn-primary mt-2" onclick="showAddCustomer()">
                            <i class="fas fa-plus"></i> Add First Customer
                        </button>
                    </div>
                </div>
            `;
            return;
        }

        customerList.innerHTML = this.customers.map(customer => `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card customer-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title mb-1">${customer.name}</h6>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-phone"></i> ${customer.phone}
                                </p>
                                ${customer.address ? `<p class="text-muted small mb-2"><i class="fas fa-map-marker-alt"></i> ${customer.address}</p>` : ''}
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> 
                                    ${new Date(customer.createdDate).toLocaleDateString()}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge ${customer.totalDebt > 0 ? 'bg-danger' : 'bg-success'}">
                                    ₹${customer.totalDebt.toFixed(2)}
                                </span>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-sm btn-primary me-1" onclick="app.showAddSaleModal('${customer.id}')">
                                <i class="fas fa-plus"></i> Sale
                            </button>
                            <button class="btn btn-sm btn-success me-1" onclick="app.showAddPaymentModal('${customer.id}')">
                                <i class="fas fa-money-bill"></i> Payment
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="app.viewCustomerDetails('${customer.id}')">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    searchCustomers() {
        const searchTerm = document.getElementById('customerSearch').value.toLowerCase();
        const filteredCustomers = this.customers.filter(customer => 
            customer.name.toLowerCase().includes(searchTerm) ||
            customer.phone.includes(searchTerm) ||
            (customer.address && customer.address.toLowerCase().includes(searchTerm))
        );
        
        const originalCustomers = this.customers;
        this.customers = filteredCustomers;
        this.displayCustomers();
        this.customers = originalCustomers;
    }

    showAddSaleModal(customerId) {
        const customer = this.customers.find(c => c.id === customerId);
        if (!customer) return;

        const modalHtml = `
            <div class="modal fade" id="addSaleModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Sale - ${customer.name}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addSaleForm">
                                <div class="mb-3">
                                    <label for="saleAmount" class="form-label">Amount (₹) *</label>
                                    <input type="number" class="form-control" id="saleAmount" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="saleDescription" class="form-label">Description *</label>
                                    <input type="text" class="form-control" id="saleDescription" placeholder="e.g., LED bulbs x10" required>
                                </div>
                                <div class="mb-3">
                                    <label for="billPhoto" class="form-label">📸 Bill Photo</label>
                                    <input type="file" class="form-control" id="billPhoto" accept="image/*">
                                    <small class="text-muted">Upload bill photo (optional) - stored in Firebase Storage</small>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="app.processSale('${customerId}')">
                                <i class="fas fa-save"></i> Add Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        const existingModal = document.getElementById('addSaleModal');
        if (existingModal) existingModal.remove();

        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('addSaleModal'));
        modal.show();
    }

    showAddPaymentModal(customerId) {
        const customer = this.customers.find(c => c.id === customerId);
        if (!customer) return;

        const modalHtml = `
            <div class="modal fade" id="addPaymentModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Record Payment - ${customer.name}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>Current Debt:</strong> ₹${customer.totalDebt.toFixed(2)}
                            </div>
                            <form id="addPaymentForm">
                                <div class="mb-3">
                                    <label for="paymentAmount" class="form-label">Payment Amount (₹) *</label>
                                    <input type="number" class="form-control" id="paymentAmount" step="0.01" max="${customer.totalDebt}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="paymentNotes" class="form-label">Notes</label>
                                    <input type="text" class="form-control" id="paymentNotes" placeholder="e.g., Cash payment">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" onclick="app.processPayment('${customerId}')">
                                <i class="fas fa-check"></i> Record Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        const existingModal = document.getElementById('addPaymentModal');
        if (existingModal) existingModal.remove();

        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
        modal.show();
    }

    async processSale(customerId) {
        const amount = document.getElementById('saleAmount').value;
        const description = document.getElementById('saleDescription').value;
        const photoFile = document.getElementById('billPhoto').files[0];

        if (!amount || !description) {
            this.showAlert('Please fill in amount and description.', 'danger');
            return;
        }

        this.showLoading();
        const success = await this.addTransaction(customerId, amount, description, photoFile);
        this.hideLoading();

        if (success) {
            const photoText = photoFile ? ' with bill photo' : '';
            this.showAlert(`Sale added successfully${photoText}!`, 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addSaleModal'));
            modal.hide();
        } else {
            this.showAlert('Failed to add sale. Please try again.', 'danger');
        }
    }

    async processPayment(customerId) {
        const amount = document.getElementById('paymentAmount').value;
        const notes = document.getElementById('paymentNotes').value;

        if (!amount) {
            this.showAlert('Please enter payment amount.', 'danger');
            return;
        }

        this.showLoading();
        const success = await this.addPayment(customerId, amount, notes);
        this.hideLoading();

        if (success) {
            this.showAlert('Payment recorded successfully!', 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
            modal.hide();
        } else {
            this.showAlert('Failed to record payment. Please try again.', 'danger');
        }
    }

    viewCustomerDetails(customerId) {
        const customer = this.customers.find(c => c.id === customerId);
        if (!customer) return;

        // Get customer transactions
        const customerTransactions = this.transactions.filter(t => t.customerId === customerId);
        
        const modalHtml = `
            <div class="modal fade" id="customerDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-user"></i> ${customer.name}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Contact Information</h6>
                                    <p><strong>Phone:</strong> ${customer.phone}</p>
                                    <p><strong>Address:</strong> ${customer.address || 'Not provided'}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Financial Summary</h6>
                                    <p><strong>Current Debt:</strong> 
                                        <span class="badge ${customer.totalDebt > 0 ? 'bg-danger' : 'bg-success'}">
                                            ₹${customer.totalDebt.toFixed(2)}
                                        </span>
                                    </p>
                                    <p><strong>Total Transactions:</strong> ${customerTransactions.length}</p>
                                </div>
                            </div>
                            
                            <h6>Recent Transactions</h6>
                            <div class="transaction-list" style="max-height: 300px; overflow-y: auto;">
                                ${customerTransactions.length === 0 ? 
                                    '<p class="text-muted">No transactions yet.</p>' :
                                    customerTransactions.map(t => `
                                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                            <div>
                                                <strong>${t.type === 'sale' ? t.description : 'Payment'}</strong>
                                                ${t.billPhoto ? '<br><small><i class="fas fa-camera text-primary"></i> Bill photo attached</small>' : ''}
                                                ${t.notes ? `<br><small class="text-muted">${t.notes}</small>` : ''}
                                                <br><small class="text-muted">${new Date(t.createdDate).toLocaleDateString()}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge ${t.type === 'sale' ? 'bg-danger' : 'bg-success'}">
                                                    ${t.type === 'sale' ? '+' : '-'}₹${t.amount.toFixed(2)}
                                                </span>
                                                ${t.billPhoto ? `<br><button class="btn btn-sm btn-outline-primary mt-1" onclick="window.open('${t.billPhoto.downloadURL}', '_blank')">View Bill</button>` : ''}
                                            </div>
                                        </div>
                                    `).join('')
                                }
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="app.showAddSaleModal('${customerId}'); bootstrap.Modal.getInstance(document.getElementById('customerDetailsModal')).hide();">
                                <i class="fas fa-plus"></i> Add Sale
                            </button>
                            <button type="button" class="btn btn-success" onclick="app.showAddPaymentModal('${customerId}'); bootstrap.Modal.getInstance(document.getElementById('customerDetailsModal')).hide();">
                                <i class="fas fa-money-bill"></i> Add Payment
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        const existingModal = document.getElementById('customerDetailsModal');
        if (existingModal) existingModal.remove();

        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
        modal.show();
    }

    updateDashboard() {
        const totalCustomers = this.customers.length;
        const totalDebt = this.customers.reduce((sum, customer) => sum + customer.totalDebt, 0);
        const customersWithDebt = this.customers.filter(customer => customer.totalDebt > 0).length;

        // Update dashboard stats
        const totalCustomersElement = document.getElementById('totalCustomers');
        const totalDebtElement = document.getElementById('totalDebt');
        const pendingPaymentsElement = document.getElementById('pendingPayments');

        if (totalCustomersElement) totalCustomersElement.textContent = totalCustomers;
        if (totalDebtElement) totalDebtElement.textContent = `₹${totalDebt.toFixed(2)}`;
        if (pendingPaymentsElement) pendingPaymentsElement.textContent = customersWithDebt;
    }

    updateCloudStatus(connected) {
        const statusElement = document.getElementById('cloudStatus');
        if (statusElement) {
            statusElement.innerHTML = connected ? 
                '<i class="fas fa-cloud text-success"></i> Firebase Connected' : 
                '<i class="fas fa-cloud-slash text-warning"></i> Offline Mode';
        }
    }

    showSection(sectionId) {
        const sections = ['dashboard', 'customers', 'billing', 'reports', 'settings'];
        sections.forEach(section => {
            const element = document.getElementById(section);
            if (element) {
                element.classList.add('hidden');
            }
        });

        if (sectionId === 'customers') {
            const customersSection = document.getElementById('customers');
            if (customersSection) {
                customersSection.classList.remove('hidden');
                this.displayCustomers();
            }
        } else {
            const dashboardSection = document.getElementById('dashboard');
            if (dashboardSection) {
                dashboardSection.classList.remove('hidden');
            } else {
                const customersSection = document.getElementById('customers');
                if (customersSection) {
                    customersSection.classList.remove('hidden');
                    this.displayCustomers();
                }
            }
        }
    }

    showMainApp() {
        document.getElementById('loginScreen').classList.add('hidden');
        document.getElementById('mainApp').classList.remove('hidden');
        this.updateDashboard();
        this.displayCustomers();
    }

    showLoading() {
        document.getElementById('loadingSpinner').classList.remove('hidden');
    }

    hideLoading() {
        document.getElementById('loadingSpinner').classList.add('hidden');
    }

    showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto dismiss after 4 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.textContent.includes(message)) {
                    alert.remove();
                }
            });
        }, 4000);
    }

    logout() {
        sessionStorage.removeItem('currentSession');
        location.reload();
    }
}

// Global functions for HTML onclick events
function showSection(sectionId) {
    app.showSection(sectionId);
}

function showAddCustomer() {
    const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    modal.show();
}

function logout() {
    app.logout();
}

// Initialize app when page loads
let app;
document.addEventListener('DOMContentLoaded', () => {
    app = new ElectricalStoreApp();
});