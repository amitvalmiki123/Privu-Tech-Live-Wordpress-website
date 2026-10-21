/**
 * Privu – Product Login Lead Form modal controller.
 *
 * Intercepts Header & Global Footer "Login" CTAs, enforces the exact same
 * Name + Work Email + [6-9] 10-digit Indian mobile checks as Template Downloads,
 * submits via Contact Form 7 (with native AJAX fallback), and redirects to
 * https://cms.privu.tech/.
 */

(function () {
    'use strict';

    var cfg = window.PrivuProductLogin;

    if (!cfg) {
        return;
    }

    var i18n = cfg.i18n || {};
    var buttonText = String(cfg.buttonText || 'Login');
    var redirectUrl = String(cfg.redirectUrl || 'https://cms.privu.tech/');
    var loginHref = String(cfg.loginUrl || '/product-login/');
    var gateEnabled = cfg.gateEnabled !== false;

    function ensureMenuLoginLinks() {
        var footerWidgets = document.querySelector('.site-footer .widgets');

        if (footerWidgets && !footerWidgets.querySelector('.privu-footer-login-item, [data-privu-login-trigger]')) {
            var firstList = footerWidgets.querySelector('ul');

            if (firstList) {
                var li = document.createElement('li');
                li.className = 'menu-item privu-footer-login-item';
                var a = document.createElement('a');
                a.href = gateEnabled ? loginHref : redirectUrl;

                if (gateEnabled) {
                    a.setAttribute('data-privu-login-trigger', 'Footer Quick Links');
                }

                a.textContent = buttonText;
                li.appendChild(a);
                firstList.appendChild(li);
            }
        }

        var mobileNav = document.querySelector('#mobile-navigation ul');

        if (mobileNav && !mobileNav.querySelector('.privu-mobile-login-item, [data-privu-login-trigger]')) {
            var mLi = document.createElement('li');
            mLi.className = 'menu-item privu-mobile-login-item';
            var mA = document.createElement('a');
            mA.href = gateEnabled ? loginHref : redirectUrl;

            if (gateEnabled) {
                mA.setAttribute('data-privu-login-trigger', 'Mobile Menu');
            }

            mA.textContent = buttonText;
            mLi.appendChild(mA);
            mobileNav.appendChild(mLi);
        }
    }

    ensureMenuLoginLinks();

    if (!gateEnabled) {
        return;
    }

    var modal = document.getElementById('privu-login-modal');

    if (!modal) {
        return;
    }

    var isCf7 = cfg.mode === 'cf7' && modal.getAttribute('data-privu-login-mode') !== 'native';
    var blockedDomains = (Array.isArray(cfg.blocked) ? cfg.blocked : []).map(function (domain) {
        return String(domain).toLowerCase();
    });

    var els = {
        dialog: modal.querySelector('.ptdl-modal'),
        close: modal.querySelector('[data-plogin-close]'),
        cf7Wrap: modal.querySelector('[data-plogin-form-wrap]'),
        cf7Form: modal.querySelector('.wpcf7-form'),
        nativeForm: modal.querySelector('[data-plogin-native-form]'),
        name: modal.querySelector('#plogin-name'),
        email: modal.querySelector('#plogin-email'),
        phone: modal.querySelector('#plogin-phone'),
        honeypot: modal.querySelector('#plogin-website'),
        submit: modal.querySelector('[data-plogin-submit]'),
        submitLabel: modal.querySelector('.ptdl-submit__label'),
        formError: modal.querySelector('[data-plogin-form-error]'),
        privacy: modal.querySelector('[data-plogin-privacy]'),
        redirectBox: modal.querySelector('[data-plogin-redirect]'),
        continueLink: modal.querySelector('[data-plogin-continue]')
    };

    var activeForm = isCf7 ? els.cf7Form : els.nativeForm;

    if (!activeForm) {
        return;
    }

    var state = {
        source: 'Header',
        nonce: '',
        nonceRequest: null,
        lastFocus: null,
        submitting: false,
        redirecting: false,
        retried: false
    };

    /* ------------------------------------------------------------------
     * Shared validation + phone input restriction helpers
     * ---------------------------------------------------------------- */

    function isValidName(name) {
        return name.length >= 2 && name.length <= 80 && /[a-z\u00C0-\u024F\u0900-\u097F]/i.test(name) && !/[<>]|https?:/i.test(name);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(email);
    }

    function isBlockedEmail(email) {
        var at = email.lastIndexOf('@');

        if (at === -1) {
            return false;
        }

        var domain = email.slice(at + 1).toLowerCase();

        for (var i = 0; i < blockedDomains.length; i++) {
            var blocked = blockedDomains[i];

            if (domain === blocked || domain.slice(-(blocked.length + 1)) === '.' + blocked) {
                return true;
            }
        }

        return false;
    }

    function normalizePhone(value) {
        var digits = String(value || '').trim();

        return /^[6-9][0-9]{9}$/.test(digits) ? digits : '';
    }

    function cleanPhoneDigits(raw, allowCountryPrefix) {
        var digits = String(raw || '').replace(/\D+/g, '');

        if (allowCountryPrefix) {
            if (digits.length === 14 && digits.slice(0, 4) === '0091' && /^[6-9]/.test(digits.slice(4))) {
                digits = digits.slice(4);
            } else if (digits.length === 12 && digits.slice(0, 2) === '91' && /^[6-9]/.test(digits.slice(2))) {
                digits = digits.slice(2);
            } else if (digits.length === 11 && digits.charAt(0) === '0' && /^[6-9]/.test(digits.slice(1))) {
                digits = digits.slice(1);
            }
        }

        if (digits && !/^[6-9]/.test(digits)) {
            return '';
        }

        return digits.slice(0, 10);
    }

    function bindPhoneRestriction(phoneInput) {
        if (!phoneInput) {
            return;
        }

        var lastValue = '';

        phoneInput.setAttribute('maxlength', '10');
        phoneInput.setAttribute('minlength', '10');
        phoneInput.setAttribute('inputmode', 'numeric');
        phoneInput.setAttribute('pattern', '[6-9][0-9]{9}');
        phoneInput.setAttribute('placeholder', '9876543210');

        if (phoneInput.form) {
            phoneInput.form.addEventListener('reset', function () {
                lastValue = '';
            });
        }

        phoneInput.addEventListener('keydown', function (event) {
            if (event.ctrlKey || event.metaKey || event.altKey) {
                return;
            }

            var key = event.key;

            if (!key || key.length > 1) {
                return;
            }

            if (!/^[0-9]$/.test(key)) {
                event.preventDefault();
                return;
            }

            var start = typeof phoneInput.selectionStart === 'number' ? phoneInput.selectionStart : phoneInput.value.length;
            var end = typeof phoneInput.selectionEnd === 'number' ? phoneInput.selectionEnd : start;

            if (start === 0 && !/^[6-9]$/.test(key)) {
                event.preventDefault();
                return;
            }

            if (phoneInput.value.length >= 10 && start === end) {
                event.preventDefault();
            }
        });

        phoneInput.addEventListener('paste', function (event) {
            var clipboard = event.clipboardData || window.clipboardData;

            if (!clipboard || typeof clipboard.getData !== 'function') {
                return;
            }

            event.preventDefault();

            var pasted = clipboard.getData('text') || '';
            var cleaned = cleanPhoneDigits(pasted, true);
            lastValue = cleaned;

            if (phoneInput.value !== cleaned) {
                phoneInput.value = cleaned;
                phoneInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });

        phoneInput.addEventListener('input', function () {
            var raw = String(phoneInput.value || '');
            var digits = raw.replace(/\D+/g, '');

            if (digits === '') {
                lastValue = '';

                if (phoneInput.value !== '') {
                    phoneInput.value = '';
                }

                return;
            }

            if (!/^[6-9]/.test(digits)) {
                if (digits.length > lastValue.length && lastValue) {
                    phoneInput.value = lastValue;
                    return;
                }

                digits = digits.replace(/^[0-5]+/, '');
            }

            digits = digits.slice(0, 10);
            lastValue = digits;

            if (phoneInput.value !== digits) {
                phoneInput.value = digits;
            }
        });
    }

    bindPhoneRestriction(els.cf7Form ? els.cf7Form.querySelector('[name="your-phone"]') : null);
    bindPhoneRestriction(els.phone);

    function isLoginTrigger(node) {
        if (!node || node.nodeType !== 1) {
            return false;
        }

        if (node.closest('#privu-login-modal')) {
            return false;
        }

        if (node.hasAttribute('data-privu-login-trigger')) {
            return true;
        }

        var href = String(node.getAttribute('href') || '').trim();

        if (!href) {
            return false;
        }

        if (/^(#login|#privu-login)$/i.test(href)) {
            return true;
        }

        if (/\/product-login\/?(\?|#|$)/i.test(href)) {
            return true;
        }

        if (/^https?:\/\/(www\.)?cms\.privu\.tech(\/|$)/i.test(href)) {
            return true;
        }

        return false;
    }

    function closeMobileDrawerIfOpen() {
        var drawer = document.getElementById('mobile-navigation');
        var btn = document.getElementById('mobile-menu-button');

        if (drawer && drawer.classList.contains('open')) {
            drawer.classList.remove('open');

            if (btn) {
                btn.classList.remove('remove');
            }

            document.body.classList.remove('remove');
        }
    }

    /* ------------------------------------------------------------------
     * Modal state & redirect flow
     * ---------------------------------------------------------------- */

    function setCf7HiddenFields() {
        if (!isCf7 || !els.cf7Form) {
            return;
        }

        var sourceField = els.cf7Form.querySelector('[name="privu-source"]');

        if (sourceField) {
            sourceField.value = state.source || 'Website';
        }

        var pageField = els.cf7Form.querySelector('[name="privu-page-url"]');

        if (pageField) {
            pageField.value = window.location.href;
        }
    }

    function resetCf7Form() {
        if (!els.cf7Form) {
            return;
        }

        els.cf7Form.reset();
        els.cf7Form.classList.remove('sent', 'invalid', 'failed', 'spam', 'submitting');

        els.cf7Form.querySelectorAll('.wpcf7-not-valid-tip').forEach(function (node) {
            if (node.parentNode) {
                node.parentNode.removeChild(node);
            }
        });

        els.cf7Form.querySelectorAll('.wpcf7-not-valid').forEach(function (node) {
            node.classList.remove('wpcf7-not-valid');
        });

        var output = els.cf7Form.querySelector('.wpcf7-response-output');

        if (output) {
            output.textContent = '';
            output.className = 'wpcf7-response-output';
            output.setAttribute('aria-hidden', 'true');
        }

        var submit = els.cf7Form.querySelector('.wpcf7-submit');

        if (submit) {
            submit.disabled = false;
            submit.removeAttribute('aria-disabled');
        }
    }

    function clearNativeErrors() {
        if (!els.nativeForm) {
            return;
        }

        els.nativeForm.querySelectorAll('[data-ptdl-error]').forEach(function (node) {
            node.textContent = '';
        });

        els.nativeForm.querySelectorAll('.ptdl-field.has-error').forEach(function (node) {
            node.classList.remove('has-error');
        });

        if (els.formError) {
            els.formError.textContent = '';
        }
    }

    function setNativeLoading(loading) {
        if (!els.submit) {
            return;
        }

        els.submit.disabled = !!loading;
        els.submit.classList.toggle('is-loading', !!loading);

        if (els.submitLabel) {
            els.submitLabel.textContent = loading
                ? (i18n.submitting || 'Verifying…')
                : (i18n.submit || 'Continue to Login');
        }
    }

    function resetModal() {
        state.submitting = false;
        state.redirecting = false;
        state.retried = false;

        if (isCf7) {
            resetCf7Form();

            if (els.cf7Wrap) {
                els.cf7Wrap.hidden = false;
            }
        } else if (els.nativeForm) {
            els.nativeForm.reset();
            clearNativeErrors();
            setNativeLoading(false);
            els.nativeForm.hidden = false;
        }

        if (els.privacy) {
            els.privacy.hidden = false;
        }

        if (els.redirectBox) {
            els.redirectBox.hidden = true;
        }
    }

    function openModal(source) {
        closeMobileDrawerIfOpen();
        resetModal();

        state.source = source || 'Header';
        state.lastFocus = document.activeElement;
        setCf7HiddenFields();

        modal.hidden = false;
        document.body.classList.add('ptdl-lock');

        void modal.offsetWidth;
        modal.classList.add('is-open');
        els.dialog.scrollTop = 0;

        window.setTimeout(function () {
            var firstInput = isCf7 && els.cf7Form
                ? els.cf7Form.querySelector('[name="your-name"]')
                : els.name;

            if (firstInput) {
                firstInput.focus();
            }
        }, 120);

        if (!isCf7) {
            ensureNonce();
        }
    }

    function closeModal() {
        if (modal.hidden) {
            return;
        }

        modal.classList.remove('is-open');
        document.body.classList.remove('ptdl-lock');

        window.setTimeout(function () {
            if (!modal.classList.contains('is-open')) {
                modal.hidden = true;
            }
        }, 220);

        if (state.lastFocus && typeof state.lastFocus.focus === 'function') {
            state.lastFocus.focus();
        }
    }

    function completeAndRedirect(targetUrl) {
        if (state.redirecting) {
            return;
        }

        state.redirecting = true;
        state.submitting = false;
        setNativeLoading(false);

        var finalUrl = String(targetUrl || redirectUrl || 'https://cms.privu.tech/');

        if (els.continueLink) {
            els.continueLink.setAttribute('href', finalUrl);
        }

        if (els.cf7Wrap) {
            els.cf7Wrap.hidden = true;
        }

        if (els.nativeForm) {
            els.nativeForm.hidden = true;
        }

        if (els.privacy) {
            els.privacy.hidden = true;
        }

        if (els.redirectBox) {
            els.redirectBox.hidden = false;
        }

        if (cfg.previewOnly && window.self !== window.top) {
            return;
        }

        window.setTimeout(function () {
            window.location.assign(finalUrl);
        }, 350);
    }

    /* ------------------------------------------------------------------
     * Native AJAX fallback (when CF7 is inactive)
     * ---------------------------------------------------------------- */

    function ajax(action, payload) {
        var body = new URLSearchParams();
        body.append('action', action);

        Object.keys(payload || {}).forEach(function (key) {
            body.append(key, payload[key] == null ? '' : payload[key]);
        });

        return fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        }).then(function (response) {
            return response.json();
        });
    }

    function ensureNonce(force) {
        if (state.nonce && !force) {
            return Promise.resolve(state.nonce);
        }

        if (state.nonceRequest && !force) {
            return state.nonceRequest;
        }

        state.nonceRequest = ajax('privu_login_nonce', {})
            .then(function (response) {
                if (response && response.success && response.data && response.data.nonce) {
                    state.nonce = response.data.nonce;
                }

                state.nonceRequest = null;

                return state.nonce;
            })
            .catch(function () {
                state.nonceRequest = null;

                return state.nonce;
            });

        return state.nonceRequest;
    }

    function setNativeFieldError(field, message) {
        if (!els.nativeForm) {
            return;
        }

        var node = els.nativeForm.querySelector('[data-ptdl-error="' + field + '"]');

        if (node) {
            node.textContent = message || '';
            var wrap = node.closest('.ptdl-field');

            if (wrap) {
                wrap.classList.add('has-error');
            }
        }
    }

    function handleNativeSubmit(event) {
        if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
        }

        if (state.submitting || !els.nativeForm) {
            return;
        }

        clearNativeErrors();

        var name = String(els.name.value || '').trim().replace(/\s+/g, ' ');
        var email = String(els.email.value || '').trim().toLowerCase();
        var phone = normalizePhone(els.phone.value);
        var fields = {};

        if (!isValidName(name)) {
            fields.name = i18n.nameError || 'Please enter your full name.';
        }

        if (!isValidEmail(email)) {
            fields.email = i18n.emailError || 'Please enter a valid work email address.';
        } else if (isBlockedEmail(email)) {
            fields.email = i18n.personal || 'Please use your work email. Gmail, Yahoo, Outlook and other personal addresses are not accepted.';
        }

        if (!phone) {
            fields.phone = i18n.phoneError || 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.';
        }

        var errorKeys = Object.keys(fields);

        if (errorKeys.length) {
            errorKeys.forEach(function (key) {
                setNativeFieldError(key, fields[key]);
            });

            var order = ['name', 'email', 'phone'];

            for (var i = 0; i < order.length; i++) {
                if (fields[order[i]] && els[order[i]]) {
                    els[order[i]].focus();
                    break;
                }
            }

            return;
        }

        state.submitting = true;
        setNativeLoading(true);

        ensureNonce().then(function (nonce) {
            return ajax('privu_login_submit', {
                nonce: nonce,
                name: name,
                email: email,
                phone: phone,
                source: state.source,
                page_url: window.location.href,
                website: els.honeypot ? els.honeypot.value : ''
            });
        }).then(function (response) {
            if (response && response.success) {
                var target = (response.data && response.data.redirect_url) || redirectUrl;
                completeAndRedirect(target);

                return;
            }

            var data = (response && response.data) || {};

            if (data.code === 'bad_nonce' && !state.retried) {
                state.retried = true;
                state.submitting = false;
                setNativeLoading(false);

                ensureNonce(true).then(function () {
                    handleNativeSubmit();
                });

                return;
            }

            state.submitting = false;
            setNativeLoading(false);

            if (data.fields) {
                Object.keys(data.fields).forEach(function (key) {
                    setNativeFieldError(key, data.fields[key]);
                });
            }

            if (els.formError) {
                els.formError.textContent = data.message || i18n.genericError || 'Something went wrong. Please try again.';
            }
        }).catch(function () {
            state.submitting = false;
            setNativeLoading(false);

            if (els.formError) {
                els.formError.textContent = i18n.networkError || 'Network error. Please check your connection and try again.';
            }
        });
    }

    /* ------------------------------------------------------------------
     * Event bindings
     * ---------------------------------------------------------------- */

    if (!isCf7 && els.nativeForm) {
        els.nativeForm.addEventListener('submit', handleNativeSubmit);

        els.nativeForm.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', function () {
                var wrap = input.closest('.ptdl-field');

                if (wrap) {
                    wrap.classList.remove('has-error');
                    var err = wrap.querySelector('[data-ptdl-error]');

                    if (err) {
                        err.textContent = '';
                    }
                }

                if (els.formError) {
                    els.formError.textContent = '';
                }
            });
        });
    }

    // Contact Form 7 completion hook: redirect only when server validation succeeded.
    document.addEventListener('wpcf7submit', function (event) {
        if (!isCf7 || !els.cf7Form || event.target !== els.cf7Form) {
            return;
        }

        var detail = event.detail || {};
        var status = detail.status || (detail.apiResponse && detail.apiResponse.status) || '';

        if (status !== 'mail_sent' && status !== 'mail_failed') {
            return;
        }

        completeAndRedirect(redirectUrl);
    }, false);

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a, button') : null;

        if (!link || !isLoginTrigger(link)) {
            return;
        }

        event.preventDefault();
        openModal(link.getAttribute('data-privu-login-trigger') || 'Website CTA');
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    if (els.close) {
        els.close.addEventListener('click', closeModal);
    }

    document.addEventListener('keydown', function (event) {
        if (modal.hidden) {
            return;
        }

        if (event.key === 'Escape' || event.keyCode === 27) {
            closeModal();

            return;
        }

        if (event.key !== 'Tab' && event.keyCode !== 9) {
            return;
        }

        var focusables = modal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([type="hidden"]):not([tabindex="-1"]), [tabindex]:not([tabindex="-1"])'
        );

        if (!focusables.length) {
            return;
        }

        var first = focusables[0];
        var last = focusables[focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    ensureMenuLoginLinks();

    // Auto-open when arriving from /product-login/ -> /?privu_login=1 or #login.
    (function maybeOpenFromUrl() {
        var shouldOpen = false;

        try {
            var params = new URLSearchParams(window.location.search || '');

            if (params.get('privu_login') === '1') {
                shouldOpen = true;

                if (window.history && typeof window.history.replaceState === 'function') {
                    params.delete('privu_login');
                    var clean = params.toString();
                    window.history.replaceState(
                        null,
                        '',
                        window.location.pathname + (clean ? '?' + clean : '') + (window.location.hash || '')
                    );
                }
            }
        } catch (e) {
            shouldOpen = false;
        }

        if (!shouldOpen && /^#(login|privu-login)$/i.test(window.location.hash || '')) {
            shouldOpen = true;
        }

        if (shouldOpen) {
            openModal('Direct URL');
        }
    })();

    cfg.open = function (source) {
        openModal(source || 'Programmatic');
    };
})();
