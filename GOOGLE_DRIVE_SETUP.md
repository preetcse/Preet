# 🚀 Google Drive Setup Guide for Amarjit Electrical Store

## Step-by-Step Instructions

### Step 1: Create Google Cloud Project

1. **Go to Google Cloud Console**
   - Open: https://console.cloud.google.com/
   - Sign in with your Google account

2. **Create New Project**
   - Click "Select a project" at the top
   - Click "NEW PROJECT"
   - Project Name: `Amarjit Electrical Store`
   - Click "CREATE"

### Step 2: Enable Google Drive API

1. **Go to APIs & Services**
   - In left sidebar, click "APIs & Services" → "Library"

2. **Enable Google Drive API**
   - Search for "Google Drive API"
   - Click on "Google Drive API"
   - Click "ENABLE" button

### Step 3: Create OAuth Credentials

1. **Go to Credentials**
   - Click "APIs & Services" → "Credentials"

2. **Configure OAuth Consent Screen**
   - Click "OAuth consent screen" tab
   - Choose "External" (unless you have Google Workspace)
   - Fill in required fields:
     - App name: `Amarjit Electrical Store`
     - User support email: Your email
     - Developer contact: Your email
   - Click "SAVE AND CONTINUE"
   - Skip "Scopes" - click "SAVE AND CONTINUE"
   - Add your email as test user
   - Click "SAVE AND CONTINUE"

3. **Create OAuth 2.0 Client ID**
   - Go back to "Credentials" tab
   - Click "CREATE CREDENTIALS" → "OAuth 2.0 Client IDs"
   - Application type: "Web application"
   - Name: `Amarjit Store Web App`
   
4. **Add Redirect URI**
   - Under "Authorized redirect URIs", click "ADD URI"
   - Enter: `http://localhost:5000/google_callback`
   - Click "CREATE"

5. **Download Credentials**
   - Click the download icon (⬇) next to your new credential
   - Save the JSON file

### Step 4: Setup Credentials File

1. **Rename Downloaded File**
   - Rename the downloaded file to `credentials.json`
   - Copy it to your project folder (same folder as app.py)

2. **Verify File Format**
   - The file should look like this:
   ```json
   {
     "web": {
       "client_id": "your-client-id.apps.googleusercontent.com",
       "project_id": "your-project-id",
       "auth_uri": "https://accounts.google.com/o/oauth2/auth",
       "token_uri": "https://oauth2.googleapis.com/token",
       "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
       "client_secret": "your-client-secret",
       "redirect_uris": ["http://localhost:5000/google_callback"]
     }
   }
   ```

### Step 5: Test Connection

1. **Start the Application**
   ```bash
   python3 app.py
   ```

2. **Open Browser**
   - Go to: http://localhost:5000
   - Create admin account (first time)
   - Click "Connect Google Drive"

3. **Authorize Access**
   - Google will ask for permission
   - Click "Allow" to grant access
   - You'll be redirected back to the app

4. **Success!**
   - You should see "Google Drive connected successfully!"
   - The sidebar will show "Drive Connected"

## 🔧 Troubleshooting

### Error: "credentials.json not found"
- **Solution**: Make sure `credentials.json` is in the same folder as `app.py`

### Error: "redirect_uri_mismatch"
- **Solution**: In Google Cloud Console, ensure redirect URI is exactly:
  `http://localhost:5000/google_callback`

### Error: "access_denied"
- **Solution**: 
  1. Check OAuth consent screen is configured
  2. Add your email as test user
  3. Make sure you're signed in with the correct Google account

### Error: "invalid_client"
- **Solution**: Re-download credentials.json from Google Cloud Console

### Can't access localhost:5000
- **Solution**: 
  1. Make sure Python app is running
  2. Check no other service is using port 5000
  3. Try: `http://127.0.0.1:5000` instead

## 📱 Mobile Access Setup

If you want to access from your phone on the same network:

1. **Find your computer's IP address**
   ```bash
   # On Linux/Mac
   ip addr show | grep inet
   
   # On Windows
   ipconfig
   ```

2. **Update redirect URI in Google Cloud**
   - Add: `http://YOUR-IP:5000/google_callback`
   - Example: `http://192.168.1.100:5000/google_callback`

3. **Run app on all interfaces**
   ```bash
   # Modify app.py last line to:
   app.run(debug=True, host='0.0.0.0', port=5000)
   ```

## 💾 Storage Information

- **Free Storage**: 15GB on Google Drive
- **File Organization**: All bills stored in "Amarjit Electrical Store" folder
- **File Access**: All uploaded images are viewable from Google Drive
- **Backup**: Your Google Drive automatically backs up all files

## 🔒 Security Notes

- Your `credentials.json` contains sensitive information
- Never share this file publicly
- The app only requests permission to create and read files it uploads
- Google OAuth is highly secure and used by millions of apps

## ✅ Verification Steps

After setup, verify everything works:

1. ✅ Application starts without errors
2. ✅ Can access http://localhost:5000
3. ✅ Can create admin account
4. ✅ "Connect Google Drive" shows success message
5. ✅ Sidebar shows "Drive Connected"
6. ✅ Can add a customer
7. ✅ Can upload a bill image when recording transaction
8. ✅ Can view uploaded image from transaction history

## 🆘 Still Need Help?

If you're still having issues:

1. **Check the application output** - Look for error messages in the terminal
2. **Verify all steps** - Make sure each step above was completed
3. **Check file permissions** - Ensure `credentials.json` is readable
4. **Try fresh setup** - Delete `credentials.json` and start over

---

**Remember**: Google Drive integration is optional. The app works fine without it, you just won't be able to store bill images in the cloud.