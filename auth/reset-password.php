<?php
// auth/reset-password.php
session_start();
require_once __DIR__ . '/../config/auth.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Verify token
if (empty($token)) {
    $error = 'Token tidak valid atau sudah kedaluwarsa.';
} else {
    $user = verifyResetToken($token);
    if (!$user) {
        $error = 'Token tidak valid atau sudah kedaluwarsa. Silakan minta link reset baru.';
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($token) || !verifyResetToken($token)) {
        $error = 'Token tidak valid atau sudah kedaluwarsa.';
    } else {
        $validation = validatePassword($password);
        if (!$validation['success']) {
            $error = $validation['error'];
        } elseif ($password !== $confirmPassword) {
            $error = 'Password dan konfirmasi password tidak sama.';
        } else {
            if (resetPasswordWithToken($token, $password)) {
                $success = 'Password berhasil direset! Silakan login dengan password baru Anda.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password — Konnyusu</title>
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
    width: 100%; max-width: 420px;
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
  .password-input-wrapper {
    display: flex; align-items: center;
    position: relative;
  }
  .password-input-wrapper input {
    width: 100%;
  }
  .password-toggle {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: color 0.2s;
  }
  .password-toggle:hover {
    color: var(--text-dark);
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
  .auth-switch { text-align: center; font-size: .88rem; color: var(--text-muted); margin-top: 1.5rem; }
  .auth-switch a { color: var(--primary); font-weight: 700; }
  .alert-error {
    background: #fff0f0; border: 1px solid #f5b8b8;
    color: var(--danger); border-radius: var(--radius-md);
    padding: .75rem 1rem; font-size: .85rem; margin-bottom: 1.25rem;
    display: flex; align-items: center; gap: .5rem;
  }
  .alert-success {
    background: #ecfaf4; border: 1px solid #a3e0c8;
    color: var(--success); border-radius: var(--radius-md);
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

  <div class="auth-visual">
    <div class="auth-visual__content">
      <img src="../assets/img/logo.jpeg" alt="Konnyusu" class="auth-visual__logo-img">
      <div class="auth-visual__logo">Konnyusu</div>
      <p class="auth-visual__text">Atur ulang kata sandi untuk melindungi akun Anda dan lanjutkan pemesanan.</p>
      <div class="auth-visual__dots">
        <span class="active"></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-form-box animate-fadeup">
      <h2>Reset Password</h2>
      <p>Masukkan password baru Anda untuk mengamankan akun.</p>

      <?php if ($error): ?>
        <div class="alert-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <div class="auth-switch" style="margin-top: 1rem;">
          <a href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login Sekarang</a>
        </div>
      <?php else: ?>
        <?php if (empty($error)): ?>
          <form method="POST" action="">
            <div class="form-group">
              <label class="form-label">Password Baru</label>
              <div style="font-size:.78rem; color: var(--text-muted); margin-bottom:.5rem;">Minimal 8 karakter, gunakan huruf besar, huruf kecil, angka, dan simbol.</div>
              <div class="password-input-wrapper">
                <input type="password" name="password" id="password" class="form-input" required placeholder="Masukkan password baru">
                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Password</label>
              <div class="password-input-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" class="form-input" required placeholder="Ulangi password baru">
                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <button type="submit" class="btn-submit"><i class="bi bi-key"></i> Reset Password</button>
          </form>
        <?php endif; ?>

        <?php if (isset($error) && strpos($error, 'kedaluwarsa') !== false): ?>
          <div class="auth-switch" style="margin-top: 1rem;">
            <a href="forgot-password.php"><i class="bi bi-arrow-left"></i> Minta Link Reset Baru</a>
          </div>
        <?php endif; ?>

        <div class="auth-switch">
          <a href="login.php"><i class="bi bi-arrow-left"></i> Kembali ke Login</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<script>
function togglePassword(fieldId) {
  const field = document.getElementById(fieldId);
  const button = event.currentTarget;
  const icon = button.querySelector('i');

  if (field.type === 'password') {
    field.type = 'text';
    icon.classList.remove('bi-eye');
    icon.classList.add('bi-eye-slash');
  } else {
    field.type = 'password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
  }
}
</script>
</body>
</html>
