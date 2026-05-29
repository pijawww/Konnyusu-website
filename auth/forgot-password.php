<?php
// auth/forgot-password.php
session_start();
require_once __DIR__ . '/../config/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $result = generateResetToken($email);
        if ($result) {
            // Redirect ke halaman reset password dengan token
            header('Location: reset-password.php?token=' . $result['token']);
            exit;
        } else {
            $error = 'Email tidak ditemukan dalam sistem kami.';
        }
    } else {
        $error = 'Silakan masukkan email Anda.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password — Konnyusu</title>
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
        <h4 style="font-family: var(--font-display); color: var(--primary);">Lupa Password?</h4>
        <p style="color: var(--text-muted); font-size: .85rem;">Masukkan email Anda untuk reset password</p>
      </div>

      <?php if ($error): ?>
      <div style="background: #fff0f0; border: 1px solid #f5b8b8; color: var(--danger); padding: .75rem 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; font-size: .85rem;">
        <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div style="margin-bottom: 1rem;">
          <label style="display: block; font-size: .8rem; font-weight: 600; color: var(--text-mid); margin-bottom: .4rem;">Alamat Email</label>
          <input type="email" name="email" required placeholder="nama@email.com"
                 style="width: 100%; background: var(--white); border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: .7rem 1rem; font-family: var(--font-body); font-size: .88rem; color: var(--text-dark); outline: none;">
        </div>
        <button type="submit" style="width: 100%; background: var(--primary); color: #fff; border: none; border-radius: var(--radius-md); padding: .7rem 1rem; font-weight: 600; font-size: .88rem; cursor: pointer;">
          <i class="bi bi-envelope"></i> Kirim Link Reset
        </button>
      </form>

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
