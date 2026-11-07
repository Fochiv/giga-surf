</main>
<footer class="site-footer">
  <div class="shell footer-inner">
    <div>© <?= date('Y') ?> Giga Surf · <span data-fr="Connecté au Cameroun." data-en="Connected in Cameroon.">Connecté au Cameroun.</span></div>
    <div class="footer-links">
      <a href="<?= h(url('forfaits.php')) ?>" data-fr="Voir les forfaits" data-en="Browse bundles">Voir les forfaits</a>
      <a href="<?= h(url('mes-commandes.php')) ?>" data-fr="Suivre une commande" data-en="Track an order">Suivre une commande</a>
    </div>
  </div>
</footer>
<nav class="bottom-nav" aria-label="Navigation mobile">
  <a class="<?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>" href="<?= h(url('index.php')) ?>"><?= giga_svg('home') ?><span data-fr="Accueil" data-en="Home">Accueil</span></a>
  <a class="<?= ($currentPage ?? '') === 'plans' ? 'active' : '' ?>" href="<?= h(url('forfaits.php')) ?>"><?= giga_svg('grid') ?><span data-fr="Forfaits" data-en="Bundles">Forfaits</span></a>
  <a class="<?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>" href="<?= h(url('mes-commandes.php')) ?>"><?= giga_svg('receipt') ?><span data-fr="Commandes" data-en="Orders">Commandes</span></a>
</nav>
<script src="<?= h(url('assets/js/app.js')) ?>" defer></script>
</body>
</html>
