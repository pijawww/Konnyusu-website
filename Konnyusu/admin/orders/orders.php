<?php
session_start();
include __DIR__ . '/../../data/products.php';

$allOrders = [
  ['id'=>'#1094','customer'=>'Rani Permata',   'phone'=>'0812-3456-7890','items'=>[['Caramel Tart Latte',2,32000],['Caramel Tart',1,18000]],'total'=>87000, 'payment'=>'GoPay',        'status'=>'Selesai',   'date'=>'15 Mei 2026, 10:23','address'=>'Jl. Sudirman No.12, Jakarta'],
  ['id'=>'#1093','customer'=>'Bagas Satrio',   'phone'=>'0813-9876-5432','items'=>[['Matcha Oat Latte',1,28000]],'total'=>33000, 'payment'=>'QRIS',         'status'=>'Diproses',  'date'=>'15 Mei 2026, 09:47','address'=>'Jl. Gatot Subroto No.45, Jakarta'],
  ['id'=>'#1092','customer'=>'Dinda Maharani', 'phone'=>'0815-2345-6789','items'=>[['Sea Salt Cream Latte',1,28000],['Savory Croissant',1,22000]],'total'=>55000,'payment'=>'OVO','status'=>'Dikirim',   'date'=>'15 Mei 2026, 09:15','address'=>'Jl. Thamrin No.88, Jakarta'],
  ['id'=>'#1091','customer'=>'Rizky Aditya',   'phone'=>'0821-4567-8901','items'=>[['Signature Series',1,45000],['Hojicha Milk',2,25000],['Caramel Tart',1,18000]],'total'=>118000,'payment'=>'BCA Transfer','status'=>'Selesai','date'=>'15 Mei 2026, 08:30','address'=>'Jl. Kuningan No.22, Jakarta'],
  ['id'=>'#1090','customer'=>'Siti Nuraini',   'phone'=>'0856-7890-1234','items'=>[['Caramel Tart',1,18000]],'total'=>23000,'payment'=>'Dana','status'=>'Dibatalkan','date'=>'14 Mei 2026, 21:55','address'=>'Jl. Kemang No.7, Jakarta Selatan'],
  ['id'=>'#1089','customer'=>'Arif Budiman',   'phone'=>'0878-2345-6780','items'=>[['Signetone Oat Latte',2,30000]],'total'=>65000,'payment'=>'GoPay','status'=>'Selesai','date'=>'14 Mei 2026, 18:40','address'=>'Jl. Fatmawati No.33, Jakarta Selatan'],
  ['id'=>'#1088','customer'=>'Laila Khadijah',  'phone'=>'0811-3456-7891','items'=>[['Matcha Oat Latte',1,28000],['Caramel Tart',2,18000]],'total'=>69000,'payment'=>'QRIS','status'=>'Selesai','date'=>'14 Mei 2026, 15:20','address'=>'Jl. Senopati No.55, Jakarta Selatan'],
  ['id'=>'#1087','customer'=>'Farhan Maulana',  'phone'=>'0823-5678-9012','items'=>[['Sea Salt Cream Latte',2,28000]],'total'=>61000,'payment'=>'OVO','status'=>'Diproses','date'=>'14 Mei 2026, 14:05','address'=>'Jl. Blok M No.10, Jakarta Selatan'],
];

$statusBadge=['Selesai'=>'k-badge-green','Diproses'=>'k-badge-accent','Dikirim'=>'k-badge-gray','Dibatalkan'=>'k-badge-red'];
$statusIcon=['Selesai'=>'bi-check-circle-fill','Diproses'=>'bi-clock-fill','Dikirim'=>'bi-truck','Dibatalkan'=>'bi-x-circle-fill'];

$filterStatus = $_GET['status'] ?? 'all';
$filtered = ($filterStatus === 'all') ? $allOrders : array_filter($allOrders, fn($o) => $o['status'] === $filterStatus);

$msg = '';
if (isset($_GET['update'])) $msg = 'Status pesanan berhasil diperbarui!';

$stats = [
  'total'     => count($allOrders),
  'selesai'   => count(array_filter($allOrders, fn($o)=>$o['status']==='Selesai')),
  'diproses'  => count(array_filter($allOrders, fn($o)=>$o['status']==='Diproses')),
  'dikirim'   => count(array_filter($allOrders, fn($o)=>$o['status']==='Dikirim')),
  'batal'     => count(array_filter($allOrders, fn($o)=>$o['status']==='Dibatalkan')),
  'revenue'   => array_sum(array_column(array_filter($allOrders, fn($o)=>$o['status']==='Selesai'),'total')),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kelola Pesanan - Admin Konnyusu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/global.css">
<style>
.admin-layout{display:flex;min-height:100vh;}
.admin-sidebar{width:240px;flex-shrink:0;background:var(--primary);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;}
.sidebar-brand{padding:1.4rem;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:.6rem;}
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
.stat-strip{display:grid;grid-template-columns:repeat(6,1fr);gap:.9rem;margin-bottom:1.75rem;}
.stat-strip-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1rem;text-align:center;}
.stat-strip-card__val{font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--primary);}
.stat-strip-card__lbl{font-size:.7rem;color:var(--text-muted);margin-top:.15rem;}
.filter-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;}
.filter-tab{padding:.4rem 1rem;border-radius:40px;border:1.5px solid var(--border);background:var(--white);font-size:.8rem;font-weight:500;color:var(--text-mid);cursor:pointer;transition:all .2s;text-decoration:none;}
.filter-tab:hover{border-color:var(--primary);color:var(--primary);}
.filter-tab.active{background:var(--primary);border-color:var(--primary);color:#fff;}
.k-table{width:100%;border-collapse:collapse;}
.k-table th{font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);padding:.6rem 1rem;text-align:left;border-bottom:1.5px solid var(--border);background:var(--cream);white-space:nowrap;}
.k-table td{padding:.8rem 1rem;font-size:.855rem;color:var(--text-dark);border-bottom:1px solid var(--border);vertical-align:middle;}
.k-table tr:last-child td{border-bottom:none;}
.k-table tr:hover td{background:var(--cream);}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
.modal-overlay.open{display:flex;}
.modal-box{background:var(--white);border-radius:var(--radius-lg);width:100%;max-width:560px;max-height:90vh;overflow-y:auto;animation:fadeUp .3s ease;}
.modal-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.modal-header h5{font-size:1rem;font-weight:700;color:var(--primary);margin:0;}
.modal-body{padding:1.5rem;}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.75rem;}
.detail-row{display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
.detail-row:last-child{border-bottom:none;}
.detail-row span:first-child{color:var(--text-muted);}
.detail-row span:last-child{font-weight:600;color:var(--primary);}
@media(max-width:900px){.admin-sidebar{display:none;}.admin-body{padding:1.25rem;}.stat-strip{grid-template-columns:repeat(3,1fr);}}
</style>
</head>
<body>
<div class="admin-layout">
<aside class="admin-sidebar">
  <div class="sidebar-brand"><span style="font-size:1.35rem">☕</span><span class="sidebar-brand-text">Konnyusu</span></div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Utama</div>
    <a href="../dashboard/dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="orders.php" class="sidebar-link active"><i class="bi bi-receipt"></i> Pesanan
      <span class="ms-auto k-badge k-badge-red" style="font-size:.65rem;"><?= $stats['diproses'] ?></span>
    </a>
    <a href="../products/products.php" class="sidebar-link"><i class="bi bi-cup-hot"></i> Produk</a>
    <a href="../customers/customers.php" class="sidebar-link"><i class="bi bi-people"></i> Pelanggan</a>
    <div class="sidebar-section-label mt-3">Laporan</div>
    <a href="#" class="sidebar-link"><i class="bi bi-bar-chart-line"></i> Analitik</a>
    <a href="#" class="sidebar-link"><i class="bi bi-cash-stack"></i> Keuangan</a>
    <div class="sidebar-section-label mt-3">Sistem</div>
    <a href="#" class="sidebar-link"><i class="bi bi-gear"></i> Pengaturan</a>
    <a href="../../home/home.php" class="sidebar-link"><i class="bi bi-arrow-left-circle"></i> Ke Toko</a>
  </nav>
  <div class="sidebar-footer"><div class="sidebar-user"><div class="sidebar-user-avatar">A</div><div><div style="color:#fff;font-weight:600;font-size:.8rem;">Admin</div><div style="font-size:.72rem;">admin@konnyusu.com</div></div></div></div>
</aside>

<div class="admin-main">
  <div class="admin-topbar">
    <h6>Kelola Pesanan</h6>
    <div style="display:flex;align-items:center;gap:.75rem;">
      <button style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.5rem 1rem;font-size:.82rem;font-weight:600;cursor:pointer;color:var(--text-mid);display:flex;align-items:center;gap:.4rem;">
        <i class="bi bi-download"></i> Export
      </button>
      <button style="background:none;border:none;font-size:1.2rem;color:var(--text-mid);position:relative;cursor:pointer;">
        <i class="bi bi-bell"></i>
        <span style="position:absolute;top:-4px;right:-4px;width:8px;height:8px;background:var(--danger);border-radius:50%;border:2px solid #fff;"></span>
      </button>
      <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">A</div>
    </div>
  </div>

  <div class="admin-body">

    <?php if($msg): ?>
    <div style="background:#ecfaf4;border:1px solid #a3e0c8;color:var(--success);border-radius:var(--radius-md);padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
      <i class="bi bi-check-circle-fill"></i> <?= $msg ?>
    </div>
    <?php endif; ?>

    <!-- Stat Strip -->
    <div class="stat-strip animate-fadeup">
      <div class="stat-strip-card"><div class="stat-strip-card__val"><?= $stats['total'] ?></div><div class="stat-strip-card__lbl">Total Pesanan</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--success);"><?= $stats['selesai'] ?></div><div class="stat-strip-card__lbl">Selesai</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--accent);"><?= $stats['diproses'] ?></div><div class="stat-strip-card__lbl">Diproses</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--text-muted);"><?= $stats['dikirim'] ?></div><div class="stat-strip-card__lbl">Dikirim</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--danger);"><?= $stats['batal'] ?></div><div class="stat-strip-card__lbl">Dibatalkan</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val">Rp <?= number_format($stats['revenue']/1000,0)  ?>K</div><div class="stat-strip-card__lbl">Pendapatan</div></div>
    </div>

    <!-- Filter + Search Toolbar -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.1rem 1.25rem;margin-bottom:1.25rem;">
      <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div class="filter-tabs" style="margin-bottom:0;">
          <?php
          $tabs=[['all','Semua'],['Diproses','Diproses'],['Dikirim','Dikirim'],['Selesai','Selesai'],['Dibatalkan','Dibatalkan']];
          foreach($tabs as $t):
          ?>
          <a href="orders.php?status=<?= $t[0] ?>" class="filter-tab <?= $filterStatus===$t[0]?'active':'' ?>">
            <?= $t[1] ?>
            <?php if($t[0]==='Diproses'&&$stats['diproses']>0): ?>
              <span style="background:var(--danger);color:#fff;border-radius:50%;width:16px;height:16px;font-size:.6rem;display:inline-flex;align-items:center;justify-content:center;margin-left:.2rem;"><?= $stats['diproses'] ?></span>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
        <div style="position:relative;margin-left:auto;max-width:240px;flex:1;">
          <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem;"></i>
          <input type="text" id="searchInput" placeholder="Cari nama pelanggan / ID..." style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:40px;padding:.5rem .9rem .5rem 2.2rem;font-family:var(--font-body);font-size:.82rem;outline:none;">
        </div>
        <select style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.48rem .85rem;font-family:var(--font-body);font-size:.82rem;outline:none;color:var(--text-mid);">
          <option>Semua Pembayaran</option><option>QRIS</option><option>GoPay</option><option>OVO</option><option>Dana</option><option>BCA Transfer</option>
        </select>
      </div>
    </div>

    <!-- Orders Table -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;" class="animate-fadeup">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.9rem;font-weight:700;color:var(--primary);">Daftar Pesanan</span>
        <span style="font-size:.78rem;color:var(--text-muted);"><?= count($filtered) ?> pesanan</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="k-table" id="ordersTable">
          <thead>
            <tr>
              <th><input type="checkbox" id="checkAll" style="cursor:pointer;accent-color:var(--primary);"></th>
              <th>ID Pesanan</th>
              <th>Pelanggan</th>
              <th>Item</th>
              <th>Total</th>
              <th>Pembayaran</th>
              <th>Status</th>
              <th>Waktu</th>
              <th style="text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($filtered as $o): ?>
          <tr class="order-row" data-name="<?= strtolower($o['customer']) ?>" data-id="<?= strtolower($o['id']) ?>">
            <td><input type="checkbox" class="row-check" style="cursor:pointer;accent-color:var(--primary);"></td>
            <td><span style="font-family:monospace;font-weight:700;color:var(--primary);"><?= $o['id'] ?></span></td>
            <td>
              <div style="display:flex;align-items:center;gap:.6rem;">
                <div style="width:30px;height:30px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;"><?= $o['customer'][0] ?></div>
                <div>
                  <div style="font-weight:600;font-size:.85rem;"><?= $o['customer'] ?></div>
                  <div style="font-size:.72rem;color:var(--text-muted);"><?= $o['phone'] ?></div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-size:.8rem;color:var(--text-mid);">
                <?php foreach($o['items'] as $item): ?>
                  <div><?= $item[0] ?> <span style="color:var(--text-muted);">x<?= $item[1] ?></span></div>
                <?php endforeach; ?>
              </div>
            </td>
            <td style="font-weight:700;color:var(--primary);"><?= formatRupiah($o['total']) ?></td>
            <td style="font-size:.8rem;color:var(--text-mid);">
              <div style="display:flex;align-items:center;gap:.3rem;">
                <i class="bi bi-credit-card" style="font-size:.8rem;"></i> <?= $o['payment'] ?>
              </div>
            </td>
            <td>
              <span class="k-badge <?= $statusBadge[$o['status']] ?>">
                <i class="bi <?= $statusIcon[$o['status']] ?>"></i>
                <?= $o['status'] ?>
              </span>
            </td>
            <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;"><?= $o['date'] ?></td>
            <td style="text-align:center;">
              <div style="display:flex;align-items:center;gap:.4rem;justify-content:center;">
                <button title="Detail Pesanan"
                  onclick="showDetail(<?= htmlspecialchars(json_encode($o), ENT_QUOTES) ?>)"
                  style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;"
                  onmouseover="this.style.background='var(--cream)';this.style.color='var(--primary)'" onmouseout="this.style.background='none';this.style.color='var(--text-muted)'">
                  <i class="bi bi-eye" style="font-size:.8rem;"></i>
                </button>
                <?php if($o['status']==='Diproses'): ?>
                <button title="Proses → Kirim"
                  onclick="if(confirm('Ubah status ke Dikirim?'))window.location.href='orders.php?update=<?= $o['id'] ?>&status=Dikirim'"
                  style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;"
                  onmouseover="this.style.background='#eff6ff';this.style.color='#3b82f6';this.style.borderColor='#3b82f6'" onmouseout="this.style.background='none';this.style.color='var(--text-muted)';this.style.borderColor='var(--border)'">
                  <i class="bi bi-truck" style="font-size:.8rem;"></i>
                </button>
                <?php endif; ?>
                <?php if($o['status']==='Dikirim'): ?>
                <button title="Tandai Selesai"
                  onclick="if(confirm('Tandai sebagai Selesai?'))window.location.href='orders.php?update=<?= $o['id'] ?>&status=Selesai'"
                  style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;"
                  onmouseover="this.style.background='#ecfaf4';this.style.color='var(--success)';this.style.borderColor='var(--success)'" onmouseout="this.style.background='none';this.style.color='var(--text-muted)';this.style.borderColor='var(--border)'">
                  <i class="bi bi-check-circle" style="font-size:.8rem;"></i>
                </button>
                <?php endif; ?>
                <button title="Cetak" style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;" onclick="window.print()">
                  <i class="bi bi-printer" style="font-size:.8rem;"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div style="padding:.85rem 1.25rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
        <span style="font-size:.78rem;color:var(--text-muted);">Menampilkan <?= count($filtered) ?> dari <?= count($allOrders) ?> pesanan</span>
        <div style="display:flex;gap:.35rem;">
          <?php for($p=1;$p<=3;$p++): ?>
          <button style="width:32px;height:32px;border-radius:var(--radius-sm);border:1.5px solid <?= $p===1?'var(--primary)':'var(--border)' ?>;background:<?= $p===1?'var(--primary)':'var(--white)' ?>;color:<?= $p===1?'#fff':'var(--text-mid)' ?>;font-size:.8rem;font-weight:600;cursor:pointer;">
            <?= $p ?>
          </button>
          <?php endfor; ?>
        </div>
      </div>
    </div>

  </div>
</div>
</div>

<!-- Order Detail Modal -->
<div class="modal-overlay" id="detailModal">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-header">
      <h5><i class="bi bi-receipt me-2"></i>Detail Pesanan <span id="modalOrderId" style="font-family:monospace;"></span></h5>
      <button onclick="closeModal()" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-footer">
      <button onclick="closeModal()" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Tutup</button>
      <button class="btn-brand" style="font-size:.85rem;padding:.55rem 1.2rem;" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak
      </button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live search in table
document.getElementById('searchInput').addEventListener('input',function(){
  const q=this.value.toLowerCase();
  document.querySelectorAll('.order-row').forEach(row=>{
    const name=row.dataset.name, id=row.dataset.id;
    row.style.display=(name.includes(q)||id.includes(q))?'':'none';
  });
});

// Check all
document.getElementById('checkAll').addEventListener('change',function(){
  document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked);
});

// Detail modal
function showDetail(o){
  document.getElementById('modalOrderId').textContent=o.id;
  let items='';
  o.items.forEach(it=>{
    items+=`<div style="display:flex;justify-content:space-between;padding:.4rem 0;font-size:.85rem;">
      <span style="color:var(--text-mid);">${it[0]} x${it[1]}</span>
      <span style="font-weight:600;color:var(--primary);">Rp ${(it[2]*it[1]).toLocaleString('id')}</span>
    </div>`;
  });
  const statusBadge={'Selesai':'k-badge-green','Diproses':'k-badge-accent','Dikirim':'k-badge-gray','Dibatalkan':'k-badge-red'};
  document.getElementById('modalBody').innerHTML=`
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <span class="k-badge ${statusBadge[o.status]||'k-badge-gray'}">${o.status}</span>
      <span style="font-size:.78rem;color:var(--text-muted);">${o.date}</span>
    </div>
    <div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;">
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.6rem;">Informasi Pelanggan</div>
      <div class="detail-row"><span>Nama</span><span>${o.customer}</span></div>
      <div class="detail-row"><span>Telepon</span><span>${o.phone}</span></div>
      <div class="detail-row"><span>Alamat</span><span style="max-width:220px;text-align:right;">${o.address}</span></div>
      <div class="detail-row"><span>Pembayaran</span><span>${o.payment}</span></div>
    </div>
    <div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;">
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.6rem;">Item Pesanan</div>
      ${items}
      <div style="border-top:2px solid var(--border);margin-top:.5rem;padding-top:.5rem;display:flex;justify-content:space-between;">
        <span style="font-weight:700;color:var(--primary);">Total</span>
        <span style="font-weight:800;color:var(--primary);font-size:1.05rem;">Rp ${o.total.toLocaleString('id')}</span>
      </div>
    </div>`;
  document.getElementById('detailModal').classList.add('open');
}
function closeModal(){document.getElementById('detailModal').classList.remove('open');}
document.getElementById('detailModal').addEventListener('click',function(e){if(e.target===this)closeModal();});
</script>
</body>
</html>
