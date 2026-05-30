<?php
// admin/reports/reports.php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin.php';
require_once __DIR__ . '/../../config/order.php';
require_once __DIR__ . '/../../data/products.php';

requireAdmin();

// Get all completed orders for stats
$completeStats = getCompleteStats();
$allOrders = getAllOrders(); // From order.php

// Filter only completed or cancelled orders for "Semua Pesanan" card
$filteredOrders = array_filter($allOrders, fn($o) => in_array($o['order_status'], ['completed', 'cancelled']));

// Get top selling products from actual order data
function getTopSellingProductsFromOrders(int $limit = 5): array {
    global $pdo;
    $stmt = $pdo->query("
        SELECT m.menu_id AS id, m.name, m.description, m.price, m.category, m.image,
               COALESCE(SUM(oi.quantity), 0) AS sold
        FROM menu m
        LEFT JOIN order_item oi ON m.menu_id = oi.menu_id
        LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'completed'
        GROUP BY m.menu_id
        HAVING sold > 0
        ORDER BY sold DESC
        LIMIT $limit
    ");
    $results = $stmt->fetchAll();

    // If no data, return some sample products
    if (empty($results)) {
        $stmt = $pdo->query("SELECT menu_id AS id, name, description, price, category, image, sold FROM menu ORDER BY sold DESC LIMIT $limit");
        $results = $stmt->fetchAll();
    }

    return $results;
}

// Stats data
$stats = [
    'total_orders'    => $completeStats['total_orders'],
    'total_revenue'   => $completeStats['total_revenue'],
    'total_sold'      => $completeStats['total_sold'],
    'total_products'  => $completeStats['total_products']
];

$topProducts = getTopSellingProductsFromOrders(5);

function getStatusLabel(string $status): string {
    $labels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Laporan - Admin Konnyusu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/global.css">
<style>
.admin-layout{display:flex;min-height:100vh;}
.admin-sidebar{width:240px;flex-shrink:0;background:var(--primary);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;transform:translateX(0);transition:transform .3s ease;z-index:999;}
.admin-sidebar.closed{transform:translateX(-100%);position:fixed;left:0;box-shadow:4px 0 20px rgba(0,0,0,.15);}
.sidebar-brand{padding:1.8rem 1rem;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;}
.sidebar-brand-text{font-family:var(--font-display);font-size:1.3rem;font-weight:700;color:#fff;}
.sidebar-nav{padding:1.25rem 0;flex:1;}
.sidebar-section-label{font-size:.65rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.35);padding:.5rem 1.25rem;margin-bottom:.25rem;}
.sidebar-link{display:flex;align-items:center;gap:.7rem;padding:.7rem 1.25rem;color:rgba(255,255,255,.7);font-size:.875rem;font-weight:500;transition:all .2s;text-decoration:none;position:relative;}
.sidebar-link:hover{background:rgba(255,255,255,.08);color:#fff;}
.sidebar-link.active{background:rgba(255,255,255,.12);color:#fff;font-weight:600;}
.sidebar-link.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--accent);border-radius:0 2px 2px 0;}
.sidebar-link i{font-size:1rem;width:20px;text-align:center;}
.sidebar-footer{padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.1);}
.sidebar-user{display:flex;align-items:center;gap:.6rem;color:rgba(255,255,255,.75);font-size:.82rem;}
.sidebar-user-avatar{width:34px;height:34px;border-radius:50%;background:var(--accent);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0;}
.admin-main{flex:1;overflow-x:hidden;}
.admin-topbar{background:var(--white);border-bottom:1px solid var(--border);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;gap:1rem;}
.admin-topbar h6{font-size:.95rem;font-weight:700;color:var(--primary);margin:0;}
.admin-topbar__left{display:flex;align-items:center;gap:.75rem;}
.hamburger-btn{background:none;border:none;cursor:pointer;padding:.5rem;border-radius:var(--radius-sm);transition:background .2s;}
.hamburger-btn:hover{background:var(--cream);}
.hamburger-btn span{display:block;width:22px;height:2px;background:var(--primary);border-radius:2px;transition:all .25s;}
.hamburger-btn span:nth-child(2){margin:.45rem 0;}
.admin-sidebar{transition:transform .3s ease;}
.admin-body{padding:2rem;padding-top:1.5rem;}
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr));gap:1.1rem;margin-bottom:2rem;}
.stat-card{background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:1.25rem;display:flex;align-items:flex-start;gap:.9rem;}
.stat-card__icon{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;}
.stat-card__icon--green{background:#ecfaf4;color:var(--success);}
.stat-card__icon--amber{background:#fff8ec;color:var(--accent);}
.stat-card__icon--blue{background:#eff6ff;color:#3b82f6;}
.stat-card__icon--purple{background:#f3f0ff;color:#8b5cf6;}
.stat-card__val{font-family:var(--font-display);font-size:1.45rem;font-weight:700;color:var(--primary);line-height:1.1;}
.stat-card__label{font-size:.75rem;color:var(--text-muted);margin-top:.2rem;}
.chart-box{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;}
.chart-box h6{font-size:.95rem;font-weight:700;color:var(--primary);margin-bottom:1.25rem;}
.k-table{width:100%;border-collapse:collapse;}
.k-table th{font-size:.72rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);padding:.6rem 1rem;text-align:left;border-bottom:1.5px solid var(--border);background:var(--cream);white-space:nowrap;}
.k-table td{padding:.85rem 1rem;font-size:.875rem;color:var(--text-dark);border-bottom:1px solid var(--border);vertical-align:middle;}
.k-table tr:last-child td{border-bottom:none;}
.k-table tr:hover td{background:var(--cream);}
.k-badge-green{background:#ecfaf4;color:var(--success);}
.k-badge-accent{background:#fff8ec;color:var(--accent);}
.k-badge-gray{background:#f5f5f5;color:var(--text-muted);}
.k-badge-red{background:#fdf0f0;color:var(--danger);}
.k-badge-blue{background:#eff6ff;color:#3b82f6;}
/* Top products card styling */
.top-product-item{display:flex;align-items:center;gap:.85rem;padding:.85rem;background:var(--cream);border-radius:var(--radius-md);margin-bottom:.6rem;transition:all .2s;}
.top-product-item:hover{background:var(--cream-dark);}
.top-product-item:last-child{margin-bottom:0;}
.product-rank{width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;}
.product-rank.gold{background:linear-gradient(135deg,#f59e0b,#d97706);}
.product-rank.silver{background:linear-gradient(135deg,#9ca3af,#6b7280);}
.product-rank.bronze{background:linear-gradient(135deg,#cd7f32,#a0522d);}
.product-img{width:48px;height:48px;border-radius:var(--radius-md);object-fit:cover;flex-shrink:0;}
.product-info{flex:1;min-width:0;}
.product-name{font-size:.88rem;font-weight:600;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.product-sold{font-size:.75rem;color:var(--text-muted);margin-top:.15rem;}
.product-bar{width:100%;height:5px;background:var(--cream-dark);border-radius:3px;margin-top:.4rem;overflow:hidden;}
.product-bar-fill{height:100%;background:var(--primary);border-radius:3px;transition:width .5s ease;}
.product-sales{text-align:right;flex-shrink:0;}
.product-sales-count{font-size:.95rem;font-weight:700;color:var(--primary);}
.product-sales-label{font-size:.65rem;color:var(--text-muted);}
@media(max-width:900px){.admin-sidebar{display:none;}.admin-body{padding:1.25rem;}}
@media print{
    .admin-sidebar,.admin-topbar,.no-print{display:none!important;}
    .admin-body{padding:0;}
    .chart-box{border:none;box-shadow:none;}
}
</style>
</head>
<body>
<div class="admin-layout">

<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <img src="../../assets/img/logo.jpeg" alt="Konnyusu" style="height:80px;border-radius:12px;">
  </div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Utama</div>
    <a href="../dashboard/dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../orders/orders.php" class="sidebar-link"><i class="bi bi-receipt"></i> Pesanan</a>
    <a href="../products/products.php" class="sidebar-link"><i class="bi bi-cup-hot"></i> Produk</a>
    <a href="../customers/customers.php" class="sidebar-link"><i class="bi bi-people"></i> Pelanggan</a>
    <div class="sidebar-section-label mt-3">Laporan</div>
    <a href="reports.php" class="sidebar-link active"><i class="bi bi-bar-chart-line"></i> Laporan</a>
    <div class="sidebar-section-label mt-3">Sistem</div>
    <a href="../../home/home.php" class="sidebar-link"><i class="bi bi-arrow-left-circle"></i> Ke Toko</a>
    <a href="../../auth/logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar">A</div>
      <div><div style="color:#fff;font-weight:600;font-size:.8rem;">Admin</div><div style="font-size:.72rem;">admin@konnyusu.com</div></div>
    </div>
  </div>
</aside>

<div class="admin-main">
  <div class="admin-topbar">
    <div class="admin-topbar__left">
      <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <h6>Laporan</h6>
    </div>
    <div class="d-flex gap-2 no-print">
      <a href="export.php" class="btn-brand" style="font-size:.85rem;padding:.55rem 1.1rem;text-decoration:none;">
        <i class="bi bi-download me-1"></i> Ekspor Laporan
      </a>
    </div>
  </div>

  <div class="admin-body">
    <!-- Stat Cards -->
    <div class="stat-grid">
      <?php
      $statData = [
        ['icon'=>'bi-receipt-cutoff','cls'=>'green','val'=>number_format($stats['total_orders']),'lbl'=>'Total Pesanan'],
        ['icon'=>'bi-cash-stack','cls'=>'amber','val'=>'Rp '.number_format($stats['total_revenue']/1000000,1).'Jt','lbl'=>'Total Pendapatan'],
        ['icon'=>'bi-cup-hot','cls'=>'purple','val'=>number_format($stats['total_sold']),'lbl'=>'Produk Terjual'],
        ['icon'=>'bi-cart-check','cls'=>'blue','val'=>number_format($stats['total_products']),'lbl'=>'Total Produk'],
      ];
      foreach ($statData as $s):
      ?>
      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--<?=$s['cls']?>"><i class="bi <?=$s['icon']?>"></i></div>
        <div>
          <div class="stat-card__val"><?=$s['val']?></div>
          <div class="stat-card__label"><?=$s['lbl']?></div>
        </div>
      </div>
      <?php endforeach;?>
    </div>

    <!-- Top Products & All Orders -->
    <div class="row g-4 mb-4">
      <div class="col-lg-4">
        <div class="chart-box">
          <h6><i class="bi bi-trophy me-1 text-warning"></i>Produk Terlaris</h6>
          <?php if(empty($topProducts)): ?>
          <div style="text-align:center;padding:2rem;color:var(--text-muted);">
            <i class="bi bi-inbox" style="font-size:2.5rem;margin-bottom:.75rem;"></i>
            <p style="font-size:.85rem;margin:0;">Belum ada data penjualan</p>
          </div>
          <?php else:
            $maxSold = $topProducts[0]['sold'] ?? 1;
            $rankClasses = ['gold', 'silver', 'bronze', '', ''];
            foreach ($topProducts as $i => $p):
              $rankClass = $rankClasses[$i] ?? '';
              $barWidth = $maxSold > 0 ? round(($p['sold'] / $maxSold) * 100) : 0;
          ?>
          <div class="top-product-item">
            <div class="product-rank <?= $rankClass ?>"><?= $i + 1 ?></div>
            <img src="../../assets/img/products/<?= htmlspecialchars($p['image']) ?>"
                 class="product-img"
                 onerror="this.src='https://placehold.co/48x48/1a3c2e/f0cb7a?text=<?= substr($p['name'], 0, 1) ?>'">
            <div class="product-info">
              <div class="product-name" title="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></div>
              <div class="product-sold"><?= number_format($p['sold']) ?> terjual</div>
              <div class="product-bar">
                <div class="product-bar-fill" style="width:<?= $barWidth ?>%"></div>
              </div>
            </div>
            <div class="product-sales">
              <div class="product-sales-count"><?= number_format($p['sold']) ?></div>
              <div class="product-sales-label">unit</div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="chart-box">
          <h6><i class="bi bi-list-ul me-1"></i>Semua Pesanan</h6>
          <div style="overflow-x:auto;">
            <table class="k-table">
              <thead>
                <tr><th>ID</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr>
              </thead>
              <tbody>
                <?php if(empty($filteredOrders)):?>
                <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Belum ada pesanan</td></tr>
                <?php else:?>
                <?php foreach($filteredOrders as $o):?>
                <tr>
                  <td><span style="font-family:monospace;font-weight:700;color:var(--primary);">#<?=$o['order_id']?></span></td>
                  <td><?=htmlspecialchars($o['user_name'] ?? 'Pelanggan')?></td>
                  <td style="color:var(--text-muted);font-size:.8rem;"><?=date('d M Y',strtotime($o['order_date']))?></td>
                  <td style="font-weight:700;"><?=formatRupiah($o['total'])?></td>
                  <td>
                    <?php
                    $statusBadgeClass = $o['order_status'] === 'completed' ? 'k-badge-green' : ($o['order_status'] === 'pending' ? 'k-badge-accent' : ($o['order_status'] === 'cancelled' ? 'k-badge-red' : 'k-badge-gray'));
                    ?>
                    <span class="k-badge <?= $statusBadgeClass ?>"><?=getStatusLabel($o['order_status'])?></span>
                  </td>
                </tr>
                <?php endforeach;?>
                <?php endif;?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    
  </div>
</div>

</div>

<!-- Hamburger Toggle Script -->
<script>
document.getElementById('hamburgerBtn')?.addEventListener('click', function() {
  document.querySelectorAll('.admin-sidebar').forEach(function(sidebar) {
    sidebar.classList.toggle('closed');
  });
});
</script>
</body>
</html>