<?php
// profile/profile.php
session_start();
include __DIR__ . '/../data/products.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';
require_once __DIR__ . '/../config/order.php';

$cartTotalItems = getCartCount();

$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: ../auth/login.php');
    exit;
}

// Refresh user data after any update
$currentUser = getCurrentUser();

// Handle profile save
$profileSaved = false;
$profileError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'birthdate' => trim($_POST['birthdate'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'bio' => trim($_POST['bio'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'address' => trim($_POST['address'] ?? '')
    ];
    if (!empty($data['name'])) {
        if (updateUserProfile($currentUser['user_id'], $data)) {
            $profileSaved = true;
            $currentUser = getCurrentUser();
        } else {
            $profileError = 'Gagal menyimpan perubahan.';
        }
    }
}

// Handle password change
$passwordMsg = '';
$passwordMsgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $result = changePassword($currentUser['user_id'], $_POST['old_password'] ?? '', $_POST['new_password'] ?? '');
    if ($result['success']) {
        $passwordMsg = 'Kata sandi berhasil diubah!';
        $passwordMsgType = 'success';
    } else {
        $passwordMsg = $result['error'];
        $passwordMsgType = 'error';
    }
}

// Handle notification toggle
$notifMsg = '';
$notifMsgType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_notifications'])) {
    $enabled = isset($_POST['notifications_enabled']) ? true : false;
    if (updateNotificationSetting($currentUser['user_id'], $enabled)) {
        $notifMsg = 'Pengaturan notifikasi disimpan!';
        $notifMsgType = 'success';
        $currentUser = getCurrentUser();
    } else {
        $notifMsg = 'Gagal menyimpan pengaturan.';
        $notifMsgType = 'error';
    }
}

// Get real order count from database
$orderCount = 0;
if ($currentUser) {
    $orders = getUserOrders($currentUser['user_id']);
    $orderCount = count($orders);
}
$userStats = ['orders' => $orderCount];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya — Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
  .profile-layout { max-width:1060px; margin:0 auto; padding:2.5rem 1.25rem; display:grid; grid-template-columns:280px 1fr; gap:2rem; align-items:start; }
  /* Sidebar */
  .profile-sidebar { position:sticky; top:90px; }
  .profile-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:2rem 1.5rem; text-align:center; margin-bottom:1rem; }
  .profile-avatar { width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--primary-light)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:2.2rem; font-weight:800; margin:0 auto 1rem; position:relative; }
  .profile-avatar__badge { position:absolute; bottom:2px; right:2px; width:24px; height:24px; background:var(--accent); border-radius:50%; border:2px solid #fff; display:flex; align-items:center; justify-content:center; font-size:.65rem; cursor:pointer; }
  .profile-name { font-family:var(--font-display); font-size:1.2rem; font-weight:700; color:var(--primary); margin-bottom:.2rem; }
  .profile-email { font-size:.8rem; color:var(--text-muted); margin-bottom:1rem; }
  .profile-level { display:inline-flex; align-items:center; gap:.35rem; background:#fff8ec; border:1px solid #f0cb7a; color:var(--accent); font-size:.75rem; font-weight:700; padding:.3rem .85rem; border-radius:40px; margin-bottom:1.25rem; }
  .profile-stats { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
  .profile-stat { background:var(--cream); border-radius:var(--radius-md); padding:.85rem; text-align:center; }
  .profile-stat__val { font-size:1.3rem; font-weight:800; color:var(--primary); font-family:var(--font-display); }
  .profile-stat__lbl { font-size:.7rem; color:var(--text-muted); margin-top:.1rem; }
  /* Nav menu */
  .profile-nav { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; }
  .profile-nav a { display:flex; align-items:center; gap:.75rem; padding:.85rem 1.25rem; color:var(--text-mid); font-size:.875rem; font-weight:500; transition:all .2s; text-decoration:none; border-bottom:1px solid var(--border); }
  .profile-nav a:last-child { border-bottom:none; }
  .profile-nav a:hover { background:var(--cream); color:var(--primary); }
  .profile-nav a.active { background:rgba(26,60,46,.06); color:var(--primary); font-weight:700; border-left:3px solid var(--primary); }
  .profile-nav a i { font-size:1rem; width:18px; text-align:center; }
  /* Main content */
  .profile-main {}
  .profile-section { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:1.25rem; overflow:hidden; }
  .profile-section__header { padding:1.1rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:.75rem; }
  .profile-section__title { font-size:.95rem; font-weight:700; color:var(--primary); margin:0; flex:1; }
  .profile-section__body { padding:1.5rem; }
  /* Form */
  .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; }
  .form-grid.single { grid-template-columns:1fr; }
  .form-group label { display:block; font-size:.8rem; font-weight:600; color:var(--text-mid); margin-bottom:.4rem; }
  .form-group input, .form-group select, .form-group textarea {
    width:100%; background:var(--cream); border:1.5px solid var(--border); border-radius:var(--radius-md);
    padding:.7rem 1rem; font-family:var(--font-body); font-size:.88rem; color:var(--text-dark); outline:none;
    transition:border-color .2s,box-shadow .2s;
  }
  .form-group input:focus, .form-group select:focus { border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(46,107,79,.1); background:var(--white); }
  /* Points card */
  .points-card { background:linear-gradient(135deg,var(--primary),var(--primary-light)); border-radius:var(--radius-lg); padding:1.5rem; color:#fff; margin-bottom:1.25rem; position:relative; overflow:hidden; }
  .points-card::before { content:'⭐'; position:absolute; right:1.5rem; top:50%; transform:translateY(-50%); font-size:5rem; opacity:.1; }
  .points-card h6 { font-size:.78rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; color:rgba(255,255,255,.65); margin-bottom:.5rem; }
  .points-card__val { font-family:var(--font-display); font-size:2.8rem; font-weight:800; line-height:1; margin-bottom:.25rem; }
  .points-card__sub { font-size:.82rem; color:rgba(255,255,255,.7); }
  .points-progress { margin-top:1.25rem; }
  .points-progress-bar { height:6px; background:rgba(255,255,255,.2); border-radius:3px; overflow:hidden; }
  .points-progress-fill { height:100%; background:var(--accent); border-radius:3px; width:72%; }
  .points-progress-labels { display:flex; justify-content:space-between; font-size:.7rem; color:rgba(255,255,255,.6); margin-top:.35rem; }
  /* Alert */
  .alert-success-inline { background:#ecfaf4; border:1px solid #a3e0c8; color:var(--success); border-radius:var(--radius-md); padding:.75rem 1rem; font-size:.85rem; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
  /* Toggle switch */
  .toggle-switch { position:relative; display:inline-flex; align-items:center; cursor:pointer; }
  .toggle-switch input { opacity:0; width:0; height: 0; position: absolute; }
  .toggle-track { width:42px; height:24px; background:var(--border); border-radius:12px; transition:background .2s; position:relative; flex-shrink:0; }
  .toggle-knob { position:absolute; top:3px; left:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:left .2s; }
  .toggle-switch input:checked + .toggle-track { background:var(--primary); }
  .toggle-switch input:checked + .toggle-track .toggle-knob { left:21px; }
  @media(max-width:900px){ .profile-layout{grid-template-columns:1fr;} .profile-sidebar{position:static;} }
  @media(max-width:576px){ .form-grid{grid-template-columns:1fr;} .profile-stats{grid-template-columns:1fr 1fr;} }
  </style>
</head>
<body class="page-wrapper">
<?php include __DIR__ . '/../layouts/navbar.php'; ?>

<!-- Breadcrumb -->
<div style="background:var(--cream-dark);border-bottom:1px solid var(--border);padding:.6rem 0;">
  <div style="max-width:1060px;margin:0 auto;padding:0 1.25rem;font-size:.8rem;color:var(--text-muted);">
    <a href="../home/home.php" style="color:var(--primary);">Beranda</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem;"></i>
    <span>Profil Saya</span>
  </div>
</div>

<div class="page-content">
<div class="profile-layout">

  <!-- Sidebar -->
  <div class="profile-sidebar animate-fadeup">
    <div class="profile-card">
      <div class="profile-avatar">
        <?= strtoupper($currentUser['name'][0]) ?>
        <div class="profile-avatar__badge" title="Ganti foto"><i class="bi bi-camera-fill"></i></div>
      </div>
      <div class="profile-name"><?= htmlspecialchars($currentUser['name']) ?></div>
      <div class="profile-email"><?= htmlspecialchars($currentUser['email']) ?></div>
      <div class="profile-stats" style="grid-template-columns:1fr;">
        <div class="profile-stat">
          <div class="profile-stat__val"><?= $userStats['orders'] ?></div>
          <div class="profile-stat__lbl">Total Pesanan</div>
        </div>
      </div>
    </div>

    <div class="profile-nav">
      <a href="#info" class="active"><i class="bi bi-person"></i> Informasi Pribadi</a>
      <a href="#notifications"><i class="bi bi-bell"></i> Notifikasi</a>
      <a href="#security"><i class="bi bi-shield-lock"></i> Keamanan</a>
      <a href="#address"><i class="bi bi-geo-alt"></i> Alamat Saya</a>
      <a href="../history/history.php"><i class="bi bi-clock-history"></i> Riwayat Pesanan</a>
      <a href="../auth/login.php" style="color:var(--danger);"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </div>

  <!-- Main Content -->
  <div>
    <!-- Personal Info -->
    <div class="profile-section animate-fadeup" id="info" style="animation-delay:.1s">
      <div class="profile-section__header">
        <h5 class="profile-section__title"><i class="bi bi-person me-2"></i>Informasi Pribadi</h5>
        <span class="k-badge k-badge-green"><i class="bi bi-check-circle-fill"></i> Terverifikasi</span>
      </div>
      <div class="profile-section__body">
        <?php if ($profileSaved): ?>
        <div class="alert-success-inline"><i class="bi bi-check-circle-fill"></i> Profil berhasil diperbarui!</div>
        <?php endif; ?>
        <form method="POST" action="">
          <div class="form-grid">
            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="name" value="<?= htmlspecialchars($currentUser['name']) ?>" required>
            </div>
            <div class="form-group">
              <label>Username</label>
              <input type="text" name="username" placeholder="@username" value="<?= htmlspecialchars($currentUser["username"] ?? "") ?>">
            </div>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label>Alamat Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" disabled>
            </div>
            <div class="form-group">
              <label>Nomor HP</label>
              <input type="tel" name="phone" placeholder="08xx-xxxx-xxxx" value="<?= htmlspecialchars($currentUser["phone"] ?? "") ?>">
            </div>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label>Tanggal Lahir</label>
              <input type="date" name="birthdate" value="<?= htmlspecialchars($currentUser["birthdate"] ?? "") ?>">
            </div>
            <div class="form-group">
              <label>Jenis Kelamin</label>
              <select name="gender">
                <option value="male"<?= ($currentUser["gender"] ?? "") === "male" ? " selected" : "" ?>>Laki-laki</option>
                <option value="female"<?= ($currentUser["gender"] ?? "") === "female" ? " selected" : "" ?>>Perempuan</option>
                <option value=""<?= ($currentUser["gender"] ?? "") === "other" ? " selected" : "" ?>>Lainnya</option>
              </select>
            </div>
          </div>
          <div class="form-grid single">
            <div class="form-group">
              <label>Alamat</label>
              <textarea name="address" placeholder="Masukkan alamat lengkap Anda" style="min-height: 80px;"><?= htmlspecialchars($currentUser["address"] ?? "") ?></textarea>
            </div>
          </div>
          <div class="form-grid single">
            <div class="form-group">
              <label>Bio Singkat (Opsional)</label>
              <input type="text" name="bio" placeholder="Pecinta kopi, matcha enthusiast..." value="<?= htmlspecialchars($currentUser["bio"] ?? "") ?>">
            </div>
          </div>
          <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:.5rem;">
            <button type="reset" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.2rem;">Batal</button>
            <button type="submit" name="save_profile" class="btn-brand" style="font-size:.85rem;padding:.6rem 1.4rem;">
              <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Notifications -->
    <div class="profile-section animate-fadeup" id="notifications" style="animation-delay:.12s">
      <div class="profile-section__header">
        <h5 class="profile-section__title"><i class="bi bi-bell me-2"></i>Pengaturan Notifikasi</h5>
      </div>
      <div class="profile-section__body">
        <?php if (!empty($notifMsg)): ?>
        <div style="background:<?= $notifMsgType === "success" ? "#ecfaf4" : "#fff0f0" ?>;border:1px solid <?= $notifMsgType === "success" ? "#a3e0c8" : "#f5b8b8" ?>;color:<?= $notifMsgType === "success" ? "var(--success)" : "var(--danger)" ?>;border-radius:var(--radius-md);padding:.75rem 1rem;font-size:.85rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
          <i class="bi <?= $notifMsgType === "success" ? "bi-check-circle-fill" : "bi-exclamation-triangle-fill" ?>"></i> <?= htmlspecialchars($notifMsg) ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="">
          <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 0;border-bottom:1px solid var(--border);">
            <div>
              <div style="font-weight:600;font-size:.9rem;color:var(--primary);">Notifikasi Pesanan</div>
              <div style="font-size:.78rem;color:var(--text-muted);">Dapatkan pemberitahuan saat pesanan diperbarui</div>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" name="notifications_enabled" <?= (isset($currentUser["notifications_enabled"]) && $currentUser["notifications_enabled"]) ? "checked" : "" ?>>
              <span class="toggle-track">
                <span class="toggle-knob"></span>
              </span>
            </label>
          </div>
          <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1rem;">
            <button type="submit" name="toggle_notifications" class="btn-brand" style="font-size:.85rem;padding:.6rem 1.4rem;">
              <i class="bi bi-check-lg me-1"></i> Simpan Pengaturan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Security -->
    <div class="profile-section animate-fadeup" id="security" style="animation-delay:.15s">
      <div class="profile-section__header">
        <h5 class="profile-section__title"><i class="bi bi-shield-lock me-2"></i>Keamanan Akun</h5>
      </div>
      <div class="profile-section__body">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 0;">
          <div>
            <div style="font-weight:600;font-size:.9rem;color:var(--primary);">Kata Sandi</div>
            <div style="font-size:.78rem;color:var(--text-muted);">Ubah kata sandi akun Anda</div>
          </div>
          <button class="btn-brand" style="font-size:.78rem;padding:.4rem .9rem;" onclick="openPasswordModal()"><i class="bi bi-key me-1"></i>Ubah Sandi</button>
        </div>
      </div>
    </div>

  </div>
</div>
</div>


<!-- Password Change Modal -->
<div id="passwordModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:var(--white);border-radius:var(--radius-lg);max-width:420px;width:100%;animation:fadeUp .3s;">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
      <h5 style="font-size:1rem;font-weight:700;color:var(--primary);margin:0;"><i class="bi bi-key me-2"></i>Ubah Kata Sandi</h5>
      <button onclick="closePasswordModal()" style="background:none;border:none;font-size:1.25rem;cursor:pointer;color:var(--text-muted);">x</button>
    </div>
    <form method="POST" action="">
      <div style="padding:1.5rem;">
        <?php if (!empty($passwordMsg)): ?>
        <div style="background:<?= $passwordMsgType === "success" ? "#ecfaf4" : "#fff0f0" ?>;border:1px solid <?= $passwordMsgType === "success" ? "#a3e0c8" : "#f5b8b8" ?>;color:<?= $passwordMsgType === "success" ? "var(--success)" : "var(--danger)" ?>;border-radius:var(--radius-md);padding:.75rem 1rem;font-size:.85rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;">
          <i class="bi <?= $passwordMsgType === "success" ? "bi-check-circle-fill" : "bi-exclamation-triangle-fill" ?>"></i> <?= htmlspecialchars($passwordMsg) ?>
        </div>
        <?php endif; ?>
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-mid);margin-bottom:.4rem;">Kata Sandi Lama</label>
          <input type="password" name="old_password" placeholder="Masukkan kata sandi lama" required style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.7rem 1rem;font-family:var(--font-body);font-size:.88rem;color:var(--text-dark);outline:none;">
        </div>
        <div class="form-group" style="margin-bottom:1rem;">
          <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-mid);margin-bottom:.4rem;">Kata Sandi Baru <small style="font-weight:400;color:var(--text-muted);">minimal 6 karakter</small></label>
          <input type="password" name="new_password" placeholder="Masukkan kata sandi baru" required minlength="6" style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.7rem 1rem;font-family:var(--font-body);font-size:.88rem;color:var(--text-dark);outline:none;">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-mid);margin-bottom:.4rem;">Konfirmasi Sandi Baru</label>
          <input type="password" name="confirm_password" placeholder="Ulangi kata sandi baru" required minlength="6" style="width:100%;background:var(--cream);border:1.5px solid var(--border);border-radius:var(--radius-md);padding:.7rem 1rem;font-family:var(--font-body);font-size:.88rem;color:var(--text-dark);outline:none;">
        </div>
      </div>
      <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.75rem;">
        <button type="button" onclick="closePasswordModal()" class="btn-outline-brand" style="font-size:.85rem;padding:.55rem 1.2rem;">Batal</button>
        <button type="submit" name="change_password" class="btn-brand" style="font-size:.85rem;padding:.6rem 1.4rem;"><i class="bi bi-check-lg me-1"></i>Simpan Sandi</button>
      </div>
    </form>
  </div>
</div>

<style>
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
</style>

<script>
function openPasswordModal() { document.getElementById("passwordModal").style.display = "flex"; }
function closePasswordModal() { document.getElementById("passwordModal").style.display = "none"; }
document.getElementById("passwordModal").addEventListener("click", function(e){ if(e.target === this) closePasswordModal(); });
</script>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Smooth scroll for nav
document.querySelectorAll('.profile-nav a[href^="#"]').forEach(a => {
  a.addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelector(this.getAttribute('href'))?.scrollIntoView({behavior:'smooth',block:'start'});
    document.querySelectorAll('.profile-nav a').forEach(x => x.classList.remove('active'));
    this.classList.add('active');
  });
});
// Scroll reveal
const io = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting){ e.target.style.opacity='1'; e.target.style.transform='none'; io.unobserve(e.target); }});
}, {threshold:0.08});
document.querySelectorAll('.animate-fadeup').forEach(el => {
  el.style.opacity='0'; el.style.transform='translateY(18px)'; el.style.transition='opacity .5s ease, transform .5s ease'; io.observe(el);
});
</script>
</body>
</html>
