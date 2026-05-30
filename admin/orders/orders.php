<?php
// admin/orders/orders.php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/order.php';
require_once __DIR__ . '/../../config/admin.php';
require_once __DIR__ . '/../../data/products.php';

requireAdmin();

$allOrders = getAllOrdersFromDB();
$filterStatus = $_GET['status'] ?? 'all';

if ($filterStatus !== 'all') {
    $allOrders = array_filter($allOrders, fn($o) => $o['order_status'] === $filterStatus);
}

$stats = [
    'total'     => count(getAllOrdersFromDB()),
    'selesai'   => count(array_filter(getAllOrdersFromDB(), fn($o)=>$o['order_status']==='completed')),
    'diproses'  => count(array_filter(getAllOrdersFromDB(), fn($o)=>$o['order_status']==='pending' || $o['order_status']==='processing')),
    'dikirim'   => count(array_filter(getAllOrdersFromDB(), fn($o)=>$o['order_status']==='shipped')),
    'batal'     => count(array_filter(getAllOrdersFromDB(), fn($o)=>$o['order_status']==='cancelled')),
    'revenue'   => array_sum(array_column(array_filter(getAllOrdersFromDB(), fn($o)=>$o['order_status']==='completed'),'total')),
];

function getAllOrdersFromDB(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT o.*, u.name as user_name, p.payment_method, p.payment_status, p.payment_proof FROM orders o LEFT JOIN users u ON o.user_id = u.user_id LEFT JOIN payment p ON o.order_id = p.order_id ORDER BY o.order_date DESC");
    return $stmt->fetchAll();
}

function getStatusBadge(string $status): string {
    $badges = [
        'pending' => 'k-badge-accent',
        'processing' => 'k-badge-accent',
        'shipped' => 'k-badge-gray',
        'completed' => 'k-badge-green',
        'cancelled' => 'k-badge-red'
    ];
    return $badges[$status] ?? 'k-badge-gray';
}

function getStatusLabel(string $status): string {
    $labels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}

function getStatusIcon(string $status): string {
    $icons = [
        'pending' => 'bi-clock-fill',
        'processing' => 'bi-clock-fill',
        'shipped' => 'bi-truck',
        'completed' => 'bi-check-circle-fill',
        'cancelled' => 'bi-x-circle-fill'
    ];
    return $icons[$status] ?? 'bi-question-circle';
}
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
.print-only{display:none;}
.cancel-modal-text{font-size:.9rem;color:var(--text-mid);margin-bottom:1rem;}
.cancel-form-group{margin-bottom:1rem;}
.cancel-form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text-dark);margin-bottom:.4rem;}
.cancel-form-group textarea{width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.7rem 1rem;font-family:var(--font-body);font-size:.88rem;color:var(--text-dark);outline:none;resize:vertical;min-height:80px;}
.cancel-form-group textarea:focus{border-color:var(--primary);}
.payment-proof-thumb{width:60px;height:60px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border);cursor:pointer;transition:transform .2s;}
.payment-proof-thumb:hover{transform:scale(1.05);}
.payment-proof-section{background:#ecfaf4;border-radius:var(--radius-md);padding:1rem;margin:1rem 0;border:1px solid #a3e0c8;}
.payment-proof-section img{max-width:100%;border-radius:var(--radius-sm);border:1px solid var(--border);cursor:pointer;}
.payment-proof-section.empty{background:#fff8ec;border-color:#f0cb7a;}
@media(max-width:900px){.admin-sidebar{display:none;}.admin-body{padding:1.25rem;}.stat-strip{grid-template-columns:repeat(3,1fr);}}
@media print{
    *{margin:0!important;padding:0!important;}
    html,body{height:auto!important;page-break-after:avoid!important;}
    .admin-layout,.admin-sidebar,.admin-topbar,.no-print,.stat-strip,.k-table{display:none!important;}
    #detailModal,#imagePreviewModal{display:block!important;position:static!important;width:100%!important;}
    .print-only{display:flex!important;flex-direction:column;align-items:center;justify-content:center;width:100%!important;text-align:center!important;}
    .modal-overlay{display:block!important;position:static!important;background:none!important;backdrop-filter:none!important;width:100%!important;}
    .modal-box{display:block!important;max-width:100%!important;width:100%!important;box-shadow:none!important;border:none!important;padding:0!important;}
    .modal-body{padding:0!important;}
    body{margin:0!important;padding:1.5cm!important;}
    .page-wrapper{padding-top:0!important;display:none!important;}
    @page {size: auto;margin: 0.5cm;}
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
    <a href="orders.php" class="sidebar-link active"><i class="bi bi-receipt"></i> Pesanan</a>
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
  <div class="admin-topbar no-print">
    <div class="admin-topbar__left">
      <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <h6>Kelola Pesanan</h6>
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

    <!-- Stat Strip -->
    <div class="stat-strip animate-fadeup no-print">
      <div class="stat-strip-card"><div class="stat-strip-card__val"><?= $stats['total'] ?></div><div class="stat-strip-card__lbl">Total Pesanan</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--success);"><?= $stats['selesai'] ?></div><div class="stat-strip-card__lbl">Selesai</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--accent);"><?= $stats['diproses'] ?></div><div class="stat-strip-card__lbl">Diproses</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--text-muted);"><?= $stats['dikirim'] ?></div><div class="stat-strip-card__lbl">Dikirim</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val" style="color:var(--danger);"><?= $stats['batal'] ?></div><div class="stat-strip-card__lbl">Dibatalkan</div></div>
      <div class="stat-strip-card"><div class="stat-strip-card__val">Rp <?= number_format($stats['revenue']/1000,0)  ?>K</div><div class="stat-strip-card__lbl">Pendapatan</div></div>
    </div>

    <!-- Filter + Search Toolbar -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.1rem 1.25rem;margin-bottom:1.25rem;" class="no-print">
      <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <div class="filter-tabs" style="margin-bottom:0;">
          <?php
          $tabs=[['all','Semua'],['pending','Menunggu'],['processing','Diproses'],['shipped','Dikirim'],['completed','Selesai'],['cancelled','Dibatalkan']];
          foreach($tabs as $t):
          ?>
          <a href="orders.php?status=<?= $t[0] ?>" class="filter-tab <?= $filterStatus===$t[0]?'active':'' ?>">
            <?= $t[1] ?>
          </a>
          <?php endforeach; ?>
        </div>
        <div style="position:relative;margin-left:auto;max-width:240px;flex:1;">
          <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem;"></i>
          <input type="text" id="searchInput" placeholder="Cari nama pelanggan / ID..." style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:40px;padding:.5rem .9rem .5rem 2.2rem;font-family:var(--font-body);font-size:.82rem;outline:none;">
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;" class="animate-fadeup no-print">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.9rem;font-weight:700;color:var(--primary);">Daftar Pesanan</span>
        <span style="font-size:.78rem;color:var(--text-muted);"><?= count($allOrders) ?> pesanan</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="k-table" id="ordersTable">
          <thead>
            <tr>
              <th>ID Pesanan</th>
              <th>Pelanggan</th>
              <th>Item</th>
              <th>Total</th>
              <th>Status</th>
              <th>Waktu</th>
              <th style="text-align:center" class="no-print">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($allOrders as $o):
            $orderItems = getOrderItems($o['order_id']);
            $customerName = $o['user_name'] ?? 'Pelanggan';
            $hasProof = !empty($o['payment_proof']);
          ?>
          <tr class="order-row" data-name="<?= strtolower($customerName) ?>" data-id="#<?= $o['order_id'] ?>">
            <td>
              <span style="font-family:monospace;font-weight:700;color:var(--primary);">#<?= $o['order_id'] ?></span>
              <?php if($hasProof): ?>
              <span class="k-badge k-badge-green" style="font-size:.65rem;margin-left:.35rem;" title="Bukti pembayaran uploaded">
                <i class="bi bi-paperclip"></i>
              </span>
              <?php endif; ?>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:.6rem;">
                <div style="width:30px;height:30px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;"><?= substr($customerName,0,1) ?></div>
                <div>
                  <div style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($customerName) ?></div>
                  <div style="font-size:.72rem;color:var(--text-muted);"><?= $o['phone'] ?? '-' ?></div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-size:.8rem;color:var(--text-mid);">
                <?php foreach($orderItems as $item): ?>
                  <div><?= htmlspecialchars($item['name'] ?? 'Produk') ?> <span style="color:var(--text-muted);">x<?= $item['quantity'] ?></span></div>
                <?php endforeach; ?>
              </div>
            </td>
            <td style="font-weight:700;color:var(--primary);"><?= formatRupiah($o['total']) ?></td>
            <td>
              <span class="k-badge <?= getStatusBadge($o['order_status']) ?>">
                <i class="bi <?= getStatusIcon($o['order_status']) ?>"></i>
                <?= getStatusLabel($o['order_status']) ?>
              </span>
            </td>
            <td style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;"><?= date('d M Y, H:i', strtotime($o['order_date'])) ?></td>
<?php $cancellationNote = getCancellationNote($o['order_id']); ?>
            <td style="text-align:center;" class="no-print">
              <div style="display:flex;align-items:center;gap:.4rem;justify-content:center;flex-wrap:wrap;">
                <button title="Detail & Cetak"
                  onclick="showDetail(<?= htmlspecialchars(json_encode([
                    'order_id' => $o['order_id'],
                    'customer' => $customerName,
                    'phone' => $o['phone'] ?? '-',
                    'address' => $o['address'] ?? '-',
                    'items' => $orderItems,
                    'total' => $o['total'],
                    'order_date' => date('d M Y, H:i', strtotime($o['order_date'])),
                    'status' => getStatusLabel($o['order_status']),
                    'order_type' => $o['order_type'] ?? 'dine_in',
                    'recipient_name' => $o['recipient_name'] ?? $customerName,
                    'recipient_phone' => $o['recipient_phone'] ?? $o['phone'] ?? '-',
                    'recipient_address' => $o['recipient_address'] ?? $o['address'] ?? '-',
                    'recipient_city' => $o['recipient_city'] ?? '-',
                    'recipient_postal' => $o['recipient_postal'] ?? '-',
                    'payment_method' => $o['payment_method'] ?? '-',
                    'payment_status' => $o['payment_status'] ?? 'pending',
                    'payment_proof' => $o['payment_proof'] ?? '',
                    'delivery_fee' => $o['delivery_fee'] ?? 0,
                    'tax' => $o['tax'] ?? 0,
                    'cancellation_note' => $cancellationNote
                  ]), ENT_QUOTES) ?>)"
                  style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;"
                  onmouseover="this.style.background='var(--cream)';this.style.color='var(--primary)'" onmouseout="this.style.background='none';this.style.color='var(--text-muted)'">
                  <i class="bi bi-eye" style="font-size:.8rem;"></i>
                </button>

                <?php if($o['order_status'] === 'pending'): ?>
                <form method="post" action="update-status.php" style="display:inline;">
                  <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                  <input type="hidden" name="status" value="processing">
                  <button type="submit" title="Proses Pesanan"
                    style="padding:.25rem .6rem;border-radius:var(--radius-sm);border:1.5px solid var(--accent);background:none;color:var(--accent);font-size:.75rem;font-weight:600;cursor:pointer;transition:all .2s;"
                    onmouseover="this.style.background='var(--accent)';this.style.color='#fff'" onmouseout="this.style.background='none';this.style.color='var(--accent)'">
                    Proses
                  </button>
                </form>
                <?php elseif($o['order_status'] === 'processing'): ?>
                <form method="post" action="update-status.php" style="display:inline;">
                  <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                  <input type="hidden" name="status" value="shipped">
                  <button type="submit" title="Kirim Pesanan"
                    style="padding:.25rem .6rem;border-radius:var(--radius-sm);border:1.5px solid var(--primary);background:none;color:var(--primary);font-size:.75rem;font-weight:600;cursor:pointer;transition:all .2s;"
                    onmouseover="this.style.background='var(--primary)';this.style.color='#fff'" onmouseout="this.style.background='none';this.style.color='var(--primary)'">
                    Kirim
                  </button>
                </form>
                <?php elseif($o['order_status'] === 'shipped'): ?>
                <form method="post" action="update-status.php" style="display:inline;">
                  <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                  <input type="hidden" name="status" value="completed">
                  <button type="submit" title="Selesaikan Pesanan"
                    style="padding:.25rem .6rem;border-radius:var(--radius-sm);border:1.5px solid var(--success);background:none;color:var(--success);font-size:.75rem;font-weight:600;cursor:pointer;transition:all .2s;"
                    onmouseover="this.style.background='var(--success)';this.style.color='#fff'" onmouseout="this.style.background='none';this.style.color='var(--success)'">
                    Selesai
                  </button>
                </form>
                <?php endif; ?>

                <?php if($o['order_status'] !== 'completed' && $o['order_status'] !== 'cancelled'): ?>
                <button type="button" title="Batalkan Pesanan"
                  onclick="openCancelModal(<?= $o['order_id'] ?>)"
                  style="padding:.25rem .6rem;border-radius:var(--radius-sm);border:1.5px solid var(--danger);background:none;color:var(--danger);font-size:.75rem;font-weight:600;cursor:pointer;transition:all .2s;"
                  onmouseover="this.style.background='var(--danger)';this.style.color='#fff'" onmouseout="this.style.background='none';this.style.color='var(--danger)'">
                  Batalkan
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($allOrders)):?>
          <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">Belum ada pesanan</td></tr>
          <?php endif;?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

</div>

<!-- Image Preview Modal -->
<div class="modal-overlay" id="imagePreviewModal">
  <div class="modal-box" style="max-width:600px;max-height:90vh;background:#000;">
    <div style="padding:1rem;display:flex;justify-content:flex-end;">
      <button onclick="closeImagePreview()" style="background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer;">×</button>
    </div>
    <div style="padding:0 1rem 1.5rem;text-align:center;">
      <img id="previewImage" src="" alt="Preview" style="max-width:100%;max-height:70vh;border-radius:var(--radius-md);">
    </div>
  </div>
</div>

<!-- Order Detail Modal -->
<div class="modal-overlay" id="detailModal">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-header no-print">
      <h5><i class="bi bi-receipt me-2"></i>Detail Pesanan <span id="modalOrderId" style="font-family:monospace;"></span></h5>
      <button onclick="closeModal()" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-footer no-print">
      <button onclick="closeModal()" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Tutup</button>
      <button class="btn-brand" style="font-size:.85rem;padding:.55rem 1.2rem;" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak PDF
      </button>
    </div>
  </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal-overlay" id="cancelModal">
  <div class="modal-box" style="max-width:420px;">
    <div class="modal-header">
      <h5><i class="bi bi-exclamation-triangle text-danger me-2"></i>Batalkan Pesanan</h5>
      <button onclick="closeCancelModal()" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button>
    </div>
    <div class="modal-body">
      <p class="cancel-modal-text">Pesanan <strong>#<span id="cancelOrderId"></span></strong> akan dibatalkan. Mohon berikan alasan pembatalan:</p>
      <form method="POST" action="update-status.php" id="cancelForm">
        <input type="hidden" name="order_id" id="cancelOrderInput">
        <input type="hidden" name="status" value="cancelled">
        <div class="cancel-form-group">
          <label for="cancellationNote">Catatan Pembatalan (opsional)</label>
          <textarea name="cancellation_note" id="cancellationNote" placeholder="Contoh: Stok produk habis, Pelanggan meminta pembatalan, dll..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button onclick="closeCancelModal()" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Batal</button>
      <button onclick="submitCancelForm()" class="btn-danger" style="background:var(--danger);color:#fff;border:none;border-radius:var(--radius-md);padding:.55rem 1.2rem;font-size:.85rem;font-weight:600;cursor:pointer;">
        <i class="bi bi-x-circle me-1"></i>Konfirmasi Batalkan
      </button>
    </div>
  </div>
</div>

<style>
.btn-danger{background:var(--danger);color:#fff;border:none;border-radius:var(--radius-md);padding:.55rem 1.2rem;font-size:.85rem;font-weight:600;cursor:pointer;transition:background .2s;}
.btn-danger:hover{background:#c0392b;}
</style>

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

// Image preview
function previewImage(src) {
  document.getElementById('previewImage').src = src;
  document.getElementById('imagePreviewModal').classList.add('open');
}

function closeImagePreview() {
  document.getElementById('imagePreviewModal').classList.remove('open');
}

// Detail modal
function showDetail(o){
  document.getElementById('modalOrderId').textContent='#'+o.order_id;

  // Show/hide print button based on status
  const printBtn = document.querySelector('#detailModal .modal-footer .btn-brand');
  const isPrintable = o.status === 'Selesai' || o.status === 'Dibatalkan';
  if (printBtn) {
    printBtn.style.display = isPrintable ? 'inline-flex' : 'none';
  }

  let items='';
  o.items.forEach(it=>{
    const itemTotal = (it.price || 0) * (it.quantity || 1);
    items+=`<div style="display:flex;justify-content:space-between;padding:.4rem 0;font-size:.85rem;">
      <span style="color:var(--text-mid);">${it.name || 'Produk'} x${it.quantity || 1}</span>
      <span style="font-weight:600;color:var(--primary);">Rp ${itemTotal.toLocaleString('id')}</span>
    </div>`;
  });

  const paymentMethodNames = {
    'qris': 'QRIS',
    'gopay': 'GoPay',
    'ovo': 'OVO',
    'dana': 'DANA',
    'bca': 'BCA Transfer',
    'cod': 'Bayar di Tempat',
    'cash': 'Tunai'
  };

  const orderTypeNames = {
    'dine_in': 'Makan di Tempat',
    'takeaway': 'Ambil Sendiri',
    'delivery': 'Pengiriman',
    'priority': 'Prioritas (< 20 menit)',
    'standard': 'Standar (30 menit)',
    'pickup': 'Ambil Sendiri',
    'instant': 'Prioritas (< 20 menit)',
    'same_day': 'Standar (30 menit)'
  };

  const deliveryFeeNames = {
    8000: 'Rp 8.000',
    5000: 'Rp 5.000',
    0: 'Gratis'
  };

  const paymentMethodDisplay = paymentMethodNames[o.payment_method] || o.payment_method;
  const orderTypeDisplay = orderTypeNames[o.order_type] || o.order_type;
  let deliveryFeeDisplay;
  const isPickup = o.order_type === 'pickup' || o.order_type === 'takeaway';
  if (isPickup) {
    deliveryFeeDisplay = ' - Gratis';
  } else if (o.delivery_fee > 0) {
    const feeLabel = deliveryFeeNames[o.delivery_fee] || `Rp ${o.delivery_fee.toLocaleString('id')}`;
    deliveryFeeDisplay = ` - ${feeLabel}`;
  } else {
    deliveryFeeDisplay = ' - Gratis';
  }
  const orderTypeWithFee = orderTypeDisplay + deliveryFeeDisplay;

  const subtotal = o.total - (o.delivery_fee || 0) - (o.tax || 0);

  // Payment proof section
  let paymentProofHtml = '';
  if (o.payment_proof) {
    const proofUrl = '../../assets/uploads/payment_proofs/' + o.payment_proof;
    paymentProofHtml = '<div class="payment-proof-section">' +
      '<div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--success);margin-bottom:.6rem;">' +
        '<i class="bi bi-paperclip me-1"></i>Bukti Pembayaran' +
      '</div>' +
      '<img src="' + proofUrl + '" alt="Bukti Pembayaran" class="payment-proof-thumb" onclick="previewImage(this.src)" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'block\'">' +
      '<div style="display:none;font-size:.78rem;color:var(--text-muted);padding:.5rem;">Gambar tidak ditemukan</div>' +
      '<p style="font-size:.72rem;color:var(--success);margin:.5rem 0 0 0;"><i class="bi bi-check-circle"></i> Bukti pembayaran sudah diupload oleh pelanggan</p>' +
    '</div>';
  } else if (o.payment_method !== 'cod' && o.payment_method !== 'cash') {
    paymentProofHtml = '<div class="payment-proof-section empty">' +
      '<div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--accent);margin-bottom:.6rem;">' +
        '<i class="bi bi-clock me-1"></i>Bukti Pembayaran' +
      '</div>' +
      '<p style="font-size:.82rem;color:var(--text-mid);margin:0;">Belum ada bukti pembayaran</p>' +
    '</div>';
  }

  document.getElementById('modalBody').innerHTML=`
    <div class="print-only" style="display:none;text-align:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px dashed var(--border);">
      <img src="../../assets/img/logo.jpeg" alt="Konnyusu" style="height:100px;object-fit:contain;margin:0 auto;display:block;">
      <p style="color:var(--primary);margin:1rem 0 0.25rem 0;font-family:var(--font-display);font-size:1.4rem;font-weight:700;text-align:center;">Bukti Pemesanan</p>
      <p style="color:var(--text-muted);margin:0;font-size:1rem;font-family:monospace;font-weight:600;text-align:center;">#${o.order_id}</p>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
      <span class="k-badge ${o.status==='Selesai'?'k-badge-green':(o.status==='Dibatalkan'?'k-badge-red':'k-badge-accent')}">${o.status}</span>
      <span style="font-size:.78rem;color:var(--text-muted);">${o.order_date}</span>
    </div>
    <div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;">
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.6rem;">Informasi Penerima</div>
      <div class="detail-row"><span>Nama</span><span>${o.recipient_name}</span></div>
      <div class="detail-row"><span>Telepon</span><span>${o.recipient_phone}</span></div>
      <div class="detail-row"><span>Alamat</span><span style="max-width:220px;text-align:right;">${o.recipient_address}</span></div>
      ${o.recipient_city !== '-' ? `<div class="detail-row"><span>Kota</span><span>${o.recipient_city}</span></div>` : ''}
      ${o.recipient_postal !== '-' ? `<div class="detail-row"><span>Kode Pos</span><span>${o.recipient_postal}</span></div>` : ''}
    </div>
    <div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;">
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.6rem;">Detail Pengiriman & Pembayaran</div>
      <div class="detail-row"><span>Metode Pengiriman</span><span>${orderTypeWithFee}</span></div>
      <div class="detail-row"><span>Metode Pembayaran</span><span>${paymentMethodDisplay}</span></div>
    </div>
    ${paymentProofHtml}
    <div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;">
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.6rem;">Item Pesanan</div>
      ${items}
      <div style="border-top:1px solid var(--border);margin-top:.5rem;padding-top:.5rem;">
        <div class="detail-row"><span>Subtotal</span><span>Rp ${subtotal.toLocaleString('id')}</span></div>
        <div class="detail-row"><span>Ongkos Kirim</span><span>Rp ${(o.delivery_fee || 0).toLocaleString('id')}</span></div>
        <div class="detail-row"><span>Pajak (1%)</span><span>Rp ${(o.tax || 0).toLocaleString('id')}</span></div>
        <div style="border-top:2px solid var(--border);margin-top:.5rem;padding-top:.5rem;display:flex;justify-content:space-between;">
          <span style="font-weight:700;color:var(--primary);">Total</span>
          <span style="font-weight:800;color:var(--primary);font-size:1.05rem;">Rp ${o.total.toLocaleString('id')}</span>
        </div>
      </div>
    </div>
    ${o.cancellation_note ? `
    <div style="background:#fdf0f0;border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;border:1px solid #f5b8b8;">
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--danger);margin-bottom:.6rem;"><i class="bi bi-exclamation-circle me-1"></i>Alasan Pembatalan</div>
      <p style="font-size:.85rem;color:var(--text-dark);margin:0;line-height:1.5;">${o.cancellation_note}</p>
    </div>
    ` : ''}
    <div class="print-only" style="text-align:center;margin-top:2rem;padding-top:1rem;border-top:1px dashed var(--border);">
      <p style="font-size:.85rem;color:var(--text-muted);margin:0;">Terima kasih sudah memesan di Konnyusu!</p>
      <p style="font-size:.75rem;color:var(--text-muted);margin:.25rem 0 0 0;">— Semoga harimu menyenangkan —</p>
    </div>`;
  document.getElementById('detailModal').classList.add('open');
}

function closeModal(){document.getElementById('detailModal').classList.remove('open');}
document.getElementById('detailModal').addEventListener('click',function(e){if(e.target===this)closeModal();});
document.getElementById('imagePreviewModal').addEventListener('click',function(e){if(e.target===this)closeImagePreview();});

// Cancel modal functions
function openCancelModal(orderId) {
  document.getElementById('cancelOrderId').textContent = orderId;
  document.getElementById('cancelOrderInput').value = orderId;
  document.getElementById('cancellationNote').value = '';
  document.getElementById('cancelModal').classList.add('open');
}

function closeCancelModal() {
  document.getElementById('cancelModal').classList.remove('open');
}

function submitCancelForm() {
  document.getElementById('cancelForm').submit();
}

document.getElementById('cancelModal').addEventListener('click', function(e) {
  if (e.target === this) closeCancelModal();
});
</script>

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