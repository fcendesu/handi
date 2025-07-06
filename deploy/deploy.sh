#!/bin/bash

# Laravel Deployment Script
# Run this script on your VPS to deploy the application

APP_NAME="handi"
APP_PATH="/var/www/html/$APP_NAME"
REPO_URL="https://github.com/fcendesu/handi.git"  # Replace with your repo
DOMAIN="yourdomain.com"  # Replace with your domain

echo "Starting deployment of $APP_NAME..."

# Create application directory
sudo mkdir -p $APP_PATH
cd /var/www/html

# Clone or update repository
if [ -d "$APP_NAME/.git" ]; then
    echo "Updating existing repository..."
    cd $APP_NAME
    git pull origin main
else
    echo "Cloning repository..."
    sudo git clone $REPO_URL $APP_NAME
    cd $APP_NAME
fi

# Install PHP dependencies
sudo composer install --optimize-autoloader --no-dev

# Install Node.js dependencies and build assets
npm install
npm run build

# Set up environment file
if [ ! -f .env ]; then
    echo "Creating .env file..."
    sudo cp .env.example .env
    echo "Please edit .env file with your database and other credentials"
fi

# Generate application key
sudo php artisan key:generate

# Set proper permissions
sudo chown -R www-data:www-data $APP_PATH
sudo chmod -R 755 $APP_PATH
sudo chmod -R 775 $APP_PATH/storage
sudo chmod -R 775 $APP_PATH/bootstrap/cache

# Create symbolic link for storage
sudo php artisan storage:link

# Run database migrations (uncomment when ready)
# sudo php artisan migrate --force

# Clear and cache config
sudo php artisan config:clear
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache

# Set up Nginx configuration
if [ ! -f "/etc/nginx/sites-available/$DOMAIN" ]; then
    echo "Setting up Nginx configuration..."
    sudo cp deploy/nginx-config /etc/nginx/sites-available/$DOMAIN
    sudo sed -i "s/yourdomain.com/$DOMAIN/g" /etc/nginx/sites-available/$DOMAIN
    sudo ln -s /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
    sudo nginx -t && sudo systemctl reload nginx
fi

echo "Deployment completed!"
echo "Next steps:"
echo "1. Configure your .env file"
echo "2. Set up database and run migrations"
echo "3. Configure SSL with: sudo certbot --nginx -d $DOMAIN"
