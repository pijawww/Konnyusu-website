# Konnyusu — Platform E-Commerce Pemesanan Minuman

## Struktur Folder

```
Konnyusu/
├── index.php                    → Entry point (redirect ke home)
├── assets/
│   └── css/
│       └── global.css           → Variabel CSS & style global
├── data/
│   └── products.php             → Data produk & helper functions
├── layouts/
│   ├── navbar.php               → Komponen navbar (include di semua halaman)
│   └── footer.php               → Komponen footer
├── home/
│   └── home.php                 → Halaman beranda / landing page
├── auth/
│   ├── login.php                → Halaman login
│   └── register.php             → Halaman registrasi
├── cart/
│   ├── cart.php                 → Halaman keranjang belanja
│   ├── add-to-cart.php          → Handler tambah ke keranjang
│   ├── update-cart.php          → Handler update kuantitas
│   └── remove-from-cart.php     → Handler hapus item
├── checkout/
│   └── checkout.php             → Halaman checkout multi-step
├── history/
│   └── history.php              → Riwayat pesanan pelanggan
├── profile/
│   └── profile.php              → Profil & pengaturan akun
├── detail-product/
│   └── detail-product.php       → Detail halaman produk
└── admin/
    ├── dashboard/
    │   └── dashboard.php        → Dashboard admin
    ├── orders/
    │   └── orders.php           → Manajemen pesanan
    ├── products/
    │   └── products.php         → Manajemen produk
    └── customers/
        └── customers.php        → Manajemen pelanggan
```

## Cara Instalasi

1. Copy folder `Konnyusu/` ke dalam direktori web server (misalnya `htdocs/` di XAMPP)
2. Akses melalui browser: `http://localhost/Konnyusu/`
3. Untuk masuk sebagai admin: `http://localhost/Konnyusu/auth/login.php`
   - Email: `admin@konnyusu.com`
   - Password: `admin123`

## Assets Gambar

Letakkan gambar produk di folder `assets/img/products/` dengan nama file:
- `caramel-tart.jpeg`
- `oat-latte.jpg`
- `signature.jpg`
- `savory.jpg`

Jika gambar tidak tersedia, website akan menampilkan placeholder otomatis.

## Teknologi

- **PHP** — Server-side scripting
- **Bootstrap 5.3** — CSS Framework
- **Bootstrap Icons** — Icon library
- **Vanilla JavaScript** — Interaksi UI
- **Google Fonts** — Playfair Display + DM Sans
- **CSS Custom Properties** — Design tokens & theming

## Akun Demo

| Role     | Email                   | Password  |
|----------|-------------------------|-----------|
| Admin    | admin@konnyusu.com      | admin123  |
| User     | user@email.com          | password  |

---
© 2026 Konnyusu. Platform E-Commerce Pemesanan Minuman Premium.
