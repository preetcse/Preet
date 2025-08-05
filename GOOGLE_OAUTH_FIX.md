# 🔧 Fix Google OAuth 403 Error - "App Not Verified"

## Problem
You're seeing this error:
```
Amarjit Electrical Store has not completed the Google verification process. 
Error 403: access_denied
```

## Quick Fix (5 minutes)

### Step 1: Add Yourself as Test User

1. **Go to Google Cloud Console**
   - Open: https://console.cloud.google.com/
   - Select your "Amarjit Electrical Store" project

2. **Go to OAuth Consent Screen**
   - Left sidebar → "APIs & Services" → "OAuth consent screen"

3. **Add Test Users**
   - Scroll down to "Test users" section
   - Click "ADD USERS"
   - Enter your email address (the one you're signed in with)
   - Click "SAVE"

4. **Test the Connection**
   - Go back to your app: http://localhost:5000
   - Click "Connect Google Drive"
   - Should work now!

### Step 2: Alternative - Make App Internal (If you have Google Workspace)

If you have Google Workspace (business account):
1. In OAuth consent screen
2. Change "User Type" from "External" to "Internal"
3. This removes the verification requirement

### Step 3: Long-term Solution (Optional)

For production use with external users:
1. **Publishing Status** → Change to "In production"
2. **But you'll need to go through Google's verification process**
3. **For personal use, just add yourself as test user** (Step 1)

## ✅ Expected Result

After adding yourself as test user:
- ✅ No more 403 error
- ✅ Google will show normal permission screen
- ✅ You can click "Allow" 
- ✅ Google Drive integration works perfectly

## 🎯 Why This Happens

- Google requires app verification for external users
- In "testing" mode, only approved test users can access
- Your own email needs to be added as test user
- This is Google's security measure

## 📱 Quick Test

After fixing:
1. Go to: http://localhost:5000
2. Click "Connect Google Drive"
3. Should see normal Google permission screen
4. Click "Allow"
5. Success! 🎉

---

**This is a 5-minute fix. Once you add your email as test user, everything will work perfectly!**