# ⚡ Amarjit Electrical Store & Repair Centre - Customer Credit Management System

A comprehensive customer credit management system designed for **Amarjit Electrical Store and Repair Centre** in India. Track customer purchases, payments, outstanding debts, and bill photos with ease.

## 🌐 **Live Website: [Legendary-Preet.ct.ws](https://legendary-preet.ct.ws)**

## 📋 **Two Versions Available**

### 🐍 **Flask Version (Original)**
- **Best for**: VPS hosting, PythonAnywhere, Heroku
- **Database**: SQLite/PostgreSQL/MySQL
- **Features**: Full Python Flask application
- **Files**: `app.py`, `cloud_app.py`, `templates/`, `static/`

### 🐘 **PHP Version (InfinityFree Compatible)**
- **Best for**: InfinityFree, shared hosting, cPanel hosting
- **Database**: MySQL
- **Features**: Same functionality as Flask version
- **Files**: `php_version/` directory

## 🚀 **Quick Start**

### **For InfinityFree Hosting (FREE):**
1. **Use PHP Version**: Upload files from `php_version/` folder
2. **Follow guide**: Read `INFINITYFREE_DEPLOYMENT_GUIDE.md`
3. **Deploy**: Free hosting with MySQL database
4. **Domain**: Works with Legendary-Preet.ct.ws

### **For VPS/Cloud Hosting:**
1. **Use Flask Version**: Use `cloud_app.py`
2. **Follow guide**: Read `CLOUD_DEPLOYMENT_GUIDE.md`
3. **Deploy**: PythonAnywhere, Heroku, or VPS
4. **Database**: PostgreSQL/MySQL

## ✨ **Features**

### 📱 **Quick Billing System**
- **Lightning-fast customer search** by phone number
- **Instant transaction processing** (sales & payments)
- **Real-time debt calculations**
- **Mobile-optimized interface** for shop use

### 👥 **Customer Management**
- **Complete customer profiles** (name, phone, address)
- **Transaction history** tracking
- **Payment history** with notes
- **Outstanding debt** calculations

### 📸 **Bill Photo Management**
- **Google Drive integration** for bill photo storage
- **Automatic organization** by customer and date
- **15GB free storage** for thousands of photos
- **Easy access** to customer's bill history

### 📊 **Business Analytics**
- **Dashboard overview** with key statistics
- **Monthly sales** and payment tracking
- **Customer debt** summaries
- **Transaction reports**

### 🔐 **Security Features**
- **Password-protected** admin access
- **Secure session** management
- **SQL injection protection**
- **HTTPS encryption** support

## 📁 **Project Structure**

```
amarjit-electrical-store/
├── php_version/                    # 🐘 PHP Version (InfinityFree)
│   ├── config.php                  # Database configuration
│   ├── functions.php               # Business logic
│   ├── index.php                   # Dashboard
│   ├── login.php                   # Authentication
│   ├── setup.php                   # First-time setup
│   ├── quick_billing.php           # Quick billing system
│   ├── logout.php                  # Logout
│   └── includes/
│       └── sidebar.php             # Navigation
├── templates/                      # 🐍 Flask HTML templates
├── static/                         # 🐍 Flask CSS/JS files
├── app.py                          # 🐍 Flask development version
├── cloud_app.py                    # 🐍 Flask production version
├── requirements.txt                # 🐍 Python dependencies
├── cloud_requirements.txt          # 🐍 Cloud deployment dependencies
├── Procfile                        # 🐍 Heroku deployment
├── runtime.txt                     # 🐍 Python version for Heroku
├── .htaccess                       # 🐘 Shared hosting configuration
├── index.cgi                       # 🐘 CGI script for shared hosting
└── Documentation/
    ├── INFINITYFREE_DEPLOYMENT_GUIDE.md    # 🐘 PHP deployment guide
    ├── CLOUD_DEPLOYMENT_GUIDE.md           # 🐍 Flask deployment guide
    ├── QUICK_BILLING_GUIDE.md              # Quick billing usage
    ├── BILL_PHOTO_MANAGEMENT.md            # Bill photo features
    ├── GOOGLE_DRIVE_SETUP.md               # Google Drive integration
    ├── GOOGLE_OAUTH_FIX.md                 # OAuth troubleshooting
    └── QUICK_START.md                      # 2-minute quick start
```

## 🛠 **Installation & Deployment**

### **Option 1: InfinityFree (FREE PHP Hosting)**

1. **Download PHP files** from `php_version/` folder
2. **Read deployment guide**: `INFINITYFREE_DEPLOYMENT_GUIDE.md`
3. **Upload to InfinityFree** htdocs folder
4. **Create MySQL database** in control panel
5. **Configure** `config.php` with database details
6. **Visit** `/setup.php` to create admin account

### **Option 2: PythonAnywhere ($5/month)**

1. **Use Flask version**: `cloud_app.py`
2. **Read deployment guide**: `CLOUD_DEPLOYMENT_GUIDE.md`
3. **Upload files** to PythonAnywhere
4. **Install dependencies**: `pip install -r cloud_requirements.txt`
5. **Configure database** and environment variables
6. **Set up custom domain**

### **Option 3: Heroku (Free/Paid)**

1. **Use Flask version**: `cloud_app.py`
2. **Deploy with Git**:
   ```bash
   git clone https://github.com/preetcse/Preet
   cd Preet
   heroku create your-app-name
   heroku addons:create heroku-postgresql:hobby-dev
   git push heroku main
   ```

## 📱 **Mobile Usage**

Perfect for electrical shop management:

1. **Bookmark** the quick billing page on your phone
2. **Search customers** instantly by phone number
3. **Record sales** and payments on the spot
4. **View bill photos** for any customer
5. **Track outstanding debts** in real-time

## 🎯 **Key Benefits**

### **For Your Business:**
- ✅ **Digital transformation** from paper records
- ✅ **Never lose customer data** with cloud storage
- ✅ **Professional image** with organized system
- ✅ **Mobile accessibility** from anywhere
- ✅ **Real-time debt tracking**
- ✅ **Automated calculations**

### **For Your Customers:**
- ✅ **Quick service** with instant lookup
- ✅ **Transparent records** with bill photos
- ✅ **Professional experience**
- ✅ **Easy payment tracking**

## 🔧 **Technical Specifications**

### **PHP Version Requirements:**
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache with mod_rewrite
- **Storage**: 50MB+ for application files
- **Hosting**: Any shared hosting with PHP/MySQL

### **Flask Version Requirements:**
- **Python**: 3.7 or higher
- **Database**: SQLite/PostgreSQL/MySQL
- **Memory**: 512MB+ RAM
- **Storage**: 100MB+ for application
- **Hosting**: VPS, PythonAnywhere, Heroku

## 📚 **Documentation**

- 🚀 **[Quick Start Guide](QUICK_START.md)** - Get started in 2 minutes
- 🐘 **[InfinityFree Deployment](INFINITYFREE_DEPLOYMENT_GUIDE.md)** - FREE hosting setup
- 🐍 **[Cloud Deployment](CLOUD_DEPLOYMENT_GUIDE.md)** - Professional hosting
- ⚡ **[Quick Billing Guide](QUICK_BILLING_GUIDE.md)** - Mobile billing system
- 📸 **[Bill Photo Management](BILL_PHOTO_MANAGEMENT.md)** - Google Drive integration
- 🔧 **[Google Drive Setup](GOOGLE_DRIVE_SETUP.md)** - Cloud storage configuration

## 🆘 **Support & Troubleshooting**

- 📖 **Documentation**: All guides included in repository
- 🐛 **Issues**: Report bugs via GitHub Issues
- 💡 **Features**: Request features via GitHub Issues
- 🔧 **Setup Help**: Follow step-by-step guides

## 📄 **License**

This project is created for **Amarjit Electrical Store and Repair Centre** and is available for educational and commercial use.

## 🤝 **Contributing**

This project is specifically designed for Amarjit Electrical Store. For feature requests or bug reports, please open an issue.

## 🏪 **About Amarjit Electrical Store**

**Amarjit Electrical Store and Repair Centre** is an electrical accessories store in India serving customers with quality products and repair services. This system helps manage customer credit and maintain professional business records.

---

## 🌟 **Get Started Today!**

1. **Choose your hosting**: InfinityFree (free) or PythonAnywhere (paid)
2. **Follow the deployment guide** for your chosen platform
3. **Set up your admin account**
4. **Start managing customers** digitally
5. **Enjoy professional business management!**

**Transform your electrical store with modern customer management!** ⚡🏪📱