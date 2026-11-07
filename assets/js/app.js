(function () {
  'use strict';
  var lang = localStorage.getItem('giga-language') || 'fr';
  var adminTranslations = {
    "ESPACE DE GESTION":"ADMIN WORKSPACE","Administrateur":"Administrator",
    "Créer votre accès sécurisé":"Create your secure access","PREMIÈRE CONFIGURATION":"FIRST-TIME SETUP",
    "Ce formulaire disparaîtra après la création du premier compte administrateur.":"This form disappears after the first administrator account is created.",
    "Identifiant":"Username","Nouveau mot de passe":"New password","Confirmer le mot de passe":"Confirm password",
    "Créer le compte admin":"Create admin account","Retour au site":"Back to website","ESPACE SÉCURISÉ":"SECURE AREA",
    "Bon retour":"Welcome back","Connectez-vous pour gérer les forfaits et les commandes.":"Sign in to manage bundles and orders.",
    "Mot de passe":"Password","Se connecter":"Sign in","Vue d’ensemble":"Overview","Gestion des forfaits":"Bundle management",
    "Commandes":"Orders","Avis clients":"Customer reviews","Paramètres":"Settings","Voir le site":"View website",
    "Déconnexion":"Sign out","Vue d’ensemble":"Overview","Solde Fapshi":"Fapshi balance","À configurer":"Not configured",
    "Ajoutez les identifiants renouvelés pour afficher le solde.":"Add renewed credentials to display the balance.",
    "Service de collecte":"Collection service","Revenus bruts":"Gross revenue","Tous les paiements confirmés":"All confirmed payments",
    "Revenu net estimé":"Estimated net revenue","Après frais Fapshi estimés":"After estimated Fapshi fees",
    "Commandes totales":"Total orders","Commandes échouées":"Failed orders","Échecs et paiements expirés":"Failed and expired payments",
    "AUJOURD’HUI":"TODAY","CETTE SEMAINE":"THIS WEEK","CE MOIS":"THIS MONTH","Net estimé :":"Estimated net:",
    "VENTES CONFIRMÉES":"CONFIRMED SALES","Revenu par opérateur":"Revenue by operator",
    "Les revenus s’afficheront après les premiers paiements confirmés.":"Revenue will appear after the first confirmed payments.",
    "commande(s)":"order(s)","POPULARITÉ":"POPULARITY","Forfaits les plus vendus":"Top-selling bundles",
    "Aucune vente confirmée pour le moment.":"No confirmed sales yet.","SUIVI":"TRACKING","Statut des commandes":"Order statuses",
    "Aucune commande enregistrée.":"No orders recorded.","RACCOURCIS":"SHORTCUTS","À faire ensuite":"Next steps",
    "Traiter les commandes payées":"Process paid orders","Mettre à jour les forfaits":"Update bundles",
    "ACTIVITÉ RÉCENTE":"RECENT ACTIVITY","Historique des commandes":"Order history","Voir toutes":"View all",
    "Référence":"Reference","Client":"Customer","Opérateur":"Operator","Montant":"Amount","Paiement":"Payment","Date":"Date",
    "Connexion administrateur":"Administrator sign in","Le paiement a été vérifié auprès de Fapshi.":"Payment status verified with Fapshi.",
    "Commande marquée comme livrée. Le client verra le nouveau statut dans son suivi.":"Order marked delivered. The customer will see the updated status in tracking.",
    "Action impossible. Vérifiez que le paiement est confirmé avant la livraison.":"Action unavailable. Confirm payment before marking delivery.",
    "Payées":"Paid","En attente":"Pending","Échouées":"Failed","Toutes":"All",
    "Rechercher une commande":"Search orders","Rechercher":"Search","Référence, client, téléphone":"Reference, customer, phone",
    "La liste principale affiche uniquement les paiements confirmés.":"The main list shows confirmed payments only.",
    "Résultats filtrés pour la gestion des commandes.":"Filtered results for order management.",
    "Aucune commande dans cette vue.":"No orders in this view.","Commande / client":"Order / customer","Forfait":"Bundle",
    "Action":"Action","Référence Fapshi en attente":"Waiting for Fapshi reference","Livrer":"Mark delivered","Livré":"Delivered",
    "Vérifier":"Verify","Gestion des forfaits":"Bundle management","MODIFICATION":"EDIT BUNDLE","NOUVELLE OFFRE":"NEW BUNDLE",
    "Modifier le forfait":"Edit bundle","Créer un forfait":"Create a bundle","Vérifiez l’opérateur, le nom, le prix, le volume et la durée de validité.":"Check the operator, name, price, data allowance and validity.",
    "Opérateur":"Operator","Choisir":"Choose","Nom du forfait":"Bundle name","Prix (FCFA)":"Price (XAF)",
    "Volume / détails":"Data / details","Validité (jours)":"Validity (days)","Ordre d’affichage":"Display order",
    "Description courte":"Short description","Enregistrer les changements":"Save changes","Annuler":"Cancel","Créer le forfait":"Create bundle",
    "CATALOGUE":"CATALOG","forfait(s)":"bundle(s)","Aucun forfait dans le catalogue.":"No bundles in the catalog.",
    "Actif sur le site":"Active on website","Masqué des clients":"Hidden from customers","Modifier":"Edit","Désactiver":"Deactivate",
    "Activer":"Activate","Supprimer":"Delete","Forfait créé et activé.":"Bundle created and activated.",
    "Forfait modifié.":"Bundle updated.","Le statut du forfait a été modifié.":"Bundle status updated.",
    "Forfait supprimé. Les commandes gardent leur récapitulatif historique.":"Bundle deleted. Orders retain their historical details.",
    "Avis clients":"Customer reviews","MODÉRATION":"MODERATION","NOUVEL AVIS":"NEW REVIEW","Modifier un avis":"Edit a review",
    "Ajouter un avis":"Add a review","Vérifiez le nom, la note et le texte de l’avis.":"Check the name, rating and review text.",
    "Nom affiché":"Display name","Note":"Rating","Avis":"Review","Publié sur le site":"Published on website",
    "Enregistrer":"Save","Ajouter l’avis":"Add review","avis":"reviews","Les avis clients s’afficheront ici.":"Customer reviews will appear here.",
    "Publié":"Published","À modérer":"Needs moderation","Masquer":"Hide","Publier":"Publish",
    "Avis supprimé.":"Review deleted.","La visibilité de l’avis a été modifiée.":"Review visibility updated.",
    "Avis modifié.":"Review updated.","Avis ajouté.":"Review added.",
    "Paramètres":"Settings","BOUTIQUE":"STORE","Disponibilité et annonces":"Availability and announcements",
    "Suspendre temporairement les commandes":"Temporarily pause orders","Annonce en français":"Announcement in French",
    "Annonce en anglais":"Announcement in English","Enregistrer les paramètres":"Save settings","Paramètres enregistrés.":"Settings saved.",
    "Les annonces sont limitées à 300 caractères.":"Announcements are limited to 300 characters.",
    "PAIEMENTS":"PAYMENTS","Connexion Fapshi":"Fapshi connection","Identifiants configurés":"Credentials configured",
    "Identifiants manquants":"Credentials missing","Les demandes de paiement peuvent être envoyées.":"Payment requests can be sent.",
    "Les paiements resteront désactivés tant que les nouvelles clés ne sont pas ajoutées aux Secrets Replit.":"Payments stay disabled until the renewed keys are added to Replit Secrets.",
    "Les identifiants API et le secret webhook sont chargés depuis la configuration serveur et ne sont jamais affichés ici.":"API credentials and the webhook secret are loaded from server configuration and never shown here.",
    "Renouvelez les identifiants API dans votre espace Fapshi.":"Renew the API credentials in your Fapshi account.",
    "Ajoutez les nouvelles valeurs dans les Secrets Replit, ou dans un fichier de configuration privé sur Hostinger.":"Add the new values to Replit Secrets or a private config file on Hostinger.",
    "Des identifiants configurés ne signifient pas que Direct Pay est autorisé en Live. Le webhook n’active pas cette option.":"Configured credentials do not mean Direct Pay is authorized in Live. The webhook does not enable it.",
    "Utilisez les identifiants du service Live dans les Secrets Replit ou la configuration privée Hostinger.":"Use the Live service credentials in Replit Secrets or Hostinger's private configuration.",
    "Demandez à Fapshi d’activer Direct Pay pour ce service Live.":"Ask Fapshi to enable Direct Pay for this Live service.",
    "Configurez l’URL webhook de ce site suivie de":"Set this site's webhook URL to",
    "Ne partagez jamais votre API Key.":"Never share your API Key.",
    "Guide d’activation Direct Pay":"Direct Pay activation guide",
    "Configurez l’URL webhook de Fapshi avec l’adresse de ce site suivie de":"Set the Fapshi webhook URL to this site's address followed by",
    "Documentation des webhooks":"Webhook documentation","Identifiant ou mot de passe incorrect.":"Incorrect username or password.",
    "Trop de tentatives. Réessayez dans 15 minutes.":"Too many attempts. Try again in 15 minutes.",
    "Vous êtes déconnecté.":"You have signed out.","Numéro WhatsApp de l’assistance":"Support WhatsApp number",
    "Format accepté : numéro camerounais, avec ou sans indicatif +237.":"Accepted format: Cameroon number, with or without the +237 prefix.",
    "Saisissez un numéro WhatsApp camerounais valide.":"Enter a valid Cameroon WhatsApp number.","Paiement confirmé":"Payment confirmed",
    "Paiement échoué":"Payment failed","Paiement expiré":"Payment expired","Paiement en attente":"Payment pending",
    "Vérification requise":"Verification required","Connectez-vous pour accéder à l’espace admin.":"Sign in to access the admin area."
  };
  var adminTranslationsBack = {};
  Object.keys(adminTranslations).forEach(function (key) { adminTranslationsBack[adminTranslations[key]] = key; });
  function translateAdminText(next) {
    if (!document.body.classList.contains('admin-body')) return;
    var translations = next === 'en' ? adminTranslations : adminTranslationsBack;
    var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    var nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach(function (node) {
      var parent = node.parentElement;
      if (!parent || parent.closest('[data-fr][data-en]') || /^(SCRIPT|STYLE|TEXTAREA)$/i.test(parent.tagName)) return;
      var raw = node.nodeValue;
      var text = raw.trim();
      if (translations[text]) node.nodeValue = raw.replace(text, translations[text]);
    });
    document.querySelectorAll('input[placeholder],textarea[placeholder]').forEach(function (field) {
      var value = field.getAttribute('placeholder');
      if (translations[value]) field.setAttribute('placeholder', translations[value]);
    });
  }
  function setLanguage(next) {
    lang = next;
    localStorage.setItem('giga-language', next);
    document.documentElement.lang = next;
    document.querySelectorAll('[data-fr][data-en]').forEach(function (el) {
      var value = el.getAttribute('data-' + next);
      if (value !== null) {
        if (el.hasAttribute('data-i18n-html')) el.innerHTML = value;
        else el.textContent = value;
      }
    });
    document.querySelectorAll('[data-fr-placeholder][data-en-placeholder]').forEach(function (el) {
      el.setAttribute('placeholder', el.getAttribute('data-' + next + '-placeholder'));
    });
    document.querySelectorAll('[data-lang]').forEach(function (button) {
      var active = button.getAttribute('data-lang') === next;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    translateAdminText(next);
  }
  document.querySelectorAll('[data-lang]').forEach(function (button) {
    button.addEventListener('click', function () { setLanguage(button.getAttribute('data-lang')); });
  });
  setLanguage(lang);

  var filterButtons = document.querySelectorAll('[data-filter]');
  var filterEmpty = document.querySelector('[data-filter-empty]');
  function updateFilterEmpty(filter) {
    if (!filterEmpty) return;
    var cards = document.querySelectorAll('[data-category]');
    var visibleCount = 0;
    cards.forEach(function (card) {
      var visible = filter === 'all' || card.getAttribute('data-category') === filter;
      card.hidden = !visible;
      if (visible) visibleCount += 1;
    });
    filterEmpty.hidden = visibleCount > 0;
    if (visibleCount === 0) {
      var weekly = filter === 'weekly';
      var title = filterEmpty.querySelector('[data-empty-title]');
      var copy = filterEmpty.querySelector('[data-empty-copy]');
      var titleFr = weekly ? 'Aucun forfait hebdomadaire disponible pour le moment.' : 'Aucun forfait ne correspond à cette durée pour le moment.';
      var titleEn = weekly ? 'No weekly bundles are available right now.' : 'No bundles match this duration right now.';
      if (title) {
        title.setAttribute('data-fr', titleFr);
        title.setAttribute('data-en', titleEn);
        title.textContent = lang === 'en' ? titleEn : titleFr;
      }
      if (copy) copy.textContent = lang === 'en' ? 'Please try again later.' : 'Veuillez réessayer plus tard.';
    }
  }
  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var filter = button.getAttribute('data-filter');
      filterButtons.forEach(function (item) {
        var active = item === button;
        item.classList.toggle('is-active', active);
        item.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      updateFilterEmpty(filter);
    });
  });

  var stepThree = document.querySelector('[data-admin-reveal]');
  if (stepThree) {
    var clicks = 0;
    var lastClickAt = 0;
    stepThree.addEventListener('click', function () {
      var now = Date.now();
      if (now - lastClickAt > 1800) clicks = 0;
      lastClickAt = now;
      clicks += 1;
      if (clicks >= 5) window.location.assign(stepThree.getAttribute('data-admin-url'));
    });
    stepThree.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); stepThree.click(); }
    });
  }

  var planSelect = document.querySelector('[data-plan-select]');
  if (planSelect) {
    var updatePlan = function () {
      var selected = planSelect.options[planSelect.selectedIndex];
      var amount = document.querySelector('[data-summary-price]');
      var title = document.querySelector('[data-summary-plan]');
      if (selected && amount) amount.textContent = selected.getAttribute('data-price-label') || '—';
      if (selected && title) title.textContent = selected.textContent || '—';
    };
    planSelect.addEventListener('change', updatePlan);
    updatePlan();
  }
  var numberInputs = document.querySelectorAll('input[type="tel"]');
  numberInputs.forEach(function (input) {
    input.addEventListener('input', function () { input.value = input.value.replace(/[^\d+ ]/g, '').slice(0, 16); });
  });

  var menuToggle = document.querySelector('[data-admin-menu-toggle]');
  var adminDrawer = document.querySelector('.admin-sidebar');
  var menuBackdrop = document.querySelector('.admin-menu-backdrop');
  if (menuToggle && adminDrawer && menuBackdrop) {
    var closeMenu = function () {
      adminDrawer.setAttribute('aria-hidden', 'true');
      adminDrawer.inert = true;
      menuToggle.setAttribute('aria-expanded', 'false');
      menuBackdrop.hidden = true;
      document.body.classList.remove('admin-menu-open');
    };
    menuToggle.addEventListener('click', function () {
      var open = menuToggle.getAttribute('aria-expanded') !== 'true';
      adminDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
      adminDrawer.inert = !open;
      menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuBackdrop.hidden = !open;
      document.body.classList.toggle('admin-menu-open', open);
      if (open) {
        var firstLink = adminDrawer.querySelector('.admin-nav a');
        if (firstLink) firstLink.focus();
      }
    });
    document.querySelectorAll('[data-admin-menu-close]').forEach(function (button) {
      button.addEventListener('click', closeMenu);
    });
    adminDrawer.querySelectorAll('.admin-nav a').forEach(function (link) {
      link.addEventListener('click', closeMenu);
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
        closeMenu();
        menuToggle.focus();
      }
    });
  }

  var pendingPaymentCards = Array.prototype.slice.call(document.querySelectorAll('[data-auto-payment]'));
  if (pendingPaymentCards.length) {
    var paymentPollInProgress = false;
    var paymentCompleted = false;
    var pollPendingPayments = function () {
      if (paymentPollInProgress || paymentCompleted || document.visibilityState === 'hidden') return;
      paymentPollInProgress = true;
      var cards = Array.prototype.slice.call(document.querySelectorAll('[data-auto-payment]'));
      var sequence = Promise.resolve();
      cards.forEach(function (card) {
        sequence = sequence.then(function () {
          if (paymentCompleted) return;
          var body = new URLSearchParams({
            order_id: card.getAttribute('data-auto-payment') || '',
            csrf_token: card.getAttribute('data-poll-csrf') || ''
          });
          return fetch('auto-refresh-payment.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: body.toString()
          }).then(function (response) {
            if (!response.ok) return null;
            return response.json();
          }).then(function (result) {
            if (result && result.ok && result.terminal) {
              paymentCompleted = true;
              window.location.reload();
            }
          }).catch(function () {});
        });
      });
      sequence.finally(function () { paymentPollInProgress = false; });
    };
    pollPendingPayments();
    window.setInterval(pollPendingPayments, 15000);
  }
})();
