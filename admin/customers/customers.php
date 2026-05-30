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

$msg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
            $stmt->execute([$name, $email, $phone, $address, $userId]);
            $msg = 'success:Pelanggan berhasil diperbarui!';
        } elseif ($action === 'delete') {
            // Check if user has orders
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $stmt->execute([$userId]);
            $orderCount = $stmt->fetchColumn();

            if ($orderCount > 0) {
                $msg = 'error:Gagal! Pelanggan memiliki ' . $orderCount . ' pesanan.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $msg = 'success:Pelanggan berhasil dihapus!';
            }
        }
    } catch (Exception $e) {
        $msg = 'error:Gagal! ' . $e->getMessage();
    }
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
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
.modal-overlay.open{display:flex;}
.modal-box{background:var(--white);border-radius:var(--radius-lg);width:100%;max-width:560px;max-height:90vh;overflow-y:auto;animation:fadeUp .3s ease;}
.modal-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.modal-header h5{font-size:1rem;font-weight:700;color:var(--primary);margin:0;}
.modal-body{padding:1.5rem;}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.75rem;}
.form-group{margin-bottom:1rem;}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text-mid);margin-bottom:.4rem;}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.7rem 1rem;font-family:var(--font-body);font-size:.88rem;color:var(--text-dark);outline:none;transition:border-color .2s;}
.form-group textarea{resize:vertical;}
.btn-brand{background:var(--primary);color:#fff;border:none;border-radius:var(--radius-md);padding:.6rem 1.2rem;font-family:var(--font-body);font-weight:600;cursor:pointer;transition:background .2s,transform .15s;}
.btn-brand:hover{background:var(--primary-light);transform:translateY(-1px);}
.btn-outline-brand{background:transparent;color:var(--primary);border:1.5px solid var(--primary);border-radius:var(--radius-md);padding:.6rem 1.2rem;font-family:var(--font-body);font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;}
.btn-outline-brand:hover{background:var(--primary);color:#fff;}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
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
    <div class="admin-topbar__left">
      <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <h6>Kelola Pelanggan</h6>
    </div>
    <button class="btn-brand" style="font-size:.85rem;padding:.55rem 1.1rem;" onclick="openModal('addModal')">
      <i class="bi bi-plus-lg me-1"></i> Tambah Pelanggan
    </button>
  </div>

  <div class="admin-body">
    <?php if($msg):[$type,$text]=explode(':',$msg,2);?>
    <div style="background:<?=$type==='success'?'#ecfaf4':'#fff0f0'?>;border:1px solid <?=$type==='success'?'#a3e0c8':'#f5b8b8'?>;color:<?=$type==='success'?'var(--success)':'var(--danger)'?>;border-radius:var(--radius-md);padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
      <i class="bi bi-<?=$type==='success'?'check-circle-fill':'exclamation-triangle-fill'?>"></i> <?=$text?>
    </div>
    <?php endif;?>

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
              <th style="text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($users)):?>
          <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">Belum ada pelanggan</td></tr>
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
            <td style="text-align:center">
              <div style="display:flex;align-items:center;gap:.4rem;justify-content:center;">
                <button onclick="openEditModal(this, <?= (int)$u['user_id'] ?>)"
                  data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?>"
                  data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>"
                  data-phone="<?= htmlspecialchars($u['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  data-address="<?= htmlspecialchars($u['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  style="width:32px;height:32px;border-radius:var(--radius-md);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;" title="Edit">
                  <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                </button>
                <button onclick="openDeleteModal(this, <?= (int)$u['user_id'] ?>)"
                  data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?>"
                  style="width:32px;height:32px;border-radius:var(--radius-md);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;" title="Hapus">
                  <i class="bi bi-trash3" style="font-size:.8rem;"></i>
                </button>
              </div>
            </td>
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

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-header"><h5><i class="bi bi-pencil me-2"></i>Edit Pelanggan</h5>
    <button onclick="closeModal('editModal')" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="editId">
      <div class="modal-body">
        <div class="form-group"><label>Nama Lengkap *</label><input type="text" name="name" id="editName" required></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" id="editEmail" required></div>
        <div class="form-group"><label>No. Telepon</label><input type="text" name="phone" id="editPhone" placeholder="08xxxxxxxxxx"></div>
        <div class="form-group"><label>Alamat</label><textarea name="address" id="editAddress" rows="3" placeholder="Alamat lengkap"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('editModal')" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Batal</button>
        <button type="submit" class="btn-brand" style="font-size:.85rem;padding:.55rem 1.2rem;"><i class="bi bi-check-lg me-1"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box" style="max-width:360px;">
    <div style="text-align:center;padding:1.5rem 1.5rem 1rem;">
      <div style="width:56px;height:56px;border-radius:50%;background:#fff0f0;color:var(--danger);display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;font-size:1.6rem;">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
      <div style="font-weight:700;font-size:1rem;color:var(--danger);margin-bottom:.4rem;">Yakin hapus pelanggan ini?</div>
      <div style="font-size:.83rem;color:var(--text-mid);line-height:1.5;">
        <strong id="deleteCustomerName"></strong> akan dihapus permanen.<br>Tindakan ini tidak bisa dibatalkan.
      </div>
    </div>
    <form method="POST" id="deleteForm">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="deleteId">
    </form>
    <div style="display:flex;gap:.6rem;padding:0 1.5rem 1.5rem;">
      <button type="button" onclick="closeModal('deleteModal')" class="btn-outline-brand" style="flex:1;font-size:.85rem;padding:.55rem;">
        Batal
      </button>
      <button type="button" onclick="document.getElementById('deleteForm').submit()" class="btn-brand" style="flex:1;font-size:.85rem;padding:.55rem;background:var(--danger);">
        <i class="bi bi-trash3 me-1"></i>Ya, Hapus
      </button>
    </div>
  </div>
</div>

<!-- Add Customer Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    
    <div class="modal-header">
      <h5>
        <i class="bi bi-person-plus me-2"></i>
        Tambah Pelanggan
      </h5>

      <button onclick="closeModal('addModal')"
        style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">
        ×
      </button>
    </div>

    <form>
      <div class="modal-body">

        <div class="form-group">
          <label>Nama Lengkap *</label>
          <input type="text" placeholder="Masukkan nama pelanggan">
        </div>

        <div class="form-group">
          <label>Email *</label>
          <input type="email" placeholder="Masukkan email">
        </div>

        <div class="form-group">
          <label>No. Telepon</label>
          <input type="text" placeholder="08xxxxxxxxxx">
        </div>

        <div class="form-group">
          <label>Alamat</label>
          <textarea rows="3" placeholder="Alamat lengkap"></textarea>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button"
          onclick="closeModal('addModal')"
          class="btn-outline-brand">

          Batal
        </button>

        <button type="submit" class="btn-brand">
          <i class="bi bi-plus-lg me-1"></i>
          Tambah
        </button>
      </div>
    </form>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}

function openEditModal(btn, id){
  document.getElementById('editId').value = id;
  document.getElementById('editName').value = btn.dataset.name;
  document.getElementById('editEmail').value = btn.dataset.email;
  document.getElementById('editPhone').value = btn.dataset.phone || '';
  document.getElementById('editAddress').value = btn.dataset.address || '';
  openModal('editModal');
}

function openDeleteModal(btn, id) {
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteCustomerName').textContent = btn.dataset.name;
  openModal('deleteModal');
}

document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});
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
