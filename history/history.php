<?php
// history/history.php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/order.php';
include __DIR__ . '/../data/products.php';

// Require login
requireLogin();

$cartTotalItems = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) $cartTotalItems += $item['quantity'];
}

$currentUser = getCurrentUser();
$ordersData = getUserOrders($currentUser['user_id']);

// Mark all orders as viewed by user
foreach ($ordersData as $ord) {
    markOrderAsViewedByUser($ord['order_id'], $currentUser['user_id']);
}

// Process orders for display
$orders = [];
foreach ($ordersData as $ord) {
    $orderItems = getOrderItems($ord['order_id']);
    $items = [];
    foreach ($orderItems as $oi) {
        $items[] = [
            'name' => $oi['name'] ?? 'Produk',
            'qty' => $oi['quantity'],
            'price' => $oi['price'],
            'image' => $oi['image'] ?? ''
        ];
    }
    
    // Map status
    $statusMap = [
        'pending' => 'Diproses',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'shipped' => 'Dikirim'
    ];
    
    $status = isset($statusMap[$ord['order_status']]) ? $statusMap[$ord['order_status']] : $ord['order_status'];
    
    $orders[] = [
        'id' => '#' . $ord['order_id'],
        'order_id' => $ord['order_id'],
        'date' => date('d M Y, H:i', strtotime($ord['order_date'])),
        'status' => $status,
        'order_status' => $ord['order_status'],
        'items' => $items,
        'total' => $ord['total'],
        'payment' => 'QRIS',
        'rating' => null
    ];
}

$statusBadge = [
    'Selesai'    => 'k-badge-green',
    'Diproses'   => 'k-badge-accent',
    'Dikirim'    => 'k-badge-gray',
    'Dibatalkan' => 'k-badge-red',
];
$statusIcon = [
    'Selesai'    => 'bi-check-circle-fill',
    'Diproses'   => 'bi-clock-fill',
    'Dikirim'    => 'bi-truck',
    'Dibatalkan' => 'bi-x-circle-fill',
];

// Calculate stats
$totalOrders = count($orders);
$completedOrders = count(array_filter($orders, fn($o) => $o['status']==='Selesai'));
$totalSpent = array_sum(array_column(array_filter($orders, fn($o) => $o['status']==='Selesai'), 'total'));

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pesanan — Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
  .history-wrap { max-width:820px; margin:0 auto; padding:2.5rem 1.25rem; }
  .page-header { margin-bottom:2rem; }
  .page-header h1 { font-size:1.85rem; font-weight:700; color:var(--primary); margin-bottom:.3rem; }
  .page-header p  { color:var(--text-muted); font-size:.9rem; }
  /* Filter tabs */
  .filter-tabs { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.75rem; }
  .filter-tab { padding:.45rem 1rem; border-radius:40px; border:1.5px solid var(--border); background:var(--white); font-size:.82rem; font-weight:500; color:var(--text-mid); cursor:pointer; transition:all .2s; }
  .filter-tab:hover { border-color:var(--primary); color:var(--primary); }
  .filter-tab.active { background:var(--primary); border-color:var(--primary); color:#fff; }
  /* Order card */
  .order-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:1.25rem; overflow:hidden; transition:box-shadow .2s; }
  .order-card:hover { box-shadow:var(--shadow-md); }
  .order-card__header { padding:1rem 1.35rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
  .order-card__id { font-family:monospace; font-weight:700; font-size:.95rem; color:var(--primary); }
  .order-card__date { font-size:.78rem; color:var(--text-muted); display:flex; align-items:center; gap:.35rem; }
  .order-card__body { padding:1.1rem 1.35rem; }
  .order-item { display:flex; align-items:center; gap:.85rem; margin-bottom:.75rem; }
  .order-item:last-child { margin-bottom:0; }
  .order-item__img { width:52px; height:52px; border-radius:var(--radius-md); object-fit:cover; flex-shrink:0; }
  .order-item__name { font-size:.88rem; font-weight:600; color:var(--primary); margin-bottom:.15rem; }
  .order-item__meta { font-size:.75rem; color:var(--text-muted); }
  .order-item__price { font-size:.88rem; font-weight:700; color:var(--primary); margin-left:auto; flex-shrink:0; }
  .order-card__footer { padding:.9rem 1.35rem; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem; background:var(--cream); }
  .order-total { font-size:.9rem; }
  .order-total strong { color:var(--primary); }
  .order-actions { display:flex; gap:.5rem; }
  .btn-sm-outline { padding:.4rem .9rem; border:1.5px solid var(--border); border-radius:40px; font-size:.78rem; font-weight:600; color:var(--text-mid); background:var(--white); cursor:pointer; transition:all .2s; text-decoration:none; display:inline-flex; align-items:center; gap:.3rem; }
  .btn-sm-outline:hover { border-color:var(--primary); color:var(--primary); }
  .btn-sm-brand { padding:.4rem .9rem; border:1.5px solid var(--primary); border-radius:40px; font-size:.78rem; font-weight:600; color:#fff; background:var(--primary); cursor:pointer; transition:all .2s; text-decoration:none; display:inline-flex; align-items:center; gap:.3rem; }
  .btn-sm-brand:hover { background:var(--primary-light); color:#fff; }
  /* Rating stars */
  .star-rating { display:flex; gap:.15rem; }
  .star-rating i { font-size:.8rem; }
  /* Summary stats top */
  .hist-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:2rem; }
  .hist-stat { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.1rem 1.25rem; text-align:center; }
  .hist-stat__val { font-family:var(--font-display); font-size:1.6rem; font-weight:700; color:var(--primary); }
  .hist-stat__lbl { font-size:.75rem; color:var(--text-muted); margin-top:.15rem; }
  /* Empty */
  .empty-history { text-align:center; padding:4rem 2rem; background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); }
  .empty-history span { font-size:3.5rem; display:block; margin-bottom:1rem; }
  @media(max-width:576px){ .hist-stats{grid-template-columns:1fr 1fr;} .hist-stat:last-child{grid-column:1/-1;} }
  </style>
</head>
<body class="page-wrapper">
<?php include __DIR__ . '/../layouts/navbar.php'; ?>

<!-- Breadcrumb -->
<div style="background:var(--cream-dark);border-bottom:1px solid var(--border);padding:.6rem 0;">
  <div style="max-width:820px;margin:0 auto;padding:0 1.25rem;font-size:.8rem;color:var(--text-muted);">
    <a href="../home/home.php" style="color:var(--primary);">Beranda</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem;"></i>
    <span>Riwayat Pesanan</span>
  </div>
</div>

<div class="page-content">
<div class="history-wrap">

  <div class="page-header animate-fadeup">
    <h1><i class="bi bi-clock-history me-2" style="font-size:1.5rem;"></i>Riwayat Pesanan</h1>
    <p>Lihat semua transaksi dan status pesanan Anda</p>
  </div>

  <!-- Summary Stats -->
  <div class="hist-stats animate-fadeup" style="animation-delay:.05s">
    <div class="hist-stat">
      <div class="hist-stat__val"><?= $totalOrders ?></div>
      <div class="hist-stat__lbl">Total Pesanan</div>
    </div>
    <div class="hist-stat">
      <div class="hist-stat__val"><?= $completedOrders ?></div>
      <div class="hist-stat__lbl">Selesai</div>
    </div>
    <div class="hist-stat">
      <div class="hist-stat__val">Rp <?= number_format($totalSpent/1000, 0, ',', '.') ?>K</div>
      <div class="hist-stat__lbl">Total Belanja</div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="filter-tabs animate-fadeup" style="animation-delay:.08s">
    <button class="filter-tab active" data-filter="all">Semua</button>
    <button class="filter-tab" data-filter="Selesai">Selesai</button>
    <button class="filter-tab" data-filter="Diproses">Diproses</button>
    <button class="filter-tab" data-filter="Dikirim">Dikirim</button>
    <button class="filter-tab" data-filter="Dibatalkan">Dibatalkan</button>
  </div>

  <!-- Orders -->
  <?php if (empty($orders)): ?>
  <div class="empty-history animate-fadeup">
    <span>📋</span>
    <h4>Belum ada pesanan</h4>
    <p class="mb-3" style="color:var(--text-muted);">Yuk, mulai pesan minuman favoritmu!</p>
    <a href="../home/home.php" class="btn-brand" style="text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
      <i class="bi bi-cup-hot"></i> Lihat Menu
    </a>
  </div>
  <?php else: ?>
    <?php foreach ($orders as $i => $order): ?>
    <div class="order-card animate-fadeup order-item-wrap"
         data-status="<?= $order['status'] ?>"
         style="animation-delay:<?= ($i * 80) ?>ms">

      <div class="order-card__header">
        <div>
          <span class="order-card__id"><?= $order['id'] ?></span>
          <div class="order-card__date">
            <i class="bi bi-calendar3"></i> <?= $order['date'] ?>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;">
          <span style="font-size:.75rem;color:var(--text-muted);">
            <i class="bi bi-credit-card"></i> <?= $order['payment'] ?>
          </span>
          <span class="k-badge <?= $statusBadge[$order['status']] ?>">
            <i class="bi <?= $statusIcon[$order['status']] ?>"></i>
            <?= $order['status'] ?>
          </span>
        </div>
      </div>

      <div class="order-card__body">
        <?php foreach ($order['items'] as $item): ?>
        <div class="order-item">
          <img src="../assets/img/products/<?= $item['image'] ?>"
               class="order-item__img"
               onerror="this.src='https://placehold.co/52x52/1a3c2e/f0cb7a?text=K'">
          <div>
            <div class="order-item__name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="order-item__meta">x<?= $item['qty'] ?> &bull; <?= formatRupiah($item['price']) ?>/item</div>
          </div>
          <div class="order-item__price"><?= formatRupiah($item['price'] * $item['qty']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="order-card__footer">
        <div class="order-total">
          Total Pesanan: <strong><?= formatRupiah($order['total']) ?></strong>
          <?php if ($order['rating']): ?>
          <div class="star-rating mt-1">
            <?php for($s=1;$s<=5;$s++): ?>
              <i class="bi bi-star<?= $s<=$order['rating']?'-fill text-warning':'' ?>"></i>
            <?php endfor; ?>
            <span style="font-size:.72rem;color:var(--text-muted);margin-left:.25rem;">Ulasan Anda</span>
          </div>
          <?php endif; ?>
        </div>
        <div class="order-actions">
          <?php if(in_array($order['order_status'], ['shipped', 'processing'])): ?>
          <a href="complete-order.php?order_id=<?= $order['order_id'] ?>" class="btn-sm-brand" style="background:var(--success);border-color:var(--success);">
            <i class="bi bi-check-circle"></i> Pesanan Diterima
          </a>
          <?php endif; ?>
          <button class="btn-sm-outline"><i class="bi bi-receipt"></i> Detail</button>
          <a href="repeat-order.php?order_id=<?= $order['order_id'] ?>"
             class="btn-sm-brand"><i class="bi bi-arrow-clockwise"></i> Pesan Lagi</a>
        </div>
      </div>

    </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filter tabs
const tabs = document.querySelectorAll('.filter-tab');
const cards = document.querySelectorAll('.order-item-wrap');
tabs.forEach(tab => {
  tab.addEventListener('click', function() {
    tabs.forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    const filter = this.dataset.filter;
    cards.forEach(card => {
      if (filter === 'all' || card.dataset.status === filter) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  });
});
// Scroll reveal
const reveals = document.querySelectorAll('.animate-fadeup');
const io = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting){ e.target.style.opacity='1'; e.target.style.transform='none'; io.unobserve(e.target); }});
}, {threshold:0.08});
reveals.forEach(el => { el.style.opacity='0'; el.style.transform='translateY(18px)'; el.style.transition='opacity .5s ease, transform .5s ease'; io.observe(el); });
</script>
</body>
</html>
