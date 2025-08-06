# Amarjit Electrical Store - Customer Management System

A comprehensive customer credit management system designed specifically for **Amarjit Electrical Store and Repair Centre** in India. This system helps track customer purchases, outstanding debts, payments, and stores bill images securely in Google Drive.

## 🌟 Features

### Customer Management
- Add and manage customer profiles with contact details
- Search customers by name or phone number
- Track customer registration dates and addresses

### Transaction Tracking
- Record customer purchases and returns
- Upload bill images directly to Google Drive
- Maintain detailed transaction history
- Support for both credit sales and cash returns

### Payment Management
- Record customer payments with dates and notes
- Automatic debt calculation and balance updates
- Payment history tracking
- Quick payment amount suggestions

### Google Drive Integration
- Secure cloud storage for bill images (FREE 15GB)
- Automatic folder organization
- Shareable links for easy access
- No local storage required

### Modern Web Interface
- Responsive design works on phones, tablets, and computers
- Beautiful and intuitive user interface
- Quick search functionality
- Real-time debt calculations

### Security
- Password-protected admin access
- Session-based authentication
- Secure file uploads

## 🚀 Quick Start

### Prerequisites
- Python 3.7 or higher
- Google account (for Google Drive integration)
- Internet connection

### Installation

1. **Clone or download this project**
   ```bash
   git clone <repository-url>
   cd electrical-store
   ```

2. **Install dependencies**
   ```bash
   pip install -r requirements.txt
   ```

3. **Set up Google Drive API** (See detailed instructions below)

4. **Run the application**
   ```bash
   python app.py
   ```

5. **Open in browser**
   - Go to: `http://localhost:5000`
   - Complete initial setup by creating admin account

## 🔧 Google Drive Setup

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Name it "Amarjit Electrical Store" or similar

### Step 2: Enable Google Drive API

1. In the Google Cloud Console, go to "APIs & Services" > "Library"
2. Search for "Google Drive API"
3. Click on it and press "Enable"

### Step 3: Create Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Choose "Web application"
4. Add authorized redirect URI: `http://localhost:5000/google_callback`
5. Download the JSON file

### Step 4: Configure Application

1. Rename the downloaded JSON file to `credentials.json`
2. Place it in your project root directory
3. The file should look like the `credentials.json.template` provided

### Important Notes:
- Google Drive provides **15GB free storage** - more than enough for bill images
- Images are stored in a dedicated "Amarjit Electrical Store" folder
- All uploaded files are accessible from your Google Drive
- No charges for Google Drive API usage within free limits

## 📱 How to Use

### First Time Setup
1. Run the application and go to `http://localhost:5000`
2. Create admin account (username/password)
3. Connect Google Drive from the dashboard
4. Start adding customers!

### Daily Operations

#### Adding a Customer
1. Click "Add New Customer" 
2. Enter name, phone number, and address
3. Phone numbers must be unique

#### Recording a Sale
1. Find customer or search by name/phone
2. Click "Record Sale" 
3. Enter amount, description, and upload bill image
4. System automatically updates customer debt

#### Recording a Payment
1. Go to customer profile
2. Click "Record Payment"
3. Enter payment amount and date
4. System automatically reduces outstanding debt

#### Viewing Reports
- Dashboard shows total customers and outstanding debts
- Individual customer pages show complete transaction history
- All bill images are linked and viewable

## 💾 Data Storage

### Local Database (SQLite)
- Customer information
- Transaction records
- Payment history
- Database file: `electrical_store.db`

### Google Drive
- Bill images and receipts
- Organized in dedicated folder
- Accessible from any device
- Free 15GB storage

## 🔒 Security Features

- Password-protected access
- Session-based authentication
- Secure file uploads
- Google OAuth2 integration
- No sensitive data in URLs

## 📊 Business Benefits

### For Store Management
- **No more lost bills** - All images stored in cloud
- **Accurate debt tracking** - Automatic calculations
- **Customer history** - Complete purchase records
- **Payment reminders** - Easy to see who owes money

### For Customers
- **Transparent records** - Complete transaction history
- **Flexible payments** - Pay in installments
- **Digital receipts** - Accessible bill images

### Cost Savings
- **Free cloud storage** (Google Drive)
- **No subscription fees** - One-time setup
- **Paperless operations** - Reduced physical storage

## 🛠 Troubleshooting

### Common Issues

**Google Drive not connecting:**
- Check `credentials.json` file exists
- Verify redirect URI in Google Cloud Console
- Ensure Google Drive API is enabled

**Application won't start:**
- Check Python version (3.7+)
- Install all dependencies: `pip install -r requirements.txt`
- Check port 5000 is available

**Images not uploading:**
- Verify Google Drive connection
- Check file size (max 16MB)
- Ensure internet connection

### Getting Help

1. Check error messages in terminal
2. Verify Google Drive API setup
3. Ensure all dependencies are installed
4. Restart the application

## 📱 Mobile Usage

The system is fully responsive and works great on mobile devices:
- Add customers on the go
- Take photos of bills directly
- Record payments instantly
- Search customers quickly

## 🔄 Backup and Recovery

### Automatic Backups
- Bill images: Stored in Google Drive (safe)
- Database: Located at `electrical_store.db`

### Manual Backup
1. Copy `electrical_store.db` file to safe location
2. Google Drive images are already backed up in cloud

### Recovery
1. Restore `electrical_store.db` file
2. Reconnect Google Drive
3. All data and images will be restored

## 📈 Future Enhancements

Possible additions for the future:
- SMS payment reminders
- Inventory management
- Sales reports and analytics
- Multiple user accounts
- Mobile app
- WhatsApp integration

## ❓ Support

For technical support or questions about the system:
1. Check this README first
2. Review error messages
3. Verify Google Drive setup
4. Contact system administrator

## 📄 License

This software is created specifically for Amarjit Electrical Store and Repair Centre. 

---

**Made with ❤️ for Amarjit Electrical Store and Repair Centre**

*Simplifying customer management, one transaction at a time.*