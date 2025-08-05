# 🗄️ Database Setup Guide for InfinityFree

This guide will help you set up the MySQL database for your Amarjit Electrical Store management system on InfinityFree hosting.

## 🎯 **What You Need**

- ✅ Active InfinityFree hosting account
- ✅ Access to InfinityFree control panel
- ✅ Basic understanding of file editing

## 🚀 **Step-by-Step Setup**

### **Step 1: Login to InfinityFree Control Panel**

1. **Go to InfinityFree**
   - Visit: https://www.infinityfree.net/
   - Click "Login" or "Client Area"

2. **Access Your Account**
   - Enter your InfinityFree username and password
   - Click "Login"

3. **Go to Control Panel**
   - Find your website: `legendary-preet.ct.ws`
   - Click "Control Panel" or "Manage"

### **Step 2: Create MySQL Database**

1. **Navigate to MySQL Databases**
   - In the control panel, look for "MySQL Databases"
   - Click on "MySQL Databases"

2. **Create New Database**
   - Find "Create Database" section
   - Database name: Enter `electrical_store`
   - It will automatically become: `if0_XXXXXXXX_electrical_store`
   - Click "Create Database"

3. **Note Your Database Details**
   After creation, you'll see:
   - **Database Host**: Something like `sql200.infinityfree.com`
   - **Database Name**: `if0_XXXXXXXX_electrical_store`
   - **Username**: `if0_XXXXXXXX`
   - **Password**: The password you set

### **Step 3: Update Configuration File**

1. **Download config.php**
   - Download the `config.php` file from your website's `php_version` folder
   - You can use File Manager or FTP

2. **Edit Database Settings**
   Open `config.php` and find these lines:

   ```php
   define('DB_HOST', 'sql200.infinityfree.com'); 
   define('DB_USER', 'if0_37114663'); 
   define('DB_PASS', 'YourDatabasePassword'); 
   define('DB_NAME', 'if0_37114663_electrical_store'); 
   ```

3. **Replace with Your Actual Details**
   
   **Example:**
   ```php
   define('DB_HOST', 'sql210.infinityfree.com'); // Your actual host
   define('DB_USER', 'if0_37456789'); // Your actual username
   define('DB_PASS', 'MySecurePassword123'); // Your actual password
   define('DB_NAME', 'if0_37456789_electrical_store'); // Your actual database name
   ```

4. **Save and Upload**
   - Save the file
   - Upload it back to your website's `php_version` folder

### **Step 4: Test the Connection**

1. **Visit Your Website**
   - Go to: https://legendary-preet.ct.ws
   - If setup is correct, you should see the setup page

2. **Complete First-Time Setup**
   - Visit: https://legendary-preet.ct.ws/setup.php
   - Create your admin account
   - The system will automatically create all database tables

## 🔧 **Finding Your Database Information**

### **Method 1: InfinityFree Control Panel**

1. Login to InfinityFree control panel
2. Go to "MySQL Databases"
3. Look for your database details in the list
4. Note down:
   - Database host
   - Database name
   - Username
   - Password (the one you set)

### **Method 2: Common InfinityFree Patterns**

- **Host**: Usually `sqlXXX.infinityfree.com` (where XXX is a number)
- **Username**: Always starts with `if0_` followed by numbers
- **Database Name**: `if0_XXXXXXXX_yourdatabasename`

## 🛠️ **Troubleshooting**

### **❌ "Access denied for user" Error**

**Problem**: Wrong username, password, or host
**Solution**:
1. Double-check your InfinityFree control panel for correct credentials
2. Make sure you're using the exact username (with `if0_` prefix)
3. Verify the database host address
4. Ensure the password is correct

### **❌ "Unknown database" Error**

**Problem**: Database name is incorrect
**Solution**:
1. Check the exact database name in InfinityFree control panel
2. Make sure it includes the full name (e.g., `if0_37456789_electrical_store`)
3. Verify the database was created successfully

### **❌ "Connection timeout" Error**

**Problem**: Wrong host address
**Solution**:
1. Verify the database host in InfinityFree control panel
2. Common hosts: `sql200.infinityfree.com`, `sql210.infinityfree.com`, etc.
3. Make sure there are no typos in the host address

### **❌ "Table doesn't exist" Error**

**Problem**: Database is empty
**Solution**:
1. Visit `/setup.php` to create initial admin account
2. The system will automatically create all required tables
3. If setup page doesn't work, check database connection first

## 📋 **Quick Reference Template**

Copy this template and fill in your actual details:

```php
// Database configuration for InfinityFree
define('DB_HOST', 'sql[NUMBER].infinityfree.com'); // Replace [NUMBER] with your actual number
define('DB_USER', 'if0_[YOUR_NUMBER]'); // Replace [YOUR_NUMBER] with your actual user number
define('DB_PASS', '[YOUR_PASSWORD]'); // Replace with your actual password
define('DB_NAME', 'if0_[YOUR_NUMBER]_electrical_store'); // Replace [YOUR_NUMBER] with your actual user number
```

## 🎯 **Example Configuration**

Here's what a real configuration might look like:

```php
// Real example (don't use these exact values)
define('DB_HOST', 'sql204.infinityfree.com');
define('DB_USER', 'if0_37123456');
define('DB_PASS', 'MyStore2024!');
define('DB_NAME', 'if0_37123456_electrical_store');
```

## ✅ **Verification Checklist**

Before testing, make sure:

- [ ] ✅ Database created in InfinityFree control panel
- [ ] ✅ Database host address is correct
- [ ] ✅ Username includes `if0_` prefix
- [ ] ✅ Password is exactly as you set it
- [ ] ✅ Database name includes full `if0_XXXXXXXX_` prefix
- [ ] ✅ `config.php` file uploaded to your website
- [ ] ✅ No typos in any of the credentials

## 🎉 **Success!**

Once configured correctly:

1. ✅ Visit https://legendary-preet.ct.ws
2. ✅ You should see the setup page (not an error)
3. ✅ Complete the admin account creation at `/setup.php`
4. ✅ Start using your electrical store management system!

## 📞 **Need Help?**

1. **Double-check credentials** in InfinityFree control panel
2. **Try recreating the database** if you're unsure about the password
3. **Check for typos** in the configuration file
4. **Verify file upload** - make sure the updated `config.php` is on your website

---

**Once the database is connected, you'll have a professional electrical store management system running on your website!** 🏪⚡📊