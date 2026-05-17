<?php
// layouts/navbar.php
// Usage: include from any page
// Expects $cartTotalItems (int) to be set by parent page

$cartTotalItems = $cartTotalItems ?? 0;

// Detect active page for nav highlighting
$currentFile = basename($_SERVER['PHP_SELF'], '.php');
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- ===================== NAVBAR ===================== -->
<nav class="kny-navbar">
  <div class="kny-navbar__container">

    <!-- Logo -->
    <a href="../home/home.php" class="kny-navbar__brand">
      <span class="kny-navbar__logo-icon">☕</span>
      <span class="kny-navbar__logo-text">Konnyusu</span>
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
      <a href="../history/history.php"
         class="kny-navbar__link <?= $currentDir==='history' ? 'active' : '' ?>">
        Riwayat
      </a>
      <a href="../profile/profile.php"
         class="kny-navbar__link <?= $currentDir==='profile' ? 'active' : '' ?>">
        Profil
      </a>
    </div>

    <!-- Cart + Mobile Toggle -->
    <div class="kny-navbar__actions">
      <a href="../cart/cart.php" class="kny-navbar__cart" title="Keranjang">
        <i class="bi bi-bag"></i>
        <?php if ($cartTotalItems > 0): ?>
          <span class="kny-navbar__cart-badge"><?= $cartTotalItems ?></span>
        <?php endif; ?>
      </a>
      <a href="../auth/login.php" class="kny-navbar__login-btn btn-brand">
        <i class="bi bi-person"></i> Masuk
      </a>
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
      <a href="../history/history.php">Riwayat</a>
      <a href="../profile/profile.php">Profil</a>
      <a href="../auth/login.php">Masuk / Daftar</a>
    </div>
  </div>
</nav>

<style>
/* ===================== NAVBAR STYLES ===================== */
.kny-navbar {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: var(--white);
  border-bottom: 1px solid var(--border);
  box-shadow: 0 2px 16px rgba(26,60,46,.07);
}
.kny-navbar__container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
  height: 68px;
  display: flex;
  align-items: center;
  gap: 1.5rem;
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
</style>

<script>
document.getElementById('navToggle')?.addEventListener('click', function() {
  document.getElementById('mobileMenu').classList.toggle('open');
});
</script>
