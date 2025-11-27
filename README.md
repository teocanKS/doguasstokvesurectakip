# Doğu AŞ Stok ve Süreç Takip Sistemi

Modern, tam özellikli stok ve süreç yönetim sistemi. PHP 8.3, PostgreSQL 14 ve Tailwind CSS ile geliştirilmiştir.

## 🚀 Özellikler

### Kimlik Doğrulama
- ✅ Güvenli giriş/kayıt sistemi
- ✅ Rol tabanlı erişim kontrolü (Yönetici/Personel)
- ✅ Kullanıcı onay sistemi
- ✅ Email bildirimleri

### Personel Paneli
- ✅ İnteraktif dashboard (grafikler ve istatistikler)
- ✅ Canlı saat ve tarih gösterimi
- ✅ En çok/az satılan ürünler analizi
- ✅ Tedarikçi ve müşteri aktivite grafikleri
- ✅ Kar analizi grafiği
- ✅ AI tabanlı satış tahmini (Weighted Average)
- ✅ Stok durumu görüntüleme ve arama
- ✅ Kategori bazlı stok özeti
- ✅ Aktif işler sayfası
- ✅ Geçmiş işlemler sayfası

### Yönetici Paneli
- ✅ Tüm personel özellikleri
- ✅ Kullanıcı onay/red sistemi
- ✅ Log görüntüleyici (filtreleme ve CSV export)
- ✅ Gelişmiş filtreleme seçenekleri
- ✅ Sistem etkinlik takibi

### Teknik Özellikler
- ✅ PostgreSQL tetikleyicileri ile otomatik stok hesaplama
- ✅ PDO ile güvenli veritabanı işlemleri
- ✅ RESTful API yapısı
- ✅ Responsive tasarım (mobil uyumlu)
- ✅ Tailwind CSS ile modern UI
- ✅ Chart.js ile interaktif grafikler
- ✅ XSS koruması
- ✅ Session yönetimi

## 📋 Gereksinimler

### Production (Raspberry Pi 5 - Ubuntu Server)
- Ubuntu Server 22.04+ (64-bit)
- Nginx 1.18+
- PHP 8.3+ with PHP-FPM
- PostgreSQL 14+
- Node.js & npm (Tailwind CSS için - opsiyonel)

### Development (macOS - Opsiyonel)
- macOS 12+ (Monterey veya üzeri)
- Nginx (via Homebrew)
- PHP 8.3+ with PHP-FPM (via Homebrew)
- PostgreSQL 14+ (via Homebrew)

## 🔧 Kurulum

### Raspberry Pi 5 (Ubuntu Server) - Production Kurulum

#### 1. Sistem Güncellemeleri
```bash
sudo apt update
sudo apt upgrade -y
```

#### 2. Nginx Kurulumu
```bash
# Nginx'i kurun
sudo apt install nginx -y

# Nginx'i başlatın ve otomatik başlatmayı etkinleştirin
sudo systemctl start nginx
sudo systemctl enable nginx

# Durumu kontrol edin
sudo systemctl status nginx
```

#### 3. PHP 8.3 ve PHP-FPM Kurulumu
```bash
# PHP repository ekleyin
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# PHP 8.3 ve gerekli extension'ları kurun
sudo apt install php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring \
                 php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath -y

# PHP-FPM'i başlatın
sudo systemctl start php8.3-fpm
sudo systemctl enable php8.3-fpm

# Durumu kontrol edin
sudo systemctl status php8.3-fpm
```

#### 4. PostgreSQL 14 Kurulumu
```bash
# PostgreSQL'i kurun
sudo apt install postgresql-14 postgresql-contrib-14 -y

# PostgreSQL'i başlatın
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Durumu kontrol edin
sudo systemctl status postgresql
```

#### 5. Projeyi Klonlayın
```bash
# Web dizinine gidin
cd /var/www

# Projeyi klonlayın
sudo git clone https://github.com/teocanKS/doguasstokvesurectakip.git
cd doguasstokvesurectakip

# İzinleri ayarlayın
sudo chown -R www-data:www-data /var/www/doguasstokvesurectakip
sudo chmod -R 755 /var/www/doguasstokvesurectakip
```

#### 6. Veritabanını Oluşturun
```bash
# PostgreSQL kullanıcısı oluşturun
sudo -u postgres psql -c "CREATE USER teocan WITH PASSWORD 'TYDM19031905';"

# Veritabanını oluşturun
sudo -u postgres psql -c "CREATE DATABASE dogu_as_db OWNER teocan;"

# Veritabanını import edin
sudo -u postgres psql -U teocan -d dogu_as_db < /var/www/doguasstokvesurectakip/dogu_as_db_full_fixed_v3.sql

# Veritabanı bağlantısını test edin
psql -U teocan -d dogu_as_db -c "SELECT version();"
```

#### 7. Environment Dosyasını Oluşturun
```bash
# .env dosyası zaten repository'de var
# Gerekirse düzenleyin
sudo nano /var/www/doguasstokvesurectakip/.env
```

`.env` dosyası içeriği:
```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=dogu_as_db
DB_USER=teocan
DB_PASSWORD=TYDM19031905

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_EMAIL=
MAIL_FROM_NAME=Doğu AŞ Sistem

APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-raspberry-pi-ip
```

#### 8. Nginx Yapılandırması
```bash
# Nginx site yapılandırma dosyasını oluşturun
sudo nano /etc/nginx/sites-available/doguas
```

Aşağıdaki içeriği yapıştırın (proje kök dizinindeki `nginx.conf` dosyasından):
```nginx
server {
    listen 80;
    listen [::]:80;

    server_name localhost;  # Raspberry Pi IP'nizi buraya yazabilirsiniz

    root /var/www/doguasstokvesurectakip/public;
    index index.php index.html;

    access_log /var/log/nginx/doguas_access.log;
    error_log /var/log/nginx/doguas_error.log;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location ~ /\. {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ ^/(src|views|vendor|node_modules) {
        deny all;
        return 404;
    }
}
```

Site'ı etkinleştirin:
```bash
# Symlink oluşturun
sudo ln -s /etc/nginx/sites-available/doguas /etc/nginx/sites-enabled/

# Default site'ı devre dışı bırakın (opsiyonel)
sudo rm /etc/nginx/sites-enabled/default

# Nginx yapılandırmasını test edin
sudo nginx -t

# Nginx'i yeniden başlatın
sudo systemctl restart nginx
```

#### 9. PHP-FPM Yapılandırması
```bash
# PHP-FPM pool ayarlarını kontrol edin
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Şu satırların doğru olduğundan emin olun:
```ini
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
```

PHP-FPM'i yeniden başlatın:
```bash
sudo systemctl restart php8.3-fpm
```

#### 10. Tailwind CSS (Opsiyonel)
```bash
# Node.js ve npm kurun (eğer yoksa)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Proje dizininde
cd /var/www/doguasstokvesurectakip
sudo npm install
sudo npm run build:css
```

#### 11. Servisleri Yeniden Başlatın
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart postgresql
```

#### 12. Test Edin
Tarayıcınızda şu adresi açın:
```
http://raspberry-pi-ip-adresi
```

veya local network'te:
```
http://localhost
```

### macOS (Local Development) - Opsiyonel

#### 1. Homebrew ile Gerekli Paketleri Kurun
```bash
# Homebrew güncelleyin
brew update

# Nginx kurun
brew install nginx

# PHP 8.3 kurun
brew install php@8.3

# PostgreSQL kurun
brew install postgresql@14

# Servisleri başlatın
brew services start nginx
brew services start php@8.3
brew services start postgresql@14
```

#### 2. Nginx Yapılandırması (macOS)
```bash
# Nginx yapılandırma dosyasını düzenleyin
nano /opt/homebrew/etc/nginx/servers/doguas.conf
```

Aynı server block'u kullanın, sadece path'leri ayarlayın:
```nginx
server {
    listen 8080;
    server_name localhost;
    root /Users/yourusername/Sites/doguasstokvesurectakip/public;

    # ... (yukarıdaki ile aynı)

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;  # macOS PHP-FPM genelde TCP kullanır
        # ...
    }
}
```

Nginx'i yeniden başlatın:
```bash
brew services restart nginx
```

Tarayıcıda `http://localhost:8080` adresini açın.

## 🎯 Kullanım

### Varsayılan Kullanıcılar

**Yönetici:**
- Email: `yonetici@test.com`
- Şifre: `password`

**Personel:**
- Email: `personel@test.com`
- Şifre: `password`

## 📁 Proje Yapısı

```
doguasstokvesurectakip/
├── api/                    # API endpoints
├── public/                # Public files
├── src/                   # Source files
├── views/                 # View templates
├── .env                   # Environment variables
└── README.md             # Bu dosya
```

## 🗄️ Veritabanı

Veritabanı şeması `dogu_as_db_full_fixed_v3.sql` dosyasında bulunmaktadır.

## 🔒 Güvenlik

- ✅ Password hashing (bcrypt)
- ✅ PDO prepared statements
- ✅ XSS koruması
- ✅ Session güvenliği
- ✅ Rol tabanlı erişim kontrolü
- ✅ Nginx güvenlik başlıkları
- ✅ .env dosyası koruması
- ✅ Hassas dizinlere erişim engelleme

## 🔧 Bakım ve Yönetim

### Servisleri Yeniden Başlatma

```bash
# Nginx'i yeniden başlat
sudo systemctl restart nginx

# PHP-FPM'i yeniden başlat
sudo systemctl restart php8.3-fpm

# PostgreSQL'i yeniden başlat
sudo systemctl restart postgresql

# Tüm servisleri yeniden başlat
sudo systemctl restart nginx php8.3-fpm postgresql
```

### Servis Durumlarını Kontrol Etme

```bash
# Nginx durumu
sudo systemctl status nginx

# PHP-FPM durumu
sudo systemctl status php8.3-fpm

# PostgreSQL durumu
sudo systemctl status postgresql
```

### Log Dosyalarını Görüntüleme

```bash
# Nginx access log
sudo tail -f /var/log/nginx/doguas_access.log

# Nginx error log
sudo tail -f /var/log/nginx/doguas_error.log

# PHP-FPM error log
sudo tail -f /var/log/php8.3-fpm.log

# PostgreSQL log
sudo tail -f /var/log/postgresql/postgresql-14-main.log
```

### Nginx Yapılandırmasını Test Etme

```bash
# Yapılandırma dosyasını test et
sudo nginx -t

# Test başarılıysa yeniden yükle
sudo nginx -s reload
```

## 🐛 Sorun Giderme

### 502 Bad Gateway Hatası

**Sebep:** PHP-FPM çalışmıyor veya Nginx ile iletişim kuramıyor.

**Çözüm:**
```bash
# PHP-FPM durumunu kontrol edin
sudo systemctl status php8.3-fpm

# Çalışmıyorsa başlatın
sudo systemctl start php8.3-fpm

# Socket dosyasının var olduğunu kontrol edin
ls -la /run/php/php8.3-fpm.sock

# İzinleri kontrol edin
sudo chmod 666 /run/php/php8.3-fpm.sock
```

### 404 Not Found Hatası

**Sebep:** Nginx root dizini yanlış yapılandırılmış veya dosyalar yanlış konumda.

**Çözüm:**
```bash
# Root dizininin doğru olduğunu kontrol edin
grep "root" /etc/nginx/sites-available/doguas

# Dosya izinlerini kontrol edin
ls -la /var/www/doguasstokvesurectakip/public

# İzinleri düzeltin
sudo chown -R www-data:www-data /var/www/doguasstokvesurectakip
sudo chmod -R 755 /var/www/doguasstokvesurectakip
```

### PHP Dosyaları İndirilmeye Çalışılıyor

**Sebep:** PHP-FPM Nginx ile doğru yapılandırılmamış.

**Çözüm:**
```bash
# Nginx yapılandırmasında PHP location block'unu kontrol edin
sudo nano /etc/nginx/sites-available/doguas

# fastcgi_pass satırının doğru olduğundan emin olun:
# fastcgi_pass unix:/run/php/php8.3-fpm.sock;

# Nginx'i yeniden başlatın
sudo systemctl restart nginx
```

### Veritabanı Bağlantı Hatası

**Sebep:** PostgreSQL çalışmıyor veya kimlik bilgileri yanlış.

**Çözüm:**
```bash
# PostgreSQL durumunu kontrol edin
sudo systemctl status postgresql

# .env dosyasındaki bilgileri kontrol edin
cat /var/www/doguasstokvesurectakip/.env | grep DB_

# Bağlantıyı test edin
psql -U teocan -d dogu_as_db -c "SELECT 1;"
```

### Session/Permission Hataları

**Sebep:** PHP session dizini izinleri yanlış.

**Çözüm:**
```bash
# PHP session dizinini kontrol edin
ls -la /var/lib/php/sessions

# İzinleri düzeltin
sudo chown -R www-data:www-data /var/lib/php/sessions
sudo chmod -R 755 /var/lib/php/sessions
```

## 🚀 Performans Optimizasyonu

### Nginx Önbellekleme

`/etc/nginx/sites-available/doguas` dosyasına ekleyin:

```nginx
# Önbellek tanımlaması (server block dışında, http seviyesinde)
fastcgi_cache_path /var/cache/nginx/fastcgi levels=1:2 keys_zone=PHPCACHE:100m inactive=60m;

# Server block içinde
location ~ \.php$ {
    fastcgi_cache PHPCACHE;
    fastcgi_cache_valid 200 60m;
    add_header X-Cache-Status $upstream_cache_status;
    # ... diğer ayarlar
}
```

### PHP-FPM Optimizasyonu

`/etc/php/8.3/fpm/pool.d/www.conf` dosyasını düzenleyin:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

Raspberry Pi 5 için optimize edilmiş değerler kullanılmıştır.

## 📝 Lisans

MIT License

## 🔄 Sürüm Geçmişi

### v1.0.0 (2025-11-27)
- İlk sürüm

---

**Doğu AŞ** - Stok ve Süreç Takip Sistemi