<?php
// cart/cart.php
session_start();
require_once __DIR__ . '/../config/cart.php';
include __DIR__ . '/../data/products.php';

$cartItems = getCartItems();
$subtotal  = getCartTotal();
$deliveryFee = $subtotal >= 50000 ? 0 : 5000;
$tax         = (int)round($subtotal * 0.01);
$total       = $subtotal + $deliveryFee + $tax;
$cartTotalItems = getCartCount();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keranjang — Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
  .cart-layout {
    max-width: 1100px; margin: 0 auto;
    padding: 2.5rem 1.25rem;
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 2rem;
    align-items: start;
  }
  .cart-header { margin-bottom: 1.5rem; }
  .cart-header h1 { font-size: 1.75rem; font-weight: 700; color: var(--primary); margin-bottom: .25rem; }
  .cart-header p  { color: var(--text-muted); font-size: .88rem; }

  /* Cart Items Panel */
  .cart-panel {
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    overflow: hidden;
  }
  .cart-panel__header {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
  }
  .cart-panel__header h5 { font-size: .95rem; font-weight: 700; color: var(--primary); margin: 0; }
  .cart-item {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 1rem;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    align-items: center;
    transition: background .2s;
  }
  .cart-item:last-child { border-bottom: none; }
  .cart-item:hover { background: var(--cream); }
  .cart-item__img {
    width: 80px; height: 80px;
    border-radius: var(--radius-md);
    object-fit: cover;
  }
  .cart-item__name {
    font-weight: 700; font-size: .95rem;
    color: var(--primary); margin-bottom: .2rem;
  }
  .cart-item__price { font-size: .85rem; color: var(--text-muted); }
  .cart-item__subtotal { font-weight: 700; font-size: .95rem; color: var(--primary); }
  .qty-ctrl {
    display: flex; align-items: center; gap: .4rem; margin-top: .6rem;
  }
  .qty-btn {
    width: 28px; height: 28px;
    border-radius: 50%;
    border: 1.5px solid var(--border);
    background: var(--white);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .85rem; color: var(--primary);
    transition: background .2s, border-color .2s;
    text-decoration: none;
  }
  .qty-btn:hover { background: var(--primary); border-color: var(--primary); color: var(--white); }
  .qty-val {
    width: 32px; text-align: center;
    font-weight: 700; font-size: .9rem; color: var(--primary);
  }
  .cart-item__right { text-align: right; }
  .btn-remove {
    background: none; border: none;
    color: var(--text-muted); font-size: .8rem;
    cursor: pointer; padding: .2rem;
    transition: color .2s;
    margin-top: .5rem;
    text-decoration: none;
    display: block;
  }
  .btn-remove:hover { color: var(--danger); }

  /* Empty State */
  .empty-cart {
    text-align: center; padding: 4rem 2rem;
  }
  .empty-cart__icon { font-size: 4rem; display: block; margin-bottom: 1rem; }
  .empty-cart h4 { color: var(--primary); margin-bottom: .5rem; }
  .empty-cart p  { color: var(--text-muted); font-size: .9rem; margin-bottom: 1.5rem; }

  /* Summary Panel */
  .summary-panel {
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    padding: 1.5rem;
    position: sticky;
    top: 90px;
  }
  .summary-panel h5 { font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 1.25rem; }
  .summary-row {
    display: flex; justify-content: space-between;
    font-size: .88rem; color: var(--text-mid);
    margin-bottom: .6rem;
  }
  .summary-row.total {
    font-size: 1.05rem; font-weight: 800;
    color: var(--primary);
    margin-top: .75rem; padding-top: .75rem;
    border-top: 2px solid var(--border);
    margin-bottom: 0;
  }
  .promo-input {
    display: flex; gap: .5rem; margin-bottom: 1.25rem;
  }
  .promo-input input {
    flex: 1;
    background: var(--cream);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: .6rem .9rem;
    font-family: var(--font-body);
    font-size: .85rem;
    outline: none;
    transition: border-color .2s;
  }
  .promo-input input:focus { border-color: var(--primary-light); }
  .promo-input button {
    background: var(--primary);
    color: #fff; border: none;
    border-radius: var(--radius-md);
    padding: .6rem 1rem;
    font-family: var(--font-body);
    font-size: .82rem; font-weight: 600;
    cursor: pointer;
    transition: background .2s;
  }
  .promo-input button:hover { background: var(--primary-light); }
  .btn-checkout {
    width: 100%;
    background: var(--primary);
    color: #fff; border: none;
    border-radius: var(--radius-xl);
    padding: 1rem;
    font-family: var(--font-body);
    font-size: 1rem; font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: background .2s, transform .15s;
    text-decoration: none;
  }
  .btn-checkout:hover { background: var(--primary-light); color: #fff; transform: translateY(-1px); }
  .delivery-note {
    background: var(--cream);
    border-radius: var(--radius-md);
    padding: .75rem 1rem;
    font-size: .8rem;
    color: var(--success);
    display: flex; gap: .5rem;
    align-items: flex-start;
    margin-top: 1rem;
  }
  .delivery-note i { flex-shrink: 0; margin-top: 1px; }
  .payment-icons { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: 1rem; }
  .payment-icon {
    background: var(--cream); border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .25rem .6rem;
    font-size: .7rem; font-weight: 700; color: var(--text-mid);
  }
  @media (max-width: 900px) {
    .cart-layout { grid-template-columns: 1fr; }
    .summary-panel { position: static; }
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
    <span>Keranjang</span>
  </div>
</div>

<div class="page-content">
<div class="cart-layout">

  <!-- ---- LEFT: Items ---- -->
  <div>
    <div class="cart-header">
      <h1><i class="bi bi-bag me-2"></i>Keranjang Saya</h1>
      <p><?= $cartTotalItems ?> item dalam keranjang</p>
    </div>

    <div class="cart-panel animate-fadeup">
      <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
          <span class="empty-cart__icon">🛒</span>
          <h4>Keranjang masih kosong</h4>
          <p>Yuk, temukan minuman favoritmu dan tambahkan ke keranjang!</p>
          <a href="../home/home.php" class="btn-brand">Lihat Menu</a>
        </div>
      <?php else: ?>
        <div class="cart-panel__header">
          <h5>Daftar Pesanan</h5>
          <a href="#" onclick="return confirm('Kosongkan semua?')"
             style="font-size:.8rem;color:var(--danger);text-decoration:none;">
            <i class="bi bi-trash3"></i> Kosongkan
          </a>
        </div>

        <?php foreach ($cartItems as $index => $item): 
            $itemId = $item['cart_item_id'] ?? $index;
            $productId = $item['menu_id'] ?? $item['id'];
        ?>
        <div class="cart-item">
          <img src="../assets/img/products/<?= $item['image'] ?>"
               class="cart-item__img"
               onerror="this.src='https://placehold.co/80x80/1a3c2e/f0cb7a?text=K'">
          <div>
            <div class="cart-item__name">
              <?= htmlspecialchars($item['name']) ?>
            </div>

            <div class="cart-item__price">
              <?= formatRupiah($item['price']) ?> / item
            </div>

            <!-- ORDER ITEM DETAIL -->
            <div style="margin-top:.45rem;display:flex;gap:.45rem;flex-wrap:wrap;">

              <?php if(!empty($item['ice_level'])): ?>
                <span style="
                  background:var(--cream);
                  border:1px solid var(--border);
                  border-radius:20px;
                  padding:.2rem .6rem;
                  font-size:.72rem;
                  color:var(--text-mid);
                ">
                  🧊 <?= htmlspecialchars($item['ice_level']) ?>
                </span>
              <?php endif; ?>
              
              <?php if(!empty($item['size'])): ?>
                <span style="
                  background:var(--cream);
                  border:1px solid var(--border);
                  border-radius:20px;
                  padding:.2rem .6rem;
                  font-size:.72rem;
                  color:var(--text-mid);
                ">
                  🥤 <?= htmlspecialchars($item['size']) ?>
                </span>
              <?php endif; ?>

              <?php if(!empty($item['sugar_level'])): ?>
                <span style="
                  background:var(--cream);
                  border:1px solid var(--border);
                  border-radius:20px;
                  padding:.2rem .6rem;
                  font-size:.72rem;
                  color:var(--text-mid);
                ">
                  🍬 <?= htmlspecialchars($item['sugar_level']) ?>
                </span>
              <?php endif; ?>

            </div>
          </div>
          <div class="cart-item__right">
            <div class="cart-item__subtotal">
              <?= formatRupiah($item['price'] * $item['quantity']) ?>
            </div>
            <a href="remove-from-cart.php?id=<?= $itemId ?>"
               class="btn-remove"
               onclick="return confirm('Hapus item ini?')">
               <i class="bi bi-x"></i> Hapus
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="mt-3">
      <a href="../home/home.php" style="font-size:.88rem;color:var(--primary);font-weight:600;text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
      </a>
    </div>
  </div>

  <!-- ---- RIGHT: Summary ---- -->
  <div>
    <div class="summary-panel animate-fadeup" style="animation-delay:.1s">
      <h5>Ringkasan Pesanan</h5>



      <!-- Rows -->
      <div class="summary-row">
        <span>Subtotal (<?= $cartTotalItems ?> item)</span>
        <span><?= formatRupiah($subtotal) ?></span>
      </div>
      <div class="summary-row">
        <span>Ongkos Kirim</span>
        <span><?= $deliveryFee === 0 ? '<span style="color:var(--success)">Gratis!</span>' : formatRupiah($deliveryFee) ?></span>
      </div>
      <div class="summary-row">
        <span>Pajak (1%)</span>
        <span><?= formatRupiah($tax) ?></span>
      </div>
      <div class="summary-row total">
        <span>Total</span>
        <span><?= formatRupiah($total) ?></span>
      </div>

      <!-- Checkout Btn -->
      <?php if (!empty($cartItems)): ?>
        <a href="../checkout/checkout.php" class="btn-checkout mt-4">
          <i class="bi bi-lock-fill"></i> Lanjut ke Pembayaran
        </a>
      <?php endif; ?>

      <?php if ($subtotal < 50000 && !empty($cartItems)):
        $diff = 50000 - $subtotal;
      ?>
      <div class="delivery-note mt-3">
        <i class="bi bi-truck"></i>
        <span>Tambah <?= formatRupiah($diff) ?> lagi untuk gratis ongkir!</span>
      </div>
      <?php elseif (!empty($cartItems)): ?>
      <div class="delivery-note mt-3">
        <i class="bi bi-check-circle-fill"></i>
        <span>Selamat! Kamu mendapat gratis ongkir.</span>
      </div>
      <?php endif; ?>

      <!-- Payment Methods -->
      <div style="margin-top:1.25rem;border-top:1px solid var(--border);padding-top:1rem;">
        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.6rem;">Metode Pembayaran</div>
        <div class="payment-icons">
          <span class="payment-icon">QRIS</span>
          <span class="payment-icon">BCA</span>
          <span class="payment-icon">Mandiri</span>
          <span class="payment-icon">GoPay</span>
          <span class="payment-icon">OVO</span>
          <span class="payment-icon">Dana</span>
        </div>
      </div>
    </div>
  </div>

</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
