#!/bin/bash
# ================================================================
# SPORTIF Deployment Script
# Server: 103.253.213.175 (Rumahweb VPS L)
# Domain: sportif.cloud
# ================================================================

set -e

echo "==========================================="
echo "   SPORTIF — Automated Deployment Script"
echo "==========================================="
echo ""

# ----- FASE 1: UPDATE SISTEM -----
echo "[1/9] Updating system packages..."
export DEBIAN_FRONTEND=noninteractive
apt update && apt upgrade -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold"
echo "✅ System updated!"

# ----- FASE 2: INSTALL NGINX -----
echo "[2/9] Installing Nginx..."
apt install nginx -y
systemctl enable nginx
systemctl start nginx
echo "✅ Nginx installed!"

# ----- FASE 3: INSTALL PHP 8.2 -----
echo "[3/9] Installing PHP 8.2 + extensions..."
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
apt install php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
    php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
    php8.2-bcmath php8.2-intl php8.2-readline php8.2-imagick \
    php8.2-fileinfo php8.2-dom php8.2-tokenizer -y

# Konfigurasi PHP
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 25M/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini
systemctl restart php8.2-fpm
echo "✅ PHP 8.2 installed & configured!"

# ----- FASE 4: INSTALL MYSQL -----
echo "[4/9] Installing MySQL..."
apt install mysql-server -y
systemctl enable mysql
systemctl start mysql

# Buat database & user
DB_PASSWORD="Sportif_Dispora_2026!"
mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS sportif_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'sportif_user'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON sportif_db.* TO 'sportif_user'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
echo "✅ MySQL installed! DB: sportif_db, User: sportif_user"

# ----- FASE 5: INSTALL COMPOSER, NODE, GIT, SUPERVISOR -----
echo "[5/9] Installing Composer, Node.js, Git, Supervisor..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install nodejs -y

apt install git supervisor unzip -y
systemctl enable supervisor
echo "✅ Composer, Node.js 20, Git, Supervisor installed!"

# ----- FASE 6: CLONE PROJECT -----
echo "[6/9] Cloning SPORTIF from GitHub..."
cd /var/www
if [ -d "sportif" ]; then
    echo "  Directory /var/www/sportif already exists, pulling latest..."
    cd sportif
    git pull origin main
else
    git clone https://github.com/ajidamn/SPORTIF-new.git sportif
    cd /var/www/sportif
fi
echo "✅ Project cloned!"

# ----- FASE 7: INSTALL DEPENDENCIES & BUILD -----
echo "[7/9] Installing dependencies & building assets..."
cd /var/www/sportif
composer install --optimize-autoloader --no-dev --no-interaction
npm install
npm run build
echo "✅ Dependencies installed & assets built!"

# ----- FASE 8: CONFIGURE LARAVEL -----
echo "[8/9] Configuring Laravel..."
cd /var/www/sportif

# Buat .env dari template
cp .env.example .env

# Update .env values
sed -i 's|APP_NAME=Laravel|APP_NAME=SPORTIF|' .env
sed -i 's|APP_ENV=local|APP_ENV=production|' .env
sed -i 's|APP_DEBUG=true|APP_DEBUG=false|' .env
sed -i 's|APP_URL=http://localhost|APP_URL=https://sportif.cloud|' .env
sed -i 's|DB_CONNECTION=sqlite|DB_CONNECTION=mysql|' .env
sed -i 's|# DB_HOST=127.0.0.1|DB_HOST=127.0.0.1|' .env
sed -i 's|# DB_PORT=3306|DB_PORT=3306|' .env
sed -i 's|# DB_DATABASE=laravel|DB_DATABASE=sportif_db|' .env
sed -i "s|# DB_USERNAME=root|DB_USERNAME=sportif_user|" .env
sed -i "s|# DB_PASSWORD=|DB_PASSWORD=${DB_PASSWORD}|" .env
sed -i 's|SESSION_DRIVER=database|SESSION_DRIVER=file|' .env
sed -i 's|CACHE_STORE=database|CACHE_STORE=file|' .env

# Tambahkan SANCTUM config
echo "" >> .env
echo "SANCTUM_STATEFUL_DOMAINS=sportif.cloud,www.sportif.cloud" >> .env

# Set APP_KEY dari lokal (KRUSIAL untuk decrypt NIK)
sed -i 's|APP_KEY=|APP_KEY=base64:sXRxnGIUI9jUSdLaYrfwyQqYC0oQ0+Z5GHOYoj9Ie78=|' .env

# Import database
echo "Importing database..."
mysql -u sportif_user -p"${DB_PASSWORD}" sportif_db < /var/www/sportif/sportif_db.sql
echo "✅ Database imported!"

# Laravel setup
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true

# Permissions
chown -R www-data:www-data /var/www/sportif
chmod -R 755 /var/www/sportif
chmod -R 775 /var/www/sportif/storage
chmod -R 775 /var/www/sportif/bootstrap/cache

echo "✅ Laravel configured!"

# ----- FASE 9: CONFIGURE NGINX -----
echo "[9/9] Configuring Nginx..."

cat > /etc/nginx/sites-available/sportif.conf <<'NGINX_CONF'
server {
    listen 80;
    listen [::]:80;
    server_name sportif.cloud www.sportif.cloud 103.253.213.175;
    root /var/www/sportif/public;

    index index.php index.html;
    charset utf-8;

    client_max_body_size 20M;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    server_tokens off;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX_CONF

ln -sf /etc/nginx/sites-available/sportif.conf /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx
echo "✅ Nginx configured!"

# ----- SETUP SUPERVISOR (Queue Worker) -----
echo "Setting up Queue Worker..."
cat > /etc/supervisor/conf.d/sportif-worker.conf <<'SUPERVISOR_CONF'
[program:sportif-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sportif/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sportif/storage/logs/worker.log
stopwaitsecs=3600
SUPERVISOR_CONF

supervisorctl reread
supervisorctl update
supervisorctl start sportif-worker:* 2>/dev/null || true
echo "✅ Queue Worker configured!"

# ----- SETUP CRON JOB -----
echo "Setting up Cron Job..."
(crontab -l 2>/dev/null | grep -v "sportif"; echo "* * * * * cd /var/www/sportif && php artisan schedule:run >> /dev/null 2>&1") | crontab -
echo "✅ Cron Job configured!"

echo ""
echo "==========================================="
echo "  ✅ DEPLOYMENT SELESAI!"
echo "==========================================="
echo ""
echo "Tes sekarang di browser:"
echo "  http://103.253.213.175"
echo ""
echo "Langkah selanjutnya:"
echo "  1. Setting DNS di Rumahweb: A record @ dan www → 103.253.213.175"
echo "  2. Setelah DNS aktif, jalankan:"
echo "     apt install certbot python3-certbot-nginx -y"
echo "     certbot --nginx -d sportif.cloud -d www.sportif.cloud"
echo ""
echo "==========================================="
