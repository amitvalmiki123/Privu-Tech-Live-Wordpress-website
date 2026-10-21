/**
 * Privu – Assessment template download modal.
 *
 * Opens the lead form when a visitor clicks an existing "Download Template"
 * link on the Assessment Templates page, validates the fields, submits the
 * lead over AJAX and shows the download button on success.
 *
 * No page-builder markup is changed; the trigger is detected from the link
 * text / surrounding card heading.
 */

(function () {
    'use strict';

    var cfg = window.PrivuTemplateDownloads;

    if (!cfg) {
        return;
    }

    var modal = document.getElementById('privu-tdl-modal');

    if (!modal) {
        return;
    }

    var i18n = cfg.i18n || {};
    var isCf7 = cfg.mode === 'cf7';
    var templates = Array.isArray(cfg.templates) ? cfg.templates : [];
    var blockedDomains = (Array.isArray(cfg.blocked) ? cfg.blocked : []).map(function (domain) {
        return String(domain).toLowerCase();
    });

    var els = {
        dialog: modal.querySelector('.ptdl-modal'),
        close: modal.querySelector('[data-ptdl-close]'),
        form: modal.querySelector('.ptdl-form') || modal.querySelector('.wpcf7-form'),
        cf7Form: modal.querySelector('.wpcf7-form'),
        title: modal.querySelector('#ptdl-title'),
        subtitle: modal.querySelector('#ptdl-subtitle'),
        templateName: modal.querySelector('[data-ptdl-template-name]'),
        name: modal.querySelector('#ptdl-name'),
        email: modal.querySelector('#ptdl-email'),
        phone: modal.querySelector('#ptdl-phone'),
        honeypot: modal.querySelector('#ptdl-website'),
        submit: modal.querySelector('[data-ptdl-submit]'),
        submitLabel: modal.querySelector('.ptdl-submit__label'),
        formError: modal.querySelector('[data-ptdl-form-error]'),
        success: modal.querySelector('.ptdl-success'),
        successTemplate: modal.querySelector('[data-ptdl-success-template]'),
        successEmail: modal.querySelector('[data-ptdl-success-email]'),
        mailWarning: modal.querySelector('[data-ptdl-mail-warning]'),
        download: modal.querySelector('[data-ptdl-download]'),
        privacy: modal.querySelector('.ptdl-privacy'),
        again: modal.querySelector('[data-ptdl-again]')
    };

    if (!els.form) {
        return;
    }

    if (!isCf7 && !els.submit) {
        return;
    }

    var state = {
        slug: '',
        nonce: '',
        lastFocus: null,
        submitting: false,
        retried: false,
        nonceRequest: null
    };

    /* ------------------------------------------------------------------
     * Small helpers
     * ---------------------------------------------------------------- */

    function normalize(text) {
        return String(text || '')
            .toLowerCase()
            .replace(/&amp;/g, '&')
            .replace(/[^a-z0-9&]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function getTemplate(slug) {
        for (var i = 0; i < templates.length; i++) {
            if (templates[i].slug === slug) {
                return templates[i];
            }
        }
        return null;
    }

    function fallbackSlug() {
        if (cfg.defaultTpl && getTemplate(cfg.defaultTpl)) {
            return cfg.defaultTpl;
        }

        return templates.length ? templates[0].slug : '';
    }

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
        var digits = String(value || '').replace(/\D+/g, '');

        if (digits.length === 14 && digits.slice(0, 4) === '0091') {
            digits = digits.slice(4);
        }

        if (digits.length === 12 && digits.slice(0, 2) === '91') {
            digits = digits.slice(2);
        }

        if (digits.length === 11 && digits.charAt(0) === '0') {
            digits = digits.slice(1);
        }

        return /^[6-9][0-9]{9}$/.test(digits) ? digits : '';
    }

    function fieldWrap(name) {
        var error = els.form.querySelector('[data-ptdl-error="' + name + '"]');

        return error ? error.closest('.ptdl-field') : null;
    }

    function setFieldError(name, message) {
        var error = els.form.querySelector('[data-ptdl-error="' + name + '"]');
        var wrap = fieldWrap(name);

        if (error) {
            error.textContent = message || '';
        }

        if (wrap) {
            wrap.classList.add('has-error');
        }
    }

    function clearErrors() {
        els.form.querySelectorAll('[data-ptdl-error]').forEach(function (node) {
            node.textContent = '';
        });

        els.form.querySelectorAll('.ptdl-field.has-error').forEach(function (node) {
            node.classList.remove('has-error');
        });

        if (els.formError) {
            els.formError.textContent = '';
        }
    }

    function showErrors(fields) {
        Object.keys(fields).forEach(function (key) {
            setFieldError(key, fields[key]);
        });
    }

    function focusFirstError(fields) {
        var order = ['name', 'email', 'phone'];

        for (var i = 0; i < order.length; i++) {
            if (fields[order[i]] && els[order[i]]) {
                els[order[i]].focus();
                return;
            }
        }
    }

    function setFormError(message) {
        if (els.formError) {
            els.formError.textContent = message || '';
        }
    }

    function setLoading(isLoading) {
        if (!els.submit) {
            return;
        }

        els.submit.disabled = !!isLoading;
        els.submit.classList.toggle('is-loading', !!isLoading);

        if (els.submitLabel) {
            els.submitLabel.textContent = isLoading
                ? (i18n.sending || 'Sending…')
                : (i18n.send || 'Send me the template');
        }

        els.submit.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    }

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

    /* ------------------------------------------------------------------
     * Nonce (page may be served from cache, so fetch at runtime)
     * ---------------------------------------------------------------- */

    function ensureNonce(force) {
        if (state.nonce && !force) {
            return Promise.resolve(state.nonce);
        }

        if (state.nonceRequest && !force) {
            return state.nonceRequest;
        }

        state.nonceRequest = ajax('privu_tdl_nonce', {})
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

    /* ------------------------------------------------------------------
     * Template detection
     * ---------------------------------------------------------------- */

    function detectTemplate(link) {
        var explicit = link.getAttribute('data-template');
        var holder = link.closest('[data-template]');

        if (!explicit && holder) {
            explicit = holder.getAttribute('data-template');
        }

        if (explicit && getTemplate(explicit)) {
            return explicit;
        }

        var heading = '';
        var scope = link.closest('.wpb_column, .vc_column_container, .so-panel, .wpb_content_element, article, li, .card');

        if (scope) {
            var node = scope.querySelector('h1, h2, h3, h4, h5, h6');

            if (node) {
                heading = node.textContent;
            }
        }

        if (!heading) {
            var parent = link.parentElement;

            for (var i = 0; parent && i < 5; i++) {
                var found = parent.querySelector('h1, h2, h3, h4, h5, h6');

                if (found) {
                    heading = found.textContent;
                    break;
                }

                parent = parent.parentElement;
            }
        }

        if (!heading) {
            heading = link.textContent;
        }

        var haystack = normalize(heading);
        var best = null;
        var bestScore = 0;

        templates.forEach(function (template) {
            var score = templateScore(template, haystack);

            if (score > bestScore) {
                bestScore = score;
                best = template;
            }
        });

        if (best) {
            return best.slug;
        }

        return fallbackSlug();
    }

    var STOP_WORDS = ['template', 'checklist', 'assessment', 'the', 'and', 'for', 'with', 'your', 'our', 'from'];

    function templateScore(template, haystack) {
        var score = 0;

        (template.keywords || []).forEach(function (keyword) {
            var needle = normalize(keyword);

            if (!needle) {
                return;
            }

            if (haystack === needle) {
                score += 1000;
            } else if (haystack.indexOf(needle) !== -1) {
                score += needle.length + 10;
            }
        });

        var title = normalize(template.title);

        if (!title) {
            return score;
        }

        if (haystack === title) {
            score += 2000;

            return score;
        }

        if (haystack.indexOf(title) !== -1) {
            score += title.length + 50;

            return score;
        }

        var tokens = title.split(' ').filter(function (token) {
            return token.length > 3 && STOP_WORDS.indexOf(token) === -1;
        });

        if (!tokens.length) {
            return score;
        }

        var hits = 0;

        tokens.forEach(function (token) {
            if (haystack.indexOf(token) !== -1) {
                hits++;
            }
        });

        score += hits * 8;

        if (hits === tokens.length) {
            score += 30;
        }

        return score;
    }

    function isDownloadTrigger(node) {
        if (!node || node.nodeType !== 1) {
            return false;
        }

        if (node.closest('#masthead, .site-header, .main-navigation, #mobile-menu, .site-footer, footer, .ptdl-overlay')) {
            return false;
        }

        if (node.hasAttribute('data-ptdl-download') || node.hasAttribute('data-template')) {
            return true;
        }

        var text = normalize(node.textContent);
        var href = node.getAttribute('href') || '';

        if (href && /\.(xlsx|xls|pdf|docx?|zip)(\?|#|$)/i.test(href)) {
            return true;
        }

        return text.indexOf('download template') !== -1 || (text.indexOf('download') !== -1 && text.indexOf('template') !== -1);
    }

    /* ------------------------------------------------------------------
     * Open / close
     * ---------------------------------------------------------------- */

    function setCf7Fields() {
        if (!isCf7 || !els.cf7Form) {
            return;
        }

        var templateField = els.cf7Form.querySelector('[name="privu-template"]');

        if (templateField) {
            templateField.value = state.slug;
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

        var spinner = els.cf7Form.querySelector('.wpcf7-spinner');

        if (spinner) {
            spinner.style.display = 'none';
        }
    }

    function resetForm() {
        if (isCf7) {
            resetCf7Form();
        } else {
            els.form.reset();
            clearErrors();
            setLoading(false);
        }

        state.submitting = false;
        state.retried = false;
        els.form.hidden = false;
        els.success.hidden = true;
        els.download.setAttribute('href', '#');

        if (els.privacy) {
            els.privacy.hidden = false;
        }

        if (els.mailWarning) {
            els.mailWarning.hidden = true;
        }
    }

    function openModal(slug) {
        resetForm();

        state.slug = getTemplate(slug) ? slug : fallbackSlug();
        state.lastFocus = document.activeElement;

        var template = getTemplate(state.slug);

        if (els.templateName) {
            els.templateName.textContent = template ? template.title : '';
        }

        if (els.title) {
            els.title.textContent = i18n.formTitle || 'Get your template';
        }

        if (els.subtitle) {
            els.subtitle.textContent = i18n.formSubtitle || '';
        }

        setCf7Fields();

        modal.hidden = false;
        document.body.classList.add('ptdl-lock');

        // Force a reflow so the opening transition runs.
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

    function showSuccess(data) {
        state.submitting = false;
        setLoading(false);

        if (els.successTemplate) {
            els.successTemplate.textContent = data.template || '';
        }

        if (els.successEmail) {
            els.successEmail.textContent = data.email || '';
        }

        if (els.download) {
            els.download.setAttribute('href', data.download_url || '#');
        }

        if (els.mailWarning) {
            els.mailWarning.hidden = !!data.user_mail;
        }

        els.form.hidden = true;
        els.success.hidden = false;

        if (els.privacy) {
            els.privacy.hidden = true;
        }

        window.setTimeout(function () {
            if (els.download) {
                els.download.focus();
            }
        }, 60);
    }

    /* ------------------------------------------------------------------
     * Submit
     * ---------------------------------------------------------------- */

    function handleSubmit(event) {
        if (event && typeof event.preventDefault === 'function') {
            event.preventDefault();
        }

        if (state.submitting) {
            return;
        }

        clearErrors();

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
            fields.email = i18n.personal || 'Please use your work email. Gmail, Yahoo and other personal addresses are not accepted.';
        }

        if (!phone) {
            fields.phone = i18n.phoneError || 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.';
        }

        if (Object.keys(fields).length) {
            showErrors(fields);
            focusFirstError(fields);

            return;
        }

        state.submitting = true;
        setLoading(true);

        ensureNonce().then(function (nonce) {
            return ajax('privu_tdl_submit', {
                nonce: nonce,
                name: name,
                email: email,
                phone: phone,
                template: state.slug,
                page_url: window.location.href,
                website: els.honeypot ? els.honeypot.value : ''
            });
        }).then(function (response) {
            if (response && response.success && response.data) {
                showSuccess(response.data);

                return;
            }

            var data = (response && response.data) || {};

            if (data.code === 'bad_nonce' && !state.retried) {
                state.retried = true;
                state.submitting = false;
                setLoading(false);

                ensureNonce(true).then(function () {
                    handleSubmit();
                });

                return;
            }

            state.submitting = false;
            setLoading(false);

            if (data.fields) {
                showErrors(data.fields);
                focusFirstError(data.fields);
            }

            setFormError(data.message || i18n.genericError || 'Something went wrong. Please try again.');
        }).catch(function () {
            state.submitting = false;
            setLoading(false);
            setFormError(i18n.networkError || 'Network error. Please check your connection and try again.');
        });
    }

    /* ------------------------------------------------------------------
     * Events
     * ---------------------------------------------------------------- */

    if (!isCf7) {
        els.form.addEventListener('submit', handleSubmit);
    }

    // Contact Form 7 handles validation / mail; we only take over the success state.
    document.addEventListener('wpcf7submit', function (event) {
        if (!isCf7 || !els.cf7Form || event.target !== els.cf7Form) {
            return;
        }

        var detail = event.detail || {};
        var status = detail.status || (detail.apiResponse && detail.apiResponse.status) || '';

        if (status !== 'mail_sent' && status !== 'mail_failed') {
            return;
        }

        var emailField = els.cf7Form.querySelector('[name="your-email"]');
        var template = getTemplate(state.slug);

        showSuccess({
            template: template ? template.title : '',
            email: emailField ? emailField.value : '',
            download_url: (cfg.downloads && cfg.downloads[state.slug]) || '#',
            user_mail: status === 'mail_sent'
        });
    }, false);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    if (els.close) {
        els.close.addEventListener('click', closeModal);
    }

    if (els.again) {
        els.again.addEventListener('click', function () {
            resetForm();
            setCf7Fields();

            var firstInput = isCf7 && els.cf7Form
                ? els.cf7Form.querySelector('[name="your-name"]')
                : els.name;

            if (firstInput) {
                firstInput.focus();
            }
        });
    }

    if (!isCf7) {
        els.form.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', function () {
                var wrap = input.closest('.ptdl-field');

                if (wrap) {
                    wrap.classList.remove('has-error');

                    var error = wrap.querySelector('[data-ptdl-error]');

                    if (error) {
                        error.textContent = '';
                    }
                }

                if (els.formError) {
                    els.formError.textContent = '';
                }
            });
        });
    }

    // Digits and common separators only – keeps the phone field clean.
    if (!isCf7 && els.phone) {
        els.phone.addEventListener('input', function () {
            var cleaned = els.phone.value.replace(/[^\d\s()+-]/g, '');

            if (cleaned !== els.phone.value) {
                els.phone.value = cleaned;
            }
        });
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a, button') : null;

        if (!link || !isDownloadTrigger(link)) {
            return;
        }

        event.preventDefault();
        openModal(detectTemplate(link));
    });

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

    // Preload the nonce so the first submit is instant (native mode only).
    if (!isCf7) {
        ensureNonce();
    }

    // Public hook for a custom button: PrivuTemplateDownloads.open('dpia').
    cfg.open = function (slug) {
        openModal(slug);
    };
})();
