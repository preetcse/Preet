# 🌟 Amarjit Electrical Store - Cloud Edition

Complete customer credit management system with **Google Drive storage** and **real-time cross-device sync**.

## ✨ Features

- 📱 **Cross-Device Sync** - Access your data from any device
- ☁️ **Google Drive Storage** - All data stored securely in the cloud
- 🔄 **Real-Time Sync** - Changes appear instantly across devices
- 📸 **Photo Upload** - Bill photos organized by customer
- 💰 **Credit Management** - Track customer debts and payments
- ⚡ **Quick Billing** - Fast customer search and transaction entry
- 📊 **Reports & Export** - Data analysis and CSV exports

## 🚀 Quick Start

### 1. Setup
```bash
cd Preet-main
pip install -r requirements.txt
cp .env.example .env
# Edit .env with your Google API credentials
```

### 2. Google Drive API Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create project and enable Google Drive API
3. Create OAuth 2.0 credentials
4. Add `http://localhost:5000/google_callback` to redirect URIs
5. Copy credentials to `.env` file

### 3. Run
```bash
python3 app.py
```

### 4. First Use
1. Visit `http://localhost:5000`
2. Connect Google Drive
3. Create admin account
4. Start adding customers!

## 📁 Data Storage

All data is stored in Google Drive:

```
📁 Amarjit Electrical Store/
  📄 admin_user.json          ← Login credentials
  📄 customers.json           ← Customer database
  📄 transactions.json        ← Sales transactions
  📄 payments.json           ← Payment records
  📁 Bills/
    📁 [Customer Name]/
      📄 bill_photos.jpg     ← Bill photos
```

## 🎯 Key Benefits

### ✅ No Local Database Issues
- No SQLite conflicts
- No "customer already exists" errors
- Fresh data check every time

### ✅ True Cross-Device Access
- Same Google account = same data everywhere
- Real-time synchronization
- Mobile-friendly interface

### ✅ Automatic Backup
- Data safely stored in Google Drive
- No risk of local data loss
- Professional scalability

## 📱 Mobile Usage

The app is fully responsive and works on:
- 📱 Smartphones
- 💻 Laptops/Desktops  
- 📱 Tablets
- 🌐 Any device with a browser

## 🔧 Environment Variables

Copy `.env.example` to `.env` and configure:

```env
SECRET_KEY=your-secret-key
DATABASE_URL=sqlite:///electrical_store.db
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:5000/google_callback
```

## 🌐 Deployment

### Render (Recommended)
```bash
# Use the included Procfile and runtime.txt
# Set environment variables in Render dashboard
```

### Heroku
```bash
heroku create your-app-name
heroku config:set GOOGLE_CLIENT_ID=...
git push heroku main
```

## 📊 Cloud vs Local Comparison

| Feature | Cloud Version | Local Version |
|---------|--------------|---------------|
| Data Storage | Google Drive | SQLite File |
| Cross-Device | ✅ Yes | ❌ No |
| Backup | ✅ Automatic | ⚠️ Manual |
| Sync Issues | ✅ None | ❌ Common |
| Mobile Access | ✅ Full | ⚠️ Limited |

## 🆘 Support

- Check `CLOUD_vs_LOCAL_GUIDE.md` for detailed setup
- All data is automatically synced to Google Drive
- No local database files needed
- Cross-device access guaranteed

## 📄 License

MIT License - Feel free to use and modify!

---

**Made with ❤️ for Amarjit Electrical Store**