# 🚀 Amarjit Electrical Store - Complete Setup Guide

## 📋 Prerequisites
- Python 3.13.4 or later
- Google account for Google Drive integration
- Git installed

## 🔧 Local Development Setup

### 1. Clone and Navigate
```bash
cd Preet-main
```

### 2. Install Dependencies
```bash
pip install -r requirements.txt
```

### 3. Environment Variables
```bash
# Copy the environment template
cp .env.example .env

# Edit .env with your values
nano .env
```

### 4. Google Drive API Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google Drive API
4. Create OAuth 2.0 credentials
5. Add `http://localhost:5000/google_callback` to redirect URIs
6. Copy Client ID and Secret to `.env` file

### 5. Run the Application
```bash
python app.py
```

### 6. Initial Setup
1. Open `http://localhost:5000`
2. You'll be redirected to `/setup`
3. Create your admin account
4. Login with your credentials
5. Connect Google Drive from dashboard

## 🌐 Production Deployment Options

### Option 1: Render (Recommended)
1. Create account on [Render.com](https://render.com)
2. Connect your GitHub repository
3. Create new Web Service
4. Use these settings:
   - Build Command: `pip install -r requirements.txt`
   - Start Command: `gunicorn app:app --bind 0.0.0.0:$PORT`
   - Environment Variables: Copy from `.env`
   - Update `GOOGLE_REDIRECT_URI` to your domain

### Option 2: Heroku
1. Install Heroku CLI
2. Create new app: `heroku create your-app-name`
3. Set environment variables: `heroku config:set GOOGLE_CLIENT_ID=...`
4. Deploy: `git push heroku main`

## 🔄 Google Drive Data Sync Features

### ✅ What Gets Synced:
- ✅ Customer names, phones, addresses
- ✅ Outstanding debt amounts
- ✅ All transactions with dates
- ✅ All payments with notes
- ✅ Bill photos
- ✅ Complete database backup

### 🔧 Sync Options:
- **Auto-sync**: Triggers after every data change
- **Manual backup**: Force sync to Google Drive
- **Restore**: Pull latest data from Google Drive
- **Cross-device**: Same data on all devices

### 📁 Google Drive Structure:
```
📁 Amarjit Electrical Store/
  📁 Bills/
    📁 [Customer Name]/
      📄 bill_photos.jpg
  📁 Data Backup/
    📄 latest_backup.json
    📄 amarjit_store_backup_20240806_123456.json
```

## 📱 Mobile Usage
- Responsive design works on all devices
- Touch-friendly interface
- PWA features for app-like experience
- Cross-device data sync

## 🔑 Default Login
After setup, use the admin account you created:
- Username: [Your chosen username]
- Password: [Your chosen password]

## 🆘 Troubleshooting

### Database Issues
```bash
# Reset database
rm electrical_store.db
python app.py  # Will recreate tables
```

### Google Drive Connection
1. Check environment variables
2. Verify redirect URI in Google Console
3. Ensure Google Drive API is enabled
4. Add your email as test user if in development

### Deployment Issues
1. Check logs in hosting platform
2. Verify all environment variables are set
3. Ensure gunicorn is in requirements.txt
4. Check Python version matches runtime.txt

## 📞 Support
For issues, check the error logs or create an issue in the repository.