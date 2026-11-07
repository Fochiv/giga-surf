(function () {
  'use strict';
  var lang = localStorage.getItem('giga-language') || 'fr';
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
  }
  document.querySelectorAll('[data-lang]').forEach(function (button) {
    button.addEventListener('click', function () { setLanguage(button.getAttribute('data-lang')); });
  });
  setLanguage(lang);

  var filterButtons = document.querySelectorAll('[data-filter]');
  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var filter = button.getAttribute('data-filter');
      filterButtons.forEach(function (item) {
        var active = item === button;
        item.classList.toggle('is-active', active);
        item.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      document.querySelectorAll('[data-category]').forEach(function (card) {
        card.hidden = filter !== 'all' && card.getAttribute('data-category') !== filter;
      });
    });
  });

  var stepThree = document.querySelector('[data-admin-reveal]');
  var adminReveal = document.querySelector('[data-admin-link]');
  if (stepThree && adminReveal) {
    var clicks = 0;
    var lastClickAt = 0;
    stepThree.addEventListener('click', function () {
      var now = Date.now();
      if (now - lastClickAt > 1800) clicks = 0;
      lastClickAt = now;
      clicks += 1;
      if (clicks >= 5) adminReveal.hidden = false;
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
})();
