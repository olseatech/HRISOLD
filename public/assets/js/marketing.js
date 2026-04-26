(function () {
    /* ── Reveal on load ────────────────────────────────────── */
    if (document.body && window.requestAnimationFrame) {
        window.requestAnimationFrame(function () {
            document.body.classList.add('mk-ready');
        });
    } else if (document.body) {
        document.body.classList.add('mk-ready');
    }

    /* ══════════════════════════════════════════════════════════
       Intersection Observer — scroll reveal + counter animation
       ══════════════════════════════════════════════════════════ */
    var observerOptions = { threshold: 0.15, rootMargin: '0px 0px -40px 0px' };

    if ('IntersectionObserver' in window) {
        var scrollRevealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    scrollRevealObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('[data-scroll-reveal]').forEach(function (el) {
            scrollRevealObserver.observe(el);
        });

        /* Counter animation */
        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('[data-count-to]').forEach(function (el) {
            counterObserver.observe(el);
        });

        /* Confidence meter animation */
        var meterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-animated');
                    meterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('.mk-plan-meter').forEach(function (el) {
            meterObserver.observe(el);
        });
    } else {
        /* Fallback: show everything immediately */
        document.querySelectorAll('[data-scroll-reveal]').forEach(function (el) {
            el.classList.add('is-visible');
        });
        document.querySelectorAll('.mk-plan-meter').forEach(function (el) {
            el.classList.add('is-animated');
        });
        document.querySelectorAll('[data-count-to]').forEach(function (el) {
            el.textContent = el.getAttribute('data-count-to');
        });
    }

    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-count-to'), 10);
        if (isNaN(target)) return;

        var duration = 1200;
        var start = 0;
        var startTime = null;

        function easeOutQuart(t) {
            return 1 - Math.pow(1 - t, 4);
        }

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var easedProgress = easeOutQuart(progress);
            var current = Math.round(start + (target - start) * easedProgress);
            el.textContent = current.toString();

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);
    }

    /* ══════════════════════════════════════════════════════════
       FAQ Accordion
       ══════════════════════════════════════════════════════════ */
    document.querySelectorAll('.mk-faq-question').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.mk-faq-item');
            if (!item) return;

            var isOpen = item.classList.contains('is-open');

            /* Close all siblings first */
            var parent = item.parentElement;
            if (parent) {
                parent.querySelectorAll('.mk-faq-item.is-open').forEach(function (openItem) {
                    openItem.classList.remove('is-open');
                    var openBtn = openItem.querySelector('.mk-faq-question');
                    if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
                });
            }

            /* Toggle current */
            if (!isOpen) {
                item.classList.add('is-open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    /* ══════════════════════════════════════════════════════════
       Plan modal
       ══════════════════════════════════════════════════════════ */
    var modal = document.getElementById('planDecisionModal');
    var loadingOverlay = document.getElementById('planLoadingOverlay');
    var modalName = modal ? modal.querySelector('[data-plan-modal-name]') : null;
    var modalPrice = modal ? modal.querySelector('[data-plan-modal-price]') : null;
    var modalHook = modal ? modal.querySelector('[data-plan-modal-hook]') : null;
    var modalConfirmButton = modal ? modal.querySelector('[data-plan-modal-confirm]') : null;
    var modalCloseButtons = modal ? modal.querySelectorAll('[data-plan-modal-close]') : [];
    var pendingConfirmAction = null;
    var modalFocusOrigin = null;

    var toText = function (value, fallback) {
        if (typeof value !== 'string') return fallback;
        var trimmed = value.trim();
        return trimmed !== '' ? trimmed : fallback;
    };

    var showLoadingOverlay = function () {
        if (!loadingOverlay) return;
        loadingOverlay.hidden = false;
    };

    var hideLoadingOverlay = function () {
        if (!loadingOverlay) return;
        loadingOverlay.hidden = true;
    };

    var setButtonLoading = function (button, loadingLabel) {
        if (!button) return;
        button.disabled = true;
        button.classList.add('is-loading');
        button.textContent = loadingLabel || 'Processing...';
    };

    var resetButtonLoading = function (button, originalLabel) {
        if (!button) return;
        button.disabled = false;
        button.classList.remove('is-loading');
        if (originalLabel) button.textContent = originalLabel;
    };

    var closePlanModal = function (restoreFocus) {
        if (!modal) return;
        modal.hidden = true;
        document.body.classList.remove('mk-modal-open');
        pendingConfirmAction = null;

        if (restoreFocus && modalFocusOrigin && typeof modalFocusOrigin.focus === 'function') {
            modalFocusOrigin.focus();
        }
        modalFocusOrigin = null;
    };

    var openPlanModal = function (payload, onConfirm, focusOrigin) {
        if (!modal || !modalConfirmButton) {
            onConfirm();
            return;
        }

        pendingConfirmAction = onConfirm;
        modalFocusOrigin = focusOrigin || null;

        if (modalName) modalName.textContent = toText(payload.name, 'Selected plan');
        if (modalPrice) modalPrice.textContent = toText(payload.price, 'Pricing details available in billing');
        if (modalHook) modalHook.textContent = toText(payload.hook, 'This plan can be adjusted anytime from billing settings.');

        modal.hidden = false;
        document.body.classList.add('mk-modal-open');
        modalConfirmButton.focus();
    };

    if (modal && modalConfirmButton) {
        modalConfirmButton.addEventListener('click', function () {
            if (typeof pendingConfirmAction === 'function') {
                var action = pendingConfirmAction;
                closePlanModal(false);
                action();
            }
        });

        modalCloseButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                closePlanModal(true);
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closePlanModal(true);
        });

        document.addEventListener('keydown', function (event) {
            if (modal.hidden) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                closePlanModal(true);
                return;
            }

            if (event.key !== 'Tab') return;

            var focusable = modal.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
            if (!focusable || focusable.length === 0) return;

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
    }

    /* ══════════════════════════════════════════════════════════
       Plan picker — card selection & form handling
       ══════════════════════════════════════════════════════════ */
    var planPickers = document.querySelectorAll('[data-plan-picker]');
    if (planPickers && planPickers.length > 0) {
        planPickers.forEach(function (picker) {
            var cards = Array.prototype.slice.call(picker.querySelectorAll('[data-plan-card]'));
            if (!cards || cards.length === 0) return;

            var liveRegion = picker.querySelector('.mk-live-region, .bill-live-region');
            var summaryPlan = picker.querySelector('[data-selection-plan]');
            var submitButton = picker.querySelector('button[type="submit"]');
            var isSubmitting = false;

            var cardRadio = function (card) {
                return card.querySelector('input[type="radio"][name="plan_id"]');
            };

            var buildPayloadFromCard = function (card) {
                return {
                    name: card ? toText(card.getAttribute('data-plan-name'), 'Selected plan') : 'Selected plan',
                    price: card ? toText(card.getAttribute('data-plan-price'), 'Pricing details are available in billing') : 'Pricing details are available in billing',
                    hook: card ? toText(card.getAttribute('data-plan-hook'), 'You can change your plan later from billing.') : 'You can change your plan later from billing.'
                };
            };

            var selectCardByIndex = function (index, moveFocus) {
                if (!cards[index]) return;
                var radio = cardRadio(cards[index]);
                if (!radio) return;
                radio.checked = true;
                setSelectedState(true);
                if (moveFocus) cards[index].focus();
            };

            var setSelectedState = function (announceSelection) {
                var selectedPlanName = '';

                cards.forEach(function (card) {
                    var radio = cardRadio(card);
                    var selected = !!radio && radio.checked;
                    card.classList.toggle('is-selected', selected);
                    card.setAttribute('data-selected', selected ? 'true' : 'false');
                    card.setAttribute('aria-checked', selected ? 'true' : 'false');
                    if (selected) selectedPlanName = card.getAttribute('data-plan-name') || '';
                });

                if (summaryPlan) {
                    summaryPlan.textContent = selectedPlanName || 'No plan selected';
                }

                if (announceSelection && liveRegion) {
                    liveRegion.textContent = selectedPlanName
                        ? selectedPlanName + ' selected.'
                        : 'No plan selected.';
                }

                /* Update progress stepper */
                updateStepper(!!selectedPlanName);
            };

            cards.forEach(function (card, index) {
                var radio = cardRadio(card);
                if (!radio) return;

                card.setAttribute('tabindex', '0');
                card.setAttribute('role', 'radio');

                card.addEventListener('click', function () {
                    if (!radio.checked) radio.checked = true;
                    setSelectedState(true);
                });

                card.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        radio.checked = true;
                        setSelectedState(true);
                        return;
                    }

                    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                        event.preventDefault();
                        selectCardByIndex((index + 1) % cards.length, true);
                        return;
                    }

                    if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        selectCardByIndex((index - 1 + cards.length) % cards.length, true);
                    }
                });

                radio.addEventListener('change', function () {
                    setSelectedState(true);
                });
            });

            picker.addEventListener('submit', function (event) {
                if (isSubmitting) {
                    event.preventDefault();
                    return;
                }

                var selected = picker.querySelector('input[type="radio"][name="plan_id"]:checked');
                if (!selected) {
                    var firstRadio = picker.querySelector('input[type="radio"][name="plan_id"]');
                    if (firstRadio) {
                        firstRadio.checked = true;
                        setSelectedState(true);
                        selected = firstRadio;
                    }
                }

                if (!selected) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();

                var selectedCard = selected.closest('[data-plan-card]');
                var submitNow = function () {
                    isSubmitting = true;
                    var originalLabel = submitButton ? submitButton.getAttribute('data-submit-label') : '';
                    setButtonLoading(submitButton, submitButton ? submitButton.getAttribute('data-loading-label') : 'Processing...');
                    showLoadingOverlay();

                    /* Safety net: auto-hide if navigation stalls */
                    var fallbackTimer = window.setTimeout(function () {
                        hideLoadingOverlay();
                        resetButtonLoading(submitButton, originalLabel);
                        isSubmitting = false;
                        if (window.console && console.warn) console.warn('Plan submission timed out; you can retry.');
                    }, 8000);

                    try {
                        HTMLFormElement.prototype.submit.call(picker);
                    } catch (e) {
                        window.clearTimeout(fallbackTimer);
                        hideLoadingOverlay();
                        resetButtonLoading(submitButton, originalLabel);
                        isSubmitting = false;
                        if (window.console && console.error) console.error('Plan submission failed:', e);
                    }
                };

                if (picker.hasAttribute('data-plan-modal-form')) {
                    openPlanModal(buildPayloadFromCard(selectedCard), submitNow, selectedCard || submitButton);
                    return;
                }

                submitNow();
            });

            if (submitButton && submitButton.getAttribute('data-submit-label')) {
                submitButton.textContent = submitButton.getAttribute('data-submit-label');
            }

            setSelectedState(false);
        });
    }

    /* ── Progress stepper update ────────────────────────────── */
    function updateStepper(hasPlan) {
        var steps = document.querySelectorAll('.mk-step');
        if (!steps || steps.length === 0) return;

        steps.forEach(function (step, idx) {
            step.classList.remove('is-active', 'is-done');
            if (hasPlan) {
                if (idx === 0) step.classList.add('is-done');
                if (idx === 1) step.classList.add('is-active');
            } else {
                if (idx === 0) step.classList.add('is-active');
            }
        });
    }

    /* ── Simple forms with modal ───────────────────────────── */
    var simpleForms = document.querySelectorAll('[data-plan-modal-form]:not([data-plan-picker])');

    simpleForms.forEach(function (form) {
        var isSubmitting = false;
        var submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (event) {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }

            event.preventDefault();

            var payload = {
                name: toText(form.getAttribute('data-plan-name'), 'Selected plan'),
                price: toText(form.getAttribute('data-plan-price'), 'Pricing details are available in billing'),
                hook: toText(form.getAttribute('data-plan-hook'), 'You can change your plan later from billing.')
            };

            var submitNow = function () {
                isSubmitting = true;
                var originalLabel = submitButton ? submitButton.textContent : '';
                setButtonLoading(submitButton, 'Processing selection...');
                showLoadingOverlay();

                /* Safety net: auto-hide if navigation stalls */
                var fallbackTimer = window.setTimeout(function () {
                    hideLoadingOverlay();
                    resetButtonLoading(submitButton, originalLabel);
                    isSubmitting = false;
                    if (window.console && console.warn) console.warn('Plan submission timed out; you can retry.');
                }, 8000);

                try {
                    HTMLFormElement.prototype.submit.call(form);
                } catch (e) {
                    window.clearTimeout(fallbackTimer);
                    hideLoadingOverlay();
                    resetButtonLoading(submitButton, originalLabel);
                    isSubmitting = false;
                    if (window.console && console.error) console.error('Plan submission failed:', e);
                }
            };

            openPlanModal(payload, submitNow, submitButton || form);
        });
    });
})();
