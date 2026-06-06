<?php
/* ============================================================
   puppy.co — Footer Component
   includes/footer.php
   Usage: require_once __DIR__ . '/includes/footer.php';
   Must be included after header.php (closes <main>)
   ============================================================ */
?>

</main><!-- /main -->

<!-- ══════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════ -->
<footer role="contentinfo">

  <div class="footer-inner container">

    <!-- Left: brand -->
    <div class="footer-brand">
      <span class="dot" aria-hidden="true"></span>
      <span class="footer-name">puppy.co</span>
      <span class="footer-tagline">Sistema de gestão pet</span>
    </div>

    <!-- Center: quick links -->
    <nav class="footer-nav" aria-label="Links rápidos">
      <a href="dashboard.php">Dashboard</a>
      <span aria-hidden="true">·</span>
      <a href="owners.php">Tutores</a>
      <span aria-hidden="true">·</span>
      <a href="pets.php">Pets</a>
      <span aria-hidden="true">·</span>
      <a href="appointments.php">Agendamentos</a>
    </nav>

    <!-- Right: year -->
    <p class="footer-copy">
      &copy; <?= date('Y') ?> puppy.co
    </p>

  </div>

</footer>

<!-- ══════════════════════════════════════════════════════════
     FOOTER STYLES  (scoped, no extra file needed)
══════════════════════════════════════════════════════════ -->
<style>
  footer {
    border-top: 1px solid var(--border);
    background: var(--bg-card);
    transition: background-color var(--t-slow), border-color var(--t-slow);
  }

  .footer-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding-top: 18px;
    padding-bottom: 18px;
    flex-wrap: wrap;
  }

  .footer-brand {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .footer-brand .dot {
    width: 7px;
    height: 7px;
    background: var(--accent);
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 0 8px var(--accent-glow);
    animation: pulse-dot 2.5s ease-in-out infinite;
  }

  .footer-name {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text);
  }

  .footer-tagline {
    font-size: 0.78rem;
    color: var(--text-muted);
    padding-left: 4px;
    border-left: 1px solid var(--border);
    margin-left: 4px;
  }

  .footer-nav {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
  }

  .footer-nav a {
    font-size: 0.8125rem;
    color: var(--text-muted);
    transition: color var(--t-fast);
  }

  .footer-nav a:hover { color: var(--accent); }

  .footer-nav span {
    color: var(--border);
    font-size: 0.75rem;
  }

  .footer-copy {
    font-size: 0.78rem;
    color: var(--text-muted);
    white-space: nowrap;
  }

  @media (max-width: 640px) {
    .footer-inner    { flex-direction: column; text-align: center; gap: 12px; }
    .footer-tagline  { display: none; }
  }
</style>

<!-- ══════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════ -->
<script src="/puppy.co/assets/js/main.js"></script>

</body>
</html>