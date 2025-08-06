@echo off
echo 🏪 Starting Amarjit Electrical Store Management System...

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Python is not installed. Please install Python 3.7 or higher.
    pause
    exit /b 1
)

REM Check if requirements file exists
if not exist "requirements.txt" (
    echo ❌ requirements.txt not found!
    pause
    exit /b 1
)

REM Install dependencies
echo 📦 Installing dependencies...
pip install -r requirements.txt

REM Check if credentials.json exists
if not exist "credentials.json" (
    echo ⚠️  Google Drive credentials not found!
    echo 📋 Please set up Google Drive API credentials:
    echo    1. Go to Google Cloud Console
    echo    2. Create a project and enable Google Drive API
    echo    3. Create OAuth 2.0 credentials
    echo    4. Download and rename to 'credentials.json'
    echo    5. See README.md for detailed instructions
    echo.
    echo 🚀 Starting application anyway ^(you can set up Google Drive later^)...
)

REM Start the application
echo 🌟 Launching the application...
echo 📱 Open your browser and go to: http://localhost:5000
echo 🛑 Press Ctrl+C to stop the application
echo.

python app.py
pause