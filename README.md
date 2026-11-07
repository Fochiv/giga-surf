# Giga Surf

Storefront PHP native pour vendre et suivre les forfaits Orange, MTN et Camtel Blue au Cameroun. Le projet utilise PHP 8+, PDO, MySQL en hébergement, cURL pour Fapshi, et Bootstrap chargé depuis un CDN. Aucune dépendance Composer ou npm n’est nécessaire.

## Prévisualisation Replit

- Le serveur utilise SQLite uniquement dans l’environnement Replit pour que la prévisualisation fonctionne sans base MySQL.
- Les tables et le catalogue initial sont créés au premier chargement.
- L’accès admin se crée une seule fois depuis `/Admin/setup.php` (le lien apparaît après cinq clics rapides sur l’étape 3 de l’accueil).
- Les paiements restent désactivés tant que les identifiants Fapshi renouvelés ne sont pas configurés.

## Installation sur Hostinger

1. Créez une base MySQL et un utilisateur dans hPanel, puis accordez les droits nécessaires pour créer et modifier les tables.
2. Copiez les fichiers du projet dans le dossier du site (par exemple `public_html`). Conservez `config/`, `includes/`, `php/`, `Admin/`, `assets/` et les logos.
3. Copiez `config/local.example.php` vers `config/local.php`, puis renseignez `base_url`, le nom et l’utilisateur de la base, ainsi que le mot de passe MySQL. Le fichier `config/local.php` est ignoré par Git et protégé par `.htaccess`; gardez-le hors du dossier web si votre hébergement le permet.
4. Activez HTTPS. La première visite crée automatiquement le schéma MySQL et ajoute le catalogue initial. Créez immédiatement un nouveau compte admin via `/Admin/setup.php` avant de partager le site.
5. Ajoutez les identifiants Fapshi renouvelés et le secret webhook uniquement dans `config/local.php` sur Hostinger. En production, utilisez `https://live.fapshi.com` comme `base_url`.
6. Dans le tableau de bord Fapshi, configurez le webhook vers `https://votre-domaine/webhook.php` avec le même secret que `webhook_secret`.

Le code appelle Fapshi côté serveur. Les montants viennent du catalogue MySQL, et un paiement n’est considéré comme réussi qu’après vérification par l’API Fapshi; un statut reçu dans le navigateur ne suffit pas. Après confirmation, l’admin effectue manuellement la recharge télécom et utilise le bouton « Livrer » pour mettre à jour le suivi client.

## Fichiers utiles

- `index.php`, `forfaits.php`, `commande.php`, `mes-commandes.php` : parcours client.
- `Admin/` : connexion, initialisation admin, tableau de bord, commandes, forfaits, avis et paramètres.
- `php/Fapshi.php` : client PHP Fapshi.
- `webhook.php` : vérification des notifications Fapshi.
- `config/local.example.php` : exemple de configuration MySQL/Fapshi pour Hostinger.
- `storage/` : base SQLite de prévisualisation locale; protégée et exclue de Git.
