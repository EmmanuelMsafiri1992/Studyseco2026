# 🚨 EMERGENCY: Server Fix Commands

## Problem: Laravel Pail Service Provider Error
Every artisan command fails because Laravel Pail was removed but still registered.

## ⚡ IMMEDIATE FIX (Run these commands on your server)

### Step 1: Manual Cache Clear (bypass artisan)
```bash
# Clear caches manually - don't use artisan commands
rm -rf bootstrap/cache/config.php
rm -rf bootstrap/cache/routes-*.php  
rm -rf bootstrap/cache/services.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
```

### Step 2: Check if config directory exists
```bash
ls -la config/
```

### Step 3: Look for Pail configuration
```bash
# Check if there's a pail config file
ls -la config/ | grep -i pail
```

### Step 4: Most Critical - Fix .env (this is the real issue!)
```bash
# Edit your .env file and change:
nano .env

# Make sure it has:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://studyseco.com
```

### Step 5: Try to run a simple PHP check
```bash
# Test if the app can load at all
php -r "require 'vendor/autoload.php'; echo 'Autoload works';"
```

### Step 6: If that works, try package discovery
```bash
php artisan package:discover --ansi
```

## 🔧 Alternative: Bypass the Issue Completely

If artisan commands still fail, you can:

1. **Check your website directly**: Visit https://studyseco.com
2. **If it still shows 127.0.0.1:5173 errors**, the problem is your `.env` file
3. **Edit `.env` manually** and set `APP_ENV=production`

## 🎯 The Real Fix

The Laravel Pail error is a **distraction**. Your main issue is:
- Your `.env` file has `APP_ENV=local`  
- This makes your app try to load development assets
- Change it to `APP_ENV=production` and the CORS errors will disappear

## 📋 Quick Test
```bash
# Check current environment setting
grep APP_ENV .env

# Should return: APP_ENV=production
# If it shows APP_ENV=local, that's your problem!
```

## 🔥 Nuclear Option (if all else fails)
```bash
# Copy the entire config from a working app
cp .env.example .env
# Then edit .env with your database settings and set APP_ENV=production
```

---
**Priority: CRITICAL** - Fix the .env file first, worry about artisan commands later!