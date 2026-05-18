<?php
// admin/reports/reports.php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin.php';
require_once __DIR__ . '/../../config/order.php';
require_once __DIR__ . '/../../data/products.php';

requireAdmin();

$completeStats = getCompleteStats();
$topProducts = getTopSellingProducts(5);
$weeklyRevenue = getWeeklyRevenue();
$allOrders = getAllOrders(); // From order.php

// Stats data
$stats = [
    'total_orders'    => $completeStats['total_orders'],
    'total_revenue'   => $completeStats['total_revenue'],
    'total_sold'      => $completeStats['total_sold'],
    'total_products'  => $completeStats['total_products']
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
.admin-sidebar{width:240px;flex-shrink:0;background:var(--primary);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
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
.admin-body{padding:2rem;}
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
    <h6>Laporan</h6>
    <div class="d-flex gap-2 no-print">
      <button onclick="window.print()" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">
        <i class="bi bi-printer me-1"></i> Print
      </button>
      <a href="export.php" class="btn-brand" style="font-size:.85rem;padding:.55rem 1.1rem;text-decoration:none;">
        <i class="bi bi-download me-1"></i> Export Semua Pesanan
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
        ['icon'=>'bi-cup-hot','cls'=>'purple','val'=>$stats['total_sold'],'lbl'=>'Produk Terjual'],
        ['icon'=>'bi-cart-check','cls'=>'blue','val'=>$stats['total_products'],'lbl'=>'Total Produk'],
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
          <h6>Produk Terlaris</h6>
          <?php foreach ($topProducts as $i => $p): if(empty($p['sold'])) continue;?>
          <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem;">
            <span style="width:28px;height:28px;border-radius:50%;background:var(--cream-dark);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:var(--primary);flex-shrink:0;"><?=$i+1?></span>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.82rem;font-weight:600;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($p['name'])?></div>
              <div style="height:5px;background:var(--cream-dark);border-radius:3px;margin-top:.3rem;overflow:hidden;">
                <?php $maxSold = $topProducts[0]['sold'] ?? 1;?>
                <div style="height:100%;width:<?=round(($p['sold']/$maxSold)*100)?>%;background:var(--primary);border-radius:3px;"></div>
              </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
              <div style="font-size:.78rem;font-weight:700;color:var(--primary);"><?=$p['sold']?></div>
            </div>
          </div>
          <?php endforeach;?>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="chart-box">
          <h6>Semua Pesanan</h6>
          <div style="overflow-x:auto;">
            <table class="k-table">
              <thead>
                <tr><th>ID</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr>
              </thead>
              <tbody>
                <?php if(empty($allOrders)):?>
                <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted);">Belum ada pesanan</td></tr>
                <?php else:?>
                <?php foreach($allOrders as $o):?>
                <tr>
                  <td><span style="font-family:monospace;font-weight:700;color:var(--primary);">#<?=$o['order_id']?></span></td>
                  <td><?=htmlspecialchars($o['user_name'] ?? 'Pelanggan')?></td>
                  <td style="color:var(--text-muted);font-size:.8rem;"><?=date('d M Y',strtotime($o['order_date']))?></td>
                  <td style="font-weight:700;"><?=formatRupiah($o['total'])?></td>
                  <td><span class="k-badge k-badge-<?=$o['order_status']==='completed'?'green':($o['order_status']==='pending'?'accent':($o['order_status']==='cancelled'?'red':'gray'))?>"><?=getStatusLabel($o['order_status'])?></span></td>
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
</body>
</html>
