#!/usr/bin/env python3
"""
Amarjit Electrical Store - Cloud-Only Version
Complete customer credit management with ALL data stored in Google Drive
"""

import os
import io
import json
import logging
from datetime import datetime, timedelta
from decimal import Decimal
from functools import wraps

import requests
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Allow insecure transport for local development
os.environ['OAUTHLIB_INSECURE_TRANSPORT'] = '1'

from flask import Flask, render_template, request, jsonify, redirect, url_for, session, flash, make_response
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename
from google.oauth2.credentials import Credentials
from google_auth_oauthlib.flow import Flow
from googleapiclient.discovery import build
from googleapiclient.http import MediaIoBaseUpload

# Initialize Flask app
app = Flask(__name__)

# Configuration
app.config['SECRET_KEY'] = os.environ.get('SECRET_KEY', 'amarjit-electrical-store-secret-key-change-in-production')

# Google Drive API Configuration
GOOGLE_CLIENT_ID = os.environ.get('GOOGLE_CLIENT_ID')
GOOGLE_CLIENT_SECRET = os.environ.get('GOOGLE_CLIENT_SECRET')
GOOGLE_REDIRECT_URI = os.environ.get('GOOGLE_REDIRECT_URI')
SCOPES = ['https://www.googleapis.com/auth/drive.file']

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Google Drive Data Structure
"""
Google Drive Structure:
📁 Amarjit Electrical Store/
  📄 admin_user.json - Admin login credentials
  📄 customers.json - All customer data
  📄 transactions.json - All transactions
  📄 payments.json - All payments
  📁 Bills/
    📁 [Customer Name]/
      📄 bill_photos.jpg
"""

def get_google_drive_service():
    """Get authenticated Google Drive service"""
    try:
        if 'google_credentials' not in session:
            return None
        
        creds_data = session['google_credentials']
        credentials = Credentials(
            token=creds_data['token'],
            refresh_token=creds_data.get('refresh_token'),
            id_token=creds_data.get('id_token'),
            token_uri=creds_data['token_uri'],
            client_id=GOOGLE_CLIENT_ID,
            client_secret=GOOGLE_CLIENT_SECRET
        )
        
        service = build('drive', 'v3', credentials=credentials)
        return service
    except Exception as e:
        logger.error(f"Error getting Google Drive service: {str(e)}")
        return None

def create_or_get_folder(service, folder_name, parent_folder_id=None):
    """Create or get folder in Google Drive"""
    try:
        # Search for existing folder
        query = f"name='{folder_name}' and mimeType='application/vnd.google-apps.folder'"
        if parent_folder_id:
            query += f" and parents in '{parent_folder_id}'"
        
        results = service.files().list(q=query).execute()
        items = results.get('files', [])
        
        if items:
            return items[0]['id']
        
        # Create new folder
        file_metadata = {
            'name': folder_name,
            'mimeType': 'application/vnd.google-apps.folder'
        }
        if parent_folder_id:
            file_metadata['parents'] = [parent_folder_id]
        
        folder = service.files().create(body=file_metadata, fields='id').execute()
        return folder.get('id')
    
    except Exception as e:
        logger.error(f"Error creating/getting folder: {str(e)}")
        return None

def save_json_to_drive(service, data, filename, folder_id):
    """Save JSON data to Google Drive"""
    try:
        json_data = json.dumps(data, indent=2, default=str).encode('utf-8')
        
        # Check if file exists
        query = f"name='{filename}' and parents in '{folder_id}'"
        results = service.files().list(q=query).execute()
        items = results.get('files', [])
        
        media = MediaIoBaseUpload(io.BytesIO(json_data), mimetype='application/json')
        
        if items:
            # Update existing file
            file_id = items[0]['id']
            service.files().update(fileId=file_id, media_body=media).execute()
        else:
            # Create new file
            file_metadata = {
                'name': filename,
                'parents': [folder_id]
            }
            service.files().create(body=file_metadata, media_body=media).execute()
        
        return True
    except Exception as e:
        logger.error(f"Error saving JSON to drive: {str(e)}")
        return False

def load_json_from_drive(service, filename, folder_id):
    """Load JSON data from Google Drive"""
    try:
        query = f"name='{filename}' and parents in '{folder_id}'"
        results = service.files().list(q=query).execute()
        items = results.get('files', [])
        
        if not items:
            return None
        
        file_id = items[0]['id']
        file_content = service.files().get_media(fileId=file_id).execute()
        data = json.loads(file_content.decode('utf-8'))
        return data
    
    except Exception as e:
        logger.error(f"Error loading JSON from drive: {str(e)}")
        return None

class CloudDataManager:
    """Manage all data operations with Google Drive"""
    
    def __init__(self):
        self.service = None
        self.main_folder_id = None
        self.bills_folder_id = None
    
    def initialize(self):
        """Initialize Google Drive connection and folder structure"""
        self.service = get_google_drive_service()
        if not self.service:
            return False
        
        # Create main folder structure
        self.main_folder_id = create_or_get_folder(self.service, "Amarjit Electrical Store")
        self.bills_folder_id = create_or_get_folder(self.service, "Bills", self.main_folder_id)
        
        return True
    
    # User Management
    def get_admin_user(self):
        """Get admin user credentials"""
        if not self.service:
            return None
        return load_json_from_drive(self.service, 'admin_user.json', self.main_folder_id)
    
    def create_admin_user(self, username, password):
        """Create admin user"""
        if not self.service:
            return False
        
        user_data = {
            'username': username,
            'password_hash': generate_password_hash(password),
            'created_at': datetime.now().isoformat()
        }
        
        return save_json_to_drive(self.service, user_data, 'admin_user.json', self.main_folder_id)
    
    def verify_admin_user(self, username, password):
        """Verify admin credentials"""
        user_data = self.get_admin_user()
        if not user_data:
            return False
        
        return (user_data['username'] == username and 
                check_password_hash(user_data['password_hash'], password))
    
    # Customer Management
    def get_customers(self):
        """Get all customers"""
        if not self.service:
            return []
        
        data = load_json_from_drive(self.service, 'customers.json', self.main_folder_id)
        return data if data else []
    
    def save_customers(self, customers):
        """Save customers list"""
        if not self.service:
            return False
        return save_json_to_drive(self.service, customers, 'customers.json', self.main_folder_id)
    
    def add_customer(self, name, phone, address=''):
        """Add new customer"""
        customers = self.get_customers()
        
        # Check if phone already exists
        for customer in customers:
            if customer['phone'] == phone:
                return False, "Customer with this phone number already exists!"
        
        # Generate new ID
        new_id = max([c.get('id', 0) for c in customers], default=0) + 1
        
        new_customer = {
            'id': new_id,
            'name': name,
            'phone': phone,
            'address': address,
            'total_debt': 0.0,
            'created_at': datetime.now().isoformat(),
            'updated_at': datetime.now().isoformat()
        }
        
        customers.append(new_customer)
        
        if self.save_customers(customers):
            return True, new_customer
        else:
            return False, "Failed to save customer"
    
    def get_customer_by_id(self, customer_id):
        """Get customer by ID"""
        customers = self.get_customers()
        for customer in customers:
            if customer['id'] == int(customer_id):
                return customer
        return None
    
    def get_customer_by_phone(self, phone):
        """Get customer by phone"""
        customers = self.get_customers()
        for customer in customers:
            if customer['phone'] == phone:
                return customer
        return None
    
    def update_customer(self, customer_id, name, phone, address):
        """Update customer information"""
        customers = self.get_customers()
        
        for i, customer in enumerate(customers):
            if customer['id'] == int(customer_id):
                # Check if phone is taken by another customer
                for other_customer in customers:
                    if other_customer['id'] != int(customer_id) and other_customer['phone'] == phone:
                        return False, "Phone number already exists for another customer!"
                
                customers[i].update({
                    'name': name,
                    'phone': phone,
                    'address': address,
                    'updated_at': datetime.now().isoformat()
                })
                
                if self.save_customers(customers):
                    return True, customers[i]
                else:
                    return False, "Failed to save changes"
        
        return False, "Customer not found"
    
    def update_customer_debt(self, customer_id, new_debt):
        """Update customer debt amount"""
        customers = self.get_customers()
        
        for i, customer in enumerate(customers):
            if customer['id'] == int(customer_id):
                customers[i]['total_debt'] = float(new_debt)
                customers[i]['updated_at'] = datetime.now().isoformat()
                
                return self.save_customers(customers)
        
        return False
    
    # Transaction Management
    def get_transactions(self):
        """Get all transactions"""
        if not self.service:
            return []
        
        data = load_json_from_drive(self.service, 'transactions.json', self.main_folder_id)
        return data if data else []
    
    def save_transactions(self, transactions):
        """Save transactions list"""
        if not self.service:
            return False
        return save_json_to_drive(self.service, transactions, 'transactions.json', self.main_folder_id)
    
    def add_transaction(self, customer_id, amount, description, bill_photo_url=None):
        """Add new transaction"""
        transactions = self.get_transactions()
        
        # Generate new ID
        new_id = max([t.get('id', 0) for t in transactions], default=0) + 1
        
        new_transaction = {
            'id': new_id,
            'customer_id': int(customer_id),
            'amount': float(amount),
            'description': description,
            'bill_photo_url': bill_photo_url,
            'transaction_date': datetime.now().isoformat(),
            'created_at': datetime.now().isoformat()
        }
        
        transactions.append(new_transaction)
        
        # Update customer debt
        customer = self.get_customer_by_id(customer_id)
        if customer:
            new_debt = customer['total_debt'] + float(amount)
            self.update_customer_debt(customer_id, new_debt)
        
        return self.save_transactions(transactions), new_transaction
    
    def get_customer_transactions(self, customer_id):
        """Get transactions for a specific customer"""
        transactions = self.get_transactions()
        return [t for t in transactions if t['customer_id'] == int(customer_id)]
    
    # Payment Management
    def get_payments(self):
        """Get all payments"""
        if not self.service:
            return []
        
        data = load_json_from_drive(self.service, 'payments.json', self.main_folder_id)
        return data if data else []
    
    def save_payments(self, payments):
        """Save payments list"""
        if not self.service:
            return False
        return save_json_to_drive(self.service, payments, 'payments.json', self.main_folder_id)
    
    def add_payment(self, customer_id, amount, notes=''):
        """Add new payment"""
        payments = self.get_payments()
        
        # Generate new ID
        new_id = max([p.get('id', 0) for p in payments], default=0) + 1
        
        new_payment = {
            'id': new_id,
            'customer_id': int(customer_id),
            'amount': float(amount),
            'notes': notes,
            'payment_date': datetime.now().isoformat(),
            'created_at': datetime.now().isoformat()
        }
        
        payments.append(new_payment)
        
        # Update customer debt
        customer = self.get_customer_by_id(customer_id)
        if customer:
            new_debt = max(0, customer['total_debt'] - float(amount))
            self.update_customer_debt(customer_id, new_debt)
        
        return self.save_payments(payments), new_payment
    
    def get_customer_payments(self, customer_id):
        """Get payments for a specific customer"""
        payments = self.get_payments()
        return [p for p in payments if p['customer_id'] == int(customer_id)]
    
    # Photo Management
    def upload_bill_photo(self, file_data, filename, customer_name):
        """Upload bill photo to Google Drive"""
        if not self.service:
            return None
        
        try:
            # Create customer folder if it doesn't exist
            customer_folder_id = create_or_get_folder(self.service, customer_name, self.bills_folder_id)
            
            file_metadata = {
                'name': filename,
                'parents': [customer_folder_id]
            }
            
            media = MediaIoBaseUpload(io.BytesIO(file_data), mimetype='image/jpeg')
            file = self.service.files().create(
                body=file_metadata,
                media_body=media,
                fields='id,webViewLink'
            ).execute()
            
            return file.get('webViewLink')
        
        except Exception as e:
            logger.error(f"Error uploading photo: {str(e)}")
            return None

# Initialize cloud data manager
cloud_data = CloudDataManager()

# Authentication decorator
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_authenticated' not in session:
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

def google_drive_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not cloud_data.initialize():
            flash('Please connect to Google Drive first', 'error')
            return redirect(url_for('google_auth'))
        return f(*args, **kwargs)
    return decorated_function

# Routes
@app.route('/')
def index():
    """Dashboard page"""
    if 'user_authenticated' not in session:
        return redirect(url_for('login'))
    
    if not cloud_data.initialize():
        flash('Please connect to Google Drive to access your data', 'warning')
        return redirect(url_for('google_auth'))
    
    # Get dashboard statistics
    customers = cloud_data.get_customers()
    transactions = cloud_data.get_transactions()
    payments = cloud_data.get_payments()
    
    total_customers = len(customers)
    total_debt = sum(c['total_debt'] for c in customers)
    recent_transactions = sorted(transactions, key=lambda x: x['created_at'], reverse=True)[:5]
    recent_payments = sorted(payments, key=lambda x: x['created_at'], reverse=True)[:5]
    
    return render_template('dashboard_cloud.html',
                         total_customers=total_customers,
                         total_debt=total_debt,
                         recent_transactions=recent_transactions,
                         recent_payments=recent_payments,
                         customers=customers)

@app.route('/login', methods=['GET', 'POST'])
def login():
    """Login page"""
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        # First check if Google Drive is connected
        if not cloud_data.initialize():
            flash('Please connect to Google Drive first', 'error')
            return redirect(url_for('google_auth'))
        
        # Check if admin user exists
        if not cloud_data.get_admin_user():
            flash('No admin user found. Please set up your account.', 'warning')
            return redirect(url_for('setup'))
        
        # Verify credentials
        if cloud_data.verify_admin_user(username, password):
            session['user_authenticated'] = True
            session['username'] = username
            return redirect(url_for('index'))
        else:
            flash('Invalid username or password', 'error')
    
    return render_template('login_cloud.html')

@app.route('/setup', methods=['GET', 'POST'])
def setup():
    """Initial setup page"""
    if not cloud_data.initialize():
        flash('Please connect to Google Drive first', 'error')
        return redirect(url_for('google_auth'))
    
    # Check if admin user already exists
    if cloud_data.get_admin_user():
        return redirect(url_for('login'))
    
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        if cloud_data.create_admin_user(username, password):
            flash('Setup completed successfully! Please login.', 'success')
            return redirect(url_for('login'))
        else:
            flash('Failed to create admin user. Please try again.', 'error')
    
    return render_template('setup_cloud.html')

@app.route('/logout')
def logout():
    """Logout user"""
    session.clear()
    return redirect(url_for('login'))

@app.route('/google_auth')
def google_auth():
    """Initiate Google OAuth flow"""
    flow = Flow.from_client_config(
        {
            "web": {
                "client_id": GOOGLE_CLIENT_ID,
                "client_secret": GOOGLE_CLIENT_SECRET,
                "auth_uri": "https://accounts.google.com/o/oauth2/auth",
                "token_uri": "https://oauth2.googleapis.com/token",
                "redirect_uris": [GOOGLE_REDIRECT_URI]
            }
        },
        scopes=SCOPES
    )
    flow.redirect_uri = GOOGLE_REDIRECT_URI
    
    authorization_url, state = flow.authorization_url(
        access_type='offline',
        include_granted_scopes='true'
    )
    
    session['state'] = state
    return redirect(authorization_url)

@app.route('/google_callback')
def google_callback():
    """Handle Google OAuth callback"""
    try:
        state = session.get('state')
        if not state or state != request.args.get('state'):
            flash('Invalid state parameter', 'error')
            return redirect(url_for('login'))
        
        flow = Flow.from_client_config(
            {
                "web": {
                    "client_id": GOOGLE_CLIENT_ID,
                    "client_secret": GOOGLE_CLIENT_SECRET,
                    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
                    "token_uri": "https://oauth2.googleapis.com/token",
                    "redirect_uris": [GOOGLE_REDIRECT_URI]
                }
            },
            scopes=SCOPES,
            state=state
        )
        flow.redirect_uri = GOOGLE_REDIRECT_URI
        
        # Fetch token
        flow.fetch_token(authorization_response=request.url)
        
        # Store credentials in session
        credentials = flow.credentials
        session['google_credentials'] = {
            'token': credentials.token,
            'refresh_token': credentials.refresh_token,
            'id_token': credentials.id_token,
            'token_uri': credentials.token_uri,
        }
        
        flash('Google Drive connected successfully! All your data is now stored in the cloud.', 'success')
        
        # Check if setup is needed
        if cloud_data.initialize() and not cloud_data.get_admin_user():
            return redirect(url_for('setup'))
        
        return redirect(url_for('login'))
        
    except Exception as e:
        logger.error(f"Error in Google callback: {str(e)}")
        import traceback
        logger.error(f"Full traceback: {traceback.format_exc()}")
        flash(f'Error connecting to Google Drive: {str(e)}', 'error')
        return redirect(url_for('login'))

@app.route('/customers')
@login_required
@google_drive_required
def customers():
    """Customers listing page"""
    search = request.args.get('search', '')
    customers = cloud_data.get_customers()
    
    if search:
        customers = [c for c in customers if 
                    search.lower() in c['name'].lower() or 
                    search.lower() in c['phone'].lower()]
    
    return render_template('customers_cloud.html', customers=customers, search=search)

@app.route('/customer/<int:customer_id>')
@login_required
@google_drive_required
def customer_detail(customer_id):
    """Customer detail page"""
    customer = cloud_data.get_customer_by_id(customer_id)
    if not customer:
        flash('Customer not found', 'error')
        return redirect(url_for('customers'))
    
    transactions = cloud_data.get_customer_transactions(customer_id)
    payments = cloud_data.get_customer_payments(customer_id)
    
    return render_template('customer_detail_cloud.html',
                         customer=customer,
                         transactions=transactions,
                         payments=payments)

@app.route('/add_customer', methods=['GET', 'POST'])
@login_required
@google_drive_required
def add_customer():
    """Add new customer"""
    if request.method == 'POST':
        name = request.form['name']
        phone = request.form['phone']
        address = request.form['address']
        
        success, result = cloud_data.add_customer(name, phone, address)
        
        if success:
            flash(f'Customer {name} added successfully!', 'success')
            return redirect(url_for('customer_detail', customer_id=result['id']))
        else:
            flash(result, 'error')
    
    return render_template('add_customer_cloud.html')

@app.route('/quick_billing')
@login_required
@google_drive_required
def quick_billing():
    """Quick billing page"""
    return render_template('quick_billing_cloud.html')

@app.route('/api/search_customer')
@login_required
@google_drive_required
def search_customer():
    """API endpoint to search customer by phone"""
    phone = request.args.get('phone', '')
    
    if not phone:
        return jsonify({'found': False})
    
    customer = cloud_data.get_customer_by_phone(phone)
    
    if customer:
        return jsonify({
            'found': True,
            'customer': customer
        })
    else:
        return jsonify({'found': False})

@app.route('/api/add_transaction', methods=['POST'])
@login_required
@google_drive_required
def add_transaction():
    """API endpoint to add new transaction"""
    try:
        customer_id = request.form.get('customer_id')
        amount = float(request.form.get('amount', 0))
        description = request.form.get('description', '')
        
        customer = cloud_data.get_customer_by_id(customer_id)
        if not customer:
            return jsonify({'error': 'Customer not found'}), 404
        
        # Handle bill photo upload
        bill_photo_url = None
        if 'bill_photo' in request.files:
            file = request.files['bill_photo']
            if file and file.filename:
                filename = secure_filename(f"{customer['name']}_{datetime.now().strftime('%Y%m%d_%H%M%S')}_{file.filename}")
                file_data = file.read()
                bill_photo_url = cloud_data.upload_bill_photo(file_data, filename, customer['name'])
                
                if not bill_photo_url:
                    return jsonify({
                        'success': False,
                        'error': 'Failed to upload photo to Google Drive. Please try again.',
                    }), 500
        
        # Create transaction
        success, transaction = cloud_data.add_transaction(customer_id, amount, description, bill_photo_url)
        
        if success:
            # Get updated customer debt
            updated_customer = cloud_data.get_customer_by_id(customer_id)
            return jsonify({
                'success': True,
                'message': 'Transaction added successfully',
                'new_debt': float(updated_customer['total_debt'])
            })
        else:
            return jsonify({'error': 'Failed to save transaction'}), 500
        
    except Exception as e:
        logger.error(f"Error adding transaction: {str(e)}")
        return jsonify({'error': str(e)}), 500

@app.route('/api/add_payment', methods=['POST'])
@login_required
@google_drive_required
def add_payment():
    """API endpoint to add new payment"""
    try:
        customer_id = request.form.get('customer_id')
        amount = float(request.form.get('amount', 0))
        notes = request.form.get('notes', '')
        
        customer = cloud_data.get_customer_by_id(customer_id)
        if not customer:
            return jsonify({'error': 'Customer not found'}), 404
        
        # Create payment
        success, payment = cloud_data.add_payment(customer_id, amount, notes)
        
        if success:
            # Get updated customer debt
            updated_customer = cloud_data.get_customer_by_id(customer_id)
            return jsonify({
                'success': True,
                'message': 'Payment recorded successfully',
                'new_debt': float(updated_customer['total_debt'])
            })
        else:
            return jsonify({'error': 'Failed to save payment'}), 500
        
    except Exception as e:
        logger.error(f"Error adding payment: {str(e)}")
        return jsonify({'error': str(e)}), 500

@app.route('/data_status')
@login_required
@google_drive_required
def data_status():
    """Show data storage status"""
    customers = cloud_data.get_customers()
    transactions = cloud_data.get_transactions()
    payments = cloud_data.get_payments()
    
    status = {
        'customers_count': len(customers),
        'transactions_count': len(transactions),
        'payments_count': len(payments),
        'total_debt': sum(c['total_debt'] for c in customers),
        'storage_location': 'Google Drive - Amarjit Electrical Store/',
        'last_updated': datetime.now().isoformat()
    }
    
    return render_template('data_status.html', status=status)

if __name__ == '__main__':
    app.run(debug=True)