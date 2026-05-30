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

// Get user notification count if logged in and notifications enabled
$userNotifCount = 0;
$userNotifications = [];
$notificationsEnabled = true;
if ($currentUser && $currentUser['role'] !== 'admin') {
    $notificationsEnabled = isNotificationsEnabled($currentUser['user_id']);
    if ($notificationsEnabled) {
        $userNotifCount = getUnviewedNotificationCount($currentUser['user_id']);
        $userNotifications = getUserNotifications($currentUser['user_id'], 5);
    }
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
             placeholder="Cari kopi, makanan, minuman...">
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
        <div class="dropdown" id="notifDropdownWrapper">
          <button class="kny-navbar__bell" id="notifBellBtn" title="Notifikasi" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell<?= $userNotifCount > 0 ? '-fill' : '' ?>" style="color: var(--primary);"></i>
            <?php if ($userNotifCount > 0): ?>
              <span id="notifBadge" style="position:absolute;top:-4px;right:-6px;min-width:18px;height:18px;background:var(--danger);color:#fff;border-radius:50%;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 4px;line-height:1;border:2px solid rgba(26,60,46,0.5);"><?= $userNotifCount ?></span>
            <?php endif; ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end kny-notif-dropdown" aria-labelledby="notifBellBtn">
            <li class="kny-notif-header">
              <div class="d-flex justify-content-between align-items-center">
                <span style="font-weight:700;color:var(--primary);font-size:.85rem;">Notifikasi</span>
                <?php if ($userNotifCount > 0): ?>
                <span style="font-size:.72rem;background:var(--accent);color:var(--primary);padding:2px 8px;border-radius:20px;font-weight:700;"><?= $userNotifCount ?> baru</span>
                <?php endif; ?>
              </div>
            </li>
            <?php if (empty($userNotifications)): ?>
            <li class="kny-notif-empty">
              <i class="bi bi-bell-slash" style="font-size:1.5rem;color:var(--text-muted);"></i>
              <span>Tidak ada notifikasi</span>
            </li>
            <?php else: ?>
              <?php foreach ($userNotifications as $notif): ?>
              <li>
                <a class="dropdown-item kny-notif-item <?= !$notif['is_read'] ? 'unread' : '' ?>"
                   href="../history/history.php?highlight=<?= $notif['order_id'] ?>"
                   onclick="showNotifOrderDetail(<?= $notif['order_id'] ?>); return false;">
                  <div class="kny-notif-row">
                    <div class="kny-notif-icon <?= $notif['icon_class'] ?? '' ?>">
                      <?php
                      $notifIcons = [
                        'pending' => 'bi-clock-fill',
                        'processing' => 'bi-hourglass-split',
                        'shipped' => 'bi-truck',
                        'completed' => 'bi-check-circle-fill',
                        'cancelled' => 'bi-x-circle-fill'
                      ];
                      ?>
                      <i class="bi <?= $notifIcons[$notif['order_status']] ?? 'bi-bell' ?>"></i>
                    </div>
                    <div class="kny-notif-body">
                      <div class="kny-notif-title"><?= htmlspecialchars($notif['title']) ?></div>
                      <div class="kny-notif-message"><?= htmlspecialchars($notif['message']) ?></div>
                      <?php if (!empty($notif['action_text'])): ?>
                      <div class="kny-notif-badge">
                        <i class="bi bi-hand-index-thumb"></i> <?= htmlspecialchars($notif['action_text']) ?>
                      </div>
                      <?php endif; ?>
                      <div class="kny-notif-time">
                        <i class="bi bi-clock"></i> <?= date('d M Y, H:i', strtotime($notif['created_at'])) ?>
                      </div>
                    </div>
                  </div>
                </a>
              </li>
              <?php endforeach; ?>
            <?php endif; ?>
            <li class="kny-notif-footer">
              <a href="../history/history.php" class="text-center" style="font-size:.78rem;color:var(--primary);font-weight:600;">Lihat Semua Riwayat</a>
            </li>
          </ul>
        </div>
      <?php endif; ?>
      <?php if (!$currentUser || $currentUser['role'] !== 'admin'): ?>
      <a href="<?= $currentUser ? '../cart/cart.php' : '../auth/login.php' ?>" class="kny-navbar__cart" title="Keranjang">
        <i class="bi bi-bag"></i>
        <?php if ($currentUser && $cartTotalItems > 0): ?>
          <span class="kny-navbar__cart-badge"><?= $cartTotalItems ?></span>
        <?php endif; ?>
      </a>
      <?php endif; ?>
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
.kny-navbar__bell {
  position: relative;
  font-size: 1.3rem;
  color: white;
  padding: .4rem;
  border-radius: var(--radius-md);
  transition: background .2s;
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: none;
}
.kny-navbar__bell:hover {
  background: rgba(255,255,255,.08);
}
.kny-navbar__bell i {
  color: white;
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
/* Notification Dropdown */
.kny-notif-dropdown {
  width: 420px !important;
  max-width: 95vw !important;
  padding: 0 !important;
  border: 1px solid var(--border) !important;
  border-radius: var(--radius-lg) !important;
  box-shadow: var(--shadow-lg) !important;
  overflow: hidden;
}
.kny-notif-header {
  padding: .85rem 1.25rem;
  border-bottom: 1px solid var(--border);
  background: var(--cream);
}
.kny-notif-empty {
  padding: 2rem 1.25rem;
  text-align: center;
  color: var(--text-muted);
  font-size: .9rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .5rem;
}
.kny-notif-item {
  padding: 1rem 1.25rem !important;
  border-bottom: 1px solid var(--border) !important;
  transition: background .2s;
  display: block !important;
  text-decoration: none !important;
  width: 100%;
  box-sizing: border-box;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  white-space: normal !important;
}
.kny-notif-item:last-child { border-bottom: none !important; }
.kny-notif-item:hover { background: var(--cream) !important; }
.kny-notif-item.unread { background: #f0f7ff; }
.kny-notif-item.unread:hover { background: #e8f0ff; }
.kny-notif-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.2rem;
}
.kny-notif-icon.processing { background: #fff8ec; color: var(--accent); }
.kny-notif-icon.shipped { background: #eff6ff; color: #3b82f6; }
.kny-notif-icon.completed { background: #ecfaf4; color: var(--success); }
.kny-notif-icon.cancelled { background: #fdf0f0; color: var(--danger); }
.kny-notif-icon.pending { background: #f5f5f5; color: var(--text-muted); }
.kny-notif-content { flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word; }
.kny-notif-row {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  width: 100%;
}
.kny-notif-body {
  flex: 1;
  min-width: 0;
  width: calc(100% - 50px);
  word-wrap: break-word;
  overflow-wrap: break-word;
}
.kny-notif-title {
 width: 100%;
  font-size: .9rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: .3rem;
  line-height: 1.3;
  word-wrap: break-word;
  overflow-wrap: break-word;
}
.kny-notif-message {
  width: 100%;
  font-size: .82rem;
  color: var(--text-mid);
  line-height: 1.5;
  display: block;
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal !important;
}
.kny-notif-action, .kny-notif-badge {
  width: auto;
  max-width: 100%;
  background: linear-gradient(135deg, #fff8ec, #fff3d9);
  color: #8a6d3b;
  padding: 5px 12px;
  border-radius: 8px;
  font-size: .78rem;
  font-weight: 700;
  margin-top: .4rem;
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  border: 1px solid #f0cb7a;
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal !important;
}
.kny-notif-time {
  width: 100%;
  font-size: .72rem;
  color: var(--text-muted);
  margin-top: .4rem;
  display: flex;
  align-items: center;
  gap: .3rem;
  white-space: nowrap;
}
.kny-notif-badge {
  background: linear-gradient(135deg, #fff8ec, #fff3d9);
  color: #8a6d3b;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: .7rem;
  font-weight: 700;
  margin-top: .35rem;
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  border: 1px solid #f0cb7a;
  word-wrap: break-word;
  max-width: 100%;
}
.kny-notif-footer {
  padding: .75rem 1rem;
  border-top: 1px solid var(--border);
  background: var(--cream);
}

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

// Mark notifications as read when dropdown is opened
document.getElementById('notifBellBtn')?.addEventListener('shown.bs.dropdown', function() {
  markNotificationsRead();
});

function markNotificationsRead() {
  // Make AJAX call to mark all notifications as read
  fetch('../history/mark-notifications-read.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Hide badge
        const badge = document.getElementById('notifBadge');
        if (badge) {
          badge.style.display = 'none';
        }
        // Remove unread class from items
        document.querySelectorAll('.kny-notif-item.unread').forEach(function(item) {
          item.classList.remove('unread');
        });
        // Update header count
        const headerCount = document.querySelector('.kny-notif-header span:last-child');
        if (headerCount) headerCount.textContent = '0 baru';
      }
    })
    .catch(err => console.log('Error marking notifications read:', err));
}

// Show order detail modal and mark as read when notification is clicked
function showNotifOrderDetail(orderId) {
  // Mark this specific notification as read via AJAX
  fetch('../history/mark-notifications-read.php?order_id=' + orderId)
    .then(response => response.json())
    .then(data => {
      // Then redirect to history page with highlight
      window.location.href = '../history/history.php?highlight=' + orderId;
    })
    .catch(err => {
      // Even if fetch fails, still redirect
      window.location.href = '../history/history.php?highlight=' + orderId;
    });
}
</script>
