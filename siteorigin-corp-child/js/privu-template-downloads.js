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
    var isCf7 = cfg.mode === 'cf7' && modal.getAttribute('data-ptdl-form-mode') !== 'native';
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

    var LEGACY_SLUGS = {
        'dpdp-checklist': { slug: 'dpdp-act-compliance-checklist', index: 0 },
        'data-mapping': { slug: 'data-mapping-inventory-template', index: 1 },
        'consent-audit': { slug: 'consent-management-audit-template', index: 2 },
        'dpia': { slug: 'data-protection-impact-assessment-dpia-template', index: 3 },
        'vendor-checklist': { slug: 'third-party-vendor-assessment-checklist', index: 4 },
        'data-breach': { slug: 'data-breach-response-plan-template', index: 5 }
    };

    function findExactTemplate(slug) {
        for (var i = 0; i < templates.length; i++) {
            if (templates[i].slug === slug) {
                return templates[i];
            }
        }
        return null;
    }

    function resolveSlug(slug) {
        var clean = String(slug || '').toLowerCase().replace(/^#+/, '').replace(/\/+$/, '').trim();

        if (!clean || !templates.length) {
            return '';
        }

        if (findExactTemplate(clean)) {
            return clean;
        }

        var alias = LEGACY_SLUGS[clean];

        if (alias) {
            if (findExactTemplate(alias.slug)) {
                return alias.slug;
            }

            if (templates[alias.index]) {
                return templates[alias.index].slug;
            }
        }

        return '';
    }

    function getTemplate(slug) {
        var resolved = resolveSlug(slug);

        return resolved ? findExactTemplate(resolved) : null;
    }

    function fallbackSlug() {
        var resolvedDefault = resolveSlug(cfg.defaultTpl);

        if (resolvedDefault) {
            return resolvedDefault;
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
        var digits = String(value || '').trim();

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

        var resolvedExplicit = explicit ? resolveSlug(explicit) : '';

        if (resolvedExplicit) {
            return resolvedExplicit;
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

    var IGNORE_WORDS = ['the', 'and', 'for', 'with', 'your', 'our', 'from', 'of', 'in', 'to', 'on', 'by', 'or', 'an', 'as', 'at'];
    var GENERIC_WORDS = ['template', 'checklist', 'assessment'];

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

        if (haystack.indexOf(title) !== -1 || title.indexOf(haystack) !== -1) {
            score += Math.min(title.length, haystack.length) + 50;

            return score;
        }

        var tokens = title.split(' ').filter(function (token) {
            return token.length >= 2 && IGNORE_WORDS.indexOf(token) === -1;
        });

        if (!tokens.length) {
            return score;
        }

        var specificTotal = 0;
        var specificHits = 0;

        tokens.forEach(function (token) {
            var isGeneric = GENERIC_WORDS.indexOf(token) !== -1;

            if (!isGeneric) {
                specificTotal++;
            }

            if (haystack.indexOf(token) !== -1) {
                score += isGeneric ? 2 : 15;

                if (!isGeneric) {
                    specificHits++;
                }
            }
        });

        if (specificTotal > 0 && specificHits === specificTotal) {
            score += 30;
        }

        return score;
    }

    function buildTriggerHref(slug) {
        var base = cfg.baseUrl ? String(cfg.baseUrl) : ((window.location.origin || '') + (window.location.pathname || '/'));

        return base.replace(/\/+$/, '') + '/' + slug + '/';
    }

    function decorateTriggers() {
        document.querySelectorAll('a').forEach(function (link) {
            if (!isDownloadTrigger(link)) {
                return;
            }

            var slug = detectTemplate(link);

            if (!slug) {
                return;
            }

            link.setAttribute('data-template', slug);
            link.setAttribute('href', buildTriggerHref(slug));
        });
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

        var resolved = resolveSlug(slug);
        state.slug = resolved || fallbackSlug();
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

    // Enforce strict 10-digit Indian mobile numbers starting with 6, 7, 8 or 9 (both CF7 and native forms).
    bindPhoneRestriction(els.cf7Form ? els.cf7Form.querySelector('[name="your-phone"]') : null);
    bindPhoneRestriction(els.phone);

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

    // Replace "#" button URLs with clean template slug URLs on hover.
    decorateTriggers();

    // Auto-open the matching modal when arriving via ?template=<slug> or #<slug>.
    (function maybeOpenFromUrl() {
        var requested = '';

        try {
            var params = new URLSearchParams(window.location.search || '');
            requested = params.get('template') || '';

            if (requested && window.history && typeof window.history.replaceState === 'function') {
                params.delete('template');
                var cleanSearch = params.toString();
                window.history.replaceState(
                    null,
                    '',
                    window.location.pathname + (cleanSearch ? '?' + cleanSearch : '') + (window.location.hash || '')
                );
            }
        } catch (e) {
            requested = '';
        }

        if (!requested && window.location.hash) {
            requested = window.location.hash.replace(/^#+/, '');
        }

        var matched = resolveSlug(requested);

        if (matched) {
            openModal(matched);
        }
    })();

    // Preload the nonce so the first submit is instant (native mode only).
    if (!isCf7) {
        ensureNonce();
    }

    // Public hook for a custom button: PrivuTemplateDownloads.open('dpia').
    cfg.open = function (slug) {
        openModal(slug);
    };
})();
