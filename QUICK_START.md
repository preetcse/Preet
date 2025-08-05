# ⚡ Quick Start Guide - Amarjit Electrical Store

## 🚀 Get Running in 2 Minutes!

### Step 1: Start the Application
```bash
python3 app.py
```

### Step 2: Open Your Browser
- Go to: **http://localhost:5000**
- Create your admin account (username/password)

### Step 3: Start Using!
✅ **The app is ready to use immediately!**

You can:
- Add customers
- Record sales and payments
- Track outstanding debts
- Search customers

**Google Drive setup is optional** - do it later when you have time.

---

## 🏪 Basic Usage

### Add Your First Customer
1. Click "Add New Customer"
2. Enter: Name, Phone, Address
3. Click "Add Customer"

### Record a Sale
1. Go to customer profile
2. Click "Record Sale"
3. Enter amount and description
4. Click "Add Transaction"

### Record a Payment
1. Go to customer profile  
2. Click "Record Payment"
3. Enter payment amount
4. Click "Record Payment"

---

## 📱 Access from Phone

1. **Find your computer's IP address:**
   ```bash
   # Linux/Mac
   hostname -I
   
   # Windows
   ipconfig
   ```

2. **On your phone, go to:**
   `http://YOUR-IP-ADDRESS:5000`
   
   Example: `http://192.168.1.100:5000`

---

## ☁️ Setup Google Drive Later

When you want cloud storage for bill images:

1. **Read the detailed guide:** `GOOGLE_DRIVE_SETUP.md`
2. **Takes 10-15 minutes** to set up
3. **15GB free storage** for your bills

---

## 🔍 What You Get

### ✅ Customer Management
- Store customer details (name, phone, address)
- Search by name or phone number
- Track when customers joined

### ✅ Financial Tracking
- Record credit sales (khata system)
- Track payments received
- See outstanding debts instantly
- Complete transaction history

### ✅ Professional Interface
- Works on phone, tablet, computer
- Indian Rupee (₹) formatting
- Clean, modern design
- Fast search and navigation

### ✅ Data Security
- Password protected access
- Local database storage
- Optional cloud backup (Google Drive)

---

## 💡 Pro Tips

1. **Use unique phone numbers** - This prevents duplicate customers
2. **Record transactions immediately** - Don't forget the details
3. **Regular backups** - Copy the `electrical_store.db` file
4. **Mobile friendly** - Perfect for shop floor use

---

## 🆘 Quick Help

**Can't access localhost:5000?**
- Make sure app is running (see terminal output)
- Try: `http://127.0.0.1:5000`

**Forgot admin password?**
- Delete `electrical_store.db` file
- Restart app and create new account
- (This will delete all data)

**Want to backup data?**
- Copy the `electrical_store.db` file to safe location
- This contains all customers, transactions, payments

---

**🎉 You're ready to go! Start adding customers and tracking your business.**