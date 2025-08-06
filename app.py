#!/usr/bin/env python3
"""
Amarjit Electrical Store - Render Deployment Version
Complete customer credit management with Google Drive integration
"""

import os
import io
import json
import logging
from datetime import datetime, timedelta
from decimal import Decimal
from functools import wraps

import requests
from flask import Flask, render_template, request, jsonify, redirect, url_for, session, flash, make_response
from flask_sqlalchemy import SQLAlchemy
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
app.config['SQLALCHEMY_DATABASE_URI'] = os.environ.get('DATABASE_URL', 'sqlite:///electrical_store.db')
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

# Google Drive API Configuration
GOOGLE_CLIENT_ID = os.environ.get('GOOGLE_CLIENT_ID')
GOOGLE_CLIENT_SECRET = os.environ.get('GOOGLE_CLIENT_SECRET')
GOOGLE_REDIRECT_URI = os.environ.get('GOOGLE_REDIRECT_URI')
SCOPES = ['https://www.googleapis.com/auth/drive.file']

# Initialize database
db = SQLAlchemy(app)

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Database Models
class User(db.Model):
    """User model for authentication"""
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    password_hash = db.Column(db.String(120), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def set_password(self, password):
        self.password_hash = generate_password_hash(password)

    def check_password(self, password):
        return check_password_hash(self.password_hash, password)

class Customer(db.Model):
    """Customer model"""
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    phone = db.Column(db.String(15), unique=True, nullable=False)
    address = db.Column(db.Text)
    total_debt = db.Column(db.Numeric(10, 2), default=0.00)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

    # Relationships
    transactions = db.relationship('Transaction', backref='customer', lazy=True, cascade='all, delete-orphan')
    payments = db.relationship('Payment', backref='customer', lazy=True, cascade='all, delete-orphan')

    def __repr__(self):
        return f'<Customer {self.name}>'

class Transaction(db.Model):
    """Sales transaction model"""
    id = db.Column(db.Integer, primary_key=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('customer.id'), nullable=False)
    amount = db.Column(db.Numeric(10, 2), nullable=False)
    description = db.Column(db.Text)
    bill_photo_url = db.Column(db.String(500))  # Google Drive file URL
    transaction_date = db.Column(db.DateTime, default=datetime.utcnow)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def __repr__(self):
        return f'<Transaction {self.id}: ${self.amount}>'

class Payment(db.Model):
    """Payment model"""
    id = db.Column(db.Integer, primary_key=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('customer.id'), nullable=False)
    amount = db.Column(db.Numeric(10, 2), nullable=False)
    payment_date = db.Column(db.DateTime, default=datetime.utcnow)
    notes = db.Column(db.Text)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def __repr__(self):
        return f'<Payment {self.id}: ${self.amount}>'

# Authentication decorator
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

# Google Drive helper functions
def get_google_drive_service():
    """Get Google Drive service with user credentials"""
    if 'google_credentials' not in session:
        return None
    
    credentials_info = session['google_credentials']
    credentials = Credentials(
        token=credentials_info['token'],
        refresh_token=credentials_info.get('refresh_token'),
        id_token=credentials_info.get('id_token'),
        token_uri=credentials_info['token_uri'],
        client_id=GOOGLE_CLIENT_ID,
        client_secret=GOOGLE_CLIENT_SECRET,
        scopes=SCOPES
    )
    
    return build('drive', 'v3', credentials=credentials)

def upload_to_google_drive(file_data, filename, customer_name):
    """Upload file to Google Drive and return file URL"""
    try:
        service = get_google_drive_service()
        if not service:
            return None

        # Create folder structure: Amarjit Store/Bills/Customer Name
        folder_id = create_drive_folder_structure(service, customer_name)
        
        # Prepare file metadata
        file_metadata = {
            'name': filename,
            'parents': [folder_id] if folder_id else []
        }
        
        # Upload file
        media = MediaIoBaseUpload(io.BytesIO(file_data), mimetype='image/jpeg')
        file = service.files().create(
            body=file_metadata,
            media_body=media,
            fields='id,webViewLink,webContentLink'
        ).execute()
        
        # Make file publicly viewable
        service.permissions().create(
            fileId=file['id'],
            body={'role': 'reader', 'type': 'anyone'}
        ).execute()
        
        return file.get('webViewLink')
        
    except Exception as e:
        logger.error(f"Error uploading to Google Drive: {str(e)}")
        return None

def create_drive_folder_structure(service, customer_name):
    """Create folder structure in Google Drive"""
    try:
        # Create main folder: Amarjit Electrical Store
        main_folder = create_or_get_folder(service, "Amarjit Electrical Store")
        
        # Create Bills folder inside main folder
        bills_folder = create_or_get_folder(service, "Bills", main_folder)
        
        # Create customer folder inside Bills folder
        customer_folder = create_or_get_folder(service, customer_name, bills_folder)
        
        return customer_folder
        
    except Exception as e:
        logger.error(f"Error creating folder structure: {str(e)}")
        return None

def create_or_get_folder(service, folder_name, parent_folder_id=None):
    """Create folder or get existing folder ID"""
    try:
        # Search for existing folder
        query = f"name='{folder_name}' and mimeType='application/vnd.google-apps.folder'"
        if parent_folder_id:
            query += f" and '{parent_folder_id}' in parents"
        
        results = service.files().list(q=query, fields="files(id, name)").execute()
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
        logger.error(f"Error creating folder {folder_name}: {str(e)}")
        return None

# Routes
@app.route('/')
def index():
    """Dashboard page"""
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    # Get dashboard statistics
    total_customers = Customer.query.count()
    total_debt = db.session.query(db.func.sum(Customer.total_debt)).scalar() or 0
    recent_transactions = Transaction.query.order_by(Transaction.created_at.desc()).limit(5).all()
    recent_payments = Payment.query.order_by(Payment.created_at.desc()).limit(5).all()
    
    return render_template('dashboard.html',
                         total_customers=total_customers,
                         total_debt=total_debt,
                         recent_transactions=recent_transactions,
                         recent_payments=recent_payments)

@app.route('/login', methods=['GET', 'POST'])
def login():
    """Login page"""
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        user = User.query.filter_by(username=username).first()
        
        if user and user.check_password(password):
            session['user_id'] = user.id
            session['username'] = user.username
            return redirect(url_for('index'))
        else:
            flash('Invalid username or password', 'error')
    
    return render_template('login.html')

@app.route('/logout')
def logout():
    """Logout user"""
    session.clear()
    return redirect(url_for('login'))

@app.route('/setup', methods=['GET', 'POST'])
def setup():
    """Initial setup page"""
    # Check if admin user already exists
    if User.query.count() > 0:
        return redirect(url_for('login'))
    
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        # Create admin user
        admin_user = User(username=username)
        admin_user.set_password(password)
        
        db.session.add(admin_user)
        db.session.commit()
        
        flash('Setup completed successfully! Please login.', 'success')
        return redirect(url_for('login'))
    
    return render_template('setup.html')

@app.route('/customers')
@login_required
def customers():
    """Customers listing page"""
    search = request.args.get('search', '')
    
    if search:
        customers_list = Customer.query.filter(
            db.or_(
                Customer.name.contains(search),
                Customer.phone.contains(search)
            )
        ).order_by(Customer.name).all()
    else:
        customers_list = Customer.query.order_by(Customer.name).all()
    
    return render_template('customers.html', customers=customers_list, search=search)

@app.route('/customer/<int:customer_id>')
@login_required
def customer_detail(customer_id):
    """Customer detail page"""
    customer = Customer.query.get_or_404(customer_id)
    transactions = Transaction.query.filter_by(customer_id=customer_id).order_by(Transaction.transaction_date.desc()).all()
    payments = Payment.query.filter_by(customer_id=customer_id).order_by(Payment.payment_date.desc()).all()
    
    return render_template('customer_detail.html',
                         customer=customer,
                         transactions=transactions,
                         payments=payments)

@app.route('/add_customer', methods=['GET', 'POST'])
@login_required
def add_customer():
    """Add new customer"""
    if request.method == 'POST':
        name = request.form['name']
        phone = request.form['phone']
        address = request.form.get('address', '')
        
        # Check if phone already exists
        existing_customer = Customer.query.filter_by(phone=phone).first()
        if existing_customer:
            flash('Customer with this phone number already exists!', 'error')
            return render_template('add_customer.html')
        
        customer = Customer(name=name, phone=phone, address=address)
        db.session.add(customer)
        db.session.commit()
        
        flash(f'Customer {name} added successfully!', 'success')
        return redirect(url_for('customer_detail', customer_id=customer.id))
    
    return render_template('add_customer.html')

@app.route('/quick_billing')
@login_required
def quick_billing():
    """Quick billing page"""
    return render_template('quick_billing.html')

@app.route('/api/search_customer')
@login_required
def search_customer():
    """API endpoint to search customer by phone"""
    phone = request.args.get('phone', '').strip()
    
    if not phone:
        return jsonify({'error': 'Phone number required'}), 400
    
    customer = Customer.query.filter_by(phone=phone).first()
    
    if customer:
        return jsonify({
            'found': True,
            'customer': {
                'id': customer.id,
                'name': customer.name,
                'phone': customer.phone,
                'address': customer.address,
                'total_debt': float(customer.total_debt)
            }
        })
    else:
        return jsonify({'found': False})

@app.route('/api/add_transaction', methods=['POST'])
@login_required
def add_transaction():
    """API endpoint to add new transaction"""
    try:
        customer_id = request.form.get('customer_id')
        amount = float(request.form.get('amount', 0))
        description = request.form.get('description', '')
        
        customer = Customer.query.get_or_404(customer_id)
        
        # Handle bill photo upload
        bill_photo_url = None
        if 'bill_photo' in request.files:
            file = request.files['bill_photo']
            if file and file.filename:
                filename = secure_filename(f"{customer.name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}_{file.filename}")
                file_data = file.read()
                bill_photo_url = upload_to_google_drive(file_data, filename, customer.name)
        
        # Create transaction
        transaction = Transaction(
            customer_id=customer_id,
            amount=amount,
            description=description,
            bill_photo_url=bill_photo_url
        )
        
        # Update customer debt
        customer.total_debt += Decimal(str(amount))
        customer.updated_at = datetime.utcnow()
        
        db.session.add(transaction)
        db.session.commit()
        
        return jsonify({
            'success': True,
            'message': 'Transaction added successfully',
            'new_debt': float(customer.total_debt)
        })
        
    except Exception as e:
        logger.error(f"Error adding transaction: {str(e)}")
        return jsonify({'error': str(e)}), 500

@app.route('/api/add_payment', methods=['POST'])
@login_required
def add_payment():
    """API endpoint to add payment"""
    try:
        customer_id = request.form.get('customer_id')
        amount = float(request.form.get('amount', 0))
        notes = request.form.get('notes', '')
        
        customer = Customer.query.get_or_404(customer_id)
        
        # Create payment
        payment = Payment(
            customer_id=customer_id,
            amount=amount,
            notes=notes
        )
        
        # Update customer debt
        customer.total_debt -= Decimal(str(amount))
        if customer.total_debt < 0:
            customer.total_debt = 0
        customer.updated_at = datetime.utcnow()
        
        db.session.add(payment)
        db.session.commit()
        
        return jsonify({
            'success': True,
            'message': 'Payment recorded successfully',
            'new_debt': float(customer.total_debt)
        })
        
    except Exception as e:
        logger.error(f"Error adding payment: {str(e)}")
        return jsonify({'error': str(e)}), 500

@app.route('/google_auth')
@login_required
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
@login_required
def google_callback():
    """Handle Google OAuth callback"""
    try:
        state = session.get('state')
        if not state or state != request.args.get('state'):
            flash('Invalid state parameter', 'error')
            return redirect(url_for('index'))
        
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
        
        flash('Google Drive connected successfully!', 'success')
        return redirect(url_for('index'))
        
    except Exception as e:
        logger.error(f"Error in Google callback: {str(e)}")
        flash('Error connecting to Google Drive', 'error')
        return redirect(url_for('index'))

@app.route('/reports')
@login_required
def reports():
    """Reports page"""
    # Date range filtering
    start_date = request.args.get('start_date')
    end_date = request.args.get('end_date')
    
    # Default to last 30 days
    if not start_date:
        start_date = (datetime.now() - timedelta(days=30)).strftime('%Y-%m-%d')
    if not end_date:
        end_date = datetime.now().strftime('%Y-%m-%d')
    
    # Convert to datetime objects
    start_dt = datetime.strptime(start_date, '%Y-%m-%d')
    end_dt = datetime.strptime(end_date, '%Y-%m-%d') + timedelta(days=1)
    
    # Get filtered data
    transactions = Transaction.query.filter(
        Transaction.transaction_date >= start_dt,
        Transaction.transaction_date < end_dt
    ).all()
    
    payments = Payment.query.filter(
        Payment.payment_date >= start_dt,
        Payment.payment_date < end_dt
    ).all()
    
    # Calculate statistics
    total_sales = sum(float(t.amount) for t in transactions)
    total_payments = sum(float(p.amount) for p in payments)
    total_outstanding = float(db.session.query(db.func.sum(Customer.total_debt)).scalar() or 0)
    
    return render_template('reports.html',
                         transactions=transactions,
                         payments=payments,
                         total_sales=total_sales,
                         total_payments=total_payments,
                         total_outstanding=total_outstanding,
                         start_date=start_date,
                         end_date=end_date)

@app.route('/customer/<int:customer_id>/edit', methods=['GET', 'POST'])
@login_required
def edit_customer(customer_id):
    """Edit customer information"""
    customer = Customer.query.get_or_404(customer_id)
    
    if request.method == 'POST':
        try:
            customer.name = request.form.get('name', '').strip()
            customer.phone = request.form.get('phone', '').strip()
            customer.address = request.form.get('address', '').strip()
            
            if not customer.name or not customer.phone:
                flash('Name and phone are required', 'error')
                return render_template('edit_customer.html', customer=customer)
            
            db.session.commit()
            flash(f'Customer {customer.name} updated successfully!', 'success')
            return redirect(url_for('customer_detail', customer_id=customer.id))
            
        except Exception as e:
            db.session.rollback()
            logger.error(f"Error updating customer: {str(e)}")
            flash('Error updating customer', 'error')
    
    return render_template('edit_customer.html', customer=customer)

@app.route('/customer/<int:customer_id>/delete', methods=['POST'])
@login_required
def delete_customer(customer_id):
    """Delete customer and all related data"""
    customer = Customer.query.get_or_404(customer_id)
    
    try:
        # Delete related transactions and payments
        Transaction.query.filter_by(customer_id=customer_id).delete()
        Payment.query.filter_by(customer_id=customer_id).delete()
        
        # Delete customer
        db.session.delete(customer)
        db.session.commit()
        
        flash(f'Customer {customer.name} and all related data deleted successfully!', 'success')
        return redirect(url_for('customers'))
        
    except Exception as e:
        db.session.rollback()
        logger.error(f"Error deleting customer: {str(e)}")
        flash('Error deleting customer', 'error')
        return redirect(url_for('customer_detail', customer_id=customer_id))

@app.route('/transaction/<int:transaction_id>/return', methods=['POST'])
@login_required
def return_transaction(transaction_id):
    """Handle product returns"""
    transaction = Transaction.query.get_or_404(transaction_id)
    
    try:
        return_amount = float(request.form.get('return_amount', 0))
        return_notes = request.form.get('return_notes', '').strip()
        
        if return_amount <= 0 or return_amount > float(transaction.amount):
            return jsonify({'success': False, 'message': 'Invalid return amount'})
        
        # Create a return transaction (negative amount)
        return_transaction = Transaction(
            customer_id=transaction.customer_id,
            amount=-return_amount,  # Negative for return
            description=f"RETURN: {transaction.description} - {return_notes}",
            transaction_date=datetime.now(),
            bill_photo_url=None
        )
        
        db.session.add(return_transaction)
        
        # Update customer debt
        customer = transaction.customer
        customer.total_debt = float(customer.total_debt) - return_amount
        
        db.session.commit()
        
        return jsonify({
            'success': True, 
            'message': f'Return of ₹{return_amount:.2f} processed successfully',
            'new_debt': customer.total_debt
        })
        
    except Exception as e:
        db.session.rollback()
        logger.error(f"Error processing return: {str(e)}")
        return jsonify({'success': False, 'message': 'Error processing return'})

@app.route('/transaction/<int:transaction_id>/delete', methods=['POST'])
@login_required
def delete_transaction(transaction_id):
    """Delete a transaction"""
    transaction = Transaction.query.get_or_404(transaction_id)
    customer = transaction.customer
    
    try:
        # Adjust customer debt
        customer.total_debt = float(customer.total_debt) - float(transaction.amount)
        
        # Delete transaction
        db.session.delete(transaction)
        db.session.commit()
        
        return jsonify({
            'success': True, 
            'message': 'Transaction deleted successfully',
            'new_debt': customer.total_debt
        })
        
    except Exception as e:
        db.session.rollback()
        logger.error(f"Error deleting transaction: {str(e)}")
        return jsonify({'success': False, 'message': 'Error deleting transaction'})

@app.route('/export/customers')
@login_required
def export_customers():
    """Export customers data as CSV"""
    import csv
    from io import StringIO
    
    output = StringIO()
    writer = csv.writer(output)
    
    # Write headers
    writer.writerow(['Name', 'Phone', 'Address', 'Total Debt', 'Created Date'])
    
    # Write customer data
    customers = Customer.query.all()
    for customer in customers:
        writer.writerow([
            customer.name,
            customer.phone,
            customer.address or '',
            f"{customer.total_debt:.2f}",
            customer.created_at.strftime('%Y-%m-%d')
        ])
    
    # Create response
    response = make_response(output.getvalue())
    response.headers["Content-Disposition"] = "attachment; filename=customers_export.csv"
    response.headers["Content-type"] = "text/csv"
    
    return response

@app.route('/export/transactions')
@login_required
def export_transactions():
    """Export transactions data as CSV"""
    import csv
    from io import StringIO
    
    output = StringIO()
    writer = csv.writer(output)
    
    # Write headers
    writer.writerow(['Date', 'Customer Name', 'Customer Phone', 'Amount', 'Description', 'Bill Photo URL'])
    
    # Write transaction data
    transactions = Transaction.query.join(Customer).all()
    for transaction in transactions:
        writer.writerow([
            transaction.transaction_date.strftime('%Y-%m-%d %H:%M'),
            transaction.customer.name,
            transaction.customer.phone,
            f"{transaction.amount:.2f}",
            transaction.description or '',
            transaction.bill_photo_url or ''
        ])
    
    # Create response
    response = make_response(output.getvalue())
    response.headers["Content-Disposition"] = "attachment; filename=transactions_export.csv"
    response.headers["Content-type"] = "text/csv"
    
    return response

@app.route('/api/search', methods=['GET'])
@login_required
def advanced_search():
    """Advanced search across customers and transactions"""
    query = request.args.get('q', '').strip()
    search_type = request.args.get('type', 'all')  # all, customers, transactions
    
    results = {
        'customers': [],
        'transactions': []
    }
    
    if query:
        if search_type in ['all', 'customers']:
            # Search customers
            customers = Customer.query.filter(
                db.or_(
                    Customer.name.ilike(f'%{query}%'),
                    Customer.phone.ilike(f'%{query}%'),
                    Customer.address.ilike(f'%{query}%')
                )
            ).limit(10).all()
            
            results['customers'] = [{
                'id': c.id,
                'name': c.name,
                'phone': c.phone,
                'debt': float(c.total_debt),
                'url': url_for('customer_detail', customer_id=c.id)
            } for c in customers]
        
        if search_type in ['all', 'transactions']:
            # Search transactions
            transactions = Transaction.query.join(Customer).filter(
                db.or_(
                    Transaction.description.ilike(f'%{query}%'),
                    Customer.name.ilike(f'%{query}%'),
                    Customer.phone.ilike(f'%{query}%')
                )
            ).limit(10).all()
            
            results['transactions'] = [{
                'id': t.id,
                'customer_name': t.customer.name,
                'amount': float(t.amount),
                'description': t.description,
                'date': t.transaction_date.strftime('%Y-%m-%d'),
                'customer_url': url_for('customer_detail', customer_id=t.customer_id)
            } for t in transactions]
    
    return jsonify(results)

# Error handlers
@app.errorhandler(404)
def not_found(error):
    return render_template('error.html', error="Page not found"), 404

@app.errorhandler(500)
def internal_error(error):
    db.session.rollback()
    return render_template('error.html', error="Internal server error"), 500

# Database initialization
def create_tables():
    """Create database tables"""
    db.create_all()

# Initialize tables when app starts (only in production)
if not app.debug:
    with app.app_context():
        create_tables()

if __name__ == '__main__':
    # Create tables for development
    with app.app_context():
        create_tables()
    app.run(debug=True)