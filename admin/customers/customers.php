<?php
// admin/customers/customers.php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../data/products.php';

requireAdmin();

// Get all users from database
function getAllUsers(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT user_id, name, email, phone, address, created_at FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

// Get user order count
function getUserOrderCount(int $userId): int {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

// Get user total spent
function getUserTotalSpent(int $userId): int {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = ? AND order_status = 'completed'");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

$users = getAllUsers();
$stats = [
    'total' => count($users),
    'active' => count($users),
    'new_mth' => 0,
    'avg_spent' => 0
];

if ($stats['total'] > 0) {
    $totalSpent = 0;
    foreach ($users as $u) {
        $totalSpent += getUserTotalSpent($u['user_id']);
    }
    $stats['avg_spent'] = (int)($totalSpent / $stats['total']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kelola Pelanggan - Admin Konnyusu</title>
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
.stat-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.75rem;}
.stat-strip-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.1rem 1.25rem;display:flex;align-items:center;gap:.85rem;}
.stat-strip-card__icon{width:42px;height:42px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0;}
.stat-strip-card__val{font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--primary);}
.stat-strip-card__lbl{font-size:.7rem;color:var(--text-muted);}
.k-table{width:100%;border-collapse:collapse;}
.k-table th{font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);padding:.6rem 1rem;text-align:left;border-bottom:1.5px solid var(--border);background:var(--cream);white-space:nowrap;}
.k-table td{padding:.85rem 1rem;font-size:.855rem;color:var(--text-dark);border-bottom:1px solid var(--border);vertical-align:middle;}
.k-table tr:last-child td{border-bottom:none;}
.k-table tr:hover td{background:var(--cream);}
@media(max-width:900px){.admin-sidebar{display:none;}.admin-body{padding:1.25rem;}.stat-strip{grid-template-columns:1fr 1fr;}}
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
    <a href="customers.php" class="sidebar-link active"><i class="bi bi-people"></i> Pelanggan</a>
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
    <h6>Kelola Pelanggan</h6>
  </div>

  <div class="admin-body">
    <!-- Stats -->
    <div class="stat-strip animate-fadeup">
      <div class="stat-strip-card">
        <div class="stat-strip-card__icon" style="background:#ecfaf4;color:var(--success);"><i class="bi bi-people-fill"></i></div>
        <div><div class="stat-strip-card__val"><?= $stats['total'] ?></div><div class="stat-strip-card__lbl">Total Pelanggan</div></div>
      </div>
      <div class="stat-strip-card">
        <div class="stat-strip-card__icon" style="background:#eff6ff;color:#3b82f6;"><i class="bi bi-person-check-fill"></i></div>
        <div><div class="stat-strip-card__val"><?= $stats['active'] ?></div><div class="stat-strip-card__lbl">Pelanggan Aktif</div></div>
      </div>
      <div class="stat-strip-card">
        <div class="stat-strip-card__icon" style="background:#fff8ec;color:var(--accent);"><i class="bi bi-person-plus-fill"></i></div>
        <div><div class="stat-strip-card__val"><?= $stats['new_mth'] ?></div><div class="stat-strip-card__lbl">Baru Bulan Ini</div></div>
      </div>
      <div class="stat-strip-card">
        <div class="stat-strip-card__icon" style="background:#f3f0ff;color:#8b5cf6;"><i class="bi bi-cash-stack"></i></div>
        <div><div class="stat-strip-card__val">Rp <?= number_format($stats['avg_spent']/1000,0) ?>K</div><div class="stat-strip-card__lbl">Rata-rata Belanja</div></div>
      </div>
    </div>

    <!-- Table -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;" class="animate-fadeup">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.9rem;font-weight:700;color:var(--primary);">Data Pelanggan</span>
        <span style="font-size:.78rem;color:var(--text-muted);"><?= count($users) ?> pelanggan</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="k-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Pelanggan</th>
              <th>Kontak</th>
              <th>Bergabung</th>
              <th>Total Pesanan</th>
              <th>Total Belanja</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($users)):?>
          <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">Belum ada pelanggan</td></tr>
          <?php else:?>
          <?php foreach($users as $i=>$u):
            $orderCount = getUserOrderCount($u['user_id']);
            $totalSpent = getUserTotalSpent($u['user_id']);
          ?>
          <tr>
            <td style="color:var(--text-muted);font-size:.8rem;"><?= $i+1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:.75rem;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0;">
                  <?= substr($u['name'], 0, 1) ?>
                </div>
                <div>
                  <div style="font-weight:700;font-size:.88rem;color:var(--primary);"><?= htmlspecialchars($u['name']) ?></div>
                  <div style="font-size:.73rem;color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td style="font-size:.82rem;color:var(--text-mid);">
              <div style="display:flex;align-items:center;gap:.3rem;"><i class="bi bi-phone" style="font-size:.75rem;"></i><?= htmlspecialchars($u['phone'] ?? '-') ?></div>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:.4rem;">
                <span style="font-weight:700;color:var(--primary);"><?= $orderCount ?></span>
                <span style="font-size:.72rem;color:var(--text-muted);">pesanan</span>
              </div>
            </td>
            <td style="font-weight:700;color:var(--primary);"><?= formatRupiah($totalSpent) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif;?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

</div>
</body>
</html>
