<?php
// auth/register.php
session_start();
$success = false;
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm_password'] ?? '';
    if (empty($name) || empty($email) || empty($pass)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($pass !== $conf) {
        $error = 'Kata sandi tidak cocok.';
    } elseif (strlen($pass) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } else {
        $_SESSION['user'] = ['name' => $name, 'email' => $email, 'role' => 'user'];
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar — Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
  /* reuse auth styles */
  .auth-page { min-height:100vh; display:grid; grid-template-columns:1fr 1fr; }
  .auth-visual { background:linear-gradient(160deg,var(--primary) 0%,#2e6b4f 100%); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:3rem; position:relative; overflow:hidden; }
  .auth-visual::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 30% 70%,rgba(212,168,83,.15) 0%,transparent 60%); }
  .auth-visual__content { position:relative; z-index:1; text-align:center; color:#fff; }
  .auth-visual__logo { font-family:var(--font-display); font-size:2.5rem; font-weight:800; color:#fff; margin-bottom:.5rem; }
  .auth-visual__cup { font-size:6rem; display:block; margin-bottom:1.5rem; animation:float 4s ease-in-out infinite; }
  .auth-visual__text { font-size:1.05rem; line-height:1.65; color:rgba(255,255,255,.8); max-width:300px; margin:0 auto; }
  .auth-form-wrap { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:3rem 2rem; background:var(--cream); overflow-y:auto; }
  .auth-form-box { width:100%; max-width:420px; }
  .auth-form-box h2 { font-family:var(--font-display); font-size:1.9rem; font-weight:700; color:var(--primary); margin-bottom:.4rem; }
  .auth-form-box p { color:var(--text-muted); font-size:.9rem; margin-bottom:1.5rem; }
  .form-group { margin-bottom:1rem; }
  .form-label { display:block; font-size:.82rem; font-weight:600; color:var(--text-mid); margin-bottom:.4rem; }
  .form-input { width:100%; background:var(--white); border:1.5px solid var(--border); border-radius:var(--radius-md); padding:.75rem 1rem; font-family:var(--font-body); font-size:.9rem; color:var(--text-dark); outline:none; transition:border-color .2s,box-shadow .2s; }
  .form-input:focus { border-color:var(--primary-light); box-shadow:0 0 0 3px rgba(46,107,79,.12); }
  .btn-submit { width:100%; background:var(--primary); color:#fff; border:none; border-radius:var(--radius-xl); padding:.85rem; font-family:var(--font-body); font-size:1rem; font-weight:700; cursor:pointer; margin-top:.5rem; transition:background .2s,transform .15s; }
  .btn-submit:hover { background:var(--primary-light); transform:translateY(-1px); }
  .auth-switch { text-align:center; font-size:.88rem; color:var(--text-muted); margin-top:1.5rem; }
  .auth-switch a { color:var(--primary); font-weight:700; }
  .alert-error { background:#fff0f0; border:1px solid #f5b8b8; color:var(--danger); border-radius:var(--radius-md); padding:.75rem 1rem; font-size:.85rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; }
  .alert-success { background:#ecfaf4; border:1px solid #a3e0c8; color:var(--success); border-radius:var(--radius-md); padding:1rem; font-size:.9rem; margin-bottom:1.25rem; text-align:center; }
  .strength-bar { height:4px; border-radius:2px; margin-top:.35rem; background:var(--border); overflow:hidden; }
  .strength-bar__fill { height:100%; border-radius:2px; transition:width .3s,background .3s; width:0; }
  @media(max-width:768px){ .auth-page{grid-template-columns:1fr;} .auth-visual{display:none;} .auth-form-wrap{padding:2rem 1.25rem;} }
  @keyframes float{ 0%,100%{transform:translateY(0);} 50%{transform:translateY(-12px);} }
  </style>
</head>
<body>
<div class="auth-page">

  <div class="auth-visual">
    <div class="auth-visual__content">
      <span class="auth-visual__cup">🌿</span>
      <div class="auth-visual__logo">Konnyusu</div>
      <p class="auth-visual__text">
        Bergabunglah dengan ribuan pecinta kopi. Dapatkan reward poin dan penawaran eksklusif member.
      </p>
    </div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-form-box animate-fadeup">
      <h2>Buat Akun Baru</h2>
      <p>Daftar gratis dan mulai pesan sekarang</p>

      <?php if ($error): ?>
        <div class="alert-error"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert-success">
          <i class="bi bi-check-circle-fill me-2"></i>
          Pendaftaran berhasil! <a href="login.php">Masuk sekarang →</a>
        </div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="name" class="form-input" placeholder="Nama lengkap Anda"
                 required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat Email</label>
          <input type="email" name="email" class="form-input" placeholder="nama@email.com"
                 required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Nomor HP (Opsional)</label>
          <input type="tel" name="phone" class="form-input" placeholder="08xx-xxxx-xxxx">
        </div>
        <div class="form-group">
          <label class="form-label">Kata Sandi</label>
          <input type="password" name="password" id="pwdInput" class="form-input"
                 placeholder="Min. 6 karakter" required oninput="checkStrength(this.value)">
          <div class="strength-bar"><div class="strength-bar__fill" id="strengthBar"></div></div>
          <div id="strengthLabel" style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Kata Sandi</label>
          <input type="password" name="confirm_password" class="form-input"
                 placeholder="Ulangi kata sandi" required>
        </div>
        <div class="d-flex align-items-start gap-2 mb-3">
          <input type="checkbox" id="agree" class="form-check-input mt-1" required style="width:16px;height:16px;">
          <label for="agree" style="font-size:.82rem;color:var(--text-mid);cursor:pointer;line-height:1.5;">
            Saya setuju dengan <a href="#" style="color:var(--primary);font-weight:600;">Syarat & Ketentuan</a>
            dan <a href="#" style="color:var(--primary);font-weight:600;">Kebijakan Privasi</a> Konnyusu
          </label>
        </div>
        <button type="submit" class="btn-submit">Buat Akun</button>
      </form>
      <?php endif; ?>

      <div class="auth-switch">
        Sudah punya akun? <a href="login.php">Masuk</a>
      </div>
      <div class="auth-switch mt-2">
        <a href="../home/home.php" style="color:var(--text-muted);">
          <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function checkStrength(val) {
  const bar = document.getElementById('strengthBar');
  const lbl = document.getElementById('strengthLabel');
  let score = 0;
  if (val.length >= 6) score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels = [
    {pct:'20%', color:'#e05252', label:'Sangat lemah'},
    {pct:'40%', color:'#f07c3a', label:'Lemah'},
    {pct:'60%', color:'#f0cb7a', label:'Sedang'},
    {pct:'80%', color:'#5cb85c', label:'Kuat'},
    {pct:'100%', color:'#2e9970', label:'Sangat kuat'},
  ];
  const lv = levels[Math.max(0, score-1)] || levels[0];
  bar.style.width = val.length ? lv.pct : '0';
  bar.style.background = lv.color;
  lbl.textContent = val.length ? lv.label : '';
  lbl.style.color = lv.color;
}
</script>
</body>
</html>
