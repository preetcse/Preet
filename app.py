from flask import Flask, render_template, request, redirect, url_for, flash, session, jsonify
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename
from datetime import datetime, date
import os
import json
from google.oauth2.credentials import Credentials
from google_auth_oauthlib.flow import Flow
from googleapiclient.discovery import build
from googleapiclient.http import MediaFileUpload
import io
import base64
from PIL import Image
import os
# Fix for development - allow insecure transport for localhost
os.environ['OAUTHLIB_INSECURE_TRANSPORT'] = '1'

app = Flask(__name__)
app.config['SECRET_KEY'] = 'your-secret-key-change-this'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///electrical_store.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16MB max file size

db = SQLAlchemy(app)

# Create uploads directory
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Google Drive API configuration
SCOPES = ['https://www.googleapis.com/auth/drive.file']
CLIENT_SECRETS_FILE = "credentials.json"

# Database Models
class Customer(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    phone = db.Column(db.String(15), nullable=False, unique=True)
    address = db.Column(db.Text)
    created_date = db.Column(db.DateTime, default=datetime.utcnow)
    total_debt = db.Column(db.Float, default=0.0)
    
    # Relationships
    transactions = db.relationship('Transaction', backref='customer', lazy=True)
    payments = db.relationship('Payment', backref='customer', lazy=True)

class Transaction(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('customer.id'), nullable=False)
    amount = db.Column(db.Float, nullable=False)
    description = db.Column(db.Text)
    transaction_date = db.Column(db.Date, nullable=False)
    bill_image_url = db.Column(db.String(500))  # Google Drive URL
    bill_image_id = db.Column(db.String(100))   # Google Drive file ID
    created_date = db.Column(db.DateTime, default=datetime.utcnow)
    transaction_type = db.Column(db.String(20), default='purchase')  # purchase or return

class Payment(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('customer.id'), nullable=False)
    amount = db.Column(db.Float, nullable=False)
    payment_date = db.Column(db.Date, nullable=False)
    notes = db.Column(db.Text)
    created_date = db.Column(db.DateTime, default=datetime.utcnow)

class User(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    password_hash = db.Column(db.String(120), nullable=False)

# Google Drive Helper Functions
def get_google_drive_service():
    """Get authenticated Google Drive service"""
    if 'credentials' not in session:
        return None
    
    credentials = Credentials.from_authorized_user_info(session['credentials'], SCOPES)
    return build('drive', 'v3', credentials=credentials)

def upload_to_google_drive(file_path, filename):
    """Upload file to Google Drive and return file ID and shareable URL"""
    try:
        service = get_google_drive_service()
        if not service:
            return None, None
        
        file_metadata = {
            'name': filename,
            'parents': [get_or_create_store_folder()]
        }
        
        media = MediaFileUpload(file_path, resumable=True)
        file = service.files().create(
            body=file_metadata,
            media_body=media,
            fields='id'
        ).execute()
        
        file_id = file.get('id')
        
        # Make file shareable
        service.permissions().create(
            fileId=file_id,
            body={'role': 'reader', 'type': 'anyone'}
        ).execute()
        
        # Get shareable URL
        shareable_url = f"https://drive.google.com/file/d/{file_id}/view"
        
        return file_id, shareable_url
    except Exception as e:
        print(f"Error uploading to Google Drive: {e}")
        return None, None

def get_or_create_store_folder():
    """Get or create the main store folder in Google Drive"""
    try:
        service = get_google_drive_service()
        if not service:
            return None
        
        # Search for existing folder
        results = service.files().list(
            q="name='Amarjit Electrical Store' and mimeType='application/vnd.google-apps.folder'",
            fields="files(id, name)"
        ).execute()
        
        files = results.get('files', [])
        if files:
            return files[0]['id']
        
        # Create folder if it doesn't exist
        file_metadata = {
            'name': 'Amarjit Electrical Store',
            'mimeType': 'application/vnd.google-apps.folder'
        }
        
        folder = service.files().create(
            body=file_metadata,
            fields='id'
        ).execute()
        
        return folder.get('id')
    except Exception as e:
        print(f"Error creating folder: {e}")
        return None

# Routes
@app.route('/')
def index():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    # Get summary statistics
    total_customers = Customer.query.count()
    total_debt = db.session.query(db.func.sum(Customer.total_debt)).scalar() or 0
    recent_transactions = Transaction.query.order_by(Transaction.created_date.desc()).limit(5).all()
    
    return render_template('index.html', 
                         total_customers=total_customers,
                         total_debt=total_debt,
                         recent_transactions=recent_transactions)

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        user = User.query.filter_by(username=username).first()
        
        if user and check_password_hash(user.password_hash, password):
            session['user_id'] = user.id
            flash('Login successful!', 'success')
            return redirect(url_for('index'))
        else:
            flash('Invalid username or password!', 'error')
    
    return render_template('login.html')

@app.route('/logout')
def logout():
    session.clear()
    flash('You have been logged out.', 'info')
    return redirect(url_for('login'))

@app.route('/setup', methods=['GET', 'POST'])
def setup():
    # Check if admin user already exists
    if User.query.first():
        flash('Setup already completed!', 'info')
        return redirect(url_for('login'))
    
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        user = User(
            username=username,
            password_hash=generate_password_hash(password)
        )
        db.session.add(user)
        db.session.commit()
        
        flash('Setup completed! You can now login.', 'success')
        return redirect(url_for('login'))
    
    return render_template('setup.html')

@app.route('/google_auth')
def google_auth():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    # Check if credentials.json exists
    if not os.path.exists(CLIENT_SECRETS_FILE):
        flash('Google Drive credentials file (credentials.json) not found! Please set up Google Drive API first.', 'error')
        return redirect(url_for('index'))
    
    try:
        flow = Flow.from_client_secrets_file(
            CLIENT_SECRETS_FILE,
            scopes=SCOPES,
            redirect_uri=url_for('google_callback', _external=True)
        )
        
        authorization_url, state = flow.authorization_url(
            access_type='offline',
            include_granted_scopes='true'
        )
        
        session['state'] = state
        return redirect(authorization_url)
    except Exception as e:
        flash(f'Error setting up Google authentication: {e}. Please check your credentials.json file.', 'error')
        return redirect(url_for('index'))

@app.route('/google_callback')
def google_callback():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    if not os.path.exists(CLIENT_SECRETS_FILE):
        flash('Google Drive credentials file not found!', 'error')
        return redirect(url_for('index'))
    
    try:
        flow = Flow.from_client_secrets_file(
            CLIENT_SECRETS_FILE,
            scopes=SCOPES,
            state=session.get('state'),
            redirect_uri=url_for('google_callback', _external=True)
        )
        
        flow.fetch_token(authorization_response=request.url)
        
        credentials = flow.credentials
        session['credentials'] = {
            'token': credentials.token,
            'refresh_token': credentials.refresh_token,
            'token_uri': credentials.token_uri,
            'client_id': credentials.client_id,
            'client_secret': credentials.client_secret,
            'scopes': credentials.scopes
        }
        
        flash('Google Drive connected successfully!', 'success')
        return redirect(url_for('index'))
    except Exception as e:
        flash(f'Error connecting to Google Drive: {e}', 'error')
        return redirect(url_for('index'))

@app.route('/customers')
def customers():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    page = request.args.get('page', 1, type=int)
    search = request.args.get('search', '')
    
    query = Customer.query
    if search:
        query = query.filter(
            db.or_(
                Customer.name.contains(search),
                Customer.phone.contains(search)
            )
        )
    
    customers = query.order_by(Customer.total_debt.desc()).paginate(
        page=page, per_page=10, error_out=False
    )
    
    return render_template('customers.html', customers=customers, search=search)

@app.route('/add_customer', methods=['GET', 'POST'])
def add_customer():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    if request.method == 'POST':
        name = request.form['name']
        phone = request.form['phone']
        address = request.form['address']
        
        # Check if customer with this phone already exists
        existing_customer = Customer.query.filter_by(phone=phone).first()
        if existing_customer:
            flash('Customer with this phone number already exists!', 'error')
            return render_template('add_customer.html')
        
        customer = Customer(
            name=name,
            phone=phone,
            address=address
        )
        
        db.session.add(customer)
        db.session.commit()
        
        flash('Customer added successfully!', 'success')
        return redirect(url_for('customers'))
    
    return render_template('add_customer.html')

@app.route('/customer/<int:id>')
def customer_detail(id):
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    customer = Customer.query.get_or_404(id)
    transactions = Transaction.query.filter_by(customer_id=id).order_by(Transaction.transaction_date.desc()).all()
    payments = Payment.query.filter_by(customer_id=id).order_by(Payment.payment_date.desc()).all()
    
    return render_template('customer_detail.html', 
                         customer=customer, 
                         transactions=transactions, 
                         payments=payments)

@app.route('/add_transaction/<int:customer_id>', methods=['GET', 'POST'])
def add_transaction(customer_id):
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    customer = Customer.query.get_or_404(customer_id)
    
    if request.method == 'POST':
        amount = float(request.form['amount'])
        description = request.form['description']
        transaction_date = datetime.strptime(request.form['transaction_date'], '%Y-%m-%d').date()
        transaction_type = request.form['transaction_type']
        
        bill_image_id = None
        bill_image_url = None
        
        # Handle file upload
        if 'bill_image' in request.files:
            file = request.files['bill_image']
            if file and file.filename != '':
                filename = secure_filename(f"{customer.phone}_{datetime.now().strftime('%Y%m%d_%H%M%S')}_{file.filename}")
                file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
                file.save(file_path)
                
                # Upload to Google Drive
                if 'credentials' in session:
                    bill_image_id, bill_image_url = upload_to_google_drive(file_path, filename)
                    # Remove local file after upload
                    os.remove(file_path)
        
        transaction = Transaction(
            customer_id=customer_id,
            amount=amount,
            description=description,
            transaction_date=transaction_date,
            transaction_type=transaction_type,
            bill_image_id=bill_image_id,
            bill_image_url=bill_image_url
        )
        
        db.session.add(transaction)
        
        # Update customer debt
        if transaction_type == 'purchase':
            customer.total_debt += amount
        else:  # return
            customer.total_debt -= amount
        
        db.session.commit()
        
        flash('Transaction added successfully!', 'success')
        return redirect(url_for('customer_detail', id=customer_id))
    
    return render_template('add_transaction.html', customer=customer)

@app.route('/add_payment/<int:customer_id>', methods=['GET', 'POST'])
def add_payment(customer_id):
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    customer = Customer.query.get_or_404(customer_id)
    
    if request.method == 'POST':
        amount = float(request.form['amount'])
        payment_date = datetime.strptime(request.form['payment_date'], '%Y-%m-%d').date()
        notes = request.form['notes']
        
        payment = Payment(
            customer_id=customer_id,
            amount=amount,
            payment_date=payment_date,
            notes=notes
        )
        
        db.session.add(payment)
        
        # Update customer debt
        customer.total_debt -= amount
        if customer.total_debt < 0:
            customer.total_debt = 0
        
        db.session.commit()
        
        flash('Payment recorded successfully!', 'success')
        return redirect(url_for('customer_detail', id=customer_id))
    
    return render_template('add_payment.html', customer=customer)

@app.route('/api/customers')
def api_customers():
    if 'user_id' not in session:
        return jsonify({'error': 'Unauthorized'}), 401
    
    customers = Customer.query.all()
    return jsonify([{
        'id': c.id,
        'name': c.name,
        'phone': c.phone,
        'total_debt': c.total_debt
    } for c in customers])

@app.route('/api/customer/search/<phone>')
def api_customer_search(phone):
    if 'user_id' not in session:
        return jsonify({'error': 'Unauthorized'}), 401
    
    customer = Customer.query.filter_by(phone=phone).first()
    if customer:
        return jsonify({
            'found': True,
            'id': customer.id,
            'name': customer.name,
            'phone': customer.phone,
            'address': customer.address,
            'total_debt': customer.total_debt,
            'created_date': customer.created_date.strftime('%d/%m/%Y')
        })
    else:
        return jsonify({'found': False})

@app.route('/quick_billing', methods=['GET', 'POST'])
def quick_billing():
    if 'user_id' not in session:
        return redirect(url_for('login'))
    
    if request.method == 'POST':
        phone = request.form['phone']
        action = request.form['action']  # 'sale' or 'payment'
        amount = float(request.form['amount'])
        description = request.form.get('description', '')
        
        customer = Customer.query.filter_by(phone=phone).first()
        if not customer:
            flash('Customer not found! Please add customer first.', 'error')
            return render_template('quick_billing.html')
        
        if action == 'sale':
            # Record a sale/purchase
            transaction = Transaction(
                customer_id=customer.id,
                amount=amount,
                description=description,
                transaction_date=datetime.now().date(),
                transaction_type='purchase'
            )
            db.session.add(transaction)
            customer.total_debt += amount
            flash(f'Sale of ₹{amount:.2f} added for {customer.name}. New debt: ₹{customer.total_debt:.2f}', 'success')
        
        elif action == 'payment':
            # Record a payment
            payment = Payment(
                customer_id=customer.id,
                amount=amount,
                payment_date=datetime.now().date(),
                notes=description
            )
            db.session.add(payment)
            customer.total_debt -= amount
            if customer.total_debt < 0:
                customer.total_debt = 0
            flash(f'Payment of ₹{amount:.2f} recorded for {customer.name}. Remaining debt: ₹{customer.total_debt:.2f}', 'success')
        
        db.session.commit()
        return render_template('quick_billing.html', customer=customer)
    
    return render_template('quick_billing.html')

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True, host='0.0.0.0', port=5000)