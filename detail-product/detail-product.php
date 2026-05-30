<?php
// detail-product/detail-product.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';
include __DIR__ . '/../data/products.php';

$cartTotalItems = getCartCount();
$currentUser = getCurrentUser();

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$product = findProduct($id);
if (!$product) {
    header('Location: ../home/home.php');
    exit;
}

// Related products (same category, exclude current)
$related = array_filter($products, fn($p) => $p['category'] === $product['category'] && $p['id'] !== $id);
$related = array_slice($related, 0, 4);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> — Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
  .detail-wrap { max-width:1100px; margin:0 auto; padding:2.5rem 1.25rem; }
  /* Product hero */
  .product-hero { display:flex; gap:3rem; margin-bottom:3.5rem; align-items: flex-start;}
  .product-img-wrap { width: 45%; flex-shrink: 0;}
  .product-main-img { width:100%; height:380px; object-fit:cover; border-radius:var(--radius-lg);box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
  .img-thumbnail-row { display:flex; gap:.65rem; margin-top:.75rem; }
  .img-thumb { width:72px; height:72px; object-fit:cover; border-radius:var(--radius-md); border:2px solid transparent; cursor:pointer; transition:border-color .2s; }
  .img-thumb.active { border-color:var(--primary); }
  /* Info */
  .product-info {flex: 1; }
  .product-cat { font-size:.72rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--accent); margin-bottom:.5rem; }
  .product-name { font-family:var(--font-display); font-size:2rem; font-weight:800; color:var(--primary); line-height:1.15; margin-bottom:.65rem; }
  .rating-count { font-size:.82rem; color:var(--text-muted); }
  .product-price { font-family:var(--font-display); font-size:2.2rem; font-weight:800; color:var(--primary); margin-bottom:1.25rem; }
  .product-desc { font-size:.9rem; color:var(--text-mid); line-height:1.7; margin-bottom:1.5rem; }
  /* Options */
  .option-label { font-size:.8rem; font-weight:700; color:var(--text-mid); margin-bottom:.5rem; letter-spacing:.04em; }
  .option-group { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.25rem; }
  .option-btn { padding:.45rem 1.05rem; border-radius:40px; border:1.5px solid var(--border); background:var(--white); font-size:.82rem; font-weight:600; color:var(--text-mid); cursor:pointer; transition:all .2s; }
  .option-btn.active { border-color:var(--primary); background:rgba(26,60,46,.07); color:var(--primary); }
  .option-btn:hover { border-color:var(--primary); color:var(--primary); }
  /* Qty */
  .qty-row { display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; }
  .qty-ctrl { display:flex; align-items:center; gap:.5rem; }
  .qty-btn { width:36px; height:36px; border-radius:50%; border:1.5px solid var(--border); background:var(--white); display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:700; cursor:pointer; color:var(--primary); transition:all .2s; }
  .qty-btn:hover { background:var(--primary); border-color:var(--primary); color:#fff; }
  .qty-display { width:44px; text-align:center; font-size:1.05rem; font-weight:700; color:var(--primary); }
  /* CTA buttons */
  .cta-row { display:flex; gap:.75rem; }
  .btn-cart { flex:1; padding:.9rem; border-radius:var(--radius-xl); border:2px solid var(--primary); background:var(--white); color:var(--primary); font-family:var(--font-body); font-size:.95rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.5rem; transition:all .2s; }
  .btn-cart:hover { background:var(--primary); color:#fff; }
  .btn-buy { flex:2; padding:.9rem; border-radius:var(--radius-xl); border:none; background:var(--primary); color:#fff; font-family:var(--font-body); font-size:.95rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.5rem; transition:all .2s; text-decoration:none; }
  .btn-buy:hover { background:var(--primary-light); color:#fff; transform:translateY(-1px); }
  /* Features */
  .product-features { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; margin-top:1.5rem; }
  .feature-item { display:flex; align-items:center; gap:.5rem; padding:.65rem .85rem; background:var(--cream); border-radius:var(--radius-md); }
  .feature-item i { color:var(--primary); font-size:1rem; }
  .feature-item span { font-size:.75rem; color:var(--text-mid); font-weight:600; }
  /* Tabs */
  .info-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:2rem; }
  .info-tab { padding:.75rem 1.25rem; font-size:.88rem; font-weight:600; color:var(--text-muted); cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .2s; }
  .info-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
  .tab-panel { display:none; }
  .tab-panel.active { display:block; }
  /* Review card */
  .review-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.25rem; margin-bottom:1rem; }
  .review-header { display:flex; align-items:center; gap:.75rem; margin-bottom:.75rem; }
  .review-avatar { width:38px; height:38px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; }
  .review-name { font-weight:700; font-size:.88rem; color:var(--primary); }
  .review-date { font-size:.72rem; color:var(--text-muted); }
  .review-text { font-size:.87rem; color:var(--text-mid); line-height:1.65; }
  /* Related */
  .related-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
  @media(max-width:576px){ .cta-row{flex-direction:column;} .info-tabs{overflow-x:auto;} }
  @media(max-width:900px){ 
    .product-hero{ flex-direction: column; gap: 2rem; } 
    .product-img-wrap{ width: 100%; }
    .related-grid{ grid-template-columns: repeat(2,1fr); } 
    .product-features{ grid-template-columns: 1fr 1fr; } 
  }
  </style>
</head>
<body class="page-wrapper">
<?php include __DIR__ . '/../layouts/navbar.php'; ?>

<!-- Breadcrumb -->
<div style="background:var(--cream-dark);border-bottom:1px solid var(--border);padding:.6rem 0;">
  <div style="max-width:1100px;margin:0 auto;padding:0 1.25rem;font-size:.8rem;color:var(--text-muted);">
    <a href="../home/home.php" style="color:var(--primary);">Beranda</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem;"></i>
    <a href="../home/home.php?cat=<?= $product['category'] ?>" style="color:var(--primary);"><?= ucfirst($product['category']) ?></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem;"></i>
    <span><?= htmlspecialchars($product['name']) ?></span>
  </div>
</div>

<div class="page-content">
<div class="detail-wrap">

  <!-- Product Hero -->
  <div class="product-hero animate-fadeup">

    <!-- Image -->
    <div class="product-img-wrap">
      <img id="mainImg"
           src="../assets/img/products/<?= $product['image'] ?>"
           class="product-main-img"
           alt="<?= htmlspecialchars($product['name']) ?>"
           onerror="this.src='https://placehold.co/600x420/1a3c2e/f0cb7a?text=Konnyusu'">
      <div class="img-thumbnail-row">
        <img src="../assets/img/products/<?= $product['image'] ?>"
             class="img-thumb active" onclick="switchImg(this)"
             onerror="this.src='https://placehold.co/72x72/1a3c2e/f0cb7a?text=K'">
        <img src="https://placehold.co/72x72/2e6b4f/fff?text=View+2"
             class="img-thumb" onclick="switchImg(this)">
        <img src="https://placehold.co/72x72/d4a853/1a3c2e?text=View+3"
             class="img-thumb" onclick="switchImg(this)">
      </div>
    </div>

    <!-- Info -->
    <div class="product-info">
      <div class="product-cat"><?= ucfirst($product['category']) ?></div>
      <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

      <div style="margin-bottom:1.25rem;">
        <span class="k-badge k-badge-green">
          <i class="bi bi-check-circle-fill"></i> Tersedia
        </span>
      </div>

      <div class="product-price"><?= formatRupiah($product['price']) ?></div>

      <p class="product-desc"><?= htmlspecialchars($product['description']) ?> Disajikan dengan bahan-bahan segar pilihan, tanpa pengawet, dan penuh cinta dari barista kami.</p>

      <form action="../cart/add-to-cart.php" method="POST" id="addToCartForm">

      <input type="hidden" name="id" value="<?= $product['id'] ?>">
      <input type="hidden" name="redirect" value="cart" id="redirectType">

      <!-- Size -->
      <div class="option-label">Ukuran</div>

      <div class="option-group">

        <label class="option-btn active">
          <input type="radio" name="size" value="Regular" checked hidden>
          Regular
        </label>

        <label class="option-btn">
          <input type="radio" name="size" value="Large" hidden>
          Large (+5k)
        </label>

      </div>

      <!-- Sugar -->
      <div class="option-label">Tingkat Manis</div>
      <div class="option-group">
        <label class="option-btn active">
          <input type="radio" name="sugar_level" value="Normal" checked hidden>
          Normal
        </label>

        <label class="option-btn">
          <input type="radio" name="sugar_level" value="Less Sugar" hidden>
          Less Sugar
        </label>

        <label class="option-btn">
          <input type="radio" name="sugar_level" value="Extra Sweet" hidden>
          Extra Sweet
        </label>
      </div>

      <!-- Ice -->
      <div class="option-label">Level Es</div>
      <div class="option-group">
        <label class="option-btn active">
          <input type="radio" name="ice_level" value="Normal Ice" checked hidden>
          Normal Ice
        </label>

        <label class="option-btn">
          <input type="radio" name="ice_level" value="Less Ice" hidden>
          Less Ice
        </label>

        <label class="option-btn">
          <input type="radio" name="ice_level" value="No Ice" hidden>
          No Ice
        </label>
      </div>

      <!-- Quantity -->
      <div class="qty-row">
        <div class="option-label mb-0">Jumlah:</div>

        <input type="number"
              name="quantity"
              value="1"
              min="1"
              style="width:80px;padding:.5rem;border:1px solid #ccc;border-radius:8px;">
      </div>

      <div class="cta-row">
        <button type="submit" form="addToCartForm" class="btn-cart">
          <i class="bi bi-bag-plus"></i>
          Masukkan Keranjang
        </button>
        <button type="button" class="btn-buy" onclick="buyNow()">
          <i class="bi bi-lightning-fill"></i>
          Beli Sekarang
        </button>
      </div>

      </form>

      <!-- Features -->
      <div class="product-features">
        <div class="feature-item"><i class="bi bi-truck"></i><span>Gratis Ongkir ≥50rb</span></div>
        <div class="feature-item"><i class="bi bi-clock"></i><span>Siap &lt; 10 Menit</span></div>
        <div class="feature-item"><i class="bi bi-shield-check"></i><span>Bahan Premium</span></div>
      </div>
    </div>
  </div>

  <!-- Tabs: Deskripsi / Nutrisi / Ulasan -->
  <div class="animate-fadeup" style="animation-delay:.1s">
    <div class="info-tabs">
      <div class="info-tab active" data-tab="desc">Deskripsi</div>
      <div class="info-tab" data-tab="nutrition">Informasi Nutrisi</div>
    </div>

    <!-- Deskripsi -->
    <div class="tab-panel active" id="tab-desc">
      <div style="max-width:680px;">
        <p style="color:var(--text-mid);line-height:1.8;margin-bottom:1rem;">
          <?= htmlspecialchars($product['name']) ?> adalah salah satu menu unggulan Konnyusu yang dibuat dari bahan-bahan berkualitas tinggi yang dipilih langsung oleh tim ahli kami. Setiap cangkir disajikan dengan standar kualitas ketat untuk memastikan Anda mendapatkan pengalaman terbaik.
        </p>
        <p style="color:var(--text-mid);line-height:1.8;margin-bottom:1rem;">
          <?= htmlspecialchars($product['description']) ?> Proses pembuatannya menggunakan teknik modern yang mempertahankan cita rasa alami bahan baku, sehingga setiap tegukan memberikan sensasi yang autentik dan memuaskan.
        </p>
        <ul style="color:var(--text-mid);line-height:2;padding-left:1.25rem;">
          <li>Bahan segar pilihan tanpa pengawet</li>
          <li>Diproses oleh barista bersertifikat</li>
          <li>Bisa dikustomisasi sesuai selera</li>
          <li>Kemasan eco-friendly &amp; food-grade</li>
        </ul>
      </div>
    </div>

    <!-- Nutrisi -->
    <div class="tab-panel" id="tab-nutrition">
      <div style="max-width:480px;">
        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
          <div style="background:var(--primary);color:#fff;padding:1rem 1.5rem;">
            <div style="font-size:.75rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.6);">Informasi Nilai Gizi</div>
            <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;"><?= htmlspecialchars($product['name']) ?></div>
            <div style="font-size:.8rem;color:rgba(255,255,255,.7);">Sajian: 350ml</div>
          </div>
          <?php
          $nutrients = [
            ['Energi','245 kkal'],['Lemak Total','8 g'],['Lemak Jenuh','5 g'],
            ['Karbohidrat','38 g'],['Gula','30 g'],['Serat Pangan','1 g'],
            ['Protein','6 g'],['Natrium','120 mg'],['Kafein','95 mg'],
          ];
          foreach ($nutrients as $i => $n):
          ?>
          <div style="display:flex;justify-content:space-between;padding:.65rem 1.5rem;border-bottom:1px solid var(--border);background:<?= $i%2===0?'var(--cream)':'var(--white)' ?>;">
            <span style="font-size:.85rem;color:var(--text-mid);"><?= $n[0] ?></span>
            <span style="font-size:.85rem;font-weight:700;color:var(--primary);"><?= $n[1] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <p style="font-size:.75rem;color:var(--text-muted);margin-top:.75rem;">*Nilai gizi perkiraan untuk sajian standar 350ml.</p>
      </div>
    </div>

  <!-- Related Products -->
  <?php if (!empty($related)): ?>
  <div style="margin-top:3.5rem;" class="animate-fadeup" style="animation-delay:.15s">
    <div class="section-label">Produk Serupa</div>
    <h2 class="section-title mb-3">Mungkin Kamu Suka</h2>
    <div class="related-grid">
      <?php foreach ($related as $rel):
        $relImg = !empty($rel['image']) ? '../assets/img/products/'.$rel['image'] : '';
      ?>
      <div class="k-card">
        <div style="position:relative;overflow:hidden;">
          <img src="<?= $relImg ?>" style="width:100%;height:160px;object-fit:cover;display:block;transition:transform .35s;"
               onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='none'"
               onerror="this.src='https://placehold.co/300x160/1a3c2e/f0cb7a?text=Konnyusu'">
        </div>
        <div style="padding:1rem;">
          <div style="font-size:.7rem;font-weight:700;color:var(--accent);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.25rem;"><?= ucfirst($rel['category']) ?></div>
          <h6 style="font-family:var(--font-display);font-size:.95rem;font-weight:700;color:var(--primary);margin-bottom:.35rem;line-height:1.2;"><?= htmlspecialchars($rel['name']) ?></h6>
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-weight:800;color:var(--primary);"><?= formatRupiah($rel['price']) ?></span>
            <a href="detail-product.php?id=<?= $rel['id'] ?>"
               style="font-size:.75rem;font-weight:600;color:var(--primary);text-decoration:none;">
              Lihat <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<!-- Admin Warning Modal -->
<div class="modal fade" id="adminWarningModal" tabindex="-1" aria-hidden="true" style="z-index:1060;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px;overflow:hidden;border:none;">
      <div class="modal-body p-4 text-center">
        <div style="width:70px;height:70px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;"><i class="bi bi-shield-lock-fill" style="font-size:2rem;color:#3b82f6;"></i></div>
        <h5 style="font-family:var(--font-display);font-weight:800;color:var(--primary);margin-bottom:.5rem;">Akses Pembelian Ditutup</h5>
        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem;">Akun admin hanya digunakan untuk mengelola toko. Untuk melakukan pembelian, silakan gunakan akun pelanggan.</p>
        <div class="d-flex gap-2 justify-content-center">
          <a href="../admin/dashboard/dashboard.php" class="btn-brand" style="text-decoration:none;padding:.6rem 1.2rem;">Ke Dashboard</a>
          <button type="button" class="btn-outline-brand" data-bs-dismiss="modal" style="padding:.6rem 1.2rem;">Tutup</button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position:absolute;top:1rem;right:1rem;"></button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const isAdmin = <?= !empty($currentUser) && ($currentUser['role'] ?? '') === 'admin' ? 'true' : 'false' ?>;
const adminWarningModal = new bootstrap.Modal('#adminWarningModal');

// Tab switching
document.querySelectorAll('.info-tab').forEach(tab => {
  tab.addEventListener('click', function() {
    document.querySelectorAll('.info-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    this.classList.add('active');
    document.getElementById('tab-' + this.dataset.tab).classList.add('active');
  });
});
// Option select
function selectOption(btn) {
  btn.closest('.option-group').querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
// Qty
let qty = 1;
function changeQty(d) {
  qty = Math.max(1, qty + d);
  document.getElementById('qtyVal').textContent = qty;
}
// Buy Now - redirect to checkout
function buyNow() {
  if (isAdmin) {
    adminWarningModal.show();
    return;
  }
  document.getElementById('redirectType').value = 'checkout';
  document.getElementById('addToCartForm').submit();
}

document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
  if (isAdmin) {
    e.preventDefault();
    adminWarningModal.show();
  }
});
// Image switch
function switchImg(thumb) {
  document.querySelectorAll('.img-thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
  document.getElementById('mainImg').src = thumb.src;
}
// Scroll reveal
const io = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting){ e.target.style.opacity='1'; e.target.style.transform='none'; io.unobserve(e.target); }});
}, {threshold:0.08});
document.querySelectorAll('.animate-fadeup').forEach(el => {
  el.style.opacity='0'; el.style.transform='translateY(20px)'; el.style.transition='opacity .5s ease, transform .5s ease'; io.observe(el);
});
</script>
</body>
</html>
