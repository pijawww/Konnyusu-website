<?php
// cart/cart.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';
include __DIR__ . '/../data/products.php';

requireLogin();

$cartItems = getCartItems();
$cartTotalItems = getCartCount();

// Calculate totals (we'll do this via JS too)
$subtotal  = getCartTotal();
$deliveryFee = $subtotal >= 50000 ? 0 : 5000;
$tax         = (int)round($subtotal * 0.01);
$total       = $subtotal + $deliveryFee + $tax;
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
    grid-template-columns: 30px 80px 1fr auto;
    gap: 1rem;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--border);
    align-items: center;
    transition: background .2s;
  }
  .cart-item:last-child { border-bottom: none; }
  .cart-item:hover { background: var(--cream); }
  .item-checkbox {
    width: 20px; height: 20px; cursor: pointer; accent-color: var(--primary);
  }
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
  .btn-checkout:disabled {
    background: var(--border);
    cursor: not-allowed;
    transform: none;
  }
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
  .select-all-container {
    display: flex; align-items: center; gap: .5rem;
  }
  .select-all-container label {
    font-size: .85rem; color: var(--text-mid); cursor: pointer;
    margin: 0;
  }
  @media (max-width: 900px) {
    .cart-layout { grid-template-columns: 1fr; }
    .summary-panel { position: static; }
    .cart-item { grid-template-columns: 30px 60px 1fr auto; gap: .75rem; }
    .cart-item__img { width: 60px; height: 60px; }
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
          <div class="select-all-container">
            <input type="checkbox" id="selectAll" class="item-checkbox" onchange="toggleSelectAll()">
            <label for="selectAll">Pilih Semua</label>
          </div>
          <a href="#" onclick="openClearCartModal(); return false;"
             style="font-size:.8rem;color:var(--danger);text-decoration:none;">
            <i class="bi bi-trash3"></i> Kosongkan
          </a>
        </div>

        <?php foreach ($cartItems as $index => $item): 
            $itemId = $item['cart_item_id'] ?? $index;
            $productId = $item['menu_id'] ?? $item['id'];
        ?>
        <div class="cart-item" data-item-id="<?= $itemId ?>" data-price="<?= $item['price'] ?>" data-qty="<?= $item['quantity'] ?>">
          <input type="checkbox" class="item-checkbox item-chk" value="<?= $itemId ?>" onchange="updateSummary()">
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

            <!-- ORDER ITEM DETAIL - Editable -->
            <div style="margin-top:.6rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
              <select class="cart-option-select" onchange="updateCartOption('<?= $itemId ?>', 'ice_level', this.value)" style="padding:.2rem .5rem;border:1px solid var(--border);border-radius:20px;font-size:.72rem;background:var(--cream);color:var(--text-mid);outline:none;cursor:pointer;">
                <option value="Normal Ice" <?= ($item['ice_level'] ?? '') === 'Normal Ice' ? 'selected' : '' ?>>Normal Ice</option>
                <option value="Less Ice" <?= ($item['ice_level'] ?? '') === 'Less Ice' ? 'selected' : '' ?>>Less Ice</option>
                <option value="No Ice" <?= ($item['ice_level'] ?? '') === 'No Ice' ? 'selected' : '' ?>>No Ice</option>
              </select>
              <select class="cart-option-select" onchange="updateCartOption('<?= $itemId ?>', 'sugar_level', this.value)" style="padding:.2rem .5rem;border:1px solid var(--border);border-radius:20px;font-size:.72rem;background:var(--cream);color:var(--text-mid);outline:none;cursor:pointer;">
                <option value="Normal" <?= ($item['sugar_level'] ?? '') === 'Normal' ? 'selected' : '' ?>>Normal</option>
                <option value="Less Sugar" <?= ($item['sugar_level'] ?? '') === 'Less Sugar' ? 'selected' : '' ?>>Less Sugar</option>
                <option value="Extra Sweet" <?= ($item['sugar_level'] ?? '') === 'Extra Sweet' ? 'selected' : '' ?>>Extra Sweet</option>
              </select>
            </div>
          </div>
          <div class="cart-item__right">
            <div class="cart-item__subtotal" data-item-subtotal="<?= $itemId ?>">
              <?= formatRupiah($item['price'] * $item['quantity']) ?>
            </div>
            <!-- Quantity Controls -->
            <div class="qty-ctrl" style="margin-top:.6rem;">
              <a href="#" class="qty-btn" onclick="updateQty('<?= $itemId ?>', -1); return false;">−</a>
              <span class="qty-val" id="qty-<?= $itemId ?>"><?= $item['quantity'] ?></span>
              <a href="#" class="qty-btn" onclick="updateQty('<?= $itemId ?>', 1); return false;">+</a>
            </div>
            <button type="button" class="btn-remove" onclick="openDeleteModal('<?= $itemId ?>')">
              <i class="bi bi-x"></i> Hapus
            </button>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      <?php if (!empty($cartItems)): ?>
        <!-- Hidden form for delete -->
        <form id="deleteForm" method="POST" action="remove-from-cart.php" style="display:none;">
          <input type="hidden" name="id" id="deleteItemId">
        </form>
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
        <span>Subtotal (<span id="selectedCount">0</span> item)</span>
        <span id="subtotalPrice">Rp 0</span>
      </div>
      <div class="summary-row">
        <span>Ongkos Kirim</span>
        <span id="deliveryFee">Rp 0</span>
      </div>
      <div class="summary-row">
        <span>Pajak (1%)</span>
        <span id="taxPrice">Rp 0</span>
      </div>
      <div class="summary-row total">
        <span>Total</span>
        <span id="totalPrice">Rp 0</span>
      </div>

      <!-- Checkout Btn -->
      <form id="checkoutForm" method="POST" action="../checkout/checkout.php" style="margin-top:1.25rem;">
        <input type="hidden" name="selected_items" id="selectedItemsInput" value="">
        <button type="submit" id="checkoutBtn" class="btn-checkout" disabled>
          <i class="bi bi-lock-fill"></i> Lanjut ke Pembayaran
        </button>
      </form>

      <div id="deliveryNote" class="delivery-note mt-3" style="display:none;">
      </div>

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

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
  <div style="background:var(--white);border-radius:var(--radius-lg);padding:2rem;max-width:380px;width:90%;text-align:center;animation:fadeUp .3s;">
    <div style="font-size:3rem;margin-bottom:1rem;">🗑️</div>
    <h5 style="font-family:var(--font-display);color:var(--primary);margin-bottom:.5rem;">Hapus Item?</h5>
    <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:1.5rem;">Item akan dihapus dari keranjang. Kamu bisa menambahkannya lagi kapan saja.</p>
    <div style="display:flex;gap:.75rem;justify-content:center;">
      <button onclick="closeDeleteModal()" style="flex:1;padding:.7rem;border:1.5px solid var(--border);border-radius:var(--radius-xl);background:var(--white);font-weight:600;cursor:pointer;color:var(--text-mid);">Batal</button>
      <button onclick="confirmDelete()" style="flex:1;padding:.7rem;border:none;border-radius:var(--radius-xl);background:var(--danger);color:#fff;font-weight:600;cursor:pointer;">Hapus</button>
    </div>
  </div>
</div>

<!-- Clear Cart Confirmation Modal -->
<div class="modal-overlay" id="clearCartModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
  <div style="background:var(--white);border-radius:var(--radius-lg);padding:2rem;max-width:380px;width:90%;text-align:center;animation:fadeUp .3s;">
    <div style="font-size:3rem;margin-bottom:1rem;">🛒</div>
    <h5 style="font-family:var(--font-display);color:var(--primary);margin-bottom:.5rem;">Kosongkan Keranjang?</h5>
    <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:1.5rem;">Semua item di keranjang akan dihapus. Apakah kamu yakin?</p>
    <div style="display:flex;gap:.75rem;justify-content:center;">
      <button onclick="closeClearCartModal()" style="flex:1;padding:.7rem;border:1.5px solid var(--border);border-radius:var(--radius-xl);background:var(--white);font-weight:600;cursor:pointer;color:var(--text-mid);">Batal</button>
      <button onclick="confirmClearCart()" style="flex:1;padding:.7rem;border:none;border-radius:var(--radius-xl);background:var(--danger);color:#fff;font-weight:600;cursor:pointer;">Kosongkan</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize - don't check all by default
document.addEventListener('DOMContentLoaded', function() {
  // Just update the summary with no items selected initially
  updateSummary();
});

let deleteTargetId = null;

function openDeleteModal(itemId) {
  console.log('Opening delete modal for item:', itemId);
  deleteTargetId = itemId;
  const modal = document.getElementById('deleteModal');
  modal.style.display = 'flex';
  modal.style.position = 'fixed';
  modal.style.inset = '0';
}

function closeDeleteModal() {
  console.log('Closing delete modal');
  deleteTargetId = null;
  const modal = document.getElementById('deleteModal');
  modal.style.display = 'none';
}

function confirmDelete() {
  console.log('Confirming delete for item:', deleteTargetId);
  if (deleteTargetId) {
    document.getElementById('deleteItemId').value = deleteTargetId;
    document.getElementById('deleteForm').submit();
  } else {
    console.error('No delete target set!');
  }
}

// Clear Cart Modal Functions
function openClearCartModal() {
  const modal = document.getElementById('clearCartModal');
  modal.style.display = 'flex';
}

function closeClearCartModal() {
  const modal = document.getElementById('clearCartModal');
  modal.style.display = 'none';
}

function confirmClearCart() {
  // Redirect to clear-cart.php to clear all items
  window.location.href = 'clear-cart.php';
}

// Close modal on backdrop click
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeDeleteModal();
});

// Update cart option (ice_level, sugar_level) via AJAX
function updateCartOption(itemId, optionType, value) {
  fetch('update-cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'id=' + encodeURIComponent(itemId) + '&' + optionType + '=' + encodeURIComponent(value)
  });
}

// Quantity update
function updateQty(itemId, change) {
  const qtyEl = document.getElementById('qty-' + itemId);
  let newQty = parseInt(qtyEl.textContent) + change;
  if (newQty < 1) newQty = 1;
  if (newQty > 99) newQty = 99;

  qtyEl.textContent = newQty;

  // Update data attribute
  const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
  if (cartItem) {
    cartItem.dataset.qty = newQty;
    // Update price
    const price = parseInt(cartItem.dataset.price);
    const newSubtotal = price * newQty;
    const subtotalEl = cartItem.querySelector('[data-item-subtotal]');
    if (subtotalEl) {
      subtotalEl.textContent = formatRupiah(newSubtotal);
    }
    // Update data-qty attribute
    cartItem.setAttribute('data-qty', newQty);
  }

  updateSummary();

  // Update quantity in session via AJAX
  fetch('update-cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'id=' + encodeURIComponent(itemId) + '&quantity=' + newQty
  });
}

function formatRupiah(angka) {
  return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function toggleSelectAll() {
  const selectAll = document.getElementById('selectAll');
  const checkboxes = document.querySelectorAll('.item-chk');
  checkboxes.forEach(cb => cb.checked = selectAll.checked);
  updateSummary();
}

function updateSummary() {
  const checkboxes = document.querySelectorAll('.item-chk');
  const selectedItems = [];
  let subtotal = 0;
  let count = 0;
  
  checkboxes.forEach(cb => {
    if (cb.checked) {
      const item = cb.closest('.cart-item');
      const price = parseInt(item.dataset.price);
      const qty = parseInt(item.dataset.qty);
      const itemTotal = price * qty;
      subtotal += itemTotal;
      count += qty;
      selectedItems.push(cb.value);
    }
  });
  
  const deliveryFee = subtotal >= 50000 ? 0 : 5000;
  const tax = Math.round(subtotal * 0.01);
  const total = subtotal + deliveryFee + tax;
  
  // Update UI
  document.getElementById('selectedCount').textContent = count;
  document.getElementById('subtotalPrice').textContent = formatRupiah(subtotal);
  document.getElementById('deliveryFee').textContent = deliveryFee === 0 ? 'Gratis!' : formatRupiah(deliveryFee);
  document.getElementById('taxPrice').textContent = formatRupiah(tax);
  document.getElementById('totalPrice').textContent = formatRupiah(total);
  
  // Update checkout button
  const checkoutBtn = document.getElementById('checkoutBtn');
  checkoutBtn.disabled = selectedItems.length === 0;
  
  // Update selected items input
  document.getElementById('selectedItemsInput').value = selectedItems.join(',');
  
  // Update delivery note
  const deliveryNote = document.getElementById('deliveryNote');
  if (selectedItems.length > 0) {
    if (subtotal < 50000) {
      const diff = 50000 - subtotal;
      deliveryNote.innerHTML = '<i class="bi bi-truck"></i><span>Tambah ' + formatRupiah(diff) + ' lagi untuk gratis ongkir!</span>';
      deliveryNote.style.display = 'flex';
    } else {
      deliveryNote.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>Selamat! Kamu mendapat gratis ongkir.</span>';
      deliveryNote.style.display = 'flex';
    }
  } else {
    deliveryNote.style.display = 'none';
  }
  
  // Update select all checkbox
  const selectAll = document.getElementById('selectAll');
  const allChecked = Array.from(checkboxes).every(cb => cb.checked);
  selectAll.checked = allChecked;
}
</script>
</body>
</html>
