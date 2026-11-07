<?php
declare(strict_types=1);

function adminIcon(string $name): string
{
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'receipt' => '<path d="M5 3h14v18l-3-2-4 2-4-2-3 2z"/><path d="M8 8h8M8 12h8M8 16h4"/>',
        'package' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 8 9 5 9-5M3 8v9l9 5 9-5V8M12 13v9"/>',
        'message' => '<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 4a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.4Z"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.7 1l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.7-1l-1.7.6-1.4-2.4L7.3 15a8 8 0 0 1 0-2l-1.4-1.1 1.4-2.4 1.7.6a8 8 0 0 1 1.7-1l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.7 1l1.7-.6 1.4 2.4-1.4 1.1a8 8 0 0 1-.1 2Z"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
    ];
    return '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? '') . '</svg>';
}

function adminHeader(string $title, string $active = '', bool $loginPage = false): void
{
    $flashItems = consumeFlashes();
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <link rel="icon" href="../logo-giga-surf.png" type="image/png">
        <title><?= h($title) ?> · Giga Surf Admin</title>
        <link rel="stylesheet" href="../assets/css/app.css">
        <link rel="stylesheet" href="../assets/css/admin.css">
    </head>
    <body class="admin-body">
    <?php if (!$loginPage): ?>
        <div class="admin-shell">
            <button class="admin-menu-backdrop" type="button" data-admin-menu-close aria-label="Fermer le menu" hidden></button>
            <aside class="admin-sidebar" id="admin-navigation" aria-hidden="true" inert>
                <div class="admin-drawer-heading">
                    <a class="admin-brand" href="index.php"><img src="../logo-giga-surf.png" alt="" /><span>Giga Surf <small>ADMIN</small></span></a>
                    <button class="admin-menu-close" type="button" data-admin-menu-close aria-label="Fermer le menu"><?= adminIcon('close') ?></button>
                </div>
                <nav class="admin-nav" aria-label="Navigation administrateur">
                    <?php foreach (adminNav($active) as $file => $item): ?>
                        <a class="<?= $item['active'] ? 'active' : '' ?>" href="<?= h($file) ?>">
                            <?= adminIcon($item['icon']) ?><span data-fr="<?= h($item['label']) ?>" data-en="<?= h(['Vue d’ensemble' => 'Overview', 'Commandes' => 'Orders', 'Forfaits' => 'Bundles', 'Avis clients' => 'Customer reviews', 'Paramètres' => 'Settings'][$item['label']] ?? $item['label']) ?>"><?= h($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="admin-sidebar-bottom">
                    <div class="admin-user-chip"><span class="admin-avatar">GS</span><span><?= h($_SESSION['admin_username'] ?? 'Admin') ?><small>Administrateur</small></span></div>
                    <form action="logout.php" method="post"><?= csrf_field() ?><button class="admin-logout" type="submit"><?= adminIcon('logout') ?><span data-fr="Déconnexion" data-en="Sign out">Déconnexion</span></button></form>
                </div>
            </aside>
            <main class="admin-main">
                <header class="admin-topbar">
                    <div><span class="admin-eyebrow" data-fr="ESPACE DE GESTION" data-en="ADMIN WORKSPACE">ESPACE DE GESTION</span><h1><?= h($title) ?></h1></div>
                    <div class="admin-top-actions">
                        <button class="admin-menu-toggle" type="button" data-admin-menu-toggle aria-expanded="false" aria-controls="admin-navigation"><?= adminIcon('menu') ?><span data-fr="Menu" data-en="Menu">Menu</span></button>
                        <div class="language-toggle" role="group" aria-label="Choisir la langue"><button type="button" data-lang="fr" aria-pressed="true">FR</button><button type="button" data-lang="en" aria-pressed="false">EN</button></div>
                        <a class="admin-view-site" href="../index.php" target="_blank" rel="noopener"><span data-fr="Voir le site" data-en="View website">Voir le site</span> <?= adminIcon('arrow') ?></a>
                    </div>
                </header>
                <?php foreach ($flashItems as $message): ?>
                    <div class="admin-alert <?= h($message['type']) ?>" role="status"><?= h($message['message']) ?></div>
                <?php endforeach; ?>
    <?php else: ?>
        <main class="admin-auth-wrap">
            <div class="admin-auth-top"><a class="admin-auth-brand" href="../index.php"><img src="../logo-giga-surf.png" alt="Giga Surf"><span>Giga Surf <small>ADMIN</small></span></a><div class="language-toggle" role="group" aria-label="Choisir la langue"><button type="button" data-lang="fr" aria-pressed="true">FR</button><button type="button" data-lang="en" aria-pressed="false">EN</button></div></div>
        <?php foreach ($flashItems as $message): ?>
            <div class="admin-alert <?= h($message['type']) ?>" role="status"><?= h($message['message']) ?></div>
        <?php endforeach; ?>
    <?php endif;
}

function adminFooter(bool $loginPage = false): void
{
    echo '<script src="../assets/js/app.js" defer></script>';
    if ($loginPage) {
        echo '</main></body></html>';
        return;
    }
    echo '</main></div></body></html>';
}
