#!/usr/bin/env python3
"""
Deployment Helper Script for Amarjit Electrical Store
Helps prepare files for cloud deployment
"""

import os
import shutil
import zipfile
import json

def create_deployment_package():
    """Create a deployment package with all necessary files"""
    
    print("🚀 Creating deployment package for Amarjit Electrical Store...")
    
    # Files to include in deployment
    files_to_deploy = [
        'cloud_app.py',
        'cloud_requirements.txt',
        'Procfile',
        'runtime.txt',
        '.htaccess',
        'index.cgi',
        'templates/',
        'static/'
    ]
    
    deployment_dir = 'deployment_package'
    
    # Create deployment directory
    if os.path.exists(deployment_dir):
        shutil.rmtree(deployment_dir)
    os.makedirs(deployment_dir)
    
    # Copy files
    for file_path in files_to_deploy:
        if os.path.exists(file_path):
            if os.path.isdir(file_path):
                shutil.copytree(file_path, os.path.join(deployment_dir, file_path))
                print(f"✅ Copied directory: {file_path}")
            else:
                shutil.copy2(file_path, deployment_dir)
                print(f"✅ Copied file: {file_path}")
        else:
            print(f"⚠️  File not found: {file_path}")
    
    # Create deployment info
    deployment_info = {
        "app_name": "Amarjit Electrical Store",
        "version": "1.0.0",
        "deployment_date": "2024",
        "files_included": [f for f in files_to_deploy if os.path.exists(f)],
        "environment_variables_needed": [
            "SECRET_KEY",
            "DATABASE_URL", 
            "GOOGLE_CREDENTIALS",
            "FLASK_ENV"
        ],
        "domain": "Legendary-Preet.ct.ws"
    }
    
    with open(os.path.join(deployment_dir, 'deployment_info.json'), 'w') as f:
        json.dump(deployment_info, f, indent=2)
    
    # Create ZIP file
    zip_path = 'amarjit_electrical_store_deployment.zip'
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(deployment_dir):
            for file in files:
                file_path = os.path.join(root, file)
                arc_name = os.path.relpath(file_path, deployment_dir)
                zipf.write(file_path, arc_name)
    
    print(f"\n🎉 Deployment package created: {zip_path}")
    print(f"📁 Deployment folder: {deployment_dir}")
    
    return deployment_dir, zip_path

def print_deployment_instructions():
    """Print deployment instructions"""
    
    instructions = """
🌐 DEPLOYMENT INSTRUCTIONS

📋 Files Ready for Upload:
✅ cloud_app.py - Main application
✅ cloud_requirements.txt - Dependencies  
✅ templates/ - HTML templates
✅ static/ - CSS/JS files
✅ Procfile - For Heroku
✅ runtime.txt - Python version
✅ .htaccess - For shared hosting
✅ index.cgi - CGI script

🔧 Environment Variables to Set:
SECRET_KEY=amarjit-electrical-store-secret-key-2024
DATABASE_URL=postgresql://user:pass@host:5432/database
GOOGLE_CREDENTIALS={"type":"service_account",...}
FLASK_ENV=production

🚀 Deployment Options:

1️⃣ PythonAnywhere ($5/month):
   - Upload files to /home/username/mysite/
   - Install: pip install -r cloud_requirements.txt
   - Set environment variables
   - Configure web app

2️⃣ Heroku (Free/Paid):
   - git init && git add . && git commit -m "Deploy"
   - heroku create your-app-name
   - heroku addons:create heroku-postgresql:hobby-dev
   - git push heroku main

3️⃣ Shared Hosting:
   - Upload all files to public_html/
   - Set file permissions: chmod 755 index.cgi
   - Configure environment variables
   - Update .htaccess if needed

🌍 Domain Setup:
CNAME: Legendary-Preet.ct.ws → your-hosting-provider.com

📱 After Deployment:
1. Visit: https://Legendary-Preet.ct.ws/setup
2. Create admin account
3. Start using your cloud electrical store!

💡 Need help? Check CLOUD_DEPLOYMENT_GUIDE.md
"""
    
    print(instructions)

if __name__ == "__main__":
    try:
        deployment_dir, zip_path = create_deployment_package()
        print_deployment_instructions()
        
        print(f"\n📦 Ready to deploy to Legendary-Preet.ct.ws!")
        print(f"📁 Upload contents of '{deployment_dir}' to your hosting provider")
        print(f"📦 Or use '{zip_path}' for easy transfer")
        
    except Exception as e:
        print(f"❌ Error creating deployment package: {e}")
        print("Make sure all required files exist in the current directory.")