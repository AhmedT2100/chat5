// public/js/reclamation_validation.js
(function () {
  'use strict';

  // run after DOM is ready (deferred script or DOMContentLoaded)
  function init() {
    // helper: find form by id or by action attribute containing the route
    var form = document.getElementById('reclamationForm') ||
               document.querySelector('form[action*="route=reclamation/store"], form[action*="route=reclamation/update"]');

    if (!form) return; // nothing to validate on this page

    // helper: find an input by id or name
    function findField(key) {
      var el = document.getElementById(key);
      if (el) return el;
      return form.querySelector('[name="' + key + '"]') || null;
    }

    function createErrorsBox() {
      var box = form.querySelector('#js-errors');
      if (!box) {
        box = document.createElement('div');
        box.id = 'js-errors';
        box.setAttribute('role', 'alert');
        box.style.color = '#c62828';
        box.style.marginBottom = '12px';
        box.style.background = '#fff6f6';
        box.style.border = '1px solid #f2c6c6';
        box.style.padding = '10px';
        box.style.borderRadius = '6px';
        box.style.fontSize = '0.95rem';
        form.insertBefore(box, form.firstChild);
      }
      return box;
    }

    function clearFieldErrors() {
      var fields = form.querySelectorAll('[aria-invalid="true"], .error');
      fields.forEach(function (f) {
        f.removeAttribute('aria-invalid');
        f.classList.remove('error');
      });
      var box = form.querySelector('#js-errors');
      if (box) box.innerHTML = '';
    }

    form.addEventListener('submit', function (e) {
      clearFieldErrors();
      var errors = [];

      // get values (trim)
      var sujet = (findField('sujet') && (findField('sujet').value || '').trim()) || '';
      var description = (findField('description') && (findField('description').value || '').trim()) || '';
      var fullName = (findField('full_name') && (findField('full_name').value || '').trim()) ||
                     (findField('fullname') && (findField('fullname').value || '').trim()) ||
                     (findField('full-name') && (findField('full-name').value || '').trim()) || '';
      var mobile = (findField('mobile_phone') && (findField('mobile_phone').value || '').trim()) ||
                   (findField('mobile') && (findField('mobile').value || '').trim()) ||
                   (findField('phone') && (findField('phone').value || '').trim()) || '';
      var priorityEl = findField('priority') || findField('priorite') || findField('prio');
      var priority = priorityEl ? (priorityEl.value || '').toString().toLowerCase() : '';

      // validation rules
      if (!sujet || sujet.length < 3) {
        errors.push({ field: findField('sujet'), message: 'Le sujet doit contenir au moins 3 caractères.' });
      }
      if (!description || description.length < 10) {
        errors.push({ field: findField('description'), message: 'La description doit contenir au moins 10 caractères.' });
      }
      if (!fullName || fullName.length < 3) {
        errors.push({ field: findField('full_name') || findField('fullname') || findField('full-name'), message: 'Le nom complet doit contenir au moins 3 caractères.' });
      }

      // phone validation: accept optional leading +, but count digits only between 8 and 15
      var digits = mobile.replace(/\D/g, '');
      if (!digits || digits.length < 8 || digits.length > 15) {
        errors.push({ field: findField('mobile_phone') || findField('mobile') || findField('phone'), message: 'Le numéro de téléphone est invalide (8-15 chiffres, + autorisé).' });
      }

      var allowed = ['low','normal','high','basse','moyenne','haute','faible'];
      if (!priority || allowed.indexOf(priority) === -1) {
        errors.push({ field: priorityEl, message: 'Priorité invalide.' });
      }

      if (errors.length > 0) {
        e.preventDefault();

        // display messages
        var box = createErrorsBox();
        box.innerHTML = errors.map(function (it) { return '<div>• ' + it.message + '</div>'; }).join('');

        // mark fields and focus first
        for (var i = 0; i < errors.length; i++) {
          var f = errors[i].field;
          if (f) {
            try {
              f.setAttribute('aria-invalid', 'true');
              f.classList.add('error');
            } catch (err) {}
          }
        }
        var firstField = errors[0] && errors[0].field;
        if (firstField && typeof firstField.focus === 'function') {
          firstField.focus();
        }

        return false;
      }

      // if no errors, allow submit
      return true;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
