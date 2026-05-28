<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/admin.php';
require_once __DIR__ . '/../../data/products.php';

requireAdmin();

$msg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (int) ($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $stock = (int) ($_POST['stock'] ?? 0);
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $isBest = isset($_POST['is_best']) ? 1 : 0;
    
    // Handle upload foto
    $image = null;
    $uploadDir = __DIR__ . '/../../assets/img/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($ext, $allowedExts) && $file['size'] <= 5 * 1024 * 1024) {
            $fileName = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $image = $fileName;
            }
        }
    }
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO menu (name, description, price, category, image, stock, is_new, is_best) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category, $image, $stock, $isNew, $isBest]);
            $msg = 'success:Produk berhasil ditambahkan!';
        } elseif ($action === 'edit') {
            $menuId = (int) $_POST['id'];
            if ($image) {
                $stmt = $pdo->prepare("UPDATE menu SET name=?, description=?, price=?, category=?, image=?, stock=?, is_new=?, is_best=? WHERE menu_id=?");
                $stmt->execute([$name, $description, $price, $category, $image, $stock, $isNew, $isBest, $menuId]);
            } else {
                $stmt = $pdo->prepare("UPDATE menu SET name=?, description=?, price=?, category=?, stock=?, is_new=?, is_best=? WHERE menu_id=?");
                $stmt->execute([$name, $description, $price, $category, $stock, $isNew, $isBest, $menuId]);
            }
            $msg = 'success:Produk berhasil diperbarui!';
        } elseif ($action === 'delete') {
            $menuId = (int) ($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM menu WHERE menu_id = ?");
            $stmt->execute([$menuId]);
            $msg = 'success:Produk berhasil dihapus!';
        }
    } catch (Exception $e) {
        $msg = 'error:Gagal! ' . $e->getMessage();
    }
}

require_once __DIR__ . '/../../config/order.php';

// Get all products with real sold count from completed orders
$products = getProductsWithRealSold();

// Stats
$stats = [
    'total'      => count($products),
    'best'       => count(array_filter($products, fn($p) => $p['is_best'])),
    'new'        => count(array_filter($products, fn($p) => $p['is_new'])),
    'total_sold' => array_sum(array_column($products, 'real_sold'))
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Produk - Admin Konnyusu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/global.css">
<style>
.admin-layout{display:flex;min-height:100vh;}
.admin-sidebar{width:240px;flex-shrink:0;background:var(--primary);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto;transform:translateX(0);transition:transform .3s ease;z-index:999;}
.admin-sidebar.closed{transform:translateX(-100%);position:fixed;left:0;box-shadow:4px 0 20px rgba(0,0,0,.15);}
.sidebar-brand{padding:1.8rem 1rem;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;}
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
.stat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;display:flex;align-items:flex-start;gap:.9rem;transition:transform .2s,box-shadow .2s;}
.stat-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-md);}
.stat-card__icon{width:44px;height:44px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;}
.stat-card__icon--green{background:#ecfaf4;color:var(--success);}
.stat-card__icon--amber{background:#fff8ec;color:var(--accent);}
.stat-card__icon--blue{background:#eff6ff;color:#3b82f6;}
.stat-card__icon--purple{background:#f3f0ff;color:#8b5cf6;}
.stat-card__val{font-family:var(--font-display);font-size:1.45rem;font-weight:700;color:var(--primary);line-height:1.1;}
.stat-card__label{font-size:.75rem;color:var(--text-muted);margin-top:.2rem;}
.search-filter{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.search-input{flex:1;min-width:200px;background:var(--white);border:1.5px solid var(--border);border-radius:40px;padding:.6rem 1rem .6rem 2.5rem;font-family:var(--font-body);font-size:.88rem;color:var(--text-dark);outline:none;position:relative;}
.search-input:focus{border-color:var(--primary-light);}
.k-table{width:100%;border-collapse:collapse;}
.k-table th{font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);padding:.6rem 1rem;text-align:left;border-bottom:1.5px solid var(--border);background:var(--cream);white-space:nowrap;}
.k-table td{padding:.8rem 1rem;font-size:.855rem;color:var(--text-dark);border-bottom:1px solid var(--border);vertical-align:middle;}
.k-table tr:last-child td{border-bottom:none;}
.k-table tr:hover td{background:var(--cream);}
.prod-thumb{width:48px;height:48px;object-fit:cover;border-radius:var(--radius-md);}
.k-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .6rem;border-radius:999px;font-size:.72rem;font-weight:700;letter-spacing:.03em;}
.k-badge-accent{background:#fff8ec;color:#926d1e;}
.k-badge-green{background:#ecfaf4;color:#0f766e;}
.k-badge-gray{background:#f3f4f6;color:#6b7280;}
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
.form-group textarea{resize:vertical;min-height:80px;}
.form-row2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.preview-img{width:100%;max-height:200px;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-top:.5rem;display:none;}
.btn-brand{background:var(--primary);color:#fff;border:none;border-radius:var(--radius-md);padding:.6rem 1.2rem;font-family:var(--font-body);font-weight:600;cursor:pointer;transition:background .2s,transform .15s;}
.btn-brand:hover{background:var(--primary-light);transform:translateY(-1px);}
.btn-outline-brand{background:transparent;color:var(--primary);border:1.5px solid var(--primary);border-radius:var(--radius-md);padding:.6rem 1.2rem;font-family:var(--font-body);font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;}
.btn-outline-brand:hover{background:var(--primary);color:#fff;}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:900px){.admin-sidebar{display:none;}.admin-body{padding:1.25rem;}.form-row2{grid-template-columns:1fr;}}
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
    <a href="products.php" class="sidebar-link active"><i class="bi bi-cup-hot"></i> Produk</a>
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
    <div class="admin-topbar__left">
      <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <h6>Kelola Produk</h6>
    </div>
    <button class="btn-brand" style="font-size:.85rem;padding:.55rem 1.1rem;" onclick="openModal('addModal')">
      <i class="bi bi-plus-lg me-1"></i> Tambah Produk
    </button>
  </div>
  <div class="admin-body">
    <?php if($msg):[$type,$text]=explode(':',$msg,2);?>
    <div style="background:<?=$type==='success'?'#ecfaf4':'#fff0f0'?>;border:1px solid <?=$type==='success'?'#a3e0c8':'#f5b8b8'?>;color:<?=$type==='success'?'var(--success)':'var(--danger)'?>;border-radius:var(--radius-md);padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
      <i class="bi bi-<?=$type==='success'?'check-circle-fill':'exclamation-triangle-fill'?>"></i> <?=$text?>
    </div>
    <?php endif;?>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--green"><i class="bi bi-box-seam-fill"></i></div>
        <div><div class="stat-card__val"><?=number_format($stats['total'])?></div><div class="stat-card__label">Total Produk</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--amber"><i class="bi bi-graph-up-arrow"></i></div>
        <div><div class="stat-card__val"><?=$stats['best']?></div><div class="stat-card__label">Best Seller</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--blue"><i class="bi bi-bag-plus-fill"></i></div>
        <div><div class="stat-card__val"><?=$stats['new']?></div><div class="stat-card__label">Produk Baru</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--purple"><i class="bi bi-cart-check-fill"></i></div>
        <div><div class="stat-card__val"><?=number_format($stats['total_sold'])?></div><div class="stat-card__label">Total Terjual</div></div>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="search-filter">
      <div style="position:relative;flex:1;min-width:200px;">
        <i class="bi bi-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
        <input type="text" placeholder="Cari produk..." class="search-input" id="searchInput">
      </div>
      <select id="categoryFilter" style="padding:.6rem 1rem;border:1.5px solid var(--border);border-radius:var(--radius-md);background:var(--white);font-family:var(--font-body);font-size:.85rem;color:var(--text-dark);">
        <option value="all">Semua Kategori</option>
        <option value="coffee">Coffee</option>
        <option value="non-coffee">Non-Coffee</option>
        <option value="makanan">Makanan</option>
      </select>
    </div>

    <!-- Table -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.9rem;font-weight:700;color:var(--primary);">Daftar Produk</span>
        <span style="font-size:.78rem;color:var(--text-muted);"><?=count($products)?> produk</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="k-table" id="productsTable">
          <thead><tr><th>No</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th style="text-align:center">Aksi</th></tr></thead>
          <tbody>
          <?php foreach($products as $i=>$p):
            $imgSrc=!empty($p['image'])?'../../assets/img/products/'.$p['image']:'';
          ?>
          <tr data-name="<?=strtolower($p['name'])?>" data-category="<?=$p['category']?>">
            <td style="color:var(--text-muted);font-size:.8rem;"><?=$i+1?></td>
            <td><div style="display:flex;align-items:center;gap:.85rem;">
              <img src="<?=$imgSrc?>" class="prod-thumb" onerror="this.src='https://placehold.co/48x48/1a3c2e/f0cb7a?text=K'">
              <div><div style="font-weight:700;font-size:.88rem;color:var(--primary);"><?=htmlspecialchars($p['name'])?></div>
              <div style="font-size:.75rem;color:var(--text-muted);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($p['description'])?></div></div>
            </div></td>
            <td><span class="k-badge k-badge-accent"><?=ucfirst($p['category'])?></span></td>
            <td style="font-weight:700;color:var(--primary);"><?=formatRupiah($p['price'])?></td>
            <td><?=$p['stock']?></td>
            <td style="text-align:center">
              <div style="display:flex;align-items:center;gap:.4rem;justify-content:center;">
                <button onclick="openEditModal(this, <?= (int)$p['id'] ?>)"
                  data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"
                  data-price="<?= (int)$p['price'] ?>"
                  data-category="<?= htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8') ?>"
                  data-desc="<?= htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8') ?>"
                  data-stock="<?= (int)$p['stock'] ?>"
                  data-is-new="<?= (int)$p['is_new'] ?>"
                  data-is-best="<?= (int)$p['is_best'] ?>"
                  data-image="<?= htmlspecialchars($p['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                  style="width:32px;height:32px;border-radius:var(--radius-md);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;" title="Edit">
                  <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                </button>
                <button onclick="openDeleteModal(this, <?= (int)$p['id'] ?>)"
                  data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"
                  style="width:32px;height:32px;border-radius:var(--radius-md);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;" title="Delete">
                  <i class="bi bi-trash3" style="font-size:.8rem;"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach;?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal-box">
    <div class="modal-header"><h5><i class="bi bi-plus-circle me-2"></i>Tambah Produk Baru</h5>
    <button onclick="closeModal('addModal')" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group"><label>Nama Produk *</label><input type="text" name="name" placeholder="Nama produk" required></div>
        <div class="form-row2">
          <div class="form-group"><label>Harga (Rp) *</label><input type="number" name="price" placeholder="25000" min="1000" required></div>
          <div class="form-group"><label>Kategori *</label>
            <select name="category" required><option value="">Pilih kategori</option>
            <option value="coffee">Coffee</option><option value="non-coffee">Non-Coffee</option>
            <option value="makanan">Makanan</option></select>
          </div>
        </div>
        <div class="form-group"><label>Deskripsi *</label><textarea name="description" required></textarea></div>
        <div class="form-row2">
          <div class="form-group">
            <label>Upload Foto Produk</label>
            <input type="file" name="image_file" id="addImageInput" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewAddImage(this)">
            <small style="font-size:.75rem;color:var(--text-muted);">Format: JPG, JPEG, PNG, WEBP | Maks: 5MB</small>
            <img id="addPreviewImg" class="preview-img">
          </div>
          <div class="form-group"><label>Stok</label><input type="number" name="stock" value="0" min="0"></div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:.5rem;">
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
            <input type="checkbox" name="is_new" value="1" style="accent-color:var(--primary);">
            <span style="font-size:.85rem;color:var(--text-mid);">Produk Baru</span>
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
            <input type="checkbox" name="is_best" value="1" style="accent-color:var(--primary);">
            <span style="font-size:.85rem;color:var(--text-mid);">Best Seller</span>
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('addModal')" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Batal</button>
        <button type="submit" class="btn-brand" style="font-size:.85rem;padding:.55rem 1.2rem;"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div class="modal-header"><h5><i class="bi bi-pencil me-2"></i>Edit Produk</h5>
    <button onclick="closeModal('editModal')" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button></div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="editId">
      <div class="modal-body">
        <div class="form-group"><label>Nama Produk *</label><input type="text" name="name" id="editName" required></div>
        <div class="form-row2">
          <div class="form-group"><label>Harga (Rp) *</label><input type="number" name="price" id="editPrice" required></div>
          <div class="form-group"><label>Kategori *</label>
            <select name="category" id="editCat" required>
            <option value="coffee">Coffee</option><option value="non-coffee">Non-Coffee</option>
            <option value="makanan">Makanan</option></select>
          </div>
        </div>
        <div class="form-group"><label>Deskripsi *</label><textarea name="description" id="editDesc" required></textarea></div>
        <div class="form-row2">
          <div class="form-group">
            <label>Upload Foto Baru (opsional)</label>
            <input type="file" name="image_file" id="editImageInput" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewEditImage(this)">
            <small style="font-size:.75rem;color:var(--text-muted);">Format: JPG, JPEG, PNG, WEBP | Maks: 5MB</small>
            <img id="editPreviewImg" class="preview-img">
          </div>
          <div class="form-group"><label>Stok</label><input type="number" name="stock" id="editStock" min="0"></div>
        </div>
        <div style="display:flex;gap:1rem;margin-top:.5rem;">
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
            <input type="checkbox" name="is_new" id="editIsNew" value="1" style="accent-color:var(--primary);">
            <span style="font-size:.85rem;color:var(--text-mid);">Produk Baru</span>
          </label>
          <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
            <input type="checkbox" name="is_best" id="editIsBest" value="1" style="accent-color:var(--primary);">
            <span style="font-size:.85rem;color:var(--text-mid);">Best Seller</span>
          </label>
        </div>
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
      <div style="font-weight:700;font-size:1rem;color:var(--danger);margin-bottom:.4rem;">Yakin hapus produk ini?</div>
      <div style="font-size:.83rem;color:var(--text-mid);line-height:1.5;">
        <strong id="deleteProductName"></strong> akan dihapus permanen.<br>Tindakan ini tidak bisa dibatalkan.
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}

function previewAddImage(input) {
  const img = document.getElementById('addPreviewImg');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) { img.src = e.target.result; img.style.display = 'block'; }
    reader.readAsDataURL(input.files[0]);
  } else { img.style.display = 'none'; }
}

function previewEditImage(input) {
  const img = document.getElementById('editPreviewImg');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) { img.src = e.target.result; img.style.display = 'block'; }
    reader.readAsDataURL(input.files[0]);
  } else { img.style.display = 'none'; }
}

function openEditModal(btn, id){
  document.getElementById('editId').value = id;
  document.getElementById('editName').value = btn.dataset.name;
  document.getElementById('editPrice').value = btn.dataset.price;
  document.getElementById('editDesc').value = btn.dataset.desc;
  document.getElementById('editStock').value = btn.dataset.stock;
  document.getElementById('editIsNew').checked = btn.dataset.isNew == '1';
  document.getElementById('editIsBest').checked = btn.dataset.isBest == '1';

  const editImg = document.getElementById('editPreviewImg');
  if (btn.dataset.image && btn.dataset.image.trim() !== '') {
    editImg.src = '../../assets/img/products/' + btn.dataset.image;
    editImg.style.display = 'block';
  } else {
    editImg.src = 'https://placehold.co/400x200/1a3c2e/f0cb7a?text=Konnyusu';
    editImg.style.display = 'block';
  }

  const sel = document.getElementById('editCat');
  for(let o of sel.options){ o.selected = (o.value === btn.dataset.category); }
  openModal('editModal');
}

function openDeleteModal(btn, id) {
  document.getElementById('deleteId').value = id;
  document.getElementById('deleteProductName').textContent = btn.dataset.name;
  openModal('deleteModal');
}

function showDeleteConfirm() {
  closeModal('deleteModal');
  openModal('deleteConfirmOverlay');
}

function closeDeleteConfirm() {
  closeModal('deleteConfirmOverlay');
}

function confirmDelete() {
  if (deleteFormRef) deleteFormRef.submit();
}

document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});
});

// Search & Filter
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const tableBody = document.getElementById('productsTable').querySelector('tbody');
const allRows = Array.from(tableBody.querySelectorAll('tr'));

function filterProducts() {
  const search = searchInput.value.toLowerCase();
  const category = categoryFilter.value;
  
  allRows.forEach(row => {
    const name = row.dataset.name;
    const cat = row.dataset.category;
    const matchSearch = name.includes(search);
    const matchCat = category === 'all' || cat === category;
    row.style.display = matchSearch && matchCat ? '' : 'none';
  });
}

searchInput.addEventListener('input', filterProducts);
categoryFilter.addEventListener('change', filterProducts);
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
