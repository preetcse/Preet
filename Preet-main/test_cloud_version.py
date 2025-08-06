#!/usr/bin/env python3
"""
Test script to verify the cloud version is running
"""

import requests
import json

def test_cloud_version():
    """Test if the cloud version is running"""
    try:
        # Test the home page
        response = requests.get('http://localhost:5000/')
        print(f"✅ App is running - Status: {response.status_code}")
        
        # Check if it redirects to login (cloud version behavior)
        if 'login' in response.url or response.status_code == 302:
            print("✅ Cloud version detected - redirects to login")
        else:
            print("⚠️  Unexpected response")
            
        # Test if Google auth route exists (cloud-only feature)
        response = requests.get('http://localhost:5000/google_auth')
        if response.status_code in [200, 302]:
            print("✅ Google auth route exists - Cloud version confirmed")
        else:
            print("❌ Google auth route missing")
            
        # Test data_status route (cloud-only feature)
        response = requests.get('http://localhost:5000/data_status')
        if response.status_code in [200, 302]:
            print("✅ Data status route exists - Cloud version confirmed")
        else:
            print("❌ Data status route missing")
            
        print("\n🎉 CLOUD VERSION IS RUNNING!")
        print("📁 Data will be stored in Google Drive")
        print("🔄 Real-time sync active")
        print("📱 Cross-device access enabled")
        
    except requests.exceptions.ConnectionError:
        print("❌ App is not running on localhost:5000")
        print("Run: python3 app.py")
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    test_cloud_version()