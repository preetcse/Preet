# 📁 Google Drive Integration Setup Guide

This guide will help you set up Google Drive integration for automatic bill photo backup in your Amarjit Electrical Store management system.

## 🎯 **What You'll Get**

- ✅ **15GB Free Storage** - Store thousands of bill photos
- ✅ **Automatic Backup** - Photos uploaded instantly to Google Drive
- ✅ **Secure Access** - View bills from any device
- ✅ **Organized Storage** - All bills in dedicated folder
- ✅ **Professional Service** - Never lose important documents

## 🚀 **Step-by-Step Setup**

### **Step 1: Create Google Cloud Project**

1. **Go to Google Cloud Console**
   - Visit: https://console.cloud.google.com/
   - Sign in with your Google account

2. **Create New Project**
   - Click "Select a project" at the top
   - Click "New Project"
   - Project name: `Amarjit Electrical Store`
   - Click "Create"

### **Step 2: Enable Google Drive API**

1. **Navigate to APIs & Services**
   - In the left menu, click "APIs & Services" → "Library"

2. **Enable Google Drive API**
   - Search for "Google Drive API"
   - Click on "Google Drive API"
   - Click "Enable"

### **Step 3: Create OAuth Credentials**

1. **Configure OAuth Consent Screen**
   - Go to "APIs & Services" → "OAuth consent screen"
   - Choose "External" (unless you have G Suite)
   - Fill in required fields:
     - App name: `Amarjit Electrical Store`
     - User support email: Your email
     - Developer contact: Your email
   - Click "Save and Continue"
   - Add yourself as a test user
   - Click "Save and Continue"

2. **Create OAuth 2.0 Client ID**
   - Go to "APIs & Services" → "Credentials"
   - Click "Create Credentials" → "OAuth 2.0 Client IDs"
   - Application type: "Web application"
   - Name: `Amarjit Store Web Client`
   - Authorized redirect URIs: `https://legendary-preet.ct.ws/google_callback.php`
   - Click "Create"

3. **Download Credentials**
   - Copy the "Client ID" and "Client Secret"
   - You'll need these for the next step

### **Step 4: Configure Your Website**

1. **Update config.php**
   - Open `php_version/config.php` in your website files
   - Find these lines and update them:

```php
define('GOOGLE_CLIENT_ID', 'your-client-id-here'); // Paste your Client ID
define('GOOGLE_CLIENT_SECRET', 'your-client-secret-here'); // Paste your Client Secret
```

2. **Save the file** and upload it to your website

### **Step 5: Connect Google Drive**

1. **Login to your store management system**
   - Go to: https://legendary-preet.ct.ws
   - Login with your admin account

2. **Go to Settings**
   - Click "Settings" in the sidebar
   - Scroll to "Google Drive Integration" section

3. **Connect Google Drive**
   - Click "Connect Drive" button
   - You'll be redirected to Google
   - Sign in and authorize access
   - You'll be redirected back to your website

4. **Verify Connection**
   - You should see "Connected" status in Settings
   - Google Drive is now ready for bill photos!

## 📸 **Using Bill Photo Upload**

Once Google Drive is connected:

1. **Go to Quick Billing**
2. **Search for a customer**
3. **Record a new sale**
4. **Upload bill photo**:
   - Click "Choose File" in the "Bill Photo" section
   - Select photo from your phone/computer
   - Photo will be automatically uploaded to Google Drive
5. **View photos later**:
   - In customer history, click on bill photo thumbnails
   - Photos open in full size

## 🔧 **Troubleshooting**

### **Common Issues:**

**❌ "Google Drive is not properly configured"**
- Check that Client ID and Client Secret are correctly set in `config.php`
- Ensure the redirect URI matches exactly: `https://legendary-preet.ct.ws/google_callback.php`

**❌ "Access denied" error**
- Add your email as a test user in Google Cloud Console
- Make sure you're signing in with the correct Google account

**❌ "Photos not uploading"**
- Check that Google Drive connection shows "Connected" in Settings
- Try disconnecting and reconnecting Google Drive
- Ensure file size is under 5MB

**❌ "Invalid redirect URI"**
- Verify the redirect URI in Google Cloud Console matches your domain exactly
- Make sure there are no typos in the URL

### **Getting Help:**

1. **Check Settings page** - Shows current connection status
2. **Try reconnecting** - Disconnect and connect again
3. **Check file size** - Keep photos under 5MB
4. **Verify configuration** - Double-check Client ID and Secret

## 🏪 **Benefits for Your Store**

### **For You:**
- 📱 **Mobile-friendly** - Upload photos directly from your phone
- 🔒 **Secure backup** - Never lose important bill photos
- 📁 **Organized storage** - All photos in one dedicated folder
- 💾 **Automatic sync** - No manual uploading required
- 🌐 **Access anywhere** - View bills from any device

### **For Your Customers:**
- 📄 **Digital receipts** - Easy access to their bill photos
- 🔍 **Quick reference** - Find specific purchases easily
- 💼 **Professional service** - Modern digital record keeping

## 📋 **Configuration Reference**

### **Required Settings in config.php:**

```php
// Google Drive settings
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_CLIENT_ID', 'your-google-client-id');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
define('GOOGLE_REDIRECT_URI', 'https://legendary-preet.ct.ws/google_callback.php');
define('GOOGLE_FOLDER_NAME', 'Amarjit Electrical Store Bills');
```

### **Google Cloud Console Settings:**

- **Project Name**: Amarjit Electrical Store
- **API**: Google Drive API (enabled)
- **OAuth Consent Screen**: External, with your email as test user
- **Redirect URI**: `https://legendary-preet.ct.ws/google_callback.php`

## 🎉 **You're All Set!**

Once configured, your store management system will automatically:

1. ✅ Upload all bill photos to Google Drive
2. ✅ Create a dedicated "Amarjit Electrical Store Bills" folder
3. ✅ Organize photos by customer and date
4. ✅ Provide instant access to all bill photos
5. ✅ Keep everything secure and backed up

**Your electrical store now has professional-grade digital bill management!** 🚀⚡🏪

---

**Need help? All settings and connection status are available in the Settings page of your store management system.**