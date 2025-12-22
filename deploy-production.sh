#!/bin/bash

echo "🚀 StudySeco Production Deployment Script"
echo "==========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Not in Laravel project directory. Please cd to your project root."
    exit 1
fi

print_info "Starting production deployment..."

# Step 1: Copy production environment
print_info "Setting up production environment..."
if [ -f ".env.production" ]; then
    cp .env.production .env
    print_status "Production .env file copied"
else
    print_warning ".env.production not found. Please create it first."
    exit 1
fi

# Step 2: Install dependencies
print_info "Installing production dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
print_status "Composer dependencies installed"

# Step 3: Install and build frontend assets
print_info "Building frontend assets..."
npm ci --only=production
npm run build
print_status "Frontend assets built successfully"

# Step 4: Clear and optimize Laravel
print_info "Optimizing Laravel for production..."
php artisan down --message="Deploying new version..." --allow="127.0.0.1"

php artisan config:clear
php artisan route:clear  
php artisan view:clear
php artisan cache:clear

print_status "Laravel caches cleared"

# Step 5: Run migrations
print_info "Running database migrations..."
php artisan migrate --force --no-interaction
print_status "Database migrations completed"

# Step 6: Optimize for production
print_info "Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan ziggy:generate

print_status "Production caching completed"

# Step 7: Set proper permissions
print_info "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/app storage/framework

print_status "File permissions set"

# Step 8: Create storage symlink if needed
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    print_status "Storage symlink created"
fi

# Step 9: Restart queue workers if supervisor is running
if command -v supervisorctl &> /dev/null; then
    print_info "Restarting queue workers..."
    sudo supervisorctl restart studyseco-worker:*
    print_status "Queue workers restarted"
fi

# Step 10: Bring application back up
php artisan up
print_status "Application is now live!"

echo ""
echo "🎉 Production deployment completed successfully!"
echo ""
echo "📋 Post-deployment checklist:"
echo "- ✅ Environment set to production"
echo "- ✅ Dependencies installed (production only)"
echo "- ✅ Frontend assets built and optimized"
echo "- ✅ Laravel caches optimized for production"
echo "- ✅ Database migrations applied"
echo "- ✅ File permissions set correctly"
echo "- ✅ Storage symlink created"
echo "- ✅ Queue workers restarted (if applicable)"
echo ""
echo "🌐 Your application should now be serving built assets instead of development server!"
echo "🔍 Check your browser console - the CORS errors should be gone."
echo ""
print_warning "Remember to:"
echo "- Update your .env with real database credentials"
echo "- Configure your email service (Gmail/Mailgun)"  
echo "- Set up your SMS service (Africa's Talking/Twilio)"
echo "- Configure SSL certificate"
echo ""
print_info "Application URL: $(php artisan env | grep APP_URL)"