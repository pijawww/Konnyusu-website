<?php
// auth/register.php
session_start();
require_once __DIR__ . '/../config/auth.php';

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($pass)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($pass !== $conf) {
        $error = 'Kata sandi tidak cocok.';
    } elseif (strlen($pass) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } else {
        if (register($name, $email, $pass, $phone ?: null)) {
            $success = true;
        } else {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar — Konnyusu</title>

  <!-- Bootstrap 5 + Icons + Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Global CSS -->
  <link rel="stylesheet" href="../assets/css/global.css">

  <style>
    /* ========== REGISTER PAGE SPECIFIC ========== */
    .auth-page {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
    }

    /* LEFT SIDE - VISUAL BRAND */
    .auth-visual {
      background: linear-gradient(145deg, var(--primary) 0%, #1e4a38 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      position: relative;
      overflow: hidden;
    }
    .auth-visual::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 30% 70%, rgba(212,168,83,0.18) 0%, transparent 70%);
      pointer-events: none;
    }
    .auth-visual__content {
      position: relative;
      z-index: 2;
      text-align: center;
      color: white;
      max-width: 380px;
    }
    .auth-visual__logo {
      font-family: var(--font-display);
      font-size: 2.8rem;
      font-weight: 800;
      letter-spacing: -0.5px;
      margin-bottom: 0.75rem;
    }
    .auth-visual__logo-img {
      width: 200px; height: auto; max-height: 200px; object-fit: contain;
      border-radius: 28px;
      display: block; margin: 0 auto 1.2rem;
      animation: gentleFloat 3s ease-in-out infinite;
      background: #fff; padding: 10px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    }
    .auth-visual__text {
      font-size: 1rem;
      line-height: 1.6;
      opacity: 0.9;
      font-weight: 400;
    }
    @keyframes gentleFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }

    /* RIGHT SIDE - FORM */
    .auth-form-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      background: var(--cream);
      overflow-y: auto;
    }
    .auth-form-box {
      width: 100%;
      max-width: 460px;
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 2rem 1.8rem;
      box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.02);
      border: 1px solid var(--border);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .auth-form-box:hover {
      box-shadow: 0 25px 45px -15px rgba(0, 0, 0, 0.25);
    }
    .auth-form-box h2 {
      font-family: var(--font-display);
      font-size: 1.9rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 0.3rem;
    }
    .auth-form-box p {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 1.8rem;
      border-left: 3px solid var(--accent);
      padding-left: 0.75rem;
    }

    /* Form elements */
    .form-group {
      margin-bottom: 1.2rem;
    }
    .form-label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-mid);
      margin-bottom: 0.35rem;
      letter-spacing: 0.3px;
    }
    .form-input {
      width: 100%;
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      padding: 0.7rem 1rem;
      font-family: var(--font-body);
      font-size: 0.9rem;
      color: var(--text-dark);
      transition: all 0.2s;
    }
    .form-input:focus {
      border-color: var(--primary-light);
      outline: none;
      box-shadow: 0 0 0 3px rgba(46,107,79,0.1);
    }

    /* Checkbox custom */
    .checkbox-custom {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin: 1rem 0 1.2rem;
    }
    .checkbox-custom input {
      width: 18px;
      height: 18px;
      margin-top: 2px;
      accent-color: var(--primary);
      cursor: pointer;
    }
    .checkbox-custom label {
      font-size: 0.8rem;
      color: var(--text-mid);
      line-height: 1.4;
      cursor: pointer;
    }
    .checkbox-custom a {
      color: var(--primary);
      font-weight: 600;
      text-decoration: none;
    }
    .checkbox-custom a:hover {
      text-decoration: underline;
    }

    /* Button */
    .btn-submit {
      width: 100%;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: var(--radius-xl);
      padding: 0.8rem;
      font-family: var(--font-body);
      font-size: 1rem;
      font-weight: 700;
      transition: all 0.2s;
      margin-top: 0.5rem;
    }
    .btn-submit:hover {
      background: var(--primary-light);
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(26,60,46,0.25);
    }

    /* Alert */
    .alert-error {
      background: #fff0f0;
      border-left: 4px solid var(--danger);
      border-radius: var(--radius-sm);
      padding: 0.7rem 1rem;
      font-size: 0.85rem;
      color: var(--danger);
      margin-bottom: 1.2rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .alert-success {
      background: #ecfaf4;
      border-left: 4px solid var(--success);
      border-radius: var(--radius-sm);
      padding: 1rem;
      font-size: 0.9rem;
      color: var(--success);
      margin-bottom: 1.2rem;
    }
    .auth-switch {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.85rem;
      color: var(--text-muted);
    }
    .auth-switch a {
      color: var(--primary);
      font-weight: 600;
      text-decoration: none;
    }
    .auth-switch a:hover {
      text-decoration: underline;
    }

    /* Password strength */
    .strength-bar {
      height: 4px;
      border-radius: 2px;
      background: var(--border);
      margin-top: 0.4rem;
      overflow: hidden;
    }
    .strength-bar__fill {
      height: 100%;
      width: 0;
      transition: width 0.2s ease;
    }
    .strength-label {
      font-size: 0.7rem;
      margin-top: 0.25rem;
      font-weight: 500;
    }

    /* Password toggle */
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

    /* Responsive */
    @media (max-width: 768px) {
      .auth-page {
        grid-template-columns: 1fr;
      }
      .auth-visual {
        display: none;
      }
      .auth-form-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: var(--cream);
        overflow-y: auto;
        position: relative;
      }
      /* RIGHT SIDE - FORM */
.auth-form-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: var(--cream);
  overflow-y: auto;
  position: relative;
}
/* Tambahkan efek garis tipis atau bayangan di background agar card lebih kontras */
.auth-form-wrap::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 70% 30%, rgba(0,0,0,0.02) 0%, transparent 80%);
  pointer-events: none;
}

.auth-form-box {
  width: 100%;
  max-width: 460px;
  background: #ffffff; /* pastikan putih bersih */
  border-radius: 32px;
  padding: 2.2rem 2rem;
  box-shadow: 0 30px 50px -20px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(212, 168, 83, 0.2);
  border: 1px solid rgba(212, 168, 83, 0.3);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.auth-form-box:hover {
  transform: translateY(-3px);
  box-shadow: 0 35px 55px -18px rgba(0, 0, 0, 0.3), 0 0 0 1px var(--accent);
}
    }
  </style>
</head>
<body>
<div class="auth-page">

  <!-- Left side: Branding -->
  <div class="auth-visual">
    <div class="auth-visual__content">
      <img src="../assets/img/logo.jpeg" alt="Konnyusu" class="auth-visual__logo-img">
      <div class="auth-visual__logo">Konnyusu</div>
      <p class="auth-visual__text">
        Akses ke koleksi premium & program loyalitas<br>
        Nikmati keistimewaan member Konnyusu
      </p>
    </div>
  </div>

  <!-- Right side: Registration Form -->
  <div class="auth-form-wrap">
    <div class="auth-form-box animate-fadeup">
      <h2>Buat Akun</h2>
      <p>Daftar gratis dan nikmati kemudahan pesan online</p>

      <?php if ($error): ?>
        <div class="alert-error">
          <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert-success">
          <i class="bi bi-check-circle-fill me-2"></i>
          Pendaftaran berhasil! <a href="login.php" style="color:var(--success);font-weight:700;">Masuk sekarang →</a>
        </div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="name" class="form-input" placeholder="Contoh: Nyusu Strawberry"
                 required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat Email</label>
          <input type="email" name="email" class="form-input" placeholder="nama@konnyusu.com"
                 required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Nomor HP (Opsional)</label>
          <input type="tel" name="phone" class="form-input" placeholder="0812-3456-7890">
        </div>
        <div class="form-group">
          <label class="form-label">Kata Sandi</label>
          <div class="password-input-wrapper" style="background: var(--white); border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: 0 1rem;">
            <input type="password" name="password" id="pwdInput" class="form-input"
                   placeholder="Minimal 6 karakter" required oninput="checkStrength(this.value)"
                   style="border: none; background: transparent; padding: .75rem 0; flex: 1;">
            <button type="button" class="password-toggle" onclick="togglePassword('pwdInput')">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="strength-bar">
            <div class="strength-bar__fill" id="strengthBar"></div>
          </div>
          <div id="strengthLabel" class="strength-label"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Konfirmasi Kata Sandi</label>
          <div class="password-input-wrapper" style="background: var(--white); border: 1.5px solid var(--border); border-radius: var(--radius-md); padding: 0 1rem;">
            <input type="password" name="confirm_password" id="confirmPwdInput" class="form-input"
                   placeholder="Ulangi kata sandi" required
                   style="border: none; background: transparent; padding: .75rem 0; flex: 1;">
            <button type="button" class="password-toggle" onclick="togglePassword('confirmPwdInput')">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="checkbox-custom">
          <input type="checkbox" id="agree" required>
          <label for="agree">
            Saya setuju dengan <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a> Konnyusu.
          </label>
        </div>

        <button type="submit" class="btn-submit">Daftar Sekarang</button>
      </form>
      <?php endif; ?>

      <div class="auth-switch">
        Sudah punya akun? <a href="login.php">Masuk di sini</a>
      </div>
      <div class="auth-switch" style="margin-top: 0.75rem;">
        <a href="../home/home.php"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
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
      { pct: '20%', color: '#e05252', label: 'Sangat lemah' },
      { pct: '40%', color: '#f07c3a', label: 'Lemah' },
      { pct: '60%', color: '#f0cb7a', label: 'Sedang' },
      { pct: '80%', color: '#5cb85c', label: 'Kuat' },
      { pct: '100%', color: '#2e9970', label: 'Sangat kuat' }
    ];
    const index = val.length ? Math.min(score, 5) - 1 : -1;
    if (index >= 0) {
      bar.style.width = levels[index].pct;
      bar.style.backgroundColor = levels[index].color;
      lbl.textContent = levels[index].label;
      lbl.style.color = levels[index].color;
    } else {
      bar.style.width = '0';
      lbl.textContent = '';
    }
  }

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