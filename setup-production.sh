#!/bin/bash

echo "🚀 StudySeco Production Setup Script"
echo "====================================="

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    echo "❌ Please don't run as root"
    exit 1
fi

# Update system
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
echo "📦 Installing required packages..."
sudo apt install -y nginx mysql-server redis-server supervisor certbot python3-certbot-nginx

# Install PHP 8.1+
echo "📦 Installing PHP..."
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-redis php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-mbstring php8.1-bcmath php8.1-intl

# Install Composer
echo "📦 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
echo "📦 Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Create application directory
echo "📁 Setting up application..."
sudo mkdir -p /var/www/studyseco
sudo chown $USER:www-data /var/www/studyseco

# Set up Nginx configuration
echo "🌐 Configuring Nginx..."
sudo tee /etc/nginx/sites-available/studyseco > /dev/null <<EOL
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/studyseco/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOL

# Enable site
sudo ln -s /etc/nginx/sites-available/studyseco /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t

# Set up supervisor for queue workers
echo "⚙️ Configuring Queue Workers..."
sudo tee /etc/supervisor/conf.d/studyseco-worker.conf > /dev/null <<EOL
[program:studyseco-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/studyseco/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/studyseco/storage/logs/worker.log
stopwaitsecs=3600
EOL

# Start services
echo "🚀 Starting services..."
sudo systemctl enable nginx php8.1-fpm mysql redis-server supervisor
sudo systemctl start nginx php8.1-fpm mysql redis-server supervisor

echo "✅ Production environment setup complete!"
echo ""
echo "📋 Next Steps:"
echo "1. Deploy your application to /var/www/studyseco"
echo "2. Configure your domain in nginx config"
echo "3. Set up SSL: sudo certbot --nginx -d yourdomain.com"
echo "4. Configure your .env file with production settings"
echo "5. Run: php artisan migrate --force && php artisan db:seed --force"
echo ""
echo "🔐 Don't forget to secure MySQL:"
echo "sudo mysql_secure_installation"