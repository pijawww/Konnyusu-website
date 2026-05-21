<?php // layouts/footer.php ?>
<!-- ===================== FOOTER ===================== -->
<footer class="kny-footer">
  <div class="kny-footer__container">

    <div class="kny-footer__brand">
      <a href="../home/home.php" class="kny-footer__logo">
        <img src="../assets/img/logo.jpeg"
            alt="Konnyusu Logo"
            class="footer-logo">
      </a>
      <p class="kny-footer__tagline">
        Dari biji pilihan ke cangkir Anda —<br>rasakan kehangatan dalam setiap tegukan.
      </p>
      <div class="kny-footer__socials">
        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
        <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
      </div>
    </div>

    <div class="kny-footer__links">
      <div>
        <h6>Menu</h6>
        <ul>
          <li><a href="../home/home.php">Beranda</a></li>
          <li><a href="../home/home.php?cat=coffee">Kopi</a></li>
          <li><a href="../home/home.php?cat=non-coffee">Non Kopi</a></li>
          <li><a href="../home/home.php?cat=makanan">Makanan</a></li>
                  </ul>
      </div>
      <div>
        <h6>Akun</h6>
        <ul>
          <li><a href="../auth/login.php">Masuk</a></li>
          <li><a href="../auth/register.php">Daftar</a></li>
          <li><a href="../profile/profile.php">Profil</a></li>
          <li><a href="../history/history.php">Riwayat</a></li>
        </ul>
      </div>
      <div>
        <h6>Bantuan</h6>
        <ul>
          <li><a href="#">FAQ</a></li>
          <li><a href="#">Kebijakan Privasi</a></li>
          <li><a href="#">Syarat & Ketentuan</a></li>
          <li><a href="#">Hubungi Kami</a></li>
        </ul>
      </div>
    </div>

  </div>
  <div class="kny-footer__bottom">
    <p>&copy; <?= date('Y') ?> Konnyusu. All rights reserved.</p>
    <p>Dibuat dengan ❤️ di Indonesia</p>
  </div>
</footer>

<style>
.kny-footer {
  background: var(--primary);
  color: rgba(255,255,255,.75);
  margin-top: 80px;
}
.kny-footer__container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 3.5rem 1.5rem 2rem;
  display: grid;
  grid-template-columns: 1.8fr 1fr 1fr 1fr;
  gap: 3rem;
}
.kny-footer__logo {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--white);
  display: flex;
  align-items: center;
  gap: .5rem;
  margin-bottom: .85rem;
  text-decoration: none;
}
.kny-footer__tagline {
  font-size: .88rem;
  line-height: 1.6;
  margin-bottom: 1.25rem;
}
.kny-footer__socials { display: flex; gap: .75rem; }
.kny-footer__socials a {
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 50%;
  background: rgba(255,255,255,.1);
  color: var(--white);
  font-size: 1rem;
  transition: background .2s;
  text-decoration: none;
}
.kny-footer__socials a:hover { background: var(--accent); color: var(--primary); }
.kny-footer__links {
  display: contents;
}
.kny-footer__links > div h6 {
  font-family: var(--font-body);
  font-size: .75rem;
  font-weight: 700;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--accent-light);
  margin-bottom: 1rem;
}
.kny-footer__links > div ul { list-style: none; }
.kny-footer__links > div ul li { margin-bottom: .5rem; }
.kny-footer__links > div ul li a {
  font-size: .88rem;
  color: rgba(255,255,255,.65);
  text-decoration: none;
  transition: color .2s;
}
.kny-footer__links > div ul li a:hover { color: var(--white); }
.kny-footer__bottom {
  border-top: 1px solid rgba(255,255,255,.1);
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  font-size: .8rem;
  color: rgba(255,255,255,.45);
}
@media (max-width: 900px) {
  .kny-footer__container {
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
  }
  .kny-footer__brand { grid-column: 1/-1; }
  .kny-footer__links { display: contents; }
}
@media (max-width: 576px) {
  .kny-footer__container { grid-template-columns: 1fr; gap: 1.5rem; }
  .kny-footer__bottom { flex-direction: column; gap: .25rem; text-align: center; }
}
.kny-footer__logo{
  display:flex;
  align-items:center;
  text-decoration:none;
}

.footer-logo{
  height:70px;
  width:auto;
  object-fit:contain;
}
</style>
