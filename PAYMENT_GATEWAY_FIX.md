<!-- @format -->

# Payment Gateway "Unreachable" Error - Fix Guide

## Problem

The alert **"Payment gateway unreachable. Try again."** appears when trying to process payments through Razorpay.

## Root Cause

This error occurs when cURL cannot establish a secure connection to Razorpay's API server. Common causes in XAMPP environments:

1. **Missing CA Certificate Bundle** - cURL cannot verify SSL certificates
2. **Improper SSL Configuration** - curl.cainfo not configured in php.ini
3. **Network connectivity issue** - Firewall or proxy blocking Razorpay API

## Fixes Applied

### 1. Enhanced SSL Certificate Handling

**File:** [pages/payment.php](pages/payment.php)

- Added intelligent CA bundle detection in multiple locations
- Automatic fallback to development mode (SSL verification disabled) in non-production environments
- Improved error logging with detailed cURL error messages

### 2. Automatic CA Bundle Search

The code now looks for certificates in:

- `{project_root}/cacert.pem` (prioritized)
- `C:/xampp/php/extras/ssl/cacert.pem`
- `C:/Program Files/xampp/php/extras/ssl/cacert.pem`
- System default locations

### 3. Better Error Messages

- Shows "Payment gateway unreachable. Please check your internet connection and try again."
- Logs detailed cURL error codes and messages for debugging

### 4. SSL Verification Helper Tool

**File:** [admin/verify-ssl.php](admin/verify-ssl.php)

Access via: `http://localhost/dhoti-mahal/admin/verify-ssl.php` (requires admin login)

Features:

- Checks PHP and cURL configuration
- Detects available CA bundles
- Tests Razorpay API connectivity
- One-click download of Mozilla's trusted CA certificates

## Quick Fix Steps

### Option 1: Automatic Fix (Recommended)

1. Log in to admin panel
2. Go to `admin/verify-ssl.php`
3. Click "Download CA Certificate Bundle"
4. Verify payment works

### Option 2: Manual Fix

1. Download from: https://curl.haxx.se/ca/cacert.pem
2. Save as `cacert.pem` in project root (`c:/xampp/htdocs/dhoti-mahal/`)
3. Clear browser cache
4. Try payment again

### Option 3: System Configuration

Edit `C:\xampp\php\php.ini`:

```ini
curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"
```

Restart Apache after editing.

## Testing After Fix

1. Add a test product to cart
2. Proceed to checkout
3. Click "Pay Now"
4. Payment should proceed without "unreachable" error

## Error Logs

Check `storage/logs.txt` for detailed error messages when debugging issues.

## Production Environment

In production, NEVER disable SSL verification (`CURLOPT_SSL_VERIFYPEER => false`). The code automatically enforces full SSL verification in production mode.
