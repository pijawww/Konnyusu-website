<?php
// home/home.php
session_start();
require_once __DIR__ . '/../config/cart.php';
require_once __DIR__ . '/../config/auth.php';
include __DIR__ . '/../data/products.php';

$currentUser = getCurrentUser();

// Cart count
$cartTotalItems = getCartCount();

// Search & filter
$searchQ   = trim($_GET['q'] ?? '');
$activecat = strtolower($_GET['cat'] ?? 'all');

// Apply filters
$filtered = array_filter($products, function($p) use ($searchQ, $activecat) {
    $matchCat    = ($activecat === 'all') || ($p['category'] === $activecat);
    $matchSearch = empty($searchQ) || stripos($p['name'], $searchQ) !== false
                                   || stripos($p['description'], $searchQ) !== false;
    return $matchCat && $matchSearch;
});

// Best sellers
$bestSellers = array_filter($products, fn($p) => $p['is_best']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konnyusu — Kopi & Minuman Premium</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Global CSS -->
  <link rel="stylesheet" href="../assets/css/global.css">

  <style>
  /* ======= HOME PAGE ======= */

  /* ---- Hero ---- */
  .hero {
    background: linear-gradient(135deg, var(--primary) 0%, #2e6b4f 60%, #1a5c42 100%);
    min-height: 520px;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
  }
  .hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  }
  .hero__content { position: relative; z-index: 1; }
  .hero__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(212,168,83,.2);
    border: 1px solid rgba(212,168,83,.4);
    color: var(--accent-light);
    font-size: .78rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: .3rem .9rem;
    border-radius: 40px;
    margin-bottom: 1.25rem;
  }
  .hero__title {
    font-family: var(--font-display);
    font-size: clamp(2.2rem, 5vw, 3.6rem);
    font-weight: 800;
    color: var(--white);
    line-height: 1.1;
    margin-bottom: 1.1rem;
  }
  .hero__title em {
    font-style: normal;
    color: var(--accent-light);
  }
  .hero__subtitle {
    font-size: 1.05rem;
    color: rgba(255,255,255,.75);
    max-width: 480px;
    margin-bottom: 2rem;
    line-height: 1.65;
  }
  .hero__cta-group { display: flex; gap: 1rem; flex-wrap: wrap; }
  .hero__search {
    position: relative;
    max-width: 380px;
    margin-top: 2rem;
  }
  .hero__search input {
    width: 100%;
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(10px);
    border: 1.5px solid rgba(255,255,255,.25);
    border-radius: 40px;
    padding: .75rem 1rem .75rem 3rem;
    color: var(--white);
    font-family: var(--font-body);
    font-size: .9rem;
    outline: none;
    transition: border-color .2s, background .2s;
  }
  .hero__search input::placeholder { color: rgba(255,255,255,.5); }
  .hero__search input:focus {
    border-color: var(--accent);
    background: rgba(255,255,255,.18);
  }
  .hero__search-icon {
    position: absolute;
    left: 16px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,.6);
    font-size: 1rem;
  }
  .hero__image-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
  }
  
  .hero__ring {
    width: 420px;
    height: 420px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: visible;

    background: rgba(255,255,255,.08);
    backdrop-filter: blur(10px);

    border: 6px solid rgba(255,255,255,.15);

    box-shadow:
      0 20px 60px rgba(0,0,0,.18),
      inset 0 0 40px rgba(255,255,255,.08);

    position: relative;
  }

  .hero__ring-img {
    width: 100%;
    height: 100%;
    object-fit: cover;

    border-radius: 50%;

    animation: floatHero 4s ease-in-out infinite;
  }

  @keyframes floatHero {
    0% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-10px);
    }
    100% {
      transform: translateY(0px);
    }
  }
  .hero__ring::before {
    content: '';
    position: absolute;
    inset: 20px;
    border-radius: 50%;
    background: rgba(212,168,83,.1);
    border: 1px solid rgba(212,168,83,.2);
  }
  .hero__cup-emoji {
    font-size: 9rem;
    position: relative; z-index: 1;
    animation: float 4s ease-in-out infinite;
  }
  .hero__stats {
    position: absolute;
    background: var(--white);
    border-radius: var(--radius-md);
    padding: .65rem 1rem;
    box-shadow: var(--shadow-md);
    font-size: .8rem;
  }
  .hero__stats strong { display: block; font-size: 1.1rem; color: var(--primary); font-weight: 700; }
  .hero__stats span   { color: var(--text-muted); font-size: .75rem; }
  .hero__stats--left  { left: -10px; top: 80px; }
  .hero__stats--right {top: 60px; left: -10px; right: auto; bottom: auto; }

  @keyframes float {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-16px); }
  }

  /* ---- Category Tabs ---- */
  .cat-tabs {
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
    padding: .5rem 0;
  }
  .cat-tab {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 1.15rem;
    border-radius: 40px;
    border: 1.5px solid var(--border);
    background: var(--white);
    color: var(--text-mid);
    font-size: .85rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
  }
  .cat-tab:hover {
    border-color: var(--primary);
    color: var(--primary);
  }
  .cat-tab.active {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(26,60,46,.25);
  }

  /* ---- Product Card ---- */
  .prod-card {
    background: var(--white);
    border-radius: 24px;
    border: 1px solid var(--border);
    overflow: hidden;
    height: 100%;
    transition: transform .25s, box-shadow .25s;
    position: relative;
  }
  .prod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(26,60,46,.14);
  }
  .prod-card__img-wrap { 
    position: relative; 
    overflow: hidden;
    border-top-left-radius: 24px;
    border-top-right-radius: 24px;
  }
  .prod-card__img {
    width: 100%; height: 210px;
    object-fit: cover;
    display: block;
    transition: transform .35s ease;
  }
  .prod-card:hover .prod-card__img { transform: scale(1.05); }
  .prod-card__badges {
    position: absolute; top: 12px; left: 12px;
    display: flex; gap: .4rem; flex-wrap: wrap;
  }
  .prod-card__badge-new  { background: var(--accent);   color: var(--primary); }
  .prod-card__badge-best { background: var(--primary);  color: var(--white); }
  .prod-card__badge-tag {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .05em;
    padding: .2rem .65rem;
    border-radius: 20px;
  }
  .prod-card__rating {
    position: absolute; top: 12px; right: 12px;
    background: rgba(255,255,255,.95);
    backdrop-filter: blur(4px);
    border-radius: 20px;
    padding: .2rem .65rem;
    font-size: .75rem;
    font-weight: 700;
    color: var(--primary);
    display: flex; align-items: center; gap: .25rem;
  }
  .prod-card__body { padding: 1.1rem 1.1rem 1.2rem; }
  .prod-card__cat {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: .3rem;
  }
  .prod-card__name {
    font-family: var(--font-display);
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: .4rem;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .prod-card__desc {
    font-size: .8rem;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: .85rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.4em;
  }
  .prod-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
  }
  .prod-card__price {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--primary);
  }
  .prod-card__price small {
    font-size: .7rem;
    font-weight: 500;
    color: var(--text-muted);
    display: block;
    line-height: 1;
  }
  .prod-card__sold { font-size: .7rem; color: var(--text-muted); }
  .btn-order {
    flex-shrink: 0;
    background: var(--primary);
    color: var(--white);
    border: none;
    border-radius: 40px;
    padding: .5rem 1rem;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    transition: background .2s, transform .15s, box-shadow .2s;
    text-decoration: none;
  }
  .btn-order:hover {
    background: var(--primary-light);
    color: var(--white);
    transform: scale(1.04);
    box-shadow: 0 4px 14px rgba(26,60,46,.25);
  }

  /* ---- Promo Banner ---- */
  .promo-banner {
    background: linear-gradient(120deg, var(--accent) 0%, #f0cb7a 100%);
    border-radius: var(--radius-lg);
    padding: 2.5rem 2rem;
    position: relative;
    overflow: visible;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
  }
  .promo-banner::after {
    content: '☕';
    position: absolute;
    right: 2rem; top: 50%;
    transform: translateY(-50%);
    font-size: 6rem;
    opacity: .15;
  }
  .promo-banner h3 {
    font-family: var(--font-display);
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: .4rem;
  }
  .promo-banner p { color: var(--primary); opacity: .75; font-size: .9rem; }

  /* ---- Why Section ---- */
  .why-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    padding: 1.75rem 1.5rem;
    text-align: center;
    height: 100%;
    transition: transform .2s, box-shadow .2s;
  }
  .why-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
  }
  .why-card__icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    display: block;
  }
  .why-card h5 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: .5rem;
  }
  .why-card p { font-size: .85rem; color: var(--text-muted); line-height: 1.55; margin: 0; }

  /* ---- Section spacing ---- */
  .section { padding: 60px 0; }
  .section--gray { background: var(--cream-dark); }

  /* ---- Empty state ---- */
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
  }
  .empty-state span { font-size: 3rem; display: block; margin-bottom: 1rem; }

  /* ---- Scroll fade ---- */
  .scroll-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity .55s ease, transform .55s ease;
  }
  .scroll-reveal.visible {
    opacity: 1;
    transform: none;
  }

  @media (max-width: 768px) {
    .hero { min-height: auto; padding: 3rem 0; }
    .hero__image-wrap { display: none; }
    .promo-banner { flex-direction: column; text-align: center; }
    .promo-banner::after { display: none; }
  }
  </style>
</head>
<body class="page-wrapper">

<?php include __DIR__ . '/../layouts/navbar.php'; ?>

<!-- ============================================================ -->
<!--  HERO                                                         -->
<!-- ============================================================ -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 hero__content animate-fadeup">
        <div class="hero__eyebrow">
          <i class="bi bi-award-fill"></i> #1 Minuman Premium Indonesia
        </div>
        <h1 class="hero__title">
          Nikmati <em>Kopi</em> &amp;<br>Minuman Terbaik Kami
        </h1>
        <p class="hero__subtitle">
          Dari biji arabika pilihan hingga teh premium grade ceremonial —
          kami hadirkan pengalaman minum yang tak terlupakan, langsung ke tangan Anda.
        </p>
        <div class="hero__cta-group">
          <a href="#menu" class="btn-accent" style="padding:.8rem 1.8rem;font-size:.95rem;">
            Lihat Menu <i class="bi bi-arrow-down ms-1"></i>
          </a>
          <a href="../auth/register.php" class="btn-outline-brand"
             style="border-color:rgba(255,255,255,.4);color:#fff;padding:.8rem 1.6rem;font-size:.95rem;">
            Daftar Gratis
          </a>
        </div>
        <div class="hero__search">
          <i class="bi bi-search hero__search-icon"></i>
          <input type="text" id="heroSearch" placeholder="Cari caramel latte, matcha..."
                 value="<?= htmlspecialchars($searchQ) ?>">
        </div>
      </div>

      <div class="col-lg-6 hero__image-wrap">
        <div class="hero__ring">
          <img src="../assets/img/hero-image.png"
              alt="Hero Image"
              class="hero__ring-img">

          <div class="hero__stats hero__stats--right">
            <strong>1.200+</strong>
            <span>Pesanan / hari</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================ -->
<!--  PROMO BANNER                                                 -->
<!-- ============================================================ -->
<div class="container my-5">
  <div class="promo-banner scroll-reveal">
    <div>
      <h3>Gratis Ongkir untuk Pesanan Pertama! 🎉</h3>
      <p>Daftar sekarang dan nikmati pengiriman gratis tanpa minimum pembelian.</p>
    </div>
    <a href="../auth/register.php"
       class="btn-brand flex-shrink-0"
       style="padding:.8rem 1.8rem;font-size:.9rem;white-space:nowrap;">
      Klaim Sekarang
    </a>
  </div>
</div>

<!-- ============================================================ -->
<!--  MENU SECTION                                                 -->
<!-- ============================================================ -->
<section class="section" id="menu">
  <div class="container">

    <!-- Header -->
    <div class="row align-items-end mb-4 scroll-reveal">
      <div class="col">
        <div class="section-label">Menu Kami</div>
        <h2 class="section-title">Temukan Minuman Favoritmu</h2>
      </div>
      <div class="col-auto d-none d-md-block">
        <span class="text-muted" style="font-size:.85rem;">
          <?= count($filtered) ?> produk tersedia
        </span>
      </div>
    </div>

    <!-- Category Tabs -->
    <div class="cat-tabs mb-4 scroll-reveal">
      <?php
      $cats = [
        'all'        => ['icon' => 'bi-grid-fill',      'label' => 'Semua'],
        'coffee'     => ['icon' => 'bi-cup-hot-fill',   'label' => 'Kopi'],
        'non-coffee' => ['icon' => 'bi-cup-straw',      'label' => 'Non Kopi'],
        'tea'        => ['icon' => 'bi-flower1',        'label' => 'Teh'],
      ];
      foreach ($cats as $key => $info):
        $isActive = ($activecat === $key) ? 'active' : '';
      ?>
        <a href="home.php?cat=<?= $key ?><?= $searchQ ? '&q='.urlencode($searchQ) : '' ?>"
           class="cat-tab <?= $isActive ?>">
          <i class="bi <?= $info['icon'] ?>"></i>
          <?= $info['label'] ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Product Grid -->
    <?php if (empty($filtered)): ?>
      <div class="empty-state scroll-reveal">
        <span>🔍</span>
        <h5>Tidak ada produk ditemukan</h5>
        <p class="mb-3">Coba kata kunci atau kategori lain</p>
        <a href="home.php" class="btn-brand">Lihat Semua Menu</a>
      </div>
    <?php else: ?>
    <div class="row g-4" id="productGrid">
      <?php foreach ($filtered as $i => $product):
        $cat   = $product['category'];
        $img   = !empty($product['image'])
                 ? '../assets/img/products/' . $product['image']
                 : '';
        $delay = ($i % 4) * 80;
      ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3 scroll-reveal"
           style="transition-delay:<?= $delay ?>ms">
        <div class="prod-card">
          <!-- Image -->
          <div class="prod-card__img-wrap">
            <img src="<?= $img ?>" class="prod-card__img"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 onerror="this.src='https://placehold.co/400x210/1a3c2e/f0cb7a?text=Konnyusu'">

            <!-- Badges -->
            <div class="prod-card__badges">
              <?php if ($product['is_new']): ?>
                <span class="prod-card__badge-tag prod-card__badge-new">Baru</span>
              <?php endif; ?>
              <?php if ($product['is_best']): ?>
                <span class="prod-card__badge-tag prod-card__badge-best">Best Seller</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Body -->
          <div class="prod-card__body">
            <div class="prod-card__cat"><?= ucfirst($cat) ?></div>
            <h5 class="prod-card__name"><?= htmlspecialchars($product['name']) ?></h5>
            <p class="prod-card__desc"><?= htmlspecialchars($product['description']) ?></p>
            <div class="prod-card__footer">
              <div>
                <div class="prod-card__price"><?= formatRupiah($product['price']) ?></div>
                <div class="prod-card__sold"><?= $product['sold'] ?> terjual</div>
              </div>
              <button class="btn-order" onclick="openProductModal(<?= $product['id'] ?>, '<?= addslashes(htmlspecialchars($product['name'])) ?>', '<?= $product['category'] ?>', '<?= addslashes(htmlspecialchars($product['description'])) ?>', <?= $product['price'] ?>, '<?= !empty($product['image']) ? '../assets/img/products/'.$product['image'] : '' ?>')">
                <i class="bi bi-plus-lg"></i> Tambah
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ============================================================ -->
<!--  WHY US                                                       -->
<!-- ============================================================ -->
<section class="section section--gray">
  <div class="container">
    <div class="text-center mb-5 scroll-reveal">
      <div class="section-label">Keunggulan Kami</div>
      <h2 class="section-title">Kenapa Memilih Konnyusu?</h2>
    </div>
    <div class="row g-4">
      <?php
      $whys = [
          ['bi-cup-hot-fill', 'Biji Kopi Premium', 'Single origin arabika dan robusta pilihan dari perkebunan terbaik Nusantara.'],
          ['bi-lightning-charge-fill', 'Pesanan Cepat', 'Siap saji dalam 5 menit, pengiriman ke lokasi Anda sebelum Anda duduk.'],
          ['bi-shield-check', 'Kualitas Terjamin', 'Barista bersertifikat internasional mengerjakan setiap pesanan dengan standar tertinggi.'],
          ['bi-tree-fill', 'Ramah Lingkungan', 'Kemasan biodegradable dan sumber bahan baku yang berkelanjutan.'],
          ['bi-credit-card', 'Pembayaran Mudah', 'Dukung QRIS, transfer bank, dan semua metode pembayaran digital populer.'],
          ['bi-gift-fill', 'Reward Program', 'Kumpulkan poin setiap pembelian dan tukar dengan minuman gratis pilihan.'],
      ];
      foreach ($whys as $i => $w):
        $delay = ($i % 3) * 100;
      ?>
      <div class="col-12 col-sm-6 col-md-4 scroll-reveal" style="transition-delay:<?= $delay ?>ms">
        <div class="why-card">
          <i class="bi <?= $w[0] ?> why-card__icon"></i>
          <h5><?= $w[1] ?></h5>
          <p><?= $w[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Login Warning Modal -->
<div class="modal fade" id="loginWarningModal" tabindex="-1" aria-hidden="true" style="z-index:1060;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;">
      <div class="modal-body p-4 text-center">
        <div style="width:70px;height:70px;background:#fff8ec;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;"><i class="bi bi-exclamation-triangle-fill" style="font-size:2rem;color:var(--accent);"></i></div>
        <h5 style="font-family:var(--font-display);font-weight:800;color:var(--primary);margin-bottom:.5rem;">Silakan Login Terlebih Dahulu</h5>
        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem;">Untuk memesan produk, Anda harus login atau mendaftar akun terlebih dahulu.</p>
        <div class="d-flex gap-2 justify-content-center">
          <a href="../auth/login.php" class="btn-brand" style="text-decoration:none;padding:.6rem 1.2rem;">Masuk</a>
          <a href="../auth/register.php" class="btn-outline-brand" style="text-decoration:none;padding:.6rem 1.2rem;">Daftar</a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position:absolute;top:1rem;right:1rem;"></button>
      </div>
    </div>
  </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;">
      <div class="modal-body p-0">
        <div class="row g-0">
          <div class="col-md-5">
            <div id="modalImage" style="height:100%;min-height:320px;background:var(--cream);display:flex;align-items:center;justify-content:center;">
              <img id="modalImg" src="" alt="" style="width:100%;height:100%;object-fit:cover;">
            </div>
          </div>
          <div class="col-md-7 p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div id="modalCategory" style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--accent);margin-bottom:.3rem;"></div>
                <h4 id="modalName" style="font-family:var(--font-display);font-weight:800;color:var(--primary);margin-bottom:.5rem;"></h4>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <p id="modalDesc" style="color:var(--text-muted);font-size:.9rem;line-height:1.6;margin-bottom:1.5rem;"></p>
            <div class="mb-3">
              <span id="modalPrice" style="font-family:var(--font-display);font-size:1.4rem;font-weight:800;color:var(--primary);"></span>
            </div>
            
            <div class="mb-3">
              <label class="form-label" style="font-weight:600;color:var(--primary);font-size:.85rem;">Kuantitas</label>
              <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="changeQty(-1)" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="bi bi-dash"></i></button>
                <input type="number" id="modalQty" value="1" min="1" style="width:70px;text-align:center;border:1.5px solid var(--border);border-radius:10px;padding:.5rem;font-weight:700;">
                <button class="btn btn-outline-secondary btn-sm" onclick="changeQty(1)" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="bi bi-plus"></i></button>
              </div>
            </div>
            
            <div class="row g-3 mb-4">
              <div class="col-6">
                <label class="form-label" style="font-weight:600;color:var(--primary);font-size:.85rem;">Level Gula</label>
                <div class="d-flex flex-column gap-2">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="sugar" id="sugarNormal" value="normal" checked>
                    <label class="form-check-label" for="sugarNormal" style="font-size:.85rem;">Normal</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="sugar" id="sugarLess" value="less">
                    <label class="form-check-label" for="sugarLess" style="font-size:.85rem;">Less Sugar</label>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <label class="form-label" style="font-weight:600;color:var(--primary);font-size:.85rem;">Level Es</label>
                <div class="d-flex flex-column gap-2">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="ice" id="iceNormal" value="normal" checked>
                    <label class="form-check-label" for="iceNormal" style="font-size:.85rem;">Normal</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="ice" id="iceLess" value="less">
                    <label class="form-check-label" for="iceLess" style="font-size:.85rem;">Less Ice</label>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="d-flex gap-2">
              <button id="btnAddToCart" class="btn-brand flex-1" style="padding:.8rem 1.5rem;font-weight:700;">
                <i class="bi bi-cart-plus me-2"></i>Masukkan Keranjang
              </button>
              <button id="btnBuyNow" class="btn-accent flex-1" style="padding:.8rem 1.5rem;font-weight:700;">
                <i class="bi bi-bag-check me-2"></i>Beli Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentProduct = null;
const productModal = new bootstrap.Modal('#productModal');

function openProductModal(id, name, category, desc, price, img) {
  currentProduct = { id, name, category, desc, price, img };
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalCategory').textContent = category.charAt(0).toUpperCase() + category.slice(1);
  document.getElementById('modalDesc').textContent = desc;
  document.getElementById('modalPrice').textContent = 'Rp ' + price.toLocaleString('id-ID');
  
  const modalImg = document.getElementById('modalImg');
  if (img) {
    modalImg.src = img;
    modalImg.style.display = 'block';
  } else {
    modalImg.src = 'https://placehold.co/400x320/1a3c2e/f0cb7a?text=Konnyusu';
    modalImg.style.display = 'block';
  }
  
  document.getElementById('modalQty').value = 1;
  document.getElementById('sugarNormal').checked = true;
  document.getElementById('iceNormal').checked = true;
  
  productModal.show();
}

function changeQty(delta) {
  const input = document.getElementById('modalQty');
  let val = parseInt(input.value) + delta;
  if (val < 1) val = 1;
  input.value = val;
}

function addToCart() {
  if (!currentProduct) return;
  
  const qty = parseInt(document.getElementById('modalQty').value);
  const sugar = document.querySelector('input[name="sugar"]:checked').value;
  const ice = document.querySelector('input[name="ice"]:checked').value;
  
  // Create form and submit
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../cart/add-to-cart.php';
  
  const addField = (name, value) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
  };
  
  addField('id', currentProduct.id);
  addField('qty', qty);
  addField('sugar_level', sugar);
  addField('ice_level', ice);
  
  document.body.appendChild(form);
  form.submit();
}

const isLoggedIn = <?= !empty($currentUser) ? 'true' : 'false' ?>;
const loginWarningModal = new bootstrap.Modal('#loginWarningModal');

document.getElementById('btnAddToCart').addEventListener('click', () => {
  if (!isLoggedIn) {
    productModal.hide();
    setTimeout(() => {
      loginWarningModal.show();
    }, 150);
    return;
  }
  addToCart();
});

document.getElementById('btnBuyNow').addEventListener('click', () => {
  if (!isLoggedIn) {
    productModal.hide();
    setTimeout(() => {
      loginWarningModal.show();
    }, 150);
    return;
  }
  addToCart();
});

// ---- Scroll reveal ----
const reveals = document.querySelectorAll('.scroll-reveal');
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
}, { threshold: 0.1 });
reveals.forEach(el => io.observe(el));

// ---- Hero search -> redirect ----
document.getElementById('heroSearch')?.addEventListener('keydown', function(e) {
  if (e.key === 'Enter') {
    const q = this.value.trim();
    if (q) window.location.href = 'home.php?q=' + encodeURIComponent(q);
  }
});
</script>
</body>
</html>
