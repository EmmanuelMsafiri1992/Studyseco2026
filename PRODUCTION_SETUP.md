# StudySeco Production Deployment Guide

## 🚀 Production Deployment Checklist

### 1. 📧 Email Service Setup

#### Option A: Gmail SMTP (Quick Start)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-app-password  # Generate in Google Account > Security > 2-Step Verification > App passwords
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@studyseco.com"
MAIL_FROM_NAME="StudySeco"
```

#### Option B: Mailgun (Recommended for Production)
1. Sign up at https://mailgun.com
2. Verify your domain
3. Add to `.env`:
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.yourdomain.com
MAILGUN_SECRET=your-mailgun-secret-key
MAIL_FROM_ADDRESS="noreply@studyseco.com"
MAIL_FROM_NAME="StudySeco"
```

### 2. 📱 SMS Service Setup

#### Option A: Africa's Talking (Best for African markets)
1. Sign up at https://africastalking.com
2. Get API credentials
3. Add to `.env`:
```env
AFRICASTALKING_USERNAME=sandbox  # or your live username
AFRICASTALKING_API_KEY=your-api-key
AFRICASTALKING_FROM=StudySeco
```

4. Install package:
```bash
composer require africastalking/africastalking
```

#### Option B: Twilio (Global)
1. Sign up at https://twilio.com
2. Add to `.env`:
```env
TWILIO_SID=your-account-sid
TWILIO_TOKEN=your-auth-token
TWILIO_FROM=+1234567890
```

### 3. 🗄️ Database Configuration

#### Production Database (MySQL/PostgreSQL)
```env
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=studyseco_production
DB_USERNAME=your-db-username
DB_PASSWORD=your-strong-password
```

#### Run Production Migrations
```bash
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
```

### 4. 📁 File Storage Configuration

#### Option A: AWS S3 (Recommended)
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=studyseco-files
AWS_URL=https://studyseco-files.s3.amazonaws.com
```

#### Option B: Local Storage (Basic)
```env
FILESYSTEM_DISK=public
```

### 5. 🔐 Security & Environment

#### Essential Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-32-character-key
APP_URL=https://your-domain.com

# Session Security
SESSION_DOMAIN=.your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Queue Configuration (for emails)
QUEUE_CONNECTION=database  # or redis for better performance

# Cache Configuration
CACHE_DRIVER=redis  # or database
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
```

### 6. 🚀 Server Requirements

#### Minimum Server Specs
- **PHP**: 8.1+
- **Memory**: 2GB RAM minimum
- **Storage**: 20GB+ SSD
- **Web Server**: Nginx or Apache
- **Database**: MySQL 8.0+ or PostgreSQL 13+

#### PHP Extensions Required
```bash
php -m | grep -E "(openssl|pdo|mbstring|tokenizer|xml|ctype|json|bcmath|curl|fileinfo|gd)"
```

### 7. 📋 Deployment Commands

#### Initial Deployment
```bash
# Clone repository
git clone https://github.com/your-repo/studyseco.git
cd studyseco

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Set up environment
cp .env.production .env
php artisan key:generate

# Database setup
php artisan migrate --force
php artisan db:seed --force

# Storage & permissions
php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan ziggy:generate
```

#### Update Deployment
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### 8. 🔄 Queue Workers (Important for Emails)

#### Set up Queue Worker (Supervisor)
```ini
# /etc/supervisor/conf.d/studyseco-worker.conf
[program:studyseco-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/studyseco/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/studyseco/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start studyseco-worker:*
```

### 9. 📊 Monitoring & Logs

#### Log Configuration
```env
LOG_CHANNEL=stack
LOG_STACK=single,slack
LOG_LEVEL=error
SLACK_BOT_USER_OAUTH_TOKEN=your-slack-token
SLACK_BOT_USER_DEFAULT_CHANNEL=alerts
```

#### Health Check Endpoint
Create a health check route for monitoring:
```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'database' => DB::connection()->getPdo() ? 'connected' : 'failed',
        'cache' => Cache::get('health_check') !== null ? 'working' : 'failed',
        'queue' => 'active'
    ]);
});
```

### 10. 🔒 SSL Certificate

#### Using Let's Encrypt (Free)
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### 11. 🎯 Testing Production Features

#### Test Email Functionality
```bash
php artisan make:command TestEmail
# Send test emails to verify SMTP configuration
```

#### Test SMS Functionality  
```bash
php artisan make:command TestSMS
# Send test SMS to verify SMS service
```

#### Test Payment Proof Upload
- Upload test files to verify storage configuration
- Check file access and security

### 12. 📱 SMS Integration Code Update

Update `VerificationController.php` to use real SMS:

```php
// In sendPhoneVerification method
public function sendPhoneVerification(Request $request, $enrollmentId)
{
    $enrollment = Enrollment::findOrFail($enrollmentId);
    
    if ($enrollment->phone_verified_at) {
        return response()->json(['message' => 'Phone already verified'], 422);
    }

    $code = $enrollment->generatePhoneVerificationCode();
    
    try {
        // Use Africa's Talking or Twilio
        if (config('services.africastalking.api_key')) {
            $this->sendSMSViaAfricasTalking($enrollment->phone, $code);
        } elseif (config('services.twilio.sid')) {
            $this->sendSMSViaTwilio($enrollment->phone, $code);
        } else {
            // Fallback to email for testing
            Notification::route('mail', $enrollment->email)
                ->notify(new VerificationCode($code, 'phone'));
        }

        return response()->json([
            'message' => 'Verification code sent successfully',
            'expires_at' => $enrollment->verification_expires_at
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send verification code'], 500);
    }
}

private function sendSMSViaAfricasTalking($phone, $code)
{
    $gateway = new \AfricasTalking\SDK\AfricasTalking(
        config('services.africastalking.username'),
        config('services.africastalking.api_key')
    );
    
    $sms = $gateway->sms();
    $message = "Your StudySeco verification code is: {$code}. Valid for 10 minutes.";
    
    $sms->send([
        'to' => $phone,
        'message' => $message,
        'from' => config('services.africastalking.from')
    ]);
}
```

## 🚀 Go Live Checklist

- [ ] Domain configured and DNS pointing to server
- [ ] SSL certificate installed
- [ ] Email service configured and tested
- [ ] SMS service configured and tested
- [ ] Database migrated and seeded
- [ ] File storage configured
- [ ] Queue workers running
- [ ] Monitoring and logs configured
- [ ] Backup strategy implemented
- [ ] Security headers configured
- [ ] Performance optimization applied

## 📞 Support Services Costs

### Email Services (Monthly)
- **Gmail**: Free (with limitations)
- **Mailgun**: $35/month for 50k emails
- **SendGrid**: $15/month for 50k emails

### SMS Services (Per SMS)
- **Africa's Talking**: $0.01-0.05 per SMS (Africa)
- **Twilio**: $0.0075 per SMS (Global)

### Hosting (Monthly)
- **DigitalOcean**: $25/month (2GB RAM, 50GB SSD)
- **AWS EC2**: $20-50/month depending on usage
- **Linode**: $20/month (2GB RAM, 50GB SSD)

Ready for production deployment! 🎉