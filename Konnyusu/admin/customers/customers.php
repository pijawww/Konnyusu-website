<?php
session_start();
include __DIR__ . '/../../data/products.php';

$customers = [
  ['id'=>1,'name'=>'Rani Permata',   'email'=>'rani@email.com',  'phone'=>'0812-3456-7890','joined'=>'10 Jan 2026','orders'=>12,'spent'=>824000, 'level'=>'Gold',     'status'=>'Aktif'],
  ['id'=>2,'name'=>'Bagas Satrio',   'email'=>'bagas@email.com', 'phone'=>'0813-9876-5432','joined'=>'22 Feb 2026','orders'=>7, 'spent'=>385000, 'level'=>'Silver',   'status'=>'Aktif'],
  ['id'=>3,'name'=>'Dinda Maharani', 'email'=>'dinda@email.com', 'phone'=>'0815-2345-6789','joined'=>'5 Mar 2026', 'orders'=>18,'spent'=>1250000,'level'=>'Platinum', 'status'=>'Aktif'],
  ['id'=>4,'name'=>'Rizky Aditya',   'email'=>'rizky@email.com', 'phone'=>'0821-4567-8901','joined'=>'18 Mar 2026','orders'=>4, 'spent'=>220000, 'level'=>'Bronze',   'status'=>'Aktif'],
  ['id'=>5,'name'=>'Siti Nuraini',   'email'=>'siti@email.com',  'phone'=>'0856-7890-1234','joined'=>'2 Apr 2026', 'orders'=>1, 'spent'=>23000,  'level'=>'Bronze',   'status'=>'Nonaktif'],
  ['id'=>6,'name'=>'Arif Budiman',   'email'=>'arif@email.com',  'phone'=>'0878-2345-6780','joined'=>'14 Apr 2026','orders'=>9, 'spent'=>612000, 'level'=>'Gold',     'status'=>'Aktif'],
  ['id'=>7,'name'=>'Laila Khadijah', 'email'=>'laila@email.com', 'phone'=>'0811-3456-7891','joined'=>'28 Apr 2026','orders'=>6, 'spent'=>418000, 'level'=>'Silver',   'status'=>'Aktif'],
  ['id'=>8,'name'=>'Farhan Maulana', 'email'=>'farhan@email.com','phone'=>'0823-5678-9012','joined'=>'5 Mei 2026', 'orders'=>3, 'spent'=>175000, 'level'=>'Bronze',   'status'=>'Aktif'],
];

$levelColor=['Platinum'=>'k-badge-green','Gold'=>'k-badge-accent','Silver'=>'k-badge-gray','Bronze'=>'k-badge-red'];
$levelIcon=['Platinum'=>'🏆','Gold'=>'🥇','Silver'=>'🥈','Bronze'=>'🥉'];

$stats=[
  'total'   => count($customers),
  'active'  => count(array_filter($customers, fn($c)=>$c['status']==='Aktif')),
  'new_mth' => 3,
  'avg_spent'=> (int)(array_sum(array_column($customers,'spent'))/count($customers)),
];
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
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
.modal-overlay.open{display:flex;}
.modal-box{background:var(--white);border-radius:var(--radius-lg);width:100%;max-width:520px;max-height:90vh;overflow-y:auto;animation:fadeUp .3s ease;}
.modal-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.modal-header h5{font-size:1rem;font-weight:700;color:var(--primary);margin:0;}
.modal-body{padding:1.5rem;}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.75rem;}
@media(max-width:900px){.admin-sidebar{display:none;}.admin-body{padding:1.25rem;}.stat-strip{grid-template-columns:1fr 1fr;}}
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
    <a href="../products/products.php" class="sidebar-link"><i class="bi bi-cup-hot"></i> Produk</a>
    <a href="customers.php" class="sidebar-link active"><i class="bi bi-people"></i> Pelanggan</a>
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
    <h6>Kelola Pelanggan</h6>
    <div style="display:flex;align-items:center;gap:.75rem;">
      <button style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.5rem 1rem;font-size:.82rem;font-weight:600;cursor:pointer;color:var(--text-mid);display:flex;align-items:center;gap:.4rem;">
        <i class="bi bi-download"></i> Export CSV
      </button>
      <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;">A</div>
    </div>
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

    <!-- Toolbar -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      <div style="position:relative;flex:1;max-width:320px;">
        <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem;"></i>
        <input type="text" id="searchInput" placeholder="Cari nama atau email..." style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:40px;padding:.52rem .9rem .52rem 2.2rem;font-family:var(--font-body);font-size:.82rem;outline:none;">
      </div>
      <select id="levelFilter" style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.48rem .85rem;font-family:var(--font-body);font-size:.82rem;outline:none;color:var(--text-mid);">
        <option value="">Semua Level</option>
        <option value="Platinum">Platinum</option>
        <option value="Gold">Gold</option>
        <option value="Silver">Silver</option>
        <option value="Bronze">Bronze</option>
      </select>
      <select id="statusFilter" style="background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.48rem .85rem;font-family:var(--font-body);font-size:.82rem;outline:none;color:var(--text-mid);">
        <option value="">Semua Status</option>
        <option value="Aktif">Aktif</option>
        <option value="Nonaktif">Nonaktif</option>
      </select>
    </div>

    <!-- Table -->
    <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;" class="animate-fadeup">
      <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.9rem;font-weight:700;color:var(--primary);">Data Pelanggan</span>
        <span style="font-size:.78rem;color:var(--text-muted);"><?= count($customers) ?> pelanggan</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="k-table" id="customerTable">
          <thead>
            <tr>
              <th>No</th>
              <th>Pelanggan</th>
              <th>Kontak</th>
              <th>Bergabung</th>
              <th>Total Pesanan</th>
              <th>Total Belanja</th>
              <th>Level</th>
              <th>Status</th>
              <th style="text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($customers as $i=>$c): ?>
          <tr class="cust-row"
              data-name="<?= strtolower($c['name']) ?>"
              data-email="<?= strtolower($c['email']) ?>"
              data-level="<?= $c['level'] ?>"
              data-status="<?= $c['status'] ?>">
            <td style="color:var(--text-muted);font-size:.8rem;"><?= $i+1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:.75rem;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0;">
                  <?= $c['name'][0] ?>
                </div>
                <div>
                  <div style="font-weight:700;font-size:.88rem;color:var(--primary);"><?= $c['name'] ?></div>
                  <div style="font-size:.73rem;color:var(--text-muted);"><?= $c['email'] ?></div>
                </div>
              </div>
            </td>
            <td style="font-size:.82rem;color:var(--text-mid);">
              <div style="display:flex;align-items:center;gap:.3rem;"><i class="bi bi-phone" style="font-size:.75rem;"></i><?= $c['phone'] ?></div>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted);"><?= $c['joined'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:.4rem;">
                <span style="font-weight:700;color:var(--primary);"><?= $c['orders'] ?></span>
                <span style="font-size:.72rem;color:var(--text-muted);">pesanan</span>
              </div>
            </td>
            <td style="font-weight:700;color:var(--primary);"><?= formatRupiah($c['spent']) ?></td>
            <td>
              <span class="k-badge <?= $levelColor[$c['level']] ?>">
                <?= $levelIcon[$c['level']] ?> <?= $c['level'] ?>
              </span>
            </td>
            <td>
              <span class="k-badge <?= $c['status']==='Aktif'?'k-badge-green':'k-badge-red' ?>">
                <?= $c['status'] ?>
              </span>
            </td>
            <td style="text-align:center;">
              <div style="display:flex;align-items:center;gap:.4rem;justify-content:center;">
                <button title="Lihat Detail"
                  onclick="showCustomer(<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>)"
                  style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;"
                  onmouseover="this.style.background='var(--cream)';this.style.color='var(--primary)'" onmouseout="this.style.background='none';this.style.color='var(--text-muted)'">
                  <i class="bi bi-eye" style="font-size:.8rem;"></i>
                </button>
                <button title="Kirim Email"
                  style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;"
                  onmouseover="this.style.background='#eff6ff';this.style.color='#3b82f6';this.style.borderColor='#3b82f6'" onmouseout="this.style.background='none';this.style.color='var(--text-muted)';this.style.borderColor='var(--border)'">
                  <i class="bi bi-envelope" style="font-size:.8rem;"></i>
                </button>
                <button title="<?= $c['status']==='Aktif'?'Nonaktifkan':'Aktifkan' ?>"
                  style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);transition:all .2s;"
                  onmouseover="this.style.background='#fdf0f0';this.style.color='var(--danger)';this.style.borderColor='var(--danger)'" onmouseout="this.style.background='none';this.style.color='var(--text-muted)';this.style.borderColor='var(--border)'">
                  <i class="bi <?= $c['status']==='Aktif'?'bi-person-x':'bi-person-check' ?>" style="font-size:.8rem;"></i>
                </button>
              </div>
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

<!-- Customer Detail Modal -->
<div class="modal-overlay" id="custModal">
  <div class="modal-box">
    <div class="modal-header">
      <h5><i class="bi bi-person-circle me-2"></i>Detail Pelanggan</h5>
      <button onclick="document.getElementById('custModal').classList.remove('open')" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">×</button>
    </div>
    <div class="modal-body" id="custModalBody"></div>
    <div class="modal-footer">
      <button onclick="document.getElementById('custModal').classList.remove('open')" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.1rem;">Tutup</button>
      <button class="btn-brand" style="font-size:.85rem;padding:.55rem 1.2rem;"><i class="bi bi-envelope me-1"></i>Kirim Email</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const levelColor={'Platinum':'k-badge-green','Gold':'k-badge-accent','Silver':'k-badge-gray','Bronze':'k-badge-red'};
const levelIcon={'Platinum':'🏆','Gold':'🥇','Silver':'🥈','Bronze':'🥉'};

// Filters
function filterTable(){
  const q=document.getElementById('searchInput').value.toLowerCase();
  const lv=document.getElementById('levelFilter').value;
  const st=document.getElementById('statusFilter').value;
  document.querySelectorAll('.cust-row').forEach(row=>{
    const matchQ=!q||(row.dataset.name.includes(q)||row.dataset.email.includes(q));
    const matchL=!lv||row.dataset.level===lv;
    const matchS=!st||row.dataset.status===st;
    row.style.display=(matchQ&&matchL&&matchS)?'':'none';
  });
}
document.getElementById('searchInput').addEventListener('input',filterTable);
document.getElementById('levelFilter').addEventListener('change',filterTable);
document.getElementById('statusFilter').addEventListener('change',filterTable);

function showCustomer(c){
  const lvBadge=levelColor[c.level]||'k-badge-gray';
  const stBadge=c.status==='Aktif'?'k-badge-green':'k-badge-red';
  document.getElementById('custModalBody').innerHTML=`
    <div style="text-align:center;margin-bottom:1.5rem;">
      <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;margin:0 auto .75rem;">${c.name[0]}</div>
      <div style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--primary);">${c.name}</div>
      <div style="font-size:.82rem;color:var(--text-muted);margin:.2rem 0 .6rem;">${c.email}</div>
      <div style="display:flex;justify-content:center;gap:.5rem;">
        <span class="k-badge ${lvBadge}">${levelIcon[c.level]} ${c.level}</span>
        <span class="k-badge ${stBadge}">${c.status}</span>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">
      <div style="background:var(--cream);border-radius:var(--radius-md);padding:.85rem;text-align:center;">
        <div style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--primary);">${c.orders}</div>
        <div style="font-size:.72rem;color:var(--text-muted);">Total Pesanan</div>
      </div>
      <div style="background:var(--cream);border-radius:var(--radius-md);padding:.85rem;text-align:center;">
        <div style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--primary);">Rp ${c.spent.toLocaleString('id')}</div>
        <div style="font-size:.72rem;color:var(--text-muted);">Total Belanja</div>
      </div>
    </div>
    <div style="background:var(--cream);border-radius:var(--radius-md);padding:1rem;">
      <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:.75rem;">Informasi Kontak</div>
      <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.85rem;"><span style="color:var(--text-muted);">Telepon</span><span style="font-weight:600;">${c.phone}</span></div>
      <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.85rem;"><span style="color:var(--text-muted);">Bergabung</span><span style="font-weight:600;">${c.joined}</span></div>
      <div style="display:flex;justify-content:space-between;padding:.4rem 0;font-size:.85rem;"><span style="color:var(--text-muted);">Rata-rata Belanja</span><span style="font-weight:600;">Rp ${Math.round(c.spent/c.orders).toLocaleString('id')}</span></div>
    </div>`;
  document.getElementById('custModal').classList.add('open');
}
document.getElementById('custModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});
</script>
</body>
</html>
