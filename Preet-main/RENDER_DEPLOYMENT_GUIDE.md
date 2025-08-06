# 🚀 Render Deployment Guide - Amarjit Electrical Store

## Complete guide to deploy your electrical store management system on Render.com

---

## 📋 **Prerequisites**

✅ **Render Account** - Sign up at [render.com](https://render.com)  
✅ **GitHub Account** - Your code repository  
✅ **Google Cloud Account** - For Google Drive API  

---

## 🔧 **Step 1: Prepare Google Drive API**

### **1.1 Create Google Cloud Project**

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project: "Amarjit Electrical Store"
3. Enable Google Drive API:
   - Go to **APIs & Services** → **Library**
   - Search for "Google Drive API"
   - Click **Enable**

### **1.2 Create OAuth 2.0 Credentials**

1. Go to **APIs & Services** → **Credentials**
2. Click **+ Create Credentials** → **OAuth 2.0 Client ID**
3. Configure OAuth consent screen (if not done):
   - User Type: **External**
   - App name: **Amarjit Electrical Store**
   - User support email: Your email
   - Developer contact: Your email
4. Create OAuth Client:
   - Application type: **Web application**
   - Name: **Amarjit Store Render**
   - Authorized JavaScript origins:
     ```
     https://your-app-name.onrender.com
     ```
   - Authorized redirect URIs:
     ```
     https://your-app-name.onrender.com/google_callback
     ```

⚠️ **Note:** Replace `your-app-name` with your actual Render app name

### **1.3 Download Credentials**

1. Download the JSON file with your credentials
2. Note down:
   - **Client ID** (ends with `.apps.googleusercontent.com`)
   - **Client Secret** (starts with `GOCSPX-`)

---

## 🌐 **Step 2: Deploy to Render**

### **2.1 Connect GitHub Repository**

1. Push your code to GitHub
2. Go to [Render Dashboard](https://dashboard.render.com)
3. Click **New** → **Web Service**
4. Connect your GitHub repository

### **2.2 Configure Deployment**

**Basic Settings:**
- **Name:** `amarjit-electrical-store`
- **Environment:** `Python 3`
- **Region:** Choose closest to your location
- **Branch:** `main`
- **Root Directory:** Leave empty
- **Build Command:** `pip install -r requirements.txt`
- **Start Command:** `gunicorn render_app:app`

### **2.3 Set Environment Variables**

In Render dashboard, go to **Environment** and add:

```bash
SECRET_KEY=your-secret-key-here-generate-random-string
GOOGLE_CLIENT_ID=your-client-id-from-google-cloud
GOOGLE_CLIENT_SECRET=your-client-secret-from-google-cloud
GOOGLE_REDIRECT_URI=https://your-app-name.onrender.com/google_callback
```

**To generate SECRET_KEY:**
```python
import secrets
print(secrets.token_hex(32))
```

### **2.4 Add PostgreSQL Database**

1. In Render dashboard, go to **Databases**
2. Click **New** → **PostgreSQL**
3. Configure:
   - **Name:** `amarjit-store-db`
   - **User:** `amarjit_user`
   - **Region:** Same as your web service
   - **Plan:** Free
4. After creation, copy the **Internal Database URL**
5. In your web service environment variables, add:
   ```bash
   DATABASE_URL=your-internal-database-url-here
   ```

---

## 🔐 **Step 3: Update Google OAuth Settings**

### **3.1 Get Your Render URL**

1. After deployment, Render will give you a URL like:
   ```
   https://amarjit-electrical-store.onrender.com
   ```

### **3.2 Update Google Cloud Console**

1. Go back to Google Cloud Console
2. **APIs & Services** → **Credentials**
3. Edit your OAuth 2.0 client
4. Update **Authorized JavaScript origins:**
   ```
   https://amarjit-electrical-store.onrender.com
   ```
5. Update **Authorized redirect URIs:**
   ```
   https://amarjit-electrical-store.onrender.com/google_callback
   ```

### **3.3 Update Environment Variable**

In Render dashboard, update:
```bash
GOOGLE_REDIRECT_URI=https://amarjit-electrical-store.onrender.com/google_callback
```

---

## 🎯 **Step 4: First-Time Setup**

### **4.1 Access Your Application**

1. Visit: `https://your-app-name.onrender.com`
2. You'll see the initial setup page
3. Create your admin account:
   - **Username:** Choose your username
   - **Password:** Choose a strong password

### **4.2 Connect Google Drive**

1. After login, click **"Connect Google Drive"**
2. You'll be redirected to Google for authorization
3. Grant permissions to your app
4. You'll be redirected back with success message

### **4.3 Test the System**

1. **Add a customer:**
   - Name: Test Customer
   - Phone: 1234567890
   - Address: Test Address

2. **Create a transaction:**
   - Go to Quick Billing
   - Search for the customer by phone
   - Add a sale with bill photo
   - Verify photo uploads to Google Drive

---

## 📊 **Step 5: Monitoring & Maintenance**

### **5.1 Monitor Deployment**

- **Logs:** Check Render dashboard logs for errors
- **Metrics:** Monitor CPU and memory usage
- **Database:** Check PostgreSQL connection

### **5.2 Backup Strategy**

- **Database:** Render provides automatic backups
- **Photos:** Stored safely in Google Drive
- **Code:** Version controlled in GitHub

---

## 🔧 **Troubleshooting**

### **Common Issues & Solutions**

#### **1. OAuth Error: redirect_uri_mismatch**
```
Solution: Check that redirect URI in Google Cloud Console exactly matches:
https://your-app-name.onrender.com/google_callback
```

#### **2. Database Connection Error**
```
Solution: Verify DATABASE_URL environment variable is set correctly
```

#### **3. App Not Starting**
```
Solution: Check Render logs and verify all environment variables are set
```

#### **4. Google Drive Upload Fails**
```
Solution: Ensure Google Drive API is enabled and credentials are correct
```

---

## 🎉 **Success Checklist**

- ✅ App deployed and accessible
- ✅ Database connected and working
- ✅ Google Drive integration working
- ✅ Can add customers
- ✅ Can create transactions
- ✅ Can upload bill photos
- ✅ Can record payments
- ✅ Mobile responsive design working

---

## 📞 **Support**

If you encounter any issues:

1. **Check Render Logs:** Dashboard → Your Service → Logs
2. **Verify Environment Variables:** All required variables are set
3. **Test Google APIs:** Ensure credentials are correct
4. **Database Status:** Verify PostgreSQL is running

---

## 🔒 **Security Notes**

- **Never commit credentials** to GitHub
- **Use strong passwords** for admin account
- **Regularly update** dependencies
- **Monitor access logs** for suspicious activity
- **Keep Google OAuth consent screen** updated

---

## 💰 **Cost Information**

**Render Free Tier Includes:**
- **750 hours/month** web service (enough for 24/7)
- **100GB bandwidth**
- **PostgreSQL database** (256MB storage)
- **Automatic SSL certificates**
- **Custom domains** (optional upgrade)

**Estimated Monthly Cost:** **$0** (Free tier sufficient for small business)

---

**🎊 Congratulations! Your electrical store management system is now live on the cloud!**