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

- PHP 8.3+
- PostgreSQL 14+
- Apache Web Server
- Node.js & npm (Tailwind CSS için)

## 🔧 Kurulum

### 1. Projeyi Klonlayın
```bash
git clone https://github.com/teocanKS/doguasstokvesurectakip.git
cd doguasstokvesurectakip
```

### 2. Veritabanını Oluşturun
```bash
# PostgreSQL'e bağlanın
psql -U postgres

# Veritabanını oluşturun
CREATE DATABASE dogu_as_db;

# SQL dosyasını import edin
psql -U teocan -d dogu_as_db < dogu_as_db_full_fixed_v3.sql
```

### 3. Environment Ayarları
`.env` dosyası zaten oluşturulmuştur. Gerekirse düzenleyin.

### 4. Apache Yapılandırması
Document root'u `public` klasörüne ayarlayın.

### 5. Tailwind CSS (Opsiyonel)
```bash
npm install
npm run build:css
```

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

## 📝 Lisans

MIT License

## 🔄 Sürüm Geçmişi

### v1.0.0 (2025-11-27)
- İlk sürüm

---

**Doğu AŞ** - Stok ve Süreç Takip Sistemi