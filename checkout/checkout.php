<?php
// checkout/checkout.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/order.php';
require_once __DIR__ . '/../config/cart.php';
include __DIR__ . '/../data/products.php';

// Require login
requireLogin();

// Get selected items or all cart items
$allCartItems = getCartItems();
$selectedItemsIds = [];

if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
    $selectedItemsIds = explode(',', $_POST['selected_items']);
    $selectedItemsIds = array_map('intval', $selectedItemsIds);
}

// Filter cart items to only selected ones
$cartItems = [];
if (!empty($selectedItemsIds)) {
    foreach ($allCartItems as $item) {
        $itemId = isset($item['cart_item_id']) ? (int)$item['cart_item_id'] : (int)$item['id'];
        if (in_array($itemId, $selectedItemsIds)) {
            $cartItems[] = $item;
        }
    }
} else {
    $cartItems = $allCartItems;
}

// Calculate totals based on selected items
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$deliveryMethodNames = [
    'priority' => 'Prioritas (< 20 menit)',
    'standard' => 'Standar (30 menit)',
    'pickup' => 'Ambil Sendiri'
];
$deliveryMethodBaseFees = [
    'priority' => 8000,
    'standard' => 5000,
    'pickup' => 0
];
$paymentMethodNames = [
    'qris' => 'QRIS',
    'gopay' => 'GoPay',
    'ovo' => 'OVO',
    'dana' => 'DANA',
    'bca' => 'BCA Transfer',
    'cod' => 'Bayar di Tempat'
];
$defaultDeliveryMethod = 'standard';

// Calculate delivery fee - free if subtotal >= 50000 and not pickup
$baseDeliveryFee = $deliveryMethodBaseFees[$defaultDeliveryMethod];
$deliveryFee = ($subtotal >= 50000) ? 0 : $baseDeliveryFee;

$tax         = (int)round($subtotal * 0.01);
$total       = $subtotal + $deliveryFee + $tax;
$cartTotalItems = 0;
foreach ($cartItems as $item) {
    $cartTotalItems += $item['quantity'];
}

$currentUser = getCurrentUser();
$orderSuccess = false;
$orderId = null;

// Create a map of product categories for quick lookup
$productCategories = [];
foreach ($products as $p) {
    $productCategories[$p['id']] = $p['category'];
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order' && !empty($cartItems)) {
    $orderType = $_POST['delivery'] ?? $defaultDeliveryMethod;
    $notes = trim($_POST['note'] ?? '');
    $paymentMethod = $_POST['payment'] ?? 'qris';
    
    // Prepare cart items for order
    $orderItems = [];
    foreach ($cartItems as $item) {
        $orderItems[] = [
            'id' => $item['menu_id'] ?? $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => $item['image'],
            'ice_level' => $item['ice_level'] ?? null,
            'sugar_level' => $item['sugar_level'] ?? null,
            'size' => $item['size'] ?? null
        ];
    }
    
    $recipientData = [
        'name' => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'postal' => $_POST['postal'] ?? ''
    ];
    $orderId = createOrder($currentUser['user_id'], $orderItems, $orderType, $notes, $paymentMethod, $recipientData);

    if ($orderId) {
        // Clear only selected items from cart (both session and database)
        if (!empty($selectedItemsIds)) {
            foreach ($selectedItemsIds as $itemId) {
                removeFromCart($itemId);
            }
        } else {
            clearCart();
        }
        $orderSuccess = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout — Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
  .checkout-layout { max-width:1060px; margin:0 auto; padding:2.5rem 1.25rem; display:grid; grid-template-columns:1fr 340px; gap:2rem; align-items:start; }
  /* Steps */
  .steps { display:flex; align-items:center; gap:0; margin-bottom:2rem; }
  .step { display:flex; align-items:center; gap:.5rem; flex:1; }
  .step-circle { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; transition:all .3s; }
  .step-circle.done    { background:var(--success); color:#fff; }
  .step-circle.active  { background:var(--primary); color:#fff; box-shadow:0 0 0 4px rgba(26,60,46,.15); }
  .step-circle.pending { background:var(--cream-dark); color:var(--text-muted); border:1.5px solid var(--border); }
  .step-label { font-size:.78rem; font-weight:600; color:var(--text-muted); }
  .step-label.active { color:var(--primary); }
  .step-line { flex:1; height:1.5px; background:var(--border); margin:0 .5rem; }
  .step-line.done { background:var(--success); }
  /* Section card */
  .section-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:1.25rem; overflow:hidden; }
  .section-card__header { padding:1.1rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.75rem; }
  .section-card__num { width:26px; height:26px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; flex-shrink:0; }
  .section-card__title { font-size:.95rem; font-weight:700; color:var(--primary); margin:0; }
  .section-card__body { padding:1.5rem; }
  /* Form inputs */
  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; }
  .form-row.single { grid-template-columns:1fr; }
  .form-group label { display:block; font-size:.8rem; font-weight:600; color:var(--text-mid); margin-bottom:.4rem; }
  .form-group input,
  .form-group select,
  .form-group textarea {
    width:100%; background:var(--cream); border:1.5px solid var(--border);
    border-radius:var(--radius-md); padding:.7rem 1rem;
    font-family:var(--font-body); font-size:.88rem; color:var(--text-dark);
    outline:none; transition:border-color .2s, box-shadow .2s;
  }
  .form-group textarea { resize:vertical; min-height:80px; }
  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus { border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(46,107,79,.1); background:var(--white); }
  /* Payment methods */
  .payment-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; }
  .payment-option { display:none; }
  .payment-label {
    display:flex; flex-direction:column; align-items:center; gap:.4rem;
    padding:.9rem .5rem; border:1.5px solid var(--border); border-radius:var(--radius-md);
    cursor:pointer; transition:all .2s; text-align:center;
    background:var(--white);
  }
  .payment-label .pay-icon { font-size:1.5rem; }
  .payment-label .pay-name { font-size:.72rem; font-weight:600; color:var(--text-mid); }
  .payment-option:checked + .payment-label { border-color:var(--primary); background:rgba(26,60,46,.05); }
  .payment-option:checked + .payment-label .pay-name { color:var(--primary); }
  /* Order summary */
  .summary-panel { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem; position:sticky; top:90px; }
  .summary-panel h5 { font-size:.95rem; font-weight:700; color:var(--primary); margin-bottom:1.25rem; }
  .summary-row { display:flex; justify-content:space-between; font-size:.87rem; color:var(--text-mid); margin-bottom:.6rem; }
  .summary-row.total { font-size:1.05rem; font-weight:800; color:var(--primary); margin-top:.75rem; padding-top:.75rem; border-top:2px solid var(--border); margin-bottom:0; }
  .order-mini-item { display:flex; align-items:center; gap:.75rem; margin-bottom:.85rem; padding-bottom:.85rem; border-bottom:1px solid var(--border); }
  .order-mini-item:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
  .order-mini-img { width:48px; height:48px; object-fit:cover; border-radius:var(--radius-sm); flex-shrink:0; }
  .btn-place { width:100%; background:var(--primary); color:#fff; border:none; border-radius:var(--radius-xl); padding:1rem; font-family:var(--font-body); font-size:1rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.5rem; transition:background .2s,transform .15s; margin-top:1.5rem; }
  .btn-place:hover { background:var(--primary-light); transform:translateY(-1px); }
  /* Success modal */
  .success-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
  .success-box { background:var(--white); border-radius:var(--radius-lg); padding:2.5rem; max-width:420px; width:90%; text-align:center; animation:fadeUp .4s ease; }
  .success-icon { font-size:4rem; display:block; margin-bottom:1rem; }
  .success-box h3 { font-family:var(--font-display); color:var(--primary); margin-bottom:.5rem; }
  .success-box p { color:var(--text-muted); font-size:.9rem; margin-bottom:1.5rem; }
  @media(max-width:900px){ .checkout-layout{grid-template-columns:1fr;} .summary-panel{position:static;} }
  @media(max-width:576px){ .form-row{grid-template-columns:1fr;} .payment-grid{grid-template-columns:repeat(2,1fr);} }
  </style>
</head>
<body class="page-wrapper">
<?php include __DIR__ . '/../layouts/navbar.php'; ?>

<!-- Breadcrumb -->
<div style="background:var(--cream-dark);border-bottom:1px solid var(--border);padding:.6rem 0;">
  <div style="max-width:1060px;margin:0 auto;padding:0 1.25rem;font-size:.8rem;color:var(--text-muted);">
    <a href="../home/home.php" style="color:var(--primary);">Beranda</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem;"></i>
    <a href="../cart/cart.php" style="color:var(--primary);">Keranjang</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem;"></i>
    <span>Checkout</span>
  </div>
</div>

<div class="page-content">
<div class="checkout-layout">

  <!-- LEFT: Form -->
  <div>
    <!-- Steps -->
    <div class="steps mb-4 animate-fadeup">
      <div class="step">
        <div class="step-circle done"><i class="bi bi-check-lg"></i></div>
        <span class="step-label">Keranjang</span>
      </div>
      <div class="step-line done"></div>
      <div class="step">
        <div class="step-circle active">2</div>
        <span class="step-label active">Checkout</span>
      </div>
      <div class="step-line"></div>
      <div class="step">
        <div class="step-circle pending">3</div>
        <span class="step-label">Konfirmasi</span>
      </div>
    </div>

    <form method="POST" action="" id="checkoutForm">
      <input type="hidden" name="action" value="place_order">
      <input type="hidden" name="selected_items" value="<?= isset($_POST['selected_items']) ? htmlspecialchars($_POST['selected_items']) : '' ?>">

      <!-- 1. Informasi Penerima -->
      <div class="section-card animate-fadeup">
        <div class="section-card__header">
          <div class="section-card__num">1</div>
          <h5 class="section-card__title">Informasi Penerima</h5>
        </div>
        <div class="section-card__body">
          <div class="form-row">
            <div class="form-group">
              <label>Nama Lengkap *</label>
              <input type="text" name="name" placeholder="Nama penerima" required>
            </div>
            <div class="form-group">
              <label>Nomor HP *</label>
              <input type="tel" name="phone" placeholder="08xx-xxxx-xxxx" required>
            </div>
          </div>
          <div class="form-row single">
            <div class="form-group">
              <label>Alamat Pengiriman *</label>
              <textarea name="address" placeholder="Jl. Nama Jalan No. XX, Kelurahan, Kecamatan..." required></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Kota *</label>
              <input type="text" name="city" placeholder="Kota / Kabupaten" required>
            </div>
            <div class="form-group">
              <label>Kode Pos *</label>
              <input type="text" name="postal" placeholder="12345" maxlength="5" required>
            </div>
          </div>
          <div class="form-row single">
            <div class="form-group">
              <label>Catatan Pesanan (Opsional)</label>
              <input type="text" name="note" placeholder="Misal: tanpa gula, es extra, dll.">
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Metode Pengiriman -->
      <div class="section-card animate-fadeup" style="animation-delay:.08s">
        <div class="section-card__header">
          <div class="section-card__num">2</div>
          <h5 class="section-card__title">Metode Pengiriman</h5>
        </div>
        <div class="section-card__body">
          <?php
          $deliveries = [
            ['priority','⚡','Prioritas (< 20 menit)','Rp 8.000','Pesanan diprioritaskan dan dikirim lebih cepat'],
            ['standard','🛵','Standar (30 menit)','Rp 5.000','Estimasi pengantaran reguler sekitar 30 menit'],
            ['pickup','🏪','Ambil Sendiri','Gratis','Ambil langsung di gerai terdekat'],
          ];
          foreach ($deliveries as $i => $d):
          ?>
          <label style="display:flex;align-items:center;gap:1rem;padding:.85rem 1rem;border:1.5px solid var(--border);border-radius:var(--radius-md);cursor:pointer;margin-bottom:.65rem;transition:all .2s;" class="delivery-opt">
            <input type="radio" name="delivery" value="<?= $d[0] ?>" <?= $d[0] === $defaultDeliveryMethod ? 'checked' : '' ?> style="accent-color:var(--primary);">
            <span style="font-size:1.5rem;"><?= $d[1] ?></span>
            <div style="flex:1;">
              <div style="font-weight:700;font-size:.9rem;color:var(--primary);"><?= $d[2] ?></div>
              <div style="font-size:.75rem;color:var(--text-muted);"><?= $d[4] ?></div>
            </div>
            <span style="font-weight:700;font-size:.88rem;color:var(--primary);"><?= $d[3] ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 3. Metode Pembayaran -->
      <div class="section-card animate-fadeup" style="animation-delay:.15s">
        <div class="section-card__header">
          <div class="section-card__num">3</div>
          <h5 class="section-card__title">Metode Pembayaran</h5>
        </div>
        <div class="section-card__body">
          <div class="payment-grid">
            <?php
            $payments = [
              ['qris',  '📱', 'QRIS'],
              ['gopay', '💚', 'GoPay'],
              ['ovo',   '💜', 'OVO'],
              ['dana',  '🔵', 'DANA'],
              ['bca',   '🏦', 'BCA Transfer'],
              ['cod',   '💵', 'Bayar di Tempat'],
            ];
            foreach ($payments as $i => $p):
            ?>
            <div>
              <input type="radio" name="payment" value="<?= $p[0] ?>" id="pay_<?= $p[0] ?>"
                     class="payment-option" <?= $i===0?'checked':'' ?>>
              <label for="pay_<?= $p[0] ?>" class="payment-label">
                <span class="pay-icon"><?= $p[1] ?></span>
                <span class="pay-name"><?= $p[2] ?></span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </form>
  </div>

  <!-- RIGHT: Order Summary -->
  <div>
    <div class="summary-panel animate-fadeup" style="animation-delay:.1s">
      <h5>Ringkasan Pesanan</h5>

      <!-- Items -->
      <div style="margin-bottom:1.25rem;">
        <?php foreach ($cartItems as $item):
            $productId = $item['menu_id'] ?? $item['id'];
            $productCategory = $productCategories[$productId] ?? '';
            $isFood = ($productCategory === 'makanan');
        ?>
        <div class="order-mini-item">
          <img src="../assets/img/products/<?= $item['image'] ?>"
               class="order-mini-img"
               onerror="this.src='https://placehold.co/48x48/1a3c2e/f0cb7a?text=K'">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.82rem;font-weight:600;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?= htmlspecialchars($item['name']) ?>
            </div>
            <div style="font-size:.75rem;color:var(--text-muted);">x<?= $item['quantity'] ?></div>
            <?php if (!$isFood && (!empty($item['ice_level']) || !empty($item['sugar_level']))): ?>
            <div style="font-size:.7rem;color:var(--text-muted);margin-top:.25rem;">
              <?php
                $options = [];
                if (!empty($item['ice_level'])) $options[] = $item['ice_level'];
                if (!empty($item['sugar_level'])) $options[] = $item['sugar_level'];
                echo implode(' • ', $options);
              ?>
            </div>
            <?php endif; ?>
          </div>
          <div style="font-size:.85rem;font-weight:700;color:var(--primary);flex-shrink:0;">
            <?= formatRupiah($item['price'] * $item['quantity']) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <hr class="k-divider">

      <div class="summary-row" id="deliveryMethodRow">
        <span>Metode Pengiriman</span>
        <span id="deliveryMethodText" style="font-weight:600;color:var(--primary);"><?= $deliveryMethodNames[$defaultDeliveryMethod] ?></span>
      </div>

      <div class="summary-row" id="paymentMethodRow" style="display:none;">
        <span>Metode Pembayaran</span>
        <span id="paymentMethodText" style="font-weight:600;color:var(--primary);"></span>
      </div>

      <div class="summary-row"><span>Subtotal</span><span><?= formatRupiah($subtotal) ?></span></div>
      <div class="summary-row">
        <span>Ongkos Kirim</span>
        <span id="deliveryFeeSummary"><?= $deliveryFee === 0 ? 'Gratis' : formatRupiah($deliveryFee) ?></span>
      </div>
      <div class="summary-row"><span>Pajak (1%)</span><span id="taxSummary"><?= formatRupiah($tax) ?></span></div>
      <div class="summary-row total"><span>Total Bayar</span><span id="totalSummary"><?= formatRupiah($total) ?></span></div>

      <button type="submit" form="checkoutForm" class="btn-place">
        <i class="bi bi-lock-fill"></i> Pesan Sekarang
      </button>

      <div style="text-align:center;font-size:.75rem;color:var(--text-muted);margin-top:.85rem;display:flex;align-items:center;justify-content:center;gap:.4rem;">
        <i class="bi bi-shield-check" style="color:var(--success);"></i>
        Transaksi aman &amp; terenkripsi SSL
      </div>
    </div>
  </div>

</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- Order Success Modal -->
<?php if ($orderSuccess && $orderId): ?>
<div class="success-overlay" id="successOverlay">
  <div class="success-box">
    <span class="success-icon">🎉</span>
    <h3>Pesanan Berhasil!</h3>
    <p>
      Pesanan Anda <strong>#<?= $orderId ?></strong> telah diterima.<br>
      Kami akan segera memproses pesanan Anda.
    </p>
    <div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;margin-bottom:1.5rem;font-size:.85rem;">
      <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;">
        <span style="color:var(--text-muted);">Metode Pembayaran</span>
        <strong style="color:var(--primary);">
          <?= htmlspecialchars($paymentMethodNames[$paymentMethod] ?? $paymentMethod) ?>
        </strong>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;">
        <span style="color:var(--text-muted);">Total Bayar</span>
        <strong style="color:var(--primary);"><?= formatRupiah($total) ?></strong>
      </div>
      <div style="display:flex;justify-content:space-between;">
        <span style="color:var(--text-muted);">Waktu Pesan</span>
        <span><?= date('d M Y, H:i') ?></span>
      </div>
    </div>
    <a href="../history/history.php" class="btn-brand" style="display:block;text-align:center;padding:.85rem;">
      Lacak Pesanan
    </a>
    <a href="../home/home.php" style="display:block;text-align:center;margin-top:.75rem;font-size:.85rem;color:var(--text-muted);">
      Kembali Belanja
    </a>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Helper function format rupiah
function formatRupiah(angka) {
  return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Delivery fees configuration
const deliveryFees = {
  'priority': 8000,
  'standard': 5000,
  'pickup': 0
};

// Payment method names
const paymentMethodNames = {
  'qris': 'QRIS',
  'gopay': 'GoPay',
  'ovo': 'OVO',
  'dana': 'DANA',
  'bca': 'BCA Transfer',
  'cod': 'Bayar di Tempat'
};

// Update summary based on selections
function updateSummary() {
  const subtotal = <?= $subtotal ?>;
  
  // Get selected delivery method and its fee
  const selectedDelivery = document.querySelector('.delivery-opt input:checked');
  const deliveryMethod = selectedDelivery ? selectedDelivery.value : 'same_day';
  let deliveryFee = deliveryFees[deliveryMethod];
  
  // Apply free delivery if subtotal >= 50000 and not pickup
  if (subtotal >= 50000 && deliveryMethod !== 'pickup') {
    deliveryFee = 0;
  }
  
  // Calculate tax and total
  const tax = Math.round(subtotal * 0.01);
  const total = subtotal + deliveryFee + tax;
  
  // Update delivery fee in UI
  const deliveryFeeEl = document.getElementById('deliveryFeeSummary');
  if (deliveryFee === 0) {
    deliveryFeeEl.innerHTML = '<span style="color:var(--success)">Gratis</span>';
  } else {
    deliveryFeeEl.textContent = formatRupiah(deliveryFee);
  }
  
  // Update tax and total
  document.getElementById('taxSummary').textContent = formatRupiah(tax);
  document.getElementById('totalSummary').textContent = formatRupiah(total);
}

// Update payment method display
function updatePaymentDisplay() {
  const selectedPayment = document.querySelector('.payment-option:checked');
  if (selectedPayment) {
    const paymentMethod = selectedPayment.value;
    document.getElementById('paymentMethodRow').style.display = 'flex';
    document.getElementById('paymentMethodText').textContent = paymentMethodNames[paymentMethod] || paymentMethod;
  }
}

// Delivery option highlight and update
document.querySelectorAll('.delivery-opt input').forEach(inp => {
  inp.addEventListener('change', function() {
    document.querySelectorAll('.delivery-opt').forEach(l => l.style.borderColor = 'var(--border)');
    if (this.checked) this.closest('.delivery-opt').style.borderColor = 'var(--primary)';
    updateSummary();
  });
});

// Payment method selection
document.querySelectorAll('.payment-option').forEach(opt => {
  opt.addEventListener('change', updatePaymentDisplay);
});

// Initialize
const checkedDelivery = document.querySelector('.delivery-opt input:checked');
if (checkedDelivery) checkedDelivery.closest('.delivery-opt').style.borderColor = 'var(--primary)';
updateSummary();
updatePaymentDisplay();
</script>
</body>
</html>
