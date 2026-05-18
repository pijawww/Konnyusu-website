<?php
session_start();
include __DIR__ . '/../../data/products.php';
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])){
    if($_POST['action']==='add') $msg='success:Produk berhasil ditambahkan!';
    if($_POST['action']==='edit') $msg='success:Produk berhasil diperbarui!';
}
if(isset($_GET['delete'])) $msg='success:Produk berhasil dihapus!';
$search=strtolower(trim($_GET['q']??''));
$filtered=$search?array_filter($products,fn($p)=>stripos($p['name'],$search)!==false):$products;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kelola Produk - Admin Konnyusu</title>
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
.k-table{width:100%;border-collapse:collapse;}
.k-table th{font-size:.72rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);padding:.6rem 1rem;text-align:left;border-bottom:1.5px solid var(--border);background:var(--cream);white-space:nowrap;}
.k-table td{padding:.85rem 1rem;font-size:.875rem;color:var(--text-dark);border-bottom:1px solid var(--border);vertical-align:middle;}
.k-table tr:last-child td{border-bottom:none;}
.k-table tr:hover td{background:var(--cream);}
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
.prod-thumb{width:48px;height:48px;object-fit:cover;border-radius:var(--radius-md);}
@media(max-width:900px){.admin-sidebar{display:none;}.admin-body{padding:1.25rem;}}
</style>
</head>
<body>
<div class="admin-layout">
<aside class="admin-sidebar">
  <div class="sidebar-brand"><span style="font-size:1.35rem">☕</span><span class="sidebar-brand-text">Konnyusu</span></div>
  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Utama</div>
    <a href="../dashboard/dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../orders/orders.php" class="sidebar-link"><i class="bi bi-receipt"></i> Pesanan</a>
    <a href="products.php" class="sidebar-link active"><i class="bi bi-cup-hot"></i> Produk</a>
    <a href="../customers/customers.php" class="sidebar-link"><i class="bi bi-people"></i> Pelanggan</a>
    <div class="sidebar-section-label mt-3">Laporan</div>
    <a href="#" class="sidebar-link"><i class="bi bi-bar-chart-line"></i> Analitik</a>
    <div class="sidebar-section-label mt-3">Sistem</div>
    <a href="#" class="sidebar-link"><i class="bi bi-gear"></i> Pengaturan</a>
    <a href="../../home/home.php" class="sidebar-link"><i class="bi bi-arrow-left-circle"></i> Ke Toko</a>
  </nav>
  <div class="sidebar-footer"><div class="sidebar-user"><div class="sidebar-user-avatar">A</div><div><div style="color:#fff;font-weight:600;font-size:.8rem;">Admin</div><div style="font-size:.72rem;">admin@konnyusu.com</div></div></div></div>
</aside>
<div class="admin-main">
  <div class="admin-topbar">
    <h6>Kelola Produk</h6>
    <button class="btn-brand" style="font-size:.85rem;padding:.55rem 1.1rem;" onclick="openModal('addModal')">
      <i class="bi bi-plus-lg me-1"></i> Tambah Produk
    </button>
  </div>
  <div class="admin-body">
    <?php if($msg):[$type,$text]=explode(':',$msg,2);?>
    <div style="background:#ecfaf4;border:1px solid #a3e0c8;color:var(--success);border-radius:var(--radius-md);padding:.75rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
      <i class="bi bi-check-circle-fill"></i> <?=$text?>
    </div>
    <?php endif;?>
    <!-- Toolbar -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      <form method="GET" action="" style="display:flex;gap:.5rem;flex:1;max-width:340px;">
        <div style="position:relative;flex:1;">
          <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem;"></i>
          <input type="text" name="q" value="<?=htmlspecialchars($search)?>" placeholder="Cari produk..." style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:40px;padding:.55rem .9rem .55rem 2.2rem;font-family:var(--font-body);font-size:.85rem;outline:none;">
        </div>
        <button type="submit" class="btn-brand" style="padding:.55rem 1rem;font-size:.85rem;">Cari</button>
      </form>
      <select style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.5rem .85rem;font-family:var(--font-body);font-size:.82rem;outline:none;color:var(--text-mid);margin-left:auto;">
        <option>Semua Kategori</option><option>Coffee</option><option>Non-Coffee</option><option>Tea</option><option>Dessert</option>
      </select>
    </div>
    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
      <?php
      $ps=[
        ['Total Produk',count($products),'bi-cup-hot','#ecfaf4','var(--success)'],
        ['Best Seller',count(array_filter($products,fn($p)=>$p['is_best'])),'bi-award','#fff8ec','var(--accent)'],
        ['Produk Baru',count(array_filter($products,fn($p)=>$p['is_new'])),'bi-stars','#eff6ff','#3b82f6'],
        ['Rata-rata Rating',number_format(array_sum(array_column($products,'rating'))/count($products),2),'bi-star-fill','#f3f0ff','#8b5cf6'],
      ];
      foreach($ps as $s):?>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.1rem;display:flex;align-items:center;gap:.75rem;">
        <div style="width:40px;height:40px;border-radius:var(--radius-md);background:<?=$s[3]?>;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:<?=$s[4]?>;flex-shrink:0;">
          <i class="bi <?=$s[2]?>"></i>
        </div>
        <div><div style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--primary);"><?=$s[1]?></div><div style="font-size:.72rem;color:var(--text-muted);"><?=$s[0]?></div></div>
      </div>
      <?php endforeach;?>
    </div>
    <!-- Table -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.9rem;font-weight:700;color:var(--primary);">Daftar Produk</span>
        <span style="font-size:.78rem;color:var(--text-muted);"><?=count($filtered)?> produk</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="k-table">
          <thead><tr><th>No</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Terjual</th><th>Rating</th><th>Status</th><th style="text-align:center">Aksi</th></tr></thead>
          <tbody>
          <?php foreach($filtered as $i=>$p):$imgSrc=!empty($p['image'])?'../../assets/img/products/'.$p['image']:'';?>
          <tr>
            <td style="color:var(--text-muted);font-size:.8rem;"><?=$i+1?></td>
            <td><div style="display:flex;align-items:center;gap:.85rem;">
              <img src="<?=$imgSrc?>" class="prod-thumb" onerror="this.src='https://placehold.co/48x48/1a3c2e/f0cb7a?text=K'">
              <div><div style="font-weight:700;font-size:.88rem;color:var(--primary);"><?=htmlspecialchars($p['name'])?></div>
              <div style="font-size:.75rem;color:var(--text-muted);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($p['description'])?></div></div>
            </div></td>
            <td><span class="k-badge k-badge-accent"><?=ucfirst($p['category'])?></span></td>
            <td style="font-weight:700;color:var(--primary);"><?=formatRupiah($p['price'])?></td>
            <td><?=$p['sold']?></td>
            <td><div style="display:flex;align-items:center;gap:.3rem;"><i class="bi bi-star-fill text-warning" style="font-size:.75rem;"></i><span style="font-weight:600;"><?=number_format($p['rating'],1)?></span></div></td>
            <td>
              <?php if($p['is_best']):?><span class="k-badge k-badge-green me-1">Best</span><?php endif;?>
              <?php if($p['is_new']):?><span class="k-badge k-badge-accent">Baru</span><?php endif;?>
              <?php if(!$p['is_best']&&!$p['is_new']):?><span class="k-badge k-badge-gray">Normal</span><?php endif;?>
            </td>
            <td style="text-align:center">
              <div style="display:flex;align-items:center;gap:.4rem;justify-content:center;">
                <button onclick="openEditModal(<?=$p['id']?>,'<?=htmlspecialchars(addslashes($p['name']),ENT_QUOTES)?>', <?=$p['price']?>,'<?=$p['category']?>','<?=htmlspecialchars(addslashes($p['description']),ENT_QUOTES)?>')"
                  style="width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;">
                  <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                </button>
                <button onclick="if(confirm('Hapus produk ini?'))window.location.href='products.php?delete=<?=$p['id']?>'"
                  style="width:32px;height:32px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;">
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
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="modal-body">
        <div class="form-group"><label>Nama Produk *</label><input type="text" name="name" placeholder="Nama produk" required></div>
        <div class="form-row2">
          <div class="form-group"><label>Harga (Rp) *</label><input type="number" name="price" placeholder="25000" min="1000" required></div>
          <div class="form-group"><label>Kategori *</label>
            <select name="category" required><option value="">Pilih kategori</option>
            <option value="coffee">Coffee</option><option value="non-coffee">Non-Coffee</option>
            <option value="tea">Tea</option><option value="dessert">Dessert</option></select>
          </div>
        </div>
        <div class="form-group"><label>Deskripsi *</label><textarea name="description" required></textarea></div>
        <div class="form-row2">
          <div class="form-group"><label>File Gambar</label><input type="text" name="image" placeholder="nama.jpg"></div>
          <div class="form-group"><label>Status</label>
            <select name="status"><option value="normal">Normal</option><option value="new">Baru</option><option value="best">Best Seller</option></select>
          </div>
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
    <form method="POST">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="editId">
      <div class="modal-body">
        <div class="form-group"><label>Nama Produk *</label><input type="text" name="name" id="editName" required></div>
        <div class="form-row2">
          <div class="form-group"><label>Harga (Rp) *</label><input type="number" name="price" id="editPrice" required></div>
          <div class="form-group"><label>Kategori *</label>
            <select name="category" id="editCat" required>
            <option value="coffee">Coffee</option><option value="non-coffee">Non-Coffee</option>
            <option value="tea">Tea</option><option value="dessert">Dessert</option></select>
          </div>
        </div>
        <div class="form-group"><label>Deskripsi *</label><textarea name="description" id="editDesc" required></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeModal('editModal')" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Batal</button>
        <button type="submit" class="btn-brand" style="font-size:.85rem;padding:.55rem 1.2rem;"><i class="bi bi-check-lg me-1"></i>Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
function openEditModal(id,name,price,cat,desc){
  document.getElementById('editId').value=id;
  document.getElementById('editName').value=name;
  document.getElementById('editPrice').value=price;
  document.getElementById('editDesc').value=desc;
  const sel=document.getElementById('editCat');
  for(let o of sel.options){o.selected=(o.value===cat);}
  openModal('editModal');
}
document.querySelectorAll('.modal-overlay').forEach(m=>{
  m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});
});
</script>
</body>
</html>
