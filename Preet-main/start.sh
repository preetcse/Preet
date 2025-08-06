#!/bin/bash

echo "🏪 Starting Amarjit Electrical Store Management System..."

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 is not installed. Please install Python 3.7 or higher."
    exit 1
fi

# Check if requirements file exists
if [ ! -f "requirements.txt" ]; then
    echo "❌ requirements.txt not found!"
    exit 1
fi

# Install dependencies
echo "📦 Installing dependencies..."
pip3 install -r requirements.txt

# Check if credentials.json exists
if [ ! -f "credentials.json" ]; then
    echo "⚠️  Google Drive credentials not found!"
    echo "📋 Please set up Google Drive API credentials:"
    echo "   1. Go to Google Cloud Console"
    echo "   2. Create a project and enable Google Drive API"
    echo "   3. Create OAuth 2.0 credentials"
    echo "   4. Download and rename to 'credentials.json'"
    echo "   5. See README.md for detailed instructions"
    echo ""
    echo "🚀 Starting application anyway (you can set up Google Drive later)..."
fi

# Start the application
echo "🌟 Launching the application..."
echo "📱 Open your browser and go to: http://localhost:5000"
echo "🛑 Press Ctrl+C to stop the application"
echo ""

python3 app.py