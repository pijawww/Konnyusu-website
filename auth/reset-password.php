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
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
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
    position: absolute;
    width: 400px; height: 400px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
    top: -100px; right: -100px;
  }
  .auth-visual::after {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: rgba(255,255,255,.03);
    border-radius: 50%;
    bottom: -80px; left: -80px;
  }
  .auth-form-panel {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    background: var(--cream);
  }
  .auth-form-card {
    width: 100%;
    max-width: 400px;
  }
  @media (max-width: 768px) {
    .auth-visual { display: none; }
    .auth-page { grid-template-columns: 1fr; }
  }
  .password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
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
  </style>
</head>
<body>
<div class="auth-page">

  <div class="auth-visual">
    <img src="../assets/img/logo.jpeg" alt="Konnyusu" style="height: 100px; margin-bottom: 1.5rem; opacity: .9;">
    <h2 style="font-family: var(--font-display); font-size: 2rem; color: #fff; margin-bottom: .5rem;">Konnyusu</h2>
    <p style="color: rgba(255,255,255,.65); font-size: .95rem;">Kopi & Minuman Premium</p>
  </div>

  <div class="auth-form-panel">
    <div class="auth-form-card">
      <div class="text-center mb-4">
        <h4 style="font-family: var(--font-display); color: var(--primary);">Reset Password</h4>
        <p style="color: var(--text-muted); font-size: .85rem;">Masukkan password baru Anda</p>
      </div>

      <?php if ($error): ?>
      <div style="background: #fff0f0; border: 1px solid #f5b8b8; color: var(--danger); padding: .75rem 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size: .85rem;">
        <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div style="background: #ecfaf4; border: 1px solid #a3e0c8; color: var(--success); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size: .85rem;">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
      <div style="text-align: center; margin-top: 1rem;">
        <a href="login.php" style="background: var(--primary); color: #fff; padding: .7rem 2rem; border-radius: var(--radius-md); text-decoration: none; font-weight: 600;">
          <i class="bi bi-box-arrow-in-right"></i> Login Sekarang
        </a>
      </div>
      <?php else: ?>
      <?php if (empty($error)): ?>
      <form method="POST" action="">
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: .8rem; font-weight: 600; color: var(--text-mid); margin-bottom: .4rem;">Password Baru</label>
          <div class="password-input-wrapper" style="background: var(--white); border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: 0 1rem;">
            <input type="password" name="password" id="password" required placeholder="Minimal 6 karakter"
                   style="border: none; background: transparent; padding: .7rem 0; font-family: var(--font-body); font-size: .88rem; color: var(--text-dark); outline: none; flex: 1;">
            <button type="button" class="password-toggle" onclick="togglePassword('password')">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: .8rem; font-weight: 600; color: var(--text-mid); margin-bottom: .4rem;">Konfirmasi Password</label>
          <div class="password-input-wrapper" style="background: var(--white); border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: 0 1rem;">
            <input type="password" name="confirm_password" id="confirm_password" required placeholder="Ulangi password baru"
                   style="border: none; background: transparent; padding: .7rem 0; font-family: var(--font-body); font-size: .88rem; color: var(--text-dark); outline: none; flex: 1;">
            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" style="width: 100%; background: var(--primary); color: #fff; border: none; border-radius: var(--radius-md); padding: .7rem 1rem; font-weight: 600; font-size: .88rem; cursor: pointer;">
          <i class="bi bi-key"></i> Reset Password
        </button>
      </form>
      <?php endif; ?>

      <?php if (isset($error) && strpos($error, 'kedaluwarsa') !== false): ?>
      <div style="text-align: center; margin-top: 1rem;">
        <a href="forgot-password.php" style="color: var(--primary); font-size: .85rem; text-decoration: none;">
          <i class="bi bi-arrow-left"></i> Minta Link Reset Baru
        </a>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <div style="text-align: center; margin-top: 1.5rem;">
        <a href="login.php" style="color: var(--primary); font-size: .85rem; text-decoration: none;">
          <i class="bi bi-arrow-left"></i> Kembali ke Login
        </a>
      </div>
    </div>
  </div>

</div>
</body>
</html>
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
