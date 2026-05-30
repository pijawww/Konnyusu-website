<?php
// checkout/checkout.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';
require_once __DIR__ . '/../config/order.php';
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../data/products.php';

requireLogin();

if (isAdmin()) {
    header('Location: ../admin/dashboard/dashboard.php');
    exit;
}

$isBuyNow = isset($_SESSION['buy_now_cart']) && !empty($_SESSION['buy_now_cart']);
$allCartItems = getCartItems();
$selectedItemsIds = [];

if ($isBuyNow) {
    $cartItems = [$_SESSION['buy_now_cart']];
} else {
    if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
        $selectedItemsIds = array_map('intval', explode(',', $_POST['selected_items']));
    }
    $cartItems = [];
    if (!empty($selectedItemsIds)) {
        foreach ($allCartItems as $item) {
            $itemId = isset($item['cart_item_id']) ? (int)$item['cart_item_id'] : (int)$item['id'];
            if (in_array($itemId, $selectedItemsIds)) $cartItems[] = $item;
        }
    } else {
        $cartItems = $allCartItems;
    }
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$deliveryMethodBaseFees = ['priority' => 8000, 'standard' => 5000, 'pickup' => 0];
$defaultDeliveryMethod = 'standard';
$baseDeliveryFee = $deliveryMethodBaseFees[$defaultDeliveryMethod];
$deliveryFee = ($subtotal >= 50000) ? 0 : $baseDeliveryFee;
$tax = (int)round($subtotal * 0.01);
$total = $subtotal + $deliveryFee + $tax;
$cartTotalItems = getCartCount();
$currentUser = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process_payment') {
    $orderType = $_POST['delivery'] ?? $defaultDeliveryMethod;
    $notes = trim($_POST['note'] ?? '');
    $paymentMethod = $_POST['payment'] ?? 'qris';
    $recipientData = [
        'name' => trim($_POST['name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'postal' => trim($_POST['postal'] ?? '')
    ];

    if (empty($recipientData['name']) || empty($recipientData['phone']) || empty($recipientData['address'])) {
        $_SESSION['checkout_error'] = 'Mohon lengkapi semua data pengiriman!';
        header('Location: checkout.php');
        exit;
    }

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

    $orderId = createOrder($currentUser['user_id'], $orderItems, $orderType, $notes, $paymentMethod, $recipientData);

    if ($orderId) {
        $paymentProofUploaded = false;
        if ($paymentMethod !== 'cod') {
            // Debug: cek $_FILES
            error_log("Checkout debug: paymentMethod = $paymentMethod");
            error_log("Checkout debug: FILES = " . print_r($_FILES, true));
            
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../assets/uploads/payment_proofs/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $file = $_FILES['payment_proof'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                error_log("Checkout debug: file type = " . $file['type'] . ", size = " . $file['size']);
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    if (empty($ext)) {
                        // Fallback to get extension from mime type
                        $mimeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
                        $ext = $mimeExtMap[$file['type']] ?? 'jpg';
                    }
                    $newFileName = 'proof_' . $orderId . '_' . time() . '.' . $ext;
                    $targetPath = $uploadDir . $newFileName;
                    error_log("Checkout debug: targetPath = $targetPath");
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        // Berhasil memindahkan file, sekarang update database
                        error_log("Checkout debug: File moved successfully!");
                        $stmt = $pdo->prepare("UPDATE payment SET payment_proof = ?, payment_date = NOW() WHERE order_id = ?");
                        $result = $stmt->execute([$newFileName, $orderId]);
                        error_log("Checkout debug: UPDATE payment result = " . ($result ? 'OK' : 'FAIL'));
                        $paymentProofUploaded = true;
                    } else {
                        error_log("Checkout debug: Failed to move uploaded file!");
                    }
                } else {
                    error_log("Checkout debug: Invalid file type or size!");
                }
            } else {
                error_log("Checkout debug: No file uploaded or upload error! Error code: " . ($_FILES['payment_proof']['error'] ?? 'no FILES'));
            }
        }

        if (!empty($selectedItemsIds)) {
            foreach ($selectedItemsIds as $itemId) removeFromCart($itemId);
        } elseif ($isBuyNow) {
            unset($_SESSION['buy_now_cart']);
        } else {
            clearCart();
        }

        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $orderId;
        // Redirect to same page with success flag to show success modal
        header('Location: checkout.php?success=1&order_id=' . $orderId);
        exit;
    } else {
        $_SESSION['checkout_error'] = 'Gagal membuat pesanan!';
        header('Location: checkout.php');
        exit;
    }
}

$errorMsg = $_SESSION['checkout_error'] ?? '';
unset($_SESSION['checkout_error']);
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
.steps { display:flex; align-items:center; gap:0; margin-bottom:2rem; }
.step { display:flex; align-items:center; gap:.5rem; flex:1; }
.step-circle { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; }
.step-circle.done { background:var(--success); color:#fff; }
.step-circle.active { background:var(--primary); color:#fff; box-shadow:0 0 0 4px rgba(26,60,46,.15); }
.step-circle.pending { background:var(--cream-dark); color:var(--text-muted); border:1.5px solid var(--border); }
.step-label { font-size:.78rem; font-weight:600; color:var(--text-muted); }
.step-label.active { color:var(--primary); }
.step-line { flex:1; height:1.5px; background:var(--border); margin:0 .5rem; }
.step-line.done { background:var(--success); }
.section-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:1.25rem; overflow:hidden; }
.section-card__header { padding:1.1rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.75rem; }
.section-card__num { width:26px; height:26px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; flex-shrink:0; }
.section-card__title { font-size:.95rem; font-weight:700; color:var(--primary); margin:0; }
.section-card__body { padding:1.5rem; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; }
.form-row.single { grid-template-columns:1fr; }
.form-group label { display:block; font-size:.8rem; font-weight:600; color:var(--text-mid); margin-bottom:.4rem; }
.form-group input, .form-group select, .form-group textarea {
    width:100%; background:var(--cream); border:1.5px solid var(--border);
    border-radius:var(--radius-md); padding:.7rem 1rem;
    font-family:var(--font-body); font-size:.88rem; color:var(--text-dark);
    outline:none; transition:border-color .2s;
}
.form-group textarea { resize:vertical; min-height:80px; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color:var(--primary-light); background:var(--white);
}
.payment-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; }
.payment-option { display:none; }
.payment-label {
    display:flex; flex-direction:column; align-items:center; gap:.5rem;
    padding:1rem .5rem; border:1.5px solid var(--border); border-radius:var(--radius-md);
    cursor:pointer; transition:all .2s; text-align:center;
    background:var(--white); justify-content: center; min-height: 90px;
}
.payment-label .pay-icon { font-size:1.5rem; display: flex; align-items: center; justify-content: center; }
.payment-label img { height: 26px; object-fit: contain; }
.payment-label .pay-name { font-size:.72rem; font-weight:600; color:var(--text-mid); margin-top: 4px; }
.payment-option:checked + .payment-label { border-color:var(--primary); background:rgba(43,108,176,.05); }
.payment-option:checked + .payment-label .pay-name { color:var(--primary); }
.summary-panel { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem; position:sticky; top:90px; }
.summary-panel h5 { font-size:.95rem; font-weight:700; color:var(--primary); margin-bottom:1.25rem; }
.summary-row { display:flex; justify-content:space-between; font-size:.87rem; color:var(--text-mid); margin-bottom:.6rem; }
.summary-row.total { font-size:1.05rem; font-weight:800; color:var(--primary); margin-top:.75rem; padding-top:.75rem; border-top:2px solid var(--border); margin-bottom:0; }
.order-mini-item { display:flex; align-items:center; gap:.75rem; margin-bottom:.85rem; padding-bottom:.85rem; border-bottom:1px solid var(--border); }
.order-mini-item:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
.order-mini-img { width:48px; height:48px; object-fit:cover; border-radius:var(--radius-sm); flex-shrink:0; }
.btn-place { width:100%; background:var(--primary); color:#fff; border:none; border-radius:var(--radius-xl); padding:1rem; font-family:var(--font-body); font-size:1rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.5rem; transition:background .2s,transform .15s; margin-top:1.5rem; }
.btn-place:hover { background:var(--primary-light); transform:translateY(-1px); }
/* Modal Sukses */
.success-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:10000; display:none; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
.success-modal-overlay.open { display:flex; }
.success-modal-box { background:var(--white); border-radius:var(--radius-lg); width:100%; max-width:420px; padding:2.5rem 2rem; text-align:center; animation:fadeUp .4s ease; }
.success-modal-icon { width:80px; height:80px; background:linear-gradient(135deg,#ecfaf4,#d4f5e9); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; }
.success-modal-icon i { font-size:2.5rem; color:var(--success); }
.success-modal-title { font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--primary); margin-bottom:.75rem; }
.success-modal-desc { font-size:.9rem; color:var(--text-muted); margin-bottom:.5rem; line-height:1.5; }
.success-modal-order { font-family:monospace; font-weight:700; font-size:1.1rem; color:var(--primary); margin-bottom:1.5rem; }
.success-modal-note { font-size:.8rem; color:var(--text-muted); background:var(--cream); border-radius:var(--radius-md); padding:.85rem 1rem; margin-bottom:1.5rem; border:1px solid var(--border); }
.success-modal-btn { background:var(--primary); color:#fff; border:none; border-radius:var(--radius-xl); padding:.85rem 2rem; font-family:var(--font-body); font-size:.95rem; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:.5rem; transition:background .2s; text-decoration:none; }
.success-modal-btn:hover { background:var(--primary-light); color:#fff; }

/* Payment Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:9999; display:none; align-items:center; justify-content:center; backdrop-filter:blur(4px); }
.modal-overlay.open { display:flex; }
.modal-box { background:var(--white); border-radius:var(--radius-lg); width:100%; max-width:480px; max-height:95vh; overflow-y:auto; animation:fadeUp .3s ease; }
.modal-header { padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.modal-header h5 { font-size:1rem; font-weight:700; color:var(--primary); margin:0; }
.modal-body { padding:1.5rem; }
.modal-footer { padding:1rem 1.5rem; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:.75rem; }
/* QRIS Box - tanpa background hijau */
.qris-display { background: transparent; border-radius: var(--radius-lg); padding: 1.5rem; text-align: center; color: #fff; margin-bottom: 1rem; border: 1px solid var(--border); }
.qris-display img { max-width: 200px; max-height: 200px; margin: 0 auto; border-radius: 12px; border: none; background: #fff; padding: 8px; }
.qris-display p { font-size: .78rem; margin-top: .75rem; opacity: .8; color: var(--text-mid); }
/* Payment Info */
.pay-info-box { background:var(--cream); border-radius:var(--radius-md); padding:1.25rem; margin-bottom:1rem; }
.pay-info-box h6 { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:1rem; display:flex; align-items:center; gap:.4rem; }
.pay-info-row { display:flex; justify-content:space-between; padding:.6rem 0; border-bottom:1px dashed var(--border); font-size:.88rem; }
.pay-info-row:last-of-type { border-bottom:none; }
.pay-info-row span:first-child { color:var(--text-muted); }
.pay-info-row span:last-child { font-weight:700; color:var(--primary); font-family:monospace; letter-spacing:1px; }
/* Upload */
.upload-area { border:2px dashed var(--border); border-radius:var(--radius-md); padding:1.5rem; text-align:center; cursor:pointer; transition:all .2s; background:var(--cream); }
.upload-area:hover { border-color:var(--primary); }
.upload-area i { font-size:2rem; color:var(--text-muted); margin-bottom:.5rem; }
.upload-area p { font-size:.85rem; color:var(--text-mid); margin:0; }
.upload-area.has-file { border-color:var(--success); background:rgba(59,158,124,.05); }
.upload-area.has-file i { color:var(--success); }
@media(max-width:900px){ .checkout-layout{grid-template-columns:1fr;} .summary-panel{position:static;} }
@media(max-width:576px){ .form-row{grid-template-columns:1fr;} .payment-grid{grid-template-columns:repeat(2,1fr);} }
</style>
</head>
<body class="page-wrapper">
<?php include __DIR__ . '/../layouts/navbar.php'; ?>

<div style="background:var(--cream-dark);border-bottom:1px solid var(--border);padding:.6rem 0;">
  <div style="max-width:1060px;margin:0 auto;padding:0 1.25rem;font-size:.8rem;color:var(--text-muted);">
    <a href="../home/home.php" style="color:var(--primary);">Beranda</a>
    <i class="bi bi-chevron-right mx-1"></i>
    <a href="../cart/cart.php" style="color:var(--primary);">Keranjang</a>
    <i class="bi bi-chevron-right mx-1"></i>
    <span>Checkout</span>
  </div>
</div>

<div class="page-content">
<div class="checkout-layout" style="padding-top:2rem;">
  <div>
    <?php if ($errorMsg): ?>
    <div style="background:#fff0f0;border:1px solid #f5b8b8;color:var(--danger);border-radius:var(--radius-md);padding:.85rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
      <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div class="steps mb-4 animate-fadeup">
      <div class="step"><div class="step-circle done"><i class="bi bi-check-lg"></i></div><span class="step-label">Keranjang</span></div>
      <div class="step-line done"></div>
      <div class="step"><div class="step-circle active">2</div><span class="step-label active">Checkout</span></div>
      <div class="step-line"></div>
      <div class="step"><div class="step-circle pending">3</div><span class="step-label">Selesai</span></div>
    </div>

    <form method="POST" enctype="multipart/form-data" id="checkoutForm">
      <input type="hidden" name="action" value="process_payment">
      <input type="hidden" name="selected_items" value="<?= isset($_POST['selected_items']) ? htmlspecialchars($_POST['selected_items']) : '' ?>">

      <!-- Recipient Info -->
      <div class="section-card animate-fadeup">
        <div class="section-card__header">
          <div class="section-card__num">1</div>
          <h5 class="section-card__title">Data Penerima</h5>
        </div>
        <div class="section-card__body">
          <div class="form-row">
            <div class="form-group">
              <label>Nama Lengkap *</label>
              <input type="text" name="name" placeholder="Nama penerima" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>No. HP *</label>
              <input type="tel" name="phone" placeholder="08xxxxxxxxxx" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row single">
            <div class="form-group">
              <label>Alamat *</label>
              <textarea name="address" placeholder="Jl. Nama Jalan No. XX, Kelurahan, Kecamatan, Kota" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Kota/Kabupaten *</label>
              <input type="text" name="city" placeholder="Contoh: Jakarta Selatan" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Kode Pos *</label>
              <input type="text" name="postal" placeholder="12345" maxlength="5" required value="<?= htmlspecialchars($_POST['postal'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row single">
            <div class="form-group">
              <label>Catatan (opsional)</label>
              <input type="text" name="note" placeholder="Tidak pakai gula, es extra, dll." value="<?= htmlspecialchars($_POST['note'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Shipping -->
      <div class="section-card animate-fadeup" style="animation-delay:.05s">
        <div class="section-card__header">
          <div class="section-card__num">2</div>
          <h5 class="section-card__title">Metode Pengiriman</h5>
        </div>
        <div class="section-card__body">
          <?php
          $deliveries = [
            ['priority', 'bi-lightning-charge-fill', 'Prioritas (< 20 menit)', 'Rp 8.000', '#f59e0b'],
            ['standard', 'bi-bicycle', 'Standar (30 menit)', 'Rp 5.000', 'var(--primary)'],
            ['pickup', 'bi-shop', 'Ambil Sendiri', 'Gratis', 'var(--primary)'],
          ];
          foreach ($deliveries as $i => $d):
          ?>
          <label style="display:flex;align-items:center;gap:1.25rem;padding:.85rem 1rem;border:1.5px solid var(--border);border-radius:var(--radius-md);cursor:pointer;margin-bottom:.65rem;transition:all .2s;" class="delivery-opt">
            <input type="radio" name="delivery" value="<?= $d[0] ?>" <?= $d[0] === $defaultDeliveryMethod ? 'checked' : '' ?> style="accent-color:var(--primary); flex-shrink:0;">
            <i class="bi <?= $d[1] ?>" style="font-size:1.6rem; color:<?= $d[4] ?>; line-height:1; flex-shrink:0;"></i>
            <div style="flex:1;">
              <div style="font-weight:700;font-size:.9rem;color:var(--primary);"><?= $d[2] ?></div>
            </div>
            <span style="font-weight:700;font-size:.88rem;color:var(--primary);flex-shrink:0;"><?= $d[3] ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="section-card animate-fadeup" style="animation-delay:.1s">
        <div class="section-card__header">
          <div class="section-card__num">3</div>
          <h5 class="section-card__title">Metode Pembayaran</h5>
        </div>
        <div class="section-card__body">
          <div class="payment-grid">
            <?php
            $payments = [
              ['qris', 'bi-qr-code-scan', 'QRIS'],
              ['gopay', 'bi-wallet2', 'GoPay'],
              ['ovo', 'bi-wallet2', 'OVO'],
              ['dana', 'bi-wallet2', 'DANA'],
              ['bca', 'bi-bank', 'BCA Transfer'],
              ['cod', 'bi-cash', 'COD'],
            ];
            foreach ($payments as $i => $p):
            ?>
            <input type="radio" name="payment" value="<?= $p[0] ?>" id="pay_<?= $p[0] ?>" class="payment-option" <?= $i === 0 ? 'checked' : '' ?>>
            <label for="pay_<?= $p[0] ?>" class="payment-label">
              <span class="pay-icon"><i class="bi <?= $p[1] ?>" style="color:var(--primary);"></i></span>
              <span class="pay-name"><?= $p[2] ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div>
    <div class="summary-panel animate-fadeup" style="animation-delay:.15s">
      <h5>Ringkasan Pesanan</h5>
      <div style="margin-bottom:1.25rem;">
        <?php foreach ($cartItems as $item):
          $productId = $item['menu_id'] ?? $item['id'];
          $productCategory = $productCategories[$productId] ?? '';
          $isFood = ($productCategory === 'makanan');
        ?>
        <div class="order-mini-item">
          <img src="../assets/img/products/<?= $item['image'] ?>" class="order-mini-img" onerror="this.src='https://placehold.co/48x48/1a3c2e/f0cb7a?text=K'">
          <div style="flex:1;min-width:0;">
            <div style="font-size:.82rem;font-weight:600;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($item['name']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted);">x<?= $item['quantity'] ?></div>
          </div>
          <div style="font-size:.85rem;font-weight:700;color:var(--primary);flex-shrink:0;"><?= formatRupiah($item['price'] * $item['quantity']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <hr class="k-divider">
      <div class="summary-row"><span>Subtotal</span><span><?= formatRupiah($subtotal) ?></span></div>
      <div class="summary-row"><span>Ongkos Kirim</span><span id="delFee"><?= $deliveryFee === 0 ? 'Gratis' : formatRupiah($deliveryFee) ?></span></div>
      <div class="summary-row"><span>Pajak (1%)</span><span><?= formatRupiah($tax) ?></span></div>
      <div class="summary-row total"><span>Total Bayar</span><span id="totalDisplay"><?= formatRupiah($total) ?></span></div>
      <button type="button" onclick="openPaymentModal()" class="btn-place">
        <i class="bi bi-credit-card"></i> Pilih Metode Pembayaran
      </button>
      <div style="text-align:center;font-size:.75rem;color:var(--text-muted);margin-top:.85rem;display:flex;align-items:center;justify-content:center;gap:.4rem;">
        <i class="bi bi-shield-check" style="color:var(--success);"></i>
        Pembayaran aman &amp; terenkripsi
      </div>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- Payment Modal -->
<div class="modal-overlay" id="paymentModal">
  <div class="modal-box">
    <div class="modal-header">
      <h5 id="modalTitle"><i class="bi bi-qr-code-scan me-2"></i>Pembayaran</h5>
      <button onclick="closeModal()" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-footer">
      <button type="button" onclick="closeModal()" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Batal</button>
      <button type="button" onclick="submitOrder()" class="btn-brand" style="font-size:.85rem;padding:.55rem 1.2rem;" id="submitBtn">
        <i class="bi bi-check-lg me-1"></i>Konfirmasi Bayar
      </button>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="success-modal-overlay" id="successModal">
  <div class="success-modal-box">
    <div class="success-modal-icon">
      <i class="bi bi-check-lg"></i>
    </div>
    <div class="success-modal-title">Pesanan Berhasil!</div>
    <div class="success-modal-desc">Terima kasih sudah memesan di Konnyusu</div>
    <div class="success-modal-order" id="successOrderId">#12345</div>
    <div class="success-modal-note">
      <i class="bi bi-info-circle me-1"></i>
      Pesanan Anda sedang menunggu pembayaran. Silakan upload bukti pembayaran di halaman riwayat pesanan.
    </div>
    <a href="../history/history.php" class="success-modal-btn">
      <i class="bi bi-list-check"></i> Lihat Riwayat Pesanan
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Config - GANTI DENGAN DATA YANG BENAR
const walletNumbers = {gopay: '0812-3456-7890', ovo: '0812-3456-7890', dana: '0812-3456-7890'};
const bankAccount = {bank: 'BCA', norek: '1234567890', atasnama: 'Konnyusu Store'};
const deliveryFees = {priority: 8000, standard: 5000, pickup: 0};
const paymentIcons = {qris:'bi-qr-code-scan', gopay:'bi-wallet2', ovo:'bi-wallet2', dana:'bi-wallet2', bca:'bi-bank', cod:'bi-cash'};

const subtotal = <?= $subtotal ?>;
const tax = <?= $tax ?>;
let selectedPayment = 'qris';
let deliveryFee = <?= $deliveryFee ?>;

function formatRup(angka) { return 'Rp ' + angka.toLocaleString('id-ID'); }

function updateTotals() {
  const sel = document.querySelector('.delivery-opt input:checked');
  const method = sel ? sel.value : 'standard';
  deliveryFee = deliveryFees[method] || 5000;
  if (subtotal >= 50000 && method !== 'pickup') deliveryFee = 0;
  const total = subtotal + deliveryFee + tax;
  document.getElementById('delFee').innerHTML = deliveryFee === 0 ? 'Gratis' : formatRup(deliveryFee);
  document.getElementById('totalDisplay').textContent = formatRup(total);
  return total;
}

document.querySelectorAll('.delivery-opt input').forEach(i => i.addEventListener('change', updateTotals));

function openModal() { document.getElementById('paymentModal').classList.add('open'); }
function closeModal() { document.getElementById('paymentModal').classList.remove('open'); }
document.getElementById('paymentModal').addEventListener('click', e => { if (e.target === document.getElementById('paymentModal')) closeModal(); });

function openPaymentModal() {
  const form = document.getElementById('checkoutForm');
  if (!form.checkValidity()) { form.reportValidity(); return; }

  const sel = document.querySelector('.payment-option:checked');
  selectedPayment = sel ? sel.value : 'qris';
  const total = updateTotals();
  const icon = paymentIcons[selectedPayment] || 'bi-credit-card';
  document.getElementById('modalTitle').innerHTML = '<i class="bi ' + icon + ' me-2"></i>' + getPaymentTitle(selectedPayment);
  document.getElementById('modalBody').innerHTML = buildModalContent(selectedPayment, total);
  openModal();
}

function getPaymentTitle(m) {
  const t = {qris:'QRIS', gopay:'GoPay', ovo:'OVO', dana:'DANA', bca:'Transfer Bank BCA', cod:'Bayar di Tempat'};
  return t[m] || m;
}

function buildModalContent(method, total) {
  let html = '';
  // Total
  html += '<div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;text-align:center;">';
  html += '<div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.35rem;">Total Pembayaran</div>';
  html += '<div style="font-family:var(--font-display);font-size:1.75rem;font-weight:800;color:var(--primary);">' + formatRup(total) + '</div>';
  html += '</div>';

  if (method === 'qris') {
    // QRIS Box - tanpa background hijau
    html += '<div class="qris-display">';
    html += '<img src="../assets/img/qris.png" alt="QRIS" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'block\'">';
    html += '<div style="display:none;font-size:1.5rem;padding:2rem;background:#fff;color:var(--text-mid);border-radius:12px;">QRIS</div>';
    html += '<p style="color:var(--text-mid);">Pindai kode QR di atas</p>';
    html += '<p style="opacity:.6;font-size:.7rem;margin-top:.35rem;color:var(--text-muted);">Bisa pakai GoPay, OVO, DANA, dll.</p>';
    html += '</div>';
  } else if (method === 'gopay') {
    html += '<div class="pay-info-box">';
    html += '<h6><i class="bi bi-wallet2 me-1"></i>Transfer ke GoPay</h6>';
    html += '<div class="pay-info-row"><span>No. HP</span><span>' + walletNumbers.gopay + '</span></div>';
    html += '<div class="pay-info-row"><span>Atas Nama</span><span>Konnyusu</span></div>';
    html += '<div class="pay-info-row"><span>Nominal</span><span style="color:var(--accent);font-weight:800;">' + formatRup(total) + '</span></div>';
    html += '</div>';
  } else if (method === 'ovo') {
    html += '<div class="pay-info-box">';
    html += '<h6><i class="bi bi-wallet2 me-1"></i>Transfer ke OVO</h6>';
    html += '<div class="pay-info-row"><span>No. HP</span><span>' + walletNumbers.ovo + '</span></div>';
    html += '<div class="pay-info-row"><span>Atas Nama</span><span>Konnyusu</span></div>';
    html += '<div class="pay-info-row"><span>Nominal</span><span style="color:var(--accent);font-weight:800;">' + formatRup(total) + '</span></div>';
    html += '</div>';
  } else if (method === 'dana') {
    html += '<div class="pay-info-box">';
    html += '<h6><i class="bi bi-wallet2 me-1"></i>Transfer ke DANA</h6>';
    html += '<div class="pay-info-row"><span>No. HP</span><span>' + walletNumbers.dana + '</span></div>';
    html += '<div class="pay-info-row"><span>Atas Nama</span><span>Konnyusu</span></div>';
    html += '<div class="pay-info-row"><span>Nominal</span><span style="color:var(--accent);font-weight:800;">' + formatRup(total) + '</span></div>';
    html += '</div>';
  } else if (method === 'bca') {
    html += '<div class="pay-info-box">';
    html += '<h6><i class="bi bi-bank me-1"></i>Transfer Bank BCA</h6>';
    html += '<div class="pay-info-row"><span>Bank</span><span>' + bankAccount.bank + '</span></div>';
    html += '<div class="pay-info-row"><span>No. Rekening</span><span style="font-size:1rem;">' + bankAccount.norek + '</span></div>';
    html += '<div class="pay-info-row"><span>Atas Nama</span><span>' + bankAccount.atasnama + '</span></div>';
    html += '<div class="pay-info-row"><span>Nominal</span><span style="color:var(--accent);font-weight:800;">' + formatRup(total) + '</span></div>';
    html += '</div>';
  } else if (method === 'cod') {
    html += '<div style="background:#ecfaf4;border-radius:var(--radius-md);padding:1.5rem;text-align:center;border:1px solid #a3e0c8;margin-bottom:1rem;">';
    html += '<i class="bi bi-cash" style="font-size:2.5rem;color:var(--success);"></i>';
    html += '<div style="font-weight:700;color:var(--primary);margin:.75rem 0 .25rem;font-family:var(--font-display);font-size:1.1rem;">Bayar di Tempat</div>';
    html += '<p style="font-size:.82rem;color:var(--text-muted);margin:0;">Siapkan uang cash sejumlah <strong>' + formatRup(total) + '</strong> saat pesanan tiba.</p>';
    html += '</div>';
  }

  // Upload Proof (except COD)
  if (method !== 'cod') {
    html += '<div class="form-group">';
    html += '<label style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem;"><i class="bi bi-cloud-upload"></i>Upload Bukti Pembayaran</label>';
    html += '<div class="upload-area" id="upArea" onclick="document.getElementById(\'proofFile\').click()">';
    html += '<i class="bi bi-image" style="font-size:1.75rem;color:var(--text-muted);margin-bottom:.4rem;display:block;"></i>';
    html += '<p style="margin:0;">Klik untuk pilih foto</p>';
    html += '<span style="font-size:.72rem;color:var(--text-muted);">JPG/PNG, Maks 5MB</span>';
    html += '<input type="file" id="proofFile" name="payment_proof" form="checkoutForm" accept="image/jpeg,image/png" style="display:none;" onchange="handleFile(this)">';
    html += '</div></div>';
  }

  return html;
}

function handleFile(input) {
  const area = document.getElementById('upArea');
  if (input.files && input.files[0]) {
    const f = input.files[0];
    // Validasi: hanya JPG/PNG, maks 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (!allowedTypes.includes(f.type)) { 
      alert('Format harus JPG/PNG'); 
      input.value=''; 
      return; 
    }
    if (f.size > maxSize) { 
      alert('Maksimal ukuran file 5MB'); 
      input.value=''; 
      return; 
    }

    // Tampilkan preview gambar tanpa mengganti input asli
    const reader = new FileReader();
    reader.onload = function(e) {
      area.classList.add('has-file');
      // Update area dengan preview, tapi simpan input asli!
      area.innerHTML = '';
      
      // Buat wrapper untuk preview
      const previewWrapper = document.createElement('div');
      previewWrapper.style.textAlign = 'center';
      previewWrapper.style.cursor = 'pointer';
      previewWrapper.onclick = function() { input.click(); };
      
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '100%';
      img.style.maxHeight = '120px';
      img.style.objectFit = 'contain';
      img.style.borderRadius = '8px';
      img.style.marginBottom = '.4rem';
      
      const fileName = document.createElement('p');
      fileName.style.color = 'var(--success)';
      fileName.style.fontWeight = '600';
      fileName.style.margin = '0 0 .2rem 0';
      fileName.textContent = f.name;
      
      const hint = document.createElement('span');
      hint.style.fontSize = '.72rem';
      hint.style.color = 'var(--text-muted)';
      hint.textContent = 'Klik untuk ganti';
      
      previewWrapper.appendChild(img);
      previewWrapper.appendChild(fileName);
      previewWrapper.appendChild(hint);
      area.appendChild(previewWrapper);
      area.appendChild(input); // Masukkan input asli kembali ke area
    };
    reader.readAsDataURL(f);
  }
}

function submitOrder() {
  // Cek apakah ada file
  const fileInput = document.getElementById('proofFile');
  const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

  // Validasi upload bukti untuk semua metode non-COD
  if (selectedPayment !== 'cod' && !hasFile) {
    alert('Upload bukti pembayaran dulu!');
    return;
  }

  // Close payment modal first
  closeModal();

  // Submit the form
  document.getElementById('checkoutForm').submit();
}

// Check for success flag from URL (after form submission)
if (window.location.search.includes('success=1')) {
  const orderIdFromUrl = new URLSearchParams(window.location.search).get('order_id');
  if (orderIdFromUrl) {
    document.getElementById('successOrderId').textContent = '#' + orderIdFromUrl;
    document.getElementById('successModal').classList.add('open');
    // Clean up URL without reload
    history.replaceState({}, '', window.location.pathname);
  }
}
</script>
</body>
</html>
