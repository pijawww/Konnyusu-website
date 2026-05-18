<?php
session_start();
include __DIR__ . '/../../data/products.php';

$stats = [
    'total_orders'    => 1247,
    'pending_orders'  => 23,
    'total_revenue'   => 42580000,
    'total_customers' => 389,
    'total_products'  => 8,
    'avg_rating'      => 4.85,
];

$recentOrders = [
    ['id'=>'#1094','customer'=>'Rani Permata',   'items'=>3,'total'=>85000,  'status'=>'Selesai',   'time'=>'10 menit lalu'],
    ['id'=>'#1093','customer'=>'Bagas Satrio',   'items'=>1,'total'=>30000,  'status'=>'Diproses',  'time'=>'23 menit lalu'],
    ['id'=>'#1092','customer'=>'Dinda Maharani', 'items'=>2,'total'=>63000,  'status'=>'Dikirim',   'time'=>'35 menit lalu'],
    ['id'=>'#1091','customer'=>'Rizky Aditya',   'items'=>4,'total'=>124000, 'status'=>'Selesai',   'time'=>'1 jam lalu'],
    ['id'=>'#1090','customer'=>'Siti Nuraini',   'items'=>1,'total'=>18000,  'status'=>'Dibatalkan','time'=>'2 jam lalu'],
];

$topProducts = [
    ['Matcha Oat Latte',    312, 4.8],
    ['Caramel Tart Latte',  238, 4.9],
    ['Sea Salt Cream Latte',195, 4.8],
    ['Caramel Tart',        201, 4.9],
    ['Signature Series',    89,  5.0],
];

$statusColor = [
    'Selesai'    => 'k-badge-green',
    'Diproses'   => 'k-badge-accent',
    'Dikirim'    => 'k-badge-gray',
    'Dibatalkan' => 'k-badge-red',
];
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
  .sidebar-brand { padding:1.4rem; border-bottom:1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:.6rem; }
  .sidebar-brand-text { font-family:var(--font-display); font-size:1.3rem; font-weight:700; color:#fff; }
  .sidebar-nav { padding:1.25rem 0; flex:1; }
  .sidebar-section-label { font-size:.65rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:rgba(255,255,255,.35); padding:.5rem 1.25rem; margin-bottom:.25rem; }
  .sidebar-link { display:flex; align-items:center; gap:.7rem; padding:.7rem 1.25rem; color:rgba(255,255,255,.7); font-size:.875rem; font-weight:500; transition:all .2s; text-decoration:none; position:relative; }
  .sidebar-link:hover { background:rgba(255,255,255,.08); color:#fff; }
  .sidebar-link.active { background:rgba(255,255,255,.12); color:#fff; font-weight:600; }
  .sidebar-link.active::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:var(--accent); border-radius:0 2px 2px 0; }
  .sidebar-link i { font-size:1rem; width:20px; text-align:center; }
  .sidebar-footer { padding:1rem 1.25rem; border-top:1px solid rgba(255,255,255,.1); }
  .sidebar-user { display:flex; align-items:center; gap:.6rem; color:rgba(255,255,255,.75); font-size:.82rem; }
  .sidebar-user-avatar { width:34px; height:34px; border-radius:50%; background:var(--accent); color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.85rem; flex-shrink:0; }
  .admin-main { flex:1; overflow-x:hidden; }
  .admin-topbar { background:var(--white); border-bottom:1px solid var(--border); padding:.9rem 2rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; gap:1rem; }
  .admin-topbar h6 { font-size:.95rem; font-weight:700; color:var(--primary); margin:0; }
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
  @media(max-width:900px){ .admin-sidebar{display:none;} .admin-body{padding:1.25rem;} }
  </style>
</head>
<body>
<div class="admin-layout">

  <aside class="admin-sidebar">
    <div class="sidebar-brand"><span style="font-size:1.35rem">☕</span><span class="sidebar-brand-text">Konnyusu</span></div>
    <nav class="sidebar-nav">
      <div class="sidebar-section-label">Utama</div>
      <a href="dashboard.php" class="sidebar-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="../orders/orders.php"   class="sidebar-link"><i class="bi bi-receipt"></i> Pesanan <span class="ms-auto k-badge k-badge-red"><?= $stats['pending_orders'] ?></span></a>
      <a href="../products/products.php" class="sidebar-link"><i class="bi bi-cup-hot"></i> Produk</a>
      <a href="../customers/customers.php" class="sidebar-link"><i class="bi bi-people"></i> Pelanggan</a>
      <div class="sidebar-section-label mt-3">Laporan</div>
      <a href="#" class="sidebar-link"><i class="bi bi-bar-chart-line"></i> Analitik</a>
      <a href="#" class="sidebar-link"><i class="bi bi-cash-stack"></i> Keuangan</a>
      <div class="sidebar-section-label mt-3">Sistem</div>
      <a href="#" class="sidebar-link"><i class="bi bi-gear"></i> Pengaturan</a>
      <a href="../../home/home.php" class="sidebar-link"><i class="bi bi-arrow-left-circle"></i> Ke Toko</a>
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
        <button style="background:none;border:none;font-size:1.2rem;color:var(--text-mid);position:relative;cursor:pointer;">
          <i class="bi bi-bell"></i>
          <span style="position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:var(--danger);border-radius:50%;border:2px solid #fff;"></span>
        </button>
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
              <?php foreach ([['Sen','60%',false],['Sel','45%',false],['Rab','75%',false],['Kam','55%',false],['Jum','90%',true],['Sab','80%',false],['Min','70%',false]] as $b): ?>
              <div class="chart-bar-wrap">
                <div class="chart-bar <?= $b[2]?'accent':'' ?>" style="height:<?= $b[1] ?>"></div>
                <span class="chart-bar-label"><?= $b[0] ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="chart-box">
            <h6>Produk Terlaris</h6>
            <?php foreach ($topProducts as $i => $p): ?>
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem;">
              <span style="width:22px;height:22px;border-radius:50%;background:var(--cream-dark);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:var(--primary);flex-shrink:0;"><?= $i+1 ?></span>
              <div style="flex:1;min-width:0;">
                <div style="font-size:.82rem;font-weight:600;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $p[0] ?></div>
                <div style="height:5px;background:var(--cream-dark);border-radius:3px;margin-top:.3rem;overflow:hidden;">
                  <div style="height:100%;width:<?= round(($p[1]/312)*100) ?>%;background:var(--primary);border-radius:3px;"></div>
                </div>
              </div>
              <div style="text-align:right;flex-shrink:0;">
                <div style="font-size:.78rem;font-weight:700;color:var(--primary);"><?= $p[1] ?></div>
                <div style="font-size:.68rem;color:var(--text-muted);">⭐ <?= $p[2] ?></div>
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
              <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td><span style="font-family:monospace;font-weight:700;color:var(--primary);"><?= $o['id'] ?></span></td>
                <td>
                  <div style="display:flex;align-items:center;gap:.5rem;">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;"><?= $o['customer'][0] ?></div>
                    <?= $o['customer'] ?>
                  </div>
                </td>
                <td><?= $o['items'] ?> item</td>
                <td style="font-weight:700;"><?= formatRupiah($o['total']) ?></td>
                <td><span class="k-badge <?= $statusColor[$o['status']] ?>"><?= $o['status'] ?></span></td>
                <td style="color:var(--text-muted);font-size:.8rem;"><?= $o['time'] ?></td>
                <td>
                  <button class="btn-outline-brand" style="padding:.25rem .75rem;font-size:.75rem;">Detail</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
