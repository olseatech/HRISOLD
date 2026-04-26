(function () {
    var modal = document.getElementById('featureLockModal');

    if (!modal) {
        return;
    }

    var dialog = modal.querySelector('[data-lock-dialog]');
    var featureLabel = modal.querySelector('[data-lock-feature]');
    var messageLabel = modal.querySelector('[data-lock-message]');
    var planLabel = modal.querySelector('[data-lock-plan]');
    var closeButtons = modal.querySelectorAll('[data-lock-close]');
    var activeTrigger = null;

    var focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'textarea:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ].join(',');

    var getFocusable = function () {
        if (!dialog) {
            return [];
        }

        return Array.prototype.slice.call(dialog.querySelectorAll(focusableSelector)).filter(function (el) {
            return !el.hasAttribute('disabled') && el.getAttribute('aria-hidden') !== 'true';
        });
    };

    var fillContent = function (trigger) {
        if (!trigger) {
            return;
        }

        var feature = trigger.getAttribute('data-lock-feature-label') || 'This module';
        var message = trigger.getAttribute('data-lock-message') || 'Upgrade your plan in Billing to unlock this module.';
        var plan = trigger.getAttribute('data-lock-plan-name') || '';

        if (featureLabel) {
            featureLabel.textContent = feature + ' is currently locked.';
        }

        if (messageLabel) {
            messageLabel.textContent = message;
        }

        if (planLabel) {
            planLabel.textContent = plan !== '' ? plan : 'No active plan selected';
        }
    };

    var openModal = function (trigger) {
        activeTrigger = trigger;
        fillContent(trigger);

        modal.hidden = false;
        document.body.classList.add('lock-modal-open');

        var focusable = getFocusable();
        if (focusable.length > 0) {
            focusable[0].focus();
        }
    };

    var closeModal = function () {
        modal.hidden = true;
        document.body.classList.remove('lock-modal-open');

        if (activeTrigger) {
            activeTrigger.focus();
        }

        activeTrigger = null;
    };

    closeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal();
        });
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-lock-trigger]');

        if (!trigger) {
            return;
        }

        event.preventDefault();
        openModal(trigger);
    });

    document.addEventListener('keydown', function (event) {
        if (modal.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        var focusable = getFocusable();
        if (focusable.length === 0) {
            return;
        }

        var first = focusable[0];
        var last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
            return;
        }

        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
})();
