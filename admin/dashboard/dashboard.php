<?php
// admin/dashboard/dashboard.php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin.php';
require_once __DIR__ . '/../../config/order.php';
require_once __DIR__ . '/../../data/products.php';

requireAdmin();

$completeStats = getCompleteStats();
$recentOrders = getRecentOrders(5);
$topProducts = getTopSellingProducts(5);
$weeklyRevenue = getWeeklyRevenue();
$unviewedOrders = getUnviewedOrders();
$unviewedCount = getUnviewedOrdersCount();

// Stats data
$stats = [
    'total_orders'    => $completeStats['total_orders'],
    'pending_orders'  => $completeStats['pending_orders'],
    'total_revenue'   => $completeStats['total_revenue'],
    'total_customers' => $completeStats['total_customers'],
    'total_products'  => $completeStats['total_products'],
    'avg_rating'      => 4.85,
];

$statusColor = [
    'Selesai'    => 'k-badge-green',
    'Diproses'   => 'k-badge-accent',
    'Dikirim'    => 'k-badge-gray',
    'Dibatalkan' => 'k-badge-red',
    'pending'    => 'k-badge-accent',
    'processing' => 'k-badge-accent',
    'completed'  => 'k-badge-green',
    'cancelled'  => 'k-badge-red',
];



function getStatusLabel(string $status): string {
    $labels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}

function timeAgo(string $datetime): string {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return round($diff / 60) . ' menit lalu';
    if ($diff < 86400) return round($diff / 3600) . ' jam lalu';
    return date('d M Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/global.css">
  <style>
  .admin-layout { display:flex; min-height:100vh; }
  .admin-sidebar { width:240px; flex-shrink:0; background:var(--primary); display:flex; flex-direction:column; position:sticky; top:0; height:100vh; overflow-y:auto; }
  .sidebar-brand { padding:1.8rem 1rem; border-bottom:1px solid rgba(255,255,255,.1); display:flex; align-items:center; justify-content:center; }
  .sidebar-brand-text { font-family:var(--font-display); font-size:1.3rem;font-weight:700;color:#fff; }
  .sidebar-nav { padding:1.25rem 0; flex:1; }
  .sidebar-section-label { font-size:.65rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.35);padding:.5rem 1.25rem;margin-bottom:.25rem; }
  .sidebar-link { display:flex;align-items:center;gap:.7rem;padding:.7rem 1.25rem;color:rgba(255,255,255,.7);font-size:.875rem;font-weight:500;transition:all .2s;text-decoration:none;position:relative; }
  .sidebar-link:hover { background:rgba(255,255,255,.08);color:#fff; }
  .sidebar-link.active { background:rgba(255,255,255,.12);color:#fff;font-weight:600; }
  .sidebar-link.active::before { content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--accent);border-radius:0 2px 2px 0; }
  .sidebar-link i { font-size:1rem;width:20px;text-align:center; }
  .sidebar-footer { padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.1); }
  .sidebar-user { display:flex;align-items:center;gap:.6rem;color:rgba(255,255,255,.75);font-size:.82rem; }
  .sidebar-user-avatar { width:34px;height:34px;border-radius:50%;background:var(--accent);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0; }
  .admin-main { flex:1; overflow-x:hidden; }
  .admin-topbar { background:var(--white);border-bottom:1px solid var(--border);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;gap:1rem; }
  .admin-topbar h6 { font-size:.95rem;font-weight:700;color:var(--primary);margin:0; }
  .admin-body { padding:2rem; }
  .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(185px,1fr)); gap:1.1rem; margin-bottom:2rem; }
  .stat-card { background:var(--white); border-radius:var(--radius-lg); border:1px solid var(--border); padding:1.25rem; display:flex; align-items:flex-start; gap:.9rem; transition:transform .2s,box-shadow .2s; }
  .stat-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-md); }
  .stat-card__icon { width:44px; height:44px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; }
  .stat-card__icon--green  { background:#ecfaf4; color:var(--success); }
  .stat-card__icon--amber  { background:#fff8ec; color:var(--accent); }
  .stat-card__icon--blue   { background:#eff6ff; color:#3b82f6; }
  .stat-card__icon--red    { background:#fdf0f0; color:var(--danger); }
  .stat-card__icon--purple { background:#f3f0ff; color:#8b5cf6; }
  .stat-card__icon--teal   { background:#effcfc; color:#0ea5e9; }
  .stat-card__val { font-family:var(--font-display); font-size:1.45rem; font-weight:700; color:var(--primary); line-height:1.1; }
  .stat-card__label { font-size:.75rem; color:var(--text-muted); margin-top:.2rem; }
  .stat-card__change { font-size:.72rem; font-weight:600; margin-top:.4rem; }
  .stat-card__change.up   { color:var(--success); }
  .stat-card__change.down { color:var(--danger); }
  .chart-box { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem; }
  .chart-box h6 { font-size:.95rem; font-weight:700; color:var(--primary); margin-bottom:1.25rem; }
  .chart-bars { display:flex; align-items:flex-end; gap:.5rem; height:140px; }
  .chart-bar-wrap { display:flex; flex-direction:column; align-items:center; gap:.3rem; flex:1; }
  .chart-bar { width:100%; border-radius:var(--radius-sm) var(--radius-sm) 0 0; background:linear-gradient(180deg,var(--primary-light),var(--primary)); transition:opacity .2s; cursor:pointer; }
  .chart-bar:hover { opacity:.75; }
  .chart-bar.accent { background:linear-gradient(180deg,var(--accent-light),var(--accent)); }
  .chart-bar-label { font-size:.68rem; color:var(--text-muted); }
  .k-table { width:100%; border-collapse:collapse; }
  .k-table th { font-size:.72rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--text-muted); padding:.6rem 1rem; text-align:left; border-bottom:1.5px solid var(--border); background:var(--cream); white-space:nowrap; }
  .k-table td { padding:.85rem 1rem; font-size:.875rem; color:var(--text-dark); border-bottom:1px solid var(--border); vertical-align:middle; }
  .k-table tr:last-child td { border-bottom:none; }
  .k-table tr:hover td { background:var(--cream); }
  .notification-dropdown { position:absolute; top:100%; right:0; width:320px; background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); box-shadow:var(--shadow-lg); z-index:9999; margin-top:.5rem; display:none; overflow:hidden; }
  .notification-dropdown.open { display:block; }
  .notification-header { padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
  .notification-header h6 { margin:0; font-size:.85rem; font-weight:700; color:var(--primary); }
  .notification-item { padding:.9rem 1.25rem; border-bottom:1px solid var(--border); transition:background .2s; cursor:pointer; text-decoration:none; display:block; color:inherit; }
  .notification-item:last-child { border-bottom:none; }
  .notification-item:hover { background:var(--cream); }
  .notification-item__title { font-size:.85rem; font-weight:600; color:var(--primary); }
  .notification-item__desc { font-size:.75rem; color:var(--text-muted); margin-top:.2rem; }
  .notification-item__time { font-size:.7rem; color:var(--text-muted); margin-top:.3rem; }
  .notification-empty { padding:1.5rem; text-align:center; color:var(--text-muted); font-size:.85rem; }
  @media(max-width:900px){ .admin-sidebar{display:none;} .admin-body{padding:1.25rem;} }
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
      <a href="dashboard.php" class="sidebar-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="../orders/orders.php"   class="sidebar-link"><i class="bi bi-receipt"></i> Pesanan</a>
      <a href="../products/products.php" class="sidebar-link"><i class="bi bi-cup-hot"></i> Produk</a>
      <a href="../customers/customers.php" class="sidebar-link"><i class="bi bi-people"></i> Pelanggan</a>
      <div class="sidebar-section-label mt-3">Laporan</div>
      <a href="../reports/reports.php" class="sidebar-link"><i class="bi bi-bar-chart-line"></i> Laporan</a>
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
      <h6>Dashboard</h6>
      <div style="position:relative;max-width:280px;flex:1;">
        <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
        <input type="text" placeholder="Cari pesanan, produk..." style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:40px;padding:.5rem .9rem .5rem 2.3rem;font-family:var(--font-body);font-size:.85rem;outline:none;">
      </div>
      <div style="display:flex;align-items:center;gap:.75rem;">
        <div style="position:relative;">
          <button id="notifBtn" style="background:none;border:none;font-size:1.2rem;color:var(--text-mid);position:relative;cursor:pointer;">
            <i class="bi bi-bell"></i>
            <?php if($unviewedCount > 0):?>
            <span style="position:absolute;top:-6px;right:-8px;min-width:18px;height:18px;background:var(--danger);border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff;"><?= $unviewedCount ?></span>
            <?php endif;?>
          </button>
          <div class="notification-dropdown" id="notifDropdown">
            <div class="notification-header">
              <h6>Notifikasi</h6>
              <?php if($unviewedCount > 0):?>
              <span style="font-size:.72rem;color:var(--text-muted);"><?= $unviewedCount ?> baru</span>
              <?php endif;?>
            </div>
            <?php if(empty($unviewedOrders)):?>
            <div class="notification-empty">
              <i class="bi bi-check-circle" style="font-size:1.5rem;margin-bottom:.5rem;display:block;color:var(--success);"></i>
              Tidak ada notifikasi baru
            </div>
            <?php else:?>
              <?php foreach($unviewedOrders as $order):?>
              <a href="../orders/mark-viewed.php?order_id=<?= $order['order_id'] ?>" class="notification-item">
                <div class="notification-item__title">Pesanan Baru #<?= $order['order_id'] ?></div>
                <div class="notification-item__desc"><?= htmlspecialchars($order['user_name'] ?? 'Pelanggan') ?> - <?= formatRupiah($order['total']) ?></div>
                <div class="notification-item__time"><?= date('d M Y, H:i', strtotime($order['order_date'])) ?></div>
              </a>
              <?php endforeach;?>
            <?php endif;?>
          </div>
        </div>
        <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">A</div>
      </div>
    </div>

    <div class="admin-body">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 style="font-size:1.4rem;font-weight:700;color:var(--primary);margin-bottom:.2rem;">Selamat Datang, Admin 👋</h4>
          <p style="font-size:.85rem;color:var(--text-muted);margin:0;"><?= date('l, d F Y') ?></p>
        </div>
        <a href="../orders/orders.php" class="btn-brand" style="font-size:.85rem;padding:.6rem 1.2rem;text-decoration:none;">
          <i class="bi bi-plus-lg me-1"></i> Pesanan Baru
        </a>
      </div>

      <!-- Stat cards -->
      <div class="stat-grid animate-fadeup">
        <?php
        $statData = [
          ['icon'=>'bi-receipt-cutoff','cls'=>'green','val'=>number_format($stats['total_orders']),'lbl'=>'Total Pesanan','chg'=>'+12% bulan ini','up'=>true],
          ['icon'=>'bi-clock','cls'=>'red','val'=>$stats['pending_orders'],'lbl'=>'Pesanan Menunggu','chg'=>'-3 berkurang','up'=>false],
          ['icon'=>'bi-cash-stack','cls'=>'amber','val'=>'Rp '.number_format($stats['total_revenue']/1000000,1).'Jt','lbl'=>'Total Pendapatan','chg'=>'+18% bulan ini','up'=>true],
          ['icon'=>'bi-people','cls'=>'blue','val'=>$stats['total_customers'],'lbl'=>'Total Pelanggan','chg'=>'+24 baru','up'=>true],
          ['icon'=>'bi-cup-hot','cls'=>'purple','val'=>$stats['total_products'],'lbl'=>'Total Produk','chg'=>'2 baru','up'=>true],
          ['icon'=>'bi-star-fill','cls'=>'teal','val'=>$stats['avg_rating'],'lbl'=>'Rating Rata-rata','chg'=>'Sangat baik','up'=>true],
        ];
        foreach ($statData as $s):
        ?>
        <div class="stat-card">
          <div class="stat-card__icon stat-card__icon--<?= $s['cls'] ?>"><i class="bi <?= $s['icon'] ?>"></i></div>
          <div>
            <div class="stat-card__val"><?= $s['val'] ?></div>
            <div class="stat-card__label"><?= $s['lbl'] ?></div>
            <div class="stat-card__change <?= $s['up']?'up':'down' ?>">
              <i class="bi <?= $s['up']?'bi-arrow-up-short':'bi-arrow-down-short' ?>"></i><?= $s['chg'] ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Charts -->
      <div class="row g-4 mb-4">
        <div class="col-lg-7">
          <div class="chart-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6>Pendapatan Mingguan</h6>
              <span style="font-size:.75rem;color:var(--text-muted);">7 hari terakhir</span>
            </div>
            <div class="chart-bars">
              <?php foreach ($weeklyRevenue as $b): ?>
              <div class="chart-bar-wrap">
                <div class="chart-bar <?= $b['is_max']?'accent':'' ?>" style="height:<?= $b['percent'] ?>"></div>
                <span class="chart-bar-label"><?= $b['day'] ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="chart-box">
            <h6>Produk Terlaris</h6>
            <?php 
            $maxSold = $topProducts[0]['total_sold'] ?? 1;
            foreach ($topProducts as $i => $p): 
              if(empty($p['total_sold']) && $i > 2) continue; 
            ?>
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem;">
              <span style="width:22px;height:22px;border-radius:50%;background:var(--cream-dark);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:var(--primary);flex-shrink:0;"><?= $i+1 ?></span>
              <div style="flex:1;min-width:0;">
                <div style="font-size:.82rem;font-weight:600;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['name']) ?></div>
                <div style="height:5px;background:var(--cream-dark);border-radius:3px;margin-top:.3rem;overflow:hidden;">
                  <div style="height:100%;width:<?= max(10, round(($p['total_sold']/$maxSold)*100)) ?>%;background:var(--primary);border-radius:3px;"></div>
                </div>
              </div>
              <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:.78rem;font-weight:700;color:var(--primary);"><?= $p['total_sold'] ?></div>
                <div style="font-size:.68rem;color:var(--text-muted);">⭐ 4.8</div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Recent orders table -->
      <div class="chart-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6>Pesanan Terbaru</h6>
          <a href="../orders/orders.php" style="font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none;">Lihat Semua <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div style="overflow-x:auto;">
          <table class="k-table">
            <thead>
              <tr><th>ID</th><th>Pelanggan</th><th>Item</th><th>Total</th><th>Status</th><th>Waktu</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php if(empty($recentOrders)): ?>
              <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">Belum ada pesanan</td></tr>
              <?php else: ?>
              <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td><span style="font-family:monospace;font-weight:700;color:var(--primary);">#<?= $o['order_id'] ?></span></td>
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem;">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;"><?= substr($o['user_name'] ?? 'U',0,1) ?></div>
                    <?= htmlspecialchars($o['user_name'] ?? 'Pelanggan') ?>
                  </div>
                </td>
                <td>
                    <?php
                    $orderItems = getOrderItems($o['order_id']);
                    echo count($orderItems) . ' item';
                    ?>
                </td>
                <td style="font-weight:700;"><?= formatRupiah($o['total']) ?></td>
                <td><span class="k-badge <?= $statusColor[$o['order_status']] ?? 'k-badge-gray' ?>"><?= getStatusLabel($o['order_status']) ?></span></td>
                <td style="color:var(--text-muted);font-size:.8rem;"><?= timeAgo($o['order_date']) ?></td>
                <td>
                  <a href="../orders/orders.php" class="btn-outline-brand" style="padding:.25rem .75rem;font-size:.75rem;text-decoration:none;">Detail</a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle notification dropdown
document.getElementById('notifBtn').addEventListener('click', function(e){
  e.stopPropagation();
  document.getElementById('notifDropdown').classList.toggle('open');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e){
  const dropdown = document.getElementById('notifDropdown');
  if(!dropdown.contains(e.target)){
    dropdown.classList.remove('open');
  }
});
</script>
</body>
</html>
