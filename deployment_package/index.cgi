#!/usr/bin/python3

import sys
import os

# Add the current directory to Python path
sys.path.insert(0, os.path.dirname(__file__))

# Set environment variables for production
os.environ['FLASK_ENV'] = 'production'

try:
    from wsgiref.handlers import CGIHandler
    from cloud_app import app
    
    # Initialize database
    with app.app_context():
        from cloud_app import db
        db.create_all()
    
    CGIHandler().run(app)
except Exception as e:
    print("Content-Type: text/html\n")
    print(f"<h1>Error</h1><p>{str(e)}</p>")
    print(f"<p>Python path: {sys.path}</p>")
    print(f"<p>Current directory: {os.getcwd()}</p>")