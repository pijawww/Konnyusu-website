<?php
// auth/login.php
session_start();
require_once __DIR__ . '/../config/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($pass)) {
        if (login($email, $pass)) {
            $user = getCurrentUser();
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard/dashboard.php');
            } else {
                header('Location: ../home/home.php');
            }
            exit;
        } else {
            $error = 'Email atau kata sandi tidak valid.';
        }
    } else {
        $error = 'Silakan isi email dan kata sandi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — Konnyusu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
  .auth-page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
  .auth-visual {
    background: linear-gradient(160deg, var(--primary) 0%, #2e6b4f 100%);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 3rem;
    position: relative; overflow: hidden;
  }
  .auth-visual::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 70% 30%, rgba(212,168,83,.15) 0%, transparent 60%);
  }
  .auth-visual__content { position: relative; z-index: 1; text-align: center; color: #fff; }
  .auth-visual__logo {
    font-family: var(--font-display);
    font-size: 2.5rem; font-weight: 800;
    color: #fff; margin-bottom: .5rem;
  }
  .auth-visual__logo-img {
    width: 200px; height: auto; max-height: 200px; object-fit: contain;
    border-radius: 28px;
    display: block; margin: 0 auto 1.5rem;
    animation: float 4s ease-in-out infinite;
    background: #fff; padding: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  }
  .auth-visual__text { font-size: 1.1rem; line-height: 1.65;
                       color: rgba(255,255,255,.8); max-width: 320px; margin: 0 auto 2rem; }
  .auth-visual__dots { display: flex; gap: .5rem; justify-content: center; margin-top: 2rem; }
  .auth-visual__dots span {
    width: 8px; height: 8px; border-radius: 50%;
    background: rgba(255,255,255,.35);
  }
  .auth-visual__dots span.active { background: var(--accent); width: 24px; border-radius: 4px; }

  .auth-form-wrap {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 3rem 2rem;
    background: var(--cream);
  }
  .auth-form-box {
    width: 100%; max-width: 400px;
  }
  .auth-form-box h2 {
    font-family: var(--font-display);
    font-size: 1.9rem; font-weight: 700;
    color: var(--primary); margin-bottom: .4rem;
  }
  .auth-form-box p { color: var(--text-muted); font-size: .9rem; margin-bottom: 2rem; }

  .form-group { margin-bottom: 1.1rem; }
  .form-label {
    display: block;
    font-size: .82rem; font-weight: 600;
    color: var(--text-mid); margin-bottom: .4rem;
    letter-spacing: .03em;
  }
  .form-input {
    width: 100%;
    background: var(--white);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: .75rem 1rem;
    font-family: var(--font-body);
    font-size: .9rem;
    color: var(--text-dark);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .form-input:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(46,107,79,.12);
  }
  .input-wrap { position: relative; }
  .input-icon {
    position: absolute; right: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted); cursor: pointer;
    font-size: 1rem;
  }
  .btn-submit {
    width: 100%;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-xl);
    padding: .85rem;
    font-family: var(--font-body);
    font-size: 1rem; font-weight: 700;
    cursor: pointer;
    margin-top: .5rem;
    transition: background .2s, transform .15s;
  }
  .btn-submit:hover { background: var(--primary-light); transform: translateY(-1px); }
  .divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
  .divider::before, .divider::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
  }
  .divider span { font-size: .78rem; color: var(--text-muted); }
  .social-btn {
    width: 100%; display: flex; align-items: center; justify-content: center; gap: .6rem;
    background: var(--white); border: 1.5px solid var(--border);
    border-radius: var(--radius-xl); padding: .7rem;
    font-family: var(--font-body); font-size: .9rem;
    font-weight: 600; color: var(--text-dark);
    cursor: pointer; transition: border-color .2s, background .2s;
  }
  .social-btn:hover { border-color: var(--primary); background: var(--cream); }
  .auth-switch { text-align: center; font-size: .88rem; color: var(--text-muted); margin-top: 1.5rem; }
  .auth-switch a { color: var(--primary); font-weight: 700; }
  .alert-error {
    background: #fff0f0; border: 1px solid #f5b8b8;
    color: var(--danger); border-radius: var(--radius-md);
    padding: .75rem 1rem; font-size: .85rem; margin-bottom: 1.25rem;
    display: flex; align-items: center; gap: .5rem;
  }
  @media (max-width: 768px) {
    .auth-page { grid-template-columns: 1fr; }
    .auth-visual { display: none; }
    .auth-form-wrap { padding: 2rem 1.25rem; }
  }
  @keyframes float {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-12px); }
  }
  </style>
</head>
<body>
<div class="auth-page">

  <!-- Visual Side -->
  <div class="auth-visual">
    <div class="auth-visual__content">
      <img src="../assets/img/logo.jpeg" alt="Konnyusu" class="auth-visual__logo-img">
      <div class="auth-visual__logo">Konnyusu</div>
      <p class="auth-visual__text">
        Selamat datang kembali! Masuk untuk menikmati minuman premium pilihan Anda.
      </p>
      <div class="auth-visual__dots">
        <span class="active"></span>
        <span></span><span></span>
      </div>
    </div>
  </div>

  <!-- Form Side -->
  <div class="auth-form-wrap">
    <div class="auth-form-box animate-fadeup">

      <h2>Selamat Datang!</h2>
      <p>Masuk ke akun Konnyusu Anda</p>

      <?php if ($error): ?>
        <div class="alert-error"><i class="bi bi-exclamation-circle"></i> <?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">Alamat Email</label>
          <input type="email" name="email" class="form-input"
                 placeholder="nama@email.com" required
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0">Kata Sandi</label>
            <a href="forgot-password.php" style="font-size:.78rem;color:var(--primary);font-weight:600;">Lupa Sandi?</a>
          </div>
          <div class="input-wrap">
            <input type="password" name="password" id="pwdInput" class="form-input"
                   placeholder="Masukkan kata sandi" required style="padding-right:2.8rem;">
            <i class="bi bi-eye input-icon" id="eyeToggle"></i>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3">
          <input type="checkbox" id="remember" class="form-check-input" style="width:16px;height:16px;">
          <label for="remember" style="font-size:.85rem;color:var(--text-mid);cursor:pointer;">
            Ingat saya
          </label>
        </div>

        <button type="submit" class="btn-submit">Masuk</button>
      </form>

      <div class="divider"><span>atau lanjutkan dengan</span></div>

      <button class="social-btn">
        <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615Z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18Z"/><path fill="#FBBC05" d="M3.964 10.706A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.706V4.962H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.038l3.007-2.332Z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.962L3.964 7.294C4.672 5.163 6.656 3.58 9 3.58Z"/></svg>
        Masuk dengan Google
      </button>

      <div class="auth-switch">
        Belum punya akun? <a href="register.php">Daftar Sekarang</a>
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
document.getElementById('eyeToggle')?.addEventListener('click', function() {
  const inp = document.getElementById('pwdInput');
  const isText = inp.type === 'text';
  inp.type = isText ? 'password' : 'text';
  this.className = 'bi input-icon ' + (isText ? 'bi-eye' : 'bi-eye-slash');
});
</script>
</body>
</html>
