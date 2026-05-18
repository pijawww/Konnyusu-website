<?php
// layouts/navbar.php
// Usage: include from any page
// Expects $cartTotalItems (int) to be set by parent page

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/cart.php';
require_once __DIR__ . '/../config/order.php';

if (!isset($cartTotalItems)) {
    $cartTotalItems = getCartCount();
}
$currentUser = getCurrentUser();

// Get user notification count if logged in
$userNotifCount = 0;
if ($currentUser && $currentUser['role'] !== 'admin') {
    $userNotifCount = getUserUnviewedCount($currentUser['user_id']);
}

// Detect active page for nav highlighting
$currentFile = basename($_SERVER['PHP_SELF'], '.php');
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- ===================== NAVBAR ===================== -->
<nav class="kny-navbar">
  <div class="kny-navbar__container">

    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="../home/home.php">
      <img src="../assets/img/logo.jpeg"
           alt="Konnyusu Logo"
           style="height:58px;width:auto;object-fit:contain;">
      </span>
    </a>

    <!-- Search Bar (desktop) -->
    <form class="kny-navbar__search" action="../home/home.php" method="GET" id="navSearchForm">
      <i class="bi bi-search kny-navbar__search-icon"></i>
      <input type="text" name="q" class="kny-navbar__search-input"
             placeholder="Cari kopi, teh, minuman...">
    </form>

    <!-- Nav Links (desktop) -->
    <div class="kny-navbar__links" id="navLinks">
      <a href="../home/home.php"
         class="kny-navbar__link <?= $currentDir==='home' ? 'active' : '' ?>">
        Beranda
      </a>
      <?php if ($currentUser): ?>
        <a href="../history/history.php"
           class="kny-navbar__link <?= $currentDir==='history' ? 'active' : '' ?>">
          Riwayat
        </a>
        <a href="../profile/profile.php"
           class="kny-navbar__link <?= $currentDir==='profile' ? 'active' : '' ?>">
          Profil
        </a>
        <?php if ($currentUser['role'] === 'admin'): ?>
          <a href="../admin/dashboard/dashboard.php"
             class="kny-navbar__link">
            <i class="bi bi-speedometer2"></i> Admin
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Cart + Mobile Toggle -->
    <div class="kny-navbar__actions">
      <?php if ($currentUser && $currentUser['role'] !== 'admin'): ?>
        <div style="position:relative;">
          <a href="../history/history.php" class="kny-navbar__cart" title="Notifikasi" style="position:relative;">
            <i class="bi bi-bell"></i>
            <?php if ($userNotifCount > 0): ?>
              <span style="position:absolute;top:-4px;right:-6px;min-width:18px;height:18px;background:var(--danger);color:#fff;border-radius:50%;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 4px;line-height:1;border:2px solid #fff;"><?= $userNotifCount ?></span>
            <?php endif; ?>
          </a>
        </div>
      <?php endif; ?>
      <a href="../cart/cart.php" class="kny-navbar__cart" title="Keranjang">
        <i class="bi bi-bag"></i>
        <?php if ($cartTotalItems > 0): ?>
          <span class="kny-navbar__cart-badge"><?= $cartTotalItems ?></span>
        <?php endif; ?>
      </a>
      <?php if ($currentUser): ?>
        <div class="dropdown">
          <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($currentUser['name']) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="../profile/profile.php"><i class="bi bi-person"></i> Profil</a></li>
            <li><a class="dropdown-item" href="../history/history.php"><i class="bi bi-clock-history"></i> Riwayat Pesanan</a></li>
            <?php if ($currentUser['role'] === 'admin'): ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="../admin/dashboard/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="../auth/login.php" class="kny-navbar__login-btn btn-brand">
          <i class="bi bi-person"></i> Masuk
        </a>
      <?php endif; ?>
      <!-- Mobile hamburger -->
      <button class="kny-navbar__toggle" id="navToggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <!-- Mobile Search + Links -->
  <div class="kny-navbar__mobile" id="mobileMenu">
    <form class="kny-navbar__search kny-navbar__search--mobile" action="../home/home.php" method="GET">
      <i class="bi bi-search kny-navbar__search-icon"></i>
      <input type="text" name="q" class="kny-navbar__search-input" placeholder="Cari minuman...">
    </form>
    <div class="kny-navbar__mobile-links">
      <a href="../home/home.php">Beranda</a>
      <?php if ($currentUser): ?>
        <a href="../history/history.php">Riwayat</a>
        <a href="../profile/profile.php">Profil</a>
        <?php if ($currentUser['role'] === 'admin'): ?>
          <a href="../admin/dashboard/dashboard.php">Dashboard Admin</a>
        <?php endif; ?>
        <a href="../auth/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Keluar</a>
      <?php else: ?>
        <a href="../auth/login.php">Masuk / Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<style>
/* ===================== NAVBAR STYLES ===================== */
.kny-navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;

  z-index: 1000;

  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);

  border-bottom: 1px solid rgba(255,255,255,0.12);

  box-shadow: 0 4px 30px rgba(0,0,0,.08);
}
.page-wrapper {
  padding-top: 78px;
}
.kny-navbar__link {
  color: white;
}

.kny-navbar__link:hover,
.kny-navbar__link.active {
  color: #f0cb7a;
  background: rgba(255,255,255,.08);
}

.kny-navbar__cart,
.kny-navbar__toggle span {
  color: white;
  background: white;
}

.kny-navbar__container {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2.5rem;
  height: 78px;

  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
}
.kny-navbar__brand {
  display: flex;
  align-items: center;
  gap: .5rem;
  text-decoration: none;
  flex-shrink: 0;
}
.kny-navbar__logo-icon { font-size: 1.5rem; }
.kny-navbar__logo-text {
  font-family: var(--font-display);
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--primary);
  line-height: 1;
}
.kny-navbar__search {
  flex: 1;
  max-width: 420px;
  position: relative;
  display: flex;
  align-items: center;
}
.kny-navbar__search-icon {
  position: absolute;
  left: 14px;
  color: var(--text-muted);
  font-size: .9rem;
  pointer-events: none;
}
.kny-navbar__search-input {
  width: 100%;
  background: var(--cream);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-xl);
  padding: .55rem 1rem .55rem 2.5rem;
  font-family: var(--font-body);
  font-size: .88rem;
  color: var(--text-dark);
  outline: none;
  transition: border-color .2s, background .2s;
}
.kny-navbar__search-input:focus {
  border-color: var(--primary-light);
  background: var(--white);
}
.kny-navbar__links {
  display: flex;
  align-items: center;
  gap: .25rem;
}
.kny-navbar__link {
  font-size: .88rem;
  font-weight: 500;
  color: var(--text-mid);
  padding: .45rem .85rem;
  border-radius: var(--radius-md);
  transition: color .2s, background .2s;
  text-decoration: none;
}
.kny-navbar__link:hover { color: var(--primary); background: var(--cream); }
.kny-navbar__link.active { color: var(--primary); font-weight: 700; background: var(--cream-dark); }
.kny-navbar__actions {
  display: flex;
  align-items: center;
  gap: .75rem;
  flex-shrink: 0;
}
.kny-navbar__cart {
  position: relative;
  font-size: 1.3rem;
  color: var(--primary);
  padding: .4rem;
  border-radius: var(--radius-md);
  transition: background .2s;
  text-decoration: none;
}
.kny-navbar__cart:hover { background: var(--cream-dark); }
.kny-navbar__cart-badge {
  position: absolute;
  top: -4px; right: -6px;
  min-width: 18px; height: 18px;
  background: var(--accent);
  color: var(--primary);
  border-radius: 50%;
  font-size: .65rem;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  padding: 0 4px;
  line-height: 1;
}
.kny-navbar__login-btn {
  font-size: .82rem;
  padding: .5rem 1.1rem;
  text-decoration: none;
  display: inline-flex; align-items: center; gap: .35rem;
}
.kny-navbar__toggle {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: .35rem;
}
.kny-navbar__toggle span {
  display: block; width: 22px; height: 2px;
  background: var(--primary); border-radius: 2px;
  transition: all .25s;
}
.kny-navbar__mobile { display: none; }
.kny-navbar__mobile-links a {
  display: block;
  padding: .75rem 1.5rem;
  color: var(--text-dark);
  font-weight: 500;
  border-bottom: 1px solid var(--border);
  text-decoration: none;
}
.kny-navbar__search--mobile { padding: .75rem 1.5rem; max-width: 100%; }

@media (max-width: 900px) {
  .kny-navbar__links,
  .kny-navbar__login-btn { display: none; }
  .kny-navbar__search { display: none; }
  .kny-navbar__toggle { display: flex; }
  .kny-navbar__mobile.open { display: block; border-top: 1px solid var(--border); }
  .kny-navbar__search--mobile { display: flex; }
}
.kny-navbar__search {
  flex: 1;
  max-width: 520px;
}
@media (max-width: 900px) {
  .kny-navbar__container {
    padding: 0 1rem;
    height: 72px;
  }

  .navbar-brand img {
    height: 48px !important;
  }
}
</style>

<script>
document.getElementById('navToggle')?.addEventListener('click', function() {
  document.getElementById('mobileMenu').classList.toggle('open');
});
</script>
