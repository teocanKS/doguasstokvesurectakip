# Deployment Guide - Nginx + PHP-FPM + PostgreSQL

This guide provides step-by-step instructions for deploying the Doğu AŞ Stock Management System on Raspberry Pi 5 with Ubuntu Server using Nginx, PHP-FPM, and PostgreSQL.

## 📋 Prerequisites

- Raspberry Pi 5 with Ubuntu Server 22.04+ (64-bit)
- SSH access to the Raspberry Pi
- Internet connection
- sudo privileges

## 🚀 Quick Start

### 1. System Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required tools
sudo apt install git curl wget software-properties-common -y
```

### 2. Install Stack Components

#### Install Nginx
```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

#### Install PHP 8.3 + PHP-FPM
```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 and extensions
sudo apt install -y \
    php8.3-fpm \
    php8.3-cli \
    php8.3-pgsql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-gd

# Enable and start PHP-FPM
sudo systemctl enable php8.3-fpm
sudo systemctl start php8.3-fpm
```

#### Install PostgreSQL 14
```bash
sudo apt install postgresql-14 postgresql-contrib-14 -y
sudo systemctl enable postgresql
sudo systemctl start postgresql
```

### 3. Deploy Application

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/teocanKS/doguasstokvesurectakip.git
cd doguasstokvesurectakip

# Set permissions
sudo chown -R www-data:www-data /var/www/doguasstokvesurectakip
sudo chmod -R 755 /var/www/doguasstokvesurectakip
```

### 4. Configure Database

```bash
# Create PostgreSQL user
sudo -u postgres psql -c "CREATE USER teocan WITH PASSWORD 'TYDM19031905';"

# Create database
sudo -u postgres psql -c "CREATE DATABASE dogu_as_db OWNER teocan;"

# Grant privileges
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE dogu_as_db TO teocan;"

# Import schema
sudo -u postgres psql -U teocan -d dogu_as_db < /var/www/doguasstokvesurectakip/dogu_as_db_full_fixed_v3.sql

# Verify
psql -U teocan -d dogu_as_db -c "\dt"
```

### 5. Configure Nginx

```bash
# Copy nginx configuration
sudo cp /var/www/doguasstokvesurectakip/nginx.conf /etc/nginx/sites-available/doguas

# OR create manually
sudo nano /etc/nginx/sites-available/doguas
```

Paste the following configuration:

```nginx
server {
    listen 80;
    listen [::]:80;

    server_name localhost;
    root /var/www/doguasstokvesurectakip/public;
    index index.php index.html;

    access_log /var/log/nginx/doguas_access.log;
    error_log /var/log/nginx/doguas_error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Deny hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_buffer_size 32k;
        fastcgi_buffers 16 16k;
    }

    # Static assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to sensitive directories
    location ~ ^/(src|views|vendor|node_modules) {
        deny all;
        return 404;
    }

    # Deny access to sensitive files
    location ~ ^/(composer\.json|composer\.lock|package\.json|\.git) {
        deny all;
        return 404;
    }
}
```

Enable the site:

```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/doguas /etc/nginx/sites-enabled/

# Remove default site (optional)
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

### 6. Configure Environment

```bash
# The .env file should already exist in the repository
# Verify and edit if needed
sudo nano /var/www/doguasstokvesurectakip/.env
```

Ensure these settings are correct:

```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=dogu_as_db
DB_USER=teocan
DB_PASSWORD=TYDM19031905

APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-raspberry-pi-ip
```

### 7. Configure PHP-FPM

```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Ensure these settings:

```ini
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Performance tuning for Raspberry Pi 5
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.3-fpm
```

### 8. Set Correct Permissions

```bash
# Application files
sudo chown -R www-data:www-data /var/www/doguasstokvesurectakip
sudo find /var/www/doguasstokvesurectakip -type d -exec chmod 755 {} \;
sudo find /var/www/doguasstokvesurectakip -type f -exec chmod 644 {} \;

# PHP session directory
sudo mkdir -p /var/lib/php/sessions
sudo chown -R www-data:www-data /var/lib/php/sessions
sudo chmod -R 755 /var/lib/php/sessions

# Nginx cache directory (if using caching)
sudo mkdir -p /var/cache/nginx/fastcgi
sudo chown -R www-data:www-data /var/cache/nginx
```

### 9. Final Restart

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart postgresql
```

### 10. Verify Installation

```bash
# Check all services are running
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status postgresql

# Check PHP-FPM socket
ls -la /run/php/php8.3-fpm.sock

# Test Nginx configuration
sudo nginx -t

# Check logs for errors
sudo tail -f /var/log/nginx/doguas_error.log
sudo tail -f /var/log/php8.3-fpm.log
```

Open in browser: `http://your-raspberry-pi-ip`

## 🔧 Configuration Tips

### Finding Your Raspberry Pi IP Address

```bash
# Method 1
hostname -I

# Method 2
ip addr show

# Method 3
ifconfig
```

### Setting Static IP (Optional but Recommended)

```bash
# Edit netplan configuration
sudo nano /etc/netplan/01-netcfg.yaml
```

Example configuration:

```yaml
network:
  version: 2
  ethernets:
    eth0:
      dhcp4: no
      addresses:
        - 192.168.1.100/24
      gateway4: 192.168.1.1
      nameservers:
        addresses:
          - 8.8.8.8
          - 8.8.4.4
```

Apply:

```bash
sudo netplan apply
```

### Firewall Configuration (UFW)

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH (important!)
sudo ufw allow 22/tcp

# Allow HTTP
sudo ufw allow 80/tcp

# Allow HTTPS (for future SSL)
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

## 🔒 Security Hardening

### 1. Secure PostgreSQL

```bash
# Edit PostgreSQL config
sudo nano /etc/postgresql/14/main/pg_hba.conf
```

Ensure local connections use md5:

```
local   all             all                                     md5
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
```

### 2. Secure PHP

```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Set these values:

```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php8.3-fpm.log
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60
memory_limit = 256M
```

### 3. Secure Nginx

Already configured in the nginx.conf file with:
- Security headers
- Denied access to sensitive files
- Denied access to hidden files
- Rate limiting (can be added)

### 4. Keep System Updated

```bash
# Create update script
sudo nano /usr/local/bin/update-system.sh
```

```bash
#!/bin/bash
apt update
apt upgrade -y
apt autoremove -y
systemctl restart nginx
systemctl restart php8.3-fpm
```

```bash
sudo chmod +x /usr/local/bin/update-system.sh

# Run weekly via cron
sudo crontab -e
# Add: 0 2 * * 0 /usr/local/bin/update-system.sh
```

## 📊 Monitoring

### System Resources

```bash
# CPU and Memory
htop

# Install if not present
sudo apt install htop -y

# Disk usage
df -h

# Check service logs
journalctl -u nginx -f
journalctl -u php8.3-fpm -f
journalctl -u postgresql -f
```

### Application Logs

```bash
# Nginx access
sudo tail -f /var/log/nginx/doguas_access.log

# Nginx errors
sudo tail -f /var/log/nginx/doguas_error.log

# PHP errors
sudo tail -f /var/log/php8.3-fpm.log

# PostgreSQL errors
sudo tail -f /var/log/postgresql/postgresql-14-main.log
```

## 🔄 Backup Strategy

### Database Backup Script

```bash
#!/bin/bash
BACKUP_DIR="/home/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="dogu_as_db_$DATE.sql"

mkdir -p $BACKUP_DIR
pg_dump -U teocan dogu_as_db > $BACKUP_DIR/$FILENAME
gzip $BACKUP_DIR/$FILENAME

# Keep only last 7 days
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
```

### Application Backup

```bash
#!/bin/bash
BACKUP_DIR="/home/backups/application"
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="doguas_app_$DATE.tar.gz"

mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/$FILENAME /var/www/doguasstokvesurectakip \
    --exclude='/var/www/doguasstokvesurectakip/.git' \
    --exclude='/var/www/doguasstokvesurectakip/node_modules'

# Keep only last 7 days
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

## 🆘 Support

For issues and questions, please visit:
- GitHub Issues: https://github.com/teocanKS/doguasstokvesurectakip/issues

---

**Last Updated:** November 27, 2025
