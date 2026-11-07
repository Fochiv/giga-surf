<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = $pageTitle ?? 'Giga Surf';
$currentPage = $currentPage ?? '';
if (!function_exists('giga_svg')) {
    function giga_svg(string $name): string {
        $icons = [
            'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7H9v7H4a1 1 0 0 1-1-1z"/>',
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'receipt' => '<path d="M4 3h16v18l-3-2-3 2-3-2-3 2-4-2z"/><path d="M8 8h8M8 12h8"/>',
            'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
            'back' => '<path d="m15 18-6-6 6-6M9 12h11"/>',
            'shield' => '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/><path d="m9 12 2 2 4-4"/>',
            'bolt' => '<path d="m13 2-3 8h7l-6 12 2-9H6z"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'check' => '<path d="m5 12 4 4L19 6"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
            'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
            'wifi' => '<path d="M5 9a11 11 0 0 1 14 0M8 12a7 7 0 0 1 8 0m-5 4h.01"/><circle cx="12" cy="19" r="1"/>',
            'headset' => '<path d="M3 14v-2a9 9 0 0 1 18 0v2"/><path d="M5 14h2v6H5a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2Zm14 0h-2v6h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2Z"/><path d="M17 20a5 5 0 0 1-5 2h-1"/>'
        ];
        return '<svg class="icon" aria-hidden="true" viewBox="0 0 24 24">'.$icons[$name].'</svg>';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f5f7fb">
  <link rel="icon" href="<?= h(url('logo-giga-surf.png')) ?>" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <title><?= h($pageTitle) ?> · Giga Surf</title>
  <link rel="stylesheet" href="<?= h(url('assets/css/app.css')) ?>">
</head>
<body>
<header class="site-header">
  <div class="shell header-inner">
    <a class="brand" href="<?= h(url('index.php')) ?>" aria-label="Giga Surf — accueil">
      <img src="<?= h(url('logo-giga-surf.png')) ?>" alt="Giga Surf">
    </a>
    <nav class="main-nav" aria-label="Navigation principale">
      <a class="<?= $currentPage === 'home' ? 'active' : '' ?>" href="<?= h(url('index.php')) ?>" data-fr="Accueil" data-en="Home">Accueil</a>
      <a class="<?= $currentPage === 'plans' ? 'active' : '' ?>" href="<?= h(url('forfaits.php')) ?>" data-fr="Forfaits" data-en="Bundles">Forfaits</a>
      <a class="<?= $currentPage === 'orders' ? 'active' : '' ?>" href="<?= h(url('mes-commandes.php')) ?>" data-fr="Mes commandes" data-en="My orders">Mes commandes</a>
    </nav>
    <div class="header-tools">
      <div class="language-toggle" role="group" aria-label="Choisir la langue">
        <button type="button" data-lang="fr" aria-pressed="true">FR</button>
        <button type="button" data-lang="en" aria-pressed="false">EN</button>
      </div>
    </div>
  </div>
</header>
<main>
<?php foreach (consumeFlashes() as $notice): ?>
  <div class="shell"><div class="notice flash-<?= h($notice['type']) ?>" role="<?= $notice['type'] === 'error' ? 'alert' : 'status' ?>"><?= h($notice['message']) ?></div></div>
<?php endforeach; ?>
