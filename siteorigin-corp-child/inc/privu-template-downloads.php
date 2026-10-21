<?php
/**
 * Privu – Assessment template downloads.
 *
 * Adds a gated download flow to the "Assessment Templates" page:
 *  1. Visitor clicks an existing "Download Template" link.
 *  2. A lead form (Name, Work Email, Phone) opens in a modal.
 *  3. Work email + Indian mobile number are validated on the client and the server.
 *  4. Lead details are emailed to eric@privu.tech and contact@privu.tech.
 *  5. The visitor receives the template as an email attachment and can also
 *     download it instantly from the success screen.
 *
 * Nothing in the page builder layout or theme design is modified. The only
 * additions are a scoped stylesheet, a scoped script and the modal markup in
 * the footer of the assessment templates page.
 *
 * @package Privu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRIVU_TDL_VERSION', '1.0.0' );

// Contact Form 7 integration (form creation, validation, attachment, mail tags).
$privu_tdl_cf7_inc = get_stylesheet_directory() . '/inc/privu-cf7-template-form.php';
if ( file_exists( $privu_tdl_cf7_inc ) ) {
    require_once $privu_tdl_cf7_inc;
}

// ACF screen (used when ACF PRO is active – the site already has it).
$privu_tdl_acf_inc = get_stylesheet_directory() . '/inc/privu-template-acf.php';
if ( file_exists( $privu_tdl_acf_inc ) ) {
    require_once $privu_tdl_acf_inc;
}

// Admin screen: add / edit templates and files without code.
$privu_tdl_admin_inc = get_stylesheet_directory() . '/inc/privu-template-admin.php';
if ( is_admin() && file_exists( $privu_tdl_admin_inc ) ) {
    require_once $privu_tdl_admin_inc;
}

/**
 * Template catalogue.
 *
 * 'file'     => file inside /assets/templates/ that is attached to the email.
 * 'title'    => label shown in the modal and in emails.
 * 'keywords' => used by the script to detect which card was clicked. A card
 *               heading like "Data Mapping & Inventory Template" matches the
 *               "mapping" keyword. Add data-template="slug" to a link to force
 *               a specific template.
 *
 * To swap a file later, just replace it in assets/templates/ and update 'file'.
 */
function privu_tdl_default_templates() {
    $templates = array(
        'dpdp-act-compliance-checklist' => array(
            'title'    => 'DPDP Act Compliance Checklist',
            'file'     => 'PrivuTech_Third_Party_Vendor_Assessment_Checklist.xlsx',
            'keywords' => array( 'compliance checklist', 'dpdp act', 'checklist' ),
        ),
        'data-mapping-inventory-template' => array(
            'title'    => 'Data Mapping & Inventory Template',
            'file'     => 'PrivuTech_Data_Mapping_Inventory_Template.xlsx',
            'keywords' => array( 'data mapping', 'mapping', 'inventory' ),
        ),
        'consent-management-audit-template' => array(
            'title'    => 'Consent Management Audit Template',
            'file'     => 'PrivuTech_Consent_Management_Audit_Template.xlsx',
            'keywords' => array( 'consent management', 'consent', 'audit' ),
        ),
        'data-protection-impact-assessment-dpia-template' => array(
            'title'    => 'Data Protection Impact Assessment (DPIA) Template',
            'file'     => 'PrivuTech_DPIA_Template.xlsx',
            'keywords' => array( 'dpia', 'impact assessment', 'impact' ),
        ),
        'third-party-vendor-assessment-checklist' => array(
            'title'    => 'Third-Party Vendor Assessment Checklist',
            'file'     => 'PrivuTech_Third_Party_Vendor_Assessment_Checklist.xlsx',
            'keywords' => array( 'third-party', 'third party', 'vendor' ),
        ),
        'data-breach-response-plan-template' => array(
            'title'    => 'Data Breach Response Plan Template',
            'file'     => 'PrivuTech_Data_Breach_Response_Plan_Template.xlsx',
            'keywords' => array( 'data breach', 'breach', 'response plan' ),
        ),
    );

    /**
     * Filter the template catalogue.
     *
     * @param array $templates Template definitions keyed by slug.
     */
    return apply_filters( 'privu_tdl_templates', $templates );
}

/**
 * Resolve any active or legacy template slug to the current canonical key in privu_tdl_templates().
 *
 * @param string $slug Requested slug.
 * @return string Canonical template key, or empty string when not found.
 */
function privu_tdl_resolve_slug( $slug ) {
    $slug      = sanitize_key( (string) $slug );
    $templates = privu_tdl_templates();

    if ( '' === $slug || empty( $templates ) ) {
        return '';
    }

    if ( isset( $templates[ $slug ] ) ) {
        return $slug;
    }

    $legacy_map = array(
        'dpdp-checklist'   => array( 'slug' => 'dpdp-act-compliance-checklist', 'index' => 0 ),
        'data-mapping'     => array( 'slug' => 'data-mapping-inventory-template', 'index' => 1 ),
        'consent-audit'    => array( 'slug' => 'consent-management-audit-template', 'index' => 2 ),
        'dpia'             => array( 'slug' => 'data-protection-impact-assessment-dpia-template', 'index' => 3 ),
        'vendor-checklist' => array( 'slug' => 'third-party-vendor-assessment-checklist', 'index' => 4 ),
        'data-breach'      => array( 'slug' => 'data-breach-response-plan-template', 'index' => 5 ),
    );

    if ( isset( $legacy_map[ $slug ] ) ) {
        $target = $legacy_map[ $slug ]['slug'];

        if ( isset( $templates[ $target ] ) ) {
            return $target;
        }

        $keys  = array_keys( $templates );
        $index = $legacy_map[ $slug ]['index'];

        if ( isset( $keys[ $index ] ) ) {
            return $keys[ $index ];
        }
    }

    return '';
}

/**
 * Handle clean template URLs like /assessment-templates/<template-slug>/ when opened directly.
 */
function privu_tdl_handle_template_slug_url() {
    if ( is_admin() || empty( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }

    $req_path  = trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' );
    $home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

    if ( '' !== $home_path && 0 === strpos( $req_path, $home_path . '/' ) ) {
        $req_path = substr( $req_path, strlen( $home_path ) + 1 );
    }

    if ( 0 !== strpos( $req_path, 'assessment-templates/' ) ) {
        return;
    }

    $candidate = substr( $req_path, strlen( 'assessment-templates/' ) );

    if ( '' === $candidate || false !== strpos( $candidate, '/' ) ) {
        return;
    }

    $resolved = privu_tdl_resolve_slug( $candidate );

    if ( '' === $resolved ) {
        return;
    }

    wp_safe_redirect(
        add_query_arg( 'template', $resolved, home_url( '/assessment-templates/' ) ),
        302
    );
    exit;
}
add_action( 'template_redirect', 'privu_tdl_handle_template_slug_url', 1 );

/**
 * Templates used on the front end.
 *
 * Saved rows from Appearance → Template Downloads win; otherwise the bundled
 * defaults are used. Keywords and files are normalised here so the rest of the
 * code does not have to care where a template came from.
 *
 * @return array
 */
function privu_tdl_templates() {
    static $cached = null;

    if ( null !== $cached ) {
        return $cached;
    }

    // 1) ACF options page (client-managed).
    if ( function_exists( 'privu_tdl_templates_from_acf' ) ) {
        $from_acf = privu_tdl_templates_from_acf();

        if ( ! empty( $from_acf ) ) {
            $cached = apply_filters( 'privu_tdl_templates', $from_acf );

            return $cached;
        }
    }

    // 2) Fallback admin table (used when ACF is not available).
    $saved = get_option( 'privu_tdl_templates' );

    if ( is_array( $saved ) && ! empty( $saved ) ) {
        $templates = array();

        foreach ( $saved as $slug => $row ) {
            $slug = sanitize_key( $slug );

            if ( '' === $slug || empty( $row['title'] ) ) {
                continue;
            }

            $keywords = array();

            if ( ! empty( $row['keywords'] ) ) {
                $raw = is_array( $row['keywords'] ) ? $row['keywords'] : preg_split( '/[,;]+/', (string) $row['keywords'] );

                foreach ( (array) $raw as $keyword ) {
                    $keyword = trim( (string) $keyword );

                    if ( '' !== $keyword ) {
                        $keywords[] = $keyword;
                    }
                }
            }

            $templates[ $slug ] = array(
                'title'         => $row['title'],
                'keywords'      => $keywords,
                'attachment_id' => ! empty( $row['attachment_id'] ) ? (int) $row['attachment_id'] : 0,
                'file'          => ! empty( $row['file'] ) ? $row['file'] : '',
            );
        }

        if ( ! empty( $templates ) ) {
            $cached = apply_filters( 'privu_tdl_templates', $templates );

            return $cached;
        }
    }

    $cached = apply_filters( 'privu_tdl_templates', privu_tdl_default_templates() );

    return $cached;
}

/**
 * Absolute path of a template file, or false when it is missing.
 *
 * @param array $template Template definition.
 * @return string|false
 */
function privu_tdl_template_path( $template ) {
    // 1) File uploaded through the Template Downloads screen.
    if ( ! empty( $template['attachment_id'] ) ) {
        $path = get_attached_file( (int) $template['attachment_id'] );

        if ( $path && file_exists( $path ) ) {
            return $path;
        }
    }

    if ( empty( $template['file'] ) ) {
        return false;
    }

    // 2) Bundled file inside the child theme.
    $path = trailingslashit( get_stylesheet_directory() ) . 'assets/templates/' . $template['file'];

    if ( file_exists( $path ) ) {
        return $path;
    }

    // 3) Absolute path (advanced use).
    if ( file_exists( $template['file'] ) ) {
        return $template['file'];
    }

    return false;
}

/**
 * Content type for a template file, based on its extension.
 *
 * @param string $path Absolute file path.
 * @return string
 */
function privu_tdl_content_type( $path ) {
    $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

    $types = array(
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls'  => 'application/vnd.ms-excel',
        'csv'  => 'text/csv',
        'pdf'  => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc'  => 'application/msword',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip'  => 'application/zip',
    );

    return isset( $types[ $extension ] ) ? $types[ $extension ] : 'application/octet-stream';
}

/**
 * Whether the current request is the assessment templates page.
 *
 * @return bool
 */
function privu_tdl_is_target_page() {
    $is_target = is_page( 'assessment-templates' );

    /**
     * Filter where the download modal and assets are loaded.
     *
     * @param bool $is_target Defaults to the assessment-templates page.
     */
    return (bool) apply_filters( 'privu_tdl_is_target_page', $is_target );
}

/**
 * Free / personal email domains that are not accepted as "work email".
 *
 * @return array
 */
function privu_tdl_blocked_email_domains() {
    $domains = array(
        // Google.
        'gmail.com',
        'googlemail.com',
        // Yahoo.
        'yahoo.com',
        'yahoo.co.in',
        'yahoo.in',
        'yahoo.co.uk',
        'yahoo.co.jp',
        'ymail.com',
        'rocketmail.com',
        // Microsoft.
        'hotmail.com',
        'hotmail.co.uk',
        'hotmail.in',
        'outlook.com',
        'outlook.in',
        'live.com',
        'live.in',
        'msn.com',
        // Apple / AOL.
        'icloud.com',
        'me.com',
        'mac.com',
        'aol.com',
        'aol.in',
        // Privacy-first providers.
        'protonmail.com',
        'proton.me',
        'pm.me',
        'tutanota.com',
        'tuta.io',
        'tutamail.com',
        // India specific.
        'rediffmail.com',
        'rediff.com',
        // Other consumer providers.
        'mail.com',
        'gmx.com',
        'gmx.net',
        'yandex.com',
        'yandex.ru',
        'zoho.com',
        'zohomail.com',
        // Disposable / temporary inboxes.
        'mailinator.com',
        'guerrillamail.com',
        '10minutemail.com',
        'tempmail.com',
        'temp-mail.org',
        'throwawaymail.com',
        'yopmail.com',
        'maildrop.cc',
        'getnada.com',
        'dispostable.com',
        'fakeinbox.com',
        'trashmail.com',
        'sharklasers.com',
        'mailnesia.com',
        'mintemail.com',
        'emailondeck.com',
        'mohmal.com',
    );

    /**
     * Filter the blocked (personal / disposable) email domains.
     *
     * @param array $domains Domain names without "www." or "@".
     */
    $domains = apply_filters( 'privu_tdl_blocked_email_domains', $domains );

    return array_values( array_unique( array_map( 'strtolower', $domains ) ) );
}

/**
 * Is the email address from a personal / disposable domain?
 *
 * @param string $email Email address.
 * @return bool
 */
function privu_tdl_is_personal_email( $email ) {
    $email = strtolower( trim( (string) $email ) );

    if ( ! is_email( $email ) ) {
        return false;
    }

    $domain = substr( $email, strrpos( $email, '@' ) + 1 );

    foreach ( privu_tdl_blocked_email_domains() as $blocked ) {
        if ( $domain === $blocked || substr( $domain, -( strlen( $blocked ) + 1 ) ) === '.' . $blocked ) {
            return true;
        }
    }

    return false;
}

/**
 * Normalise an Indian mobile number.
 *
 * Accepts "9876543210", "+91 98765 43210", "09876543210", "0091-9876543210"
 * and returns the bare 10 digit number, or an empty string when invalid.
 *
 * @param string $phone Raw phone input.
 * @return string
 */
function privu_tdl_normalize_phone( $phone ) {
    $digits = trim( (string) $phone );

    if ( preg_match( '/^[6-9][0-9]{9}$/', $digits ) ) {
        return $digits;
    }

    return '';
}

/**
 * Client IP address (best effort, used only for rate limiting and the lead email).
 *
 * @return string
 */
function privu_tdl_client_ip() {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

    return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Signed key for a template download URL.
 *
 * Stateless HMAC – the link keeps working from the email without expiring,
 * and cannot be guessed by browsing /assets/templates/.
 *
 * @param string $slug Template slug.
 * @return string
 */
function privu_tdl_download_key( $slug ) {
    return substr( hash_hmac( 'sha256', 'privu_tdl|' . $slug, wp_salt( 'auth' ) ), 0, 32 );
}

/**
 * Download URL for a template.
 *
 * @param string $slug Template slug.
 * @return string
 */
function privu_tdl_download_url( $slug ) {
    return add_query_arg(
        array(
            'privu_tdl' => $slug,
            'key'       => privu_tdl_download_key( $slug ),
        ),
        home_url( '/' )
    );
}

/**
 * Load assets on the assessment templates page only.
 */
function privu_tdl_enqueue_assets( $force = false ) {
    static $done = false;

    if ( $done ) {
        return;
    }

    if ( ! $force && ! privu_tdl_is_target_page() ) {
        return;
    }

    $done = true;

    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    $css_rel = '/css/privu-template-downloads.css';
    $js_rel  = '/js/privu-template-downloads.js';

    $css_ver = file_exists( $dir . $css_rel ) ? filemtime( $dir . $css_rel ) : PRIVU_TDL_VERSION;
    $js_ver  = file_exists( $dir . $js_rel ) ? filemtime( $dir . $js_rel ) : PRIVU_TDL_VERSION;

    wp_enqueue_style( 'privu-tdl', $uri . $css_rel, array(), $css_ver );
    wp_enqueue_script( 'privu-tdl', $uri . $js_rel, array(), $js_ver, true );

    // When the popup uses CF7, make sure CF7's own assets are present.
    $cf7_form_id = function_exists( 'privu_tdl_cf7_form_id' ) ? (int) privu_tdl_cf7_form_id() : 0;

    if ( $cf7_form_id ) {
        if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
            wpcf7_enqueue_styles();
        }

        if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
            wpcf7_enqueue_scripts();
        }
    }

    $js_templates = array();
    $js_downloads = array();

    $all_templates = privu_tdl_templates();
    $template_keys = array_keys( $all_templates );
    $default_slug  = ! empty( $template_keys ) ? $template_keys[0] : 'dpdp-act-compliance-checklist';

    foreach ( $all_templates as $slug => $template ) {
        $js_templates[] = array(
            'slug'     => $slug,
            'title'    => isset( $template['title'] ) ? $template['title'] : $slug,
            'keywords' => isset( $template['keywords'] ) ? array_values( (array) $template['keywords'] ) : array(),
        );

        $js_downloads[ $slug ] = privu_tdl_download_url( $slug );
    }

    wp_localize_script(
        'privu-tdl',
        'PrivuTemplateDownloads',
        array(
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'baseUrl'    => home_url( '/assessment-templates/' ),
            'mode'       => $cf7_form_id ? 'cf7' : 'native',
            'formId'     => $cf7_form_id,
            'templates'  => $js_templates,
            'downloads'  => $js_downloads,
            'blocked'    => privu_tdl_blocked_email_domains(),
            'defaultTpl' => $default_slug,
            'i18n'       => array(
                'formTitle'    => 'Get your template',
                'formSubtitle' => 'Enter your work details and we will email the template to you. You can also download it right away.',
                'send'         => 'Send me the template',
                'sending'      => 'Sending…',
                'download'     => 'Download now',
                'another'      => 'Download another template',
                'genericError' => 'Something went wrong. Please try again.',
                'networkError' => 'Network error. Please check your connection and try again.',
                'nameError'    => 'Please enter your full name.',
                'emailError'   => 'Please enter a valid work email address.',
                'personal'     => 'Please use your work email. Gmail, Yahoo, Outlook and other personal addresses are not accepted.',
                'phoneError'   => 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.',
                'successTitle' => 'Check your inbox',
                'successText'  => 'We have emailed the template to',
                'mailWarning'  => 'The email could not be sent right now, but your download is ready below.',
                'privacy'      => 'By submitting, you agree to receive this template and related compliance resources from Privu.',
            ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'privu_tdl_enqueue_assets', 20 );

/**
 * Keep the modal script out of "defer / delay JS" optimisations – the form
 * must open on the very first click, not after a second interaction.
 */
function privu_tdl_script_attributes( $tag, $handle ) {
    if ( 'privu-tdl' !== $handle ) {
        return $tag;
    }

    return str_replace(
        '<script ',
        '<script data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false" ',
        $tag
    );
}
add_filter( 'script_loader_tag', 'privu_tdl_script_attributes', 10, 2 );

/**
 * [privu_template_download slug="dpia" text="Download Template"]
 *
 * Optional button for any other page. The modal opens exactly like on the
 * Assessment Templates page.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function privu_tdl_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'slug'  => '',
            'text'  => 'Download Template',
            'class' => '',
        ),
        $atts,
        'privu_template_download'
    );

    $templates = privu_tdl_templates();

    if ( empty( $templates ) ) {
        return '';
    }

    $slug = privu_tdl_resolve_slug( $atts['slug'] );

    if ( '' === $slug ) {
        $keys = array_keys( $templates );
        $slug = $keys[0];
    }

    $GLOBALS['privu_tdl_shortcode_used'] = true;

    privu_tdl_enqueue_assets( true );

    return sprintf(
        '<a href="%1$s" class="ptdl-inline-link %2$s" data-template="%3$s">%4$s</a>',
        esc_url( home_url( '/assessment-templates/' . $slug . '/' ) ),
        esc_attr( $atts['class'] ),
        esc_attr( $slug ),
        esc_html( $atts['text'] )
    );
}
add_shortcode( 'privu_template_download', 'privu_tdl_shortcode' );

/**
 * Modal markup, printed in the footer of the assessment templates page only.
 */
function privu_tdl_print_modal() {
    if ( ! privu_tdl_is_target_page() && empty( $GLOBALS['privu_tdl_shortcode_used'] ) ) {
        return;
    }

    $cf7_form_id   = function_exists( 'privu_tdl_cf7_form_id' ) ? (int) privu_tdl_cf7_form_id() : 0;
    $default_slug  = 'dpdp-act-compliance-checklist';
    $templates     = privu_tdl_templates();
    $rendered_cf7  = '';
    $cf7_mode      = 'native';

    if ( ! empty( $templates ) ) {
        $first        = array_keys( $templates );
        $default_slug = $first[0];
    }

    // Render the configured CF7 shortcode (editable in Admin -> Templates & Checklists) and verify it compiled cleanly.
    $cf7_shortcode = function_exists( 'privu_tdl_cf7_shortcode' ) ? privu_tdl_cf7_shortcode() : '';

    if ( $cf7_form_id && '' !== $cf7_shortcode ) {
        $rendered_cf7 = do_shortcode( $cf7_shortcode );

        if ( '' !== trim( strip_tags( $rendered_cf7 ) ) && ! preg_match( '/\[(text|email|tel|hidden|submit)[* ]/', $rendered_cf7 ) ) {
            $cf7_mode = 'cf7';
        }
    }
    ?>
    <div class="ptdl-overlay" id="privu-tdl-modal" hidden data-ptdl-form-mode="<?php echo esc_attr( $cf7_mode ); ?>">
        <div class="ptdl-modal" role="dialog" aria-modal="true" aria-labelledby="ptdl-title" aria-describedby="ptdl-subtitle">
            <button type="button" class="ptdl-close" data-ptdl-close aria-label="Close dialog">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
                    <path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="ptdl-modal__head">
                <span class="ptdl-badge">Free download</span>
                <h2 class="ptdl-title" id="ptdl-title">Get your template</h2>
                <p class="ptdl-subtitle" id="ptdl-subtitle">Enter your work details and we will email the template to you. You can also download it right away.</p>
                <p class="ptdl-template-pill"><span class="ptdl-template-pill__label">Template:</span> <span data-ptdl-template-name>DPDP Act Compliance Checklist</span></p>
            </div>

            <?php if ( 'cf7' === $cf7_mode ) : ?>
                <div class="ptdl-cf7-wrap">
                    <?php echo $rendered_cf7; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered CF7 form markup. ?>
                </div>
                <p class="ptdl-privacy">By submitting, you agree to receive this template and related compliance resources from Privu.</p>
            <?php else : ?>
                <form class="ptdl-form" novalidate>
                    <div class="ptdl-field">
                        <label for="ptdl-name">Full name <span aria-hidden="true">*</span></label>
                        <input type="text" id="ptdl-name" name="name" autocomplete="name" placeholder="e.g. Priya Nair" maxlength="80" required>
                        <p class="ptdl-error" data-ptdl-error="name" role="alert"></p>
                    </div>

                    <div class="ptdl-field">
                        <label for="ptdl-email">Work email <span aria-hidden="true">*</span></label>
                        <input type="email" id="ptdl-email" name="email" autocomplete="email" placeholder="you@company.com" maxlength="120" required>
                        <p class="ptdl-error" data-ptdl-error="email" role="alert"></p>
                    </div>

                    <div class="ptdl-field">
                        <label for="ptdl-phone">Phone number <span aria-hidden="true">*</span></label>
                        <div class="ptdl-phone-wrap">
                            <span class="ptdl-phone-prefix" aria-hidden="true">+91</span>
                            <input type="tel" id="ptdl-phone" name="phone" inputmode="numeric" autocomplete="tel" placeholder="9876543210" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" required>
                        </div>
                        <p class="ptdl-hint">10 digits, starting with 6, 7, 8 or 9.</p>
                        <p class="ptdl-error" data-ptdl-error="phone" role="alert"></p>
                    </div>

                    <div class="ptdl-hp" aria-hidden="true">
                        <label for="ptdl-website">Website</label>
                        <input type="text" id="ptdl-website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <button type="submit" class="ptdl-submit" data-ptdl-submit>
                        <span class="ptdl-spinner" aria-hidden="true"></span>
                        <span class="ptdl-submit__label">Send me the template</span>
                    </button>

                    <p class="ptdl-form-error" data-ptdl-form-error role="alert"></p>
                    <p class="ptdl-privacy">By submitting, you agree to receive this template and related compliance resources from Privu.</p>
                </form>
            <?php endif; ?>

            <div class="ptdl-success" hidden>
                <div class="ptdl-check" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28" focusable="false">
                        <path d="M5 12.5l4.5 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3 class="ptdl-success__title">Check your inbox</h3>
                <p class="ptdl-success__text">We have emailed <strong data-ptdl-success-template>the template</strong> to <strong data-ptdl-success-email></strong>.</p>
                <p class="ptdl-mail-warning" data-ptdl-mail-warning hidden>The email could not be sent right now, but your download is ready below.</p>
                <a class="ptdl-download" href="#" data-ptdl-download download>
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
                        <path d="M12 4v10m0 0l4-4m-4 4l-4-4M5 19h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Download now</span>
                </a>
                <button type="button" class="ptdl-again" data-ptdl-again>Download another template</button>
            </div>

            <p class="ptdl-foot">Privu · DPDP compliance templates</p>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'privu_tdl_print_modal', 5 );

/**
 * Returns a fresh nonce (the page may be served from a page cache, so the
 * nonce is fetched at runtime instead of being printed into cached HTML).
 */
function privu_tdl_ajax_nonce() {
    wp_send_json_success(
        array(
            'nonce' => wp_create_nonce( 'privu_tdl_submit' ),
        )
    );
}
add_action( 'wp_ajax_privu_tdl_nonce', 'privu_tdl_ajax_nonce' );
add_action( 'wp_ajax_nopriv_privu_tdl_nonce', 'privu_tdl_ajax_nonce' );

/**
 * Lead form submission.
 */
function privu_tdl_ajax_submit() {
    if ( ! check_ajax_referer( 'privu_tdl_submit', 'nonce', false ) ) {
        wp_send_json_error(
            array(
                'code'    => 'bad_nonce',
                'message' => 'Your session expired. Please try again.',
            ),
            403
        );
    }

    // Honeypot – bots fill hidden fields.
    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_error(
            array( 'message' => 'Something went wrong. Please try again.' ),
            400
        );
    }

    $name_raw  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $email_raw = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $phone_raw = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $slug      = isset( $_POST['template'] ) ? privu_tdl_resolve_slug( wp_unslash( $_POST['template'] ) ) : '';
    $page_url  = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : home_url( '/' );

    $templates = privu_tdl_templates();

    if ( '' === $slug || ! isset( $templates[ $slug ] ) ) {
        wp_send_json_error(
            array( 'message' => 'Please choose a valid template and try again.' ),
            400
        );
    }

    $template = $templates[ $slug ];

    if ( ! privu_tdl_template_path( $template ) ) {
        error_log( '[Privu template download] Missing file for template: ' . $slug );
        wp_send_json_error(
            array( 'message' => 'This template is temporarily unavailable. Please contact contact@privu.tech.' ),
            500
        );
    }

    $errors = array();

    // Name.
    $name = trim( preg_replace( '/\s+/', ' ', $name_raw ) );

    if ( strlen( $name ) < 2 || strlen( $name ) > 80 || ! preg_match( '/\p{L}/u', $name ) || preg_match( '/[<>]|https?:/i', $name ) ) {
        $errors['name'] = 'Please enter your full name.';
    }

    // Work email.
    $email = strtolower( trim( $email_raw ) );

    if ( ! is_email( $email ) ) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif ( privu_tdl_is_personal_email( $email ) ) {
        $errors['email'] = 'Please use your work email. Gmail, Yahoo, Outlook and other personal addresses are not accepted.';
    }

    // Phone.
    $phone = privu_tdl_normalize_phone( $phone_raw );

    if ( '' === $phone ) {
        $errors['phone'] = 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.';
    }

    if ( ! empty( $errors ) ) {
        wp_send_json_error(
            array(
                'message' => 'Please check the highlighted fields.',
                'fields'  => $errors,
            ),
            422
        );
    }

    // Light rate limit: max 12 submissions per 15 minutes per IP.
    $ip       = privu_tdl_client_ip();
    $rate_key = 'privu_tdl_rl_' . md5( $ip );
    $hits     = (int) get_transient( $rate_key );

    if ( $hits >= 12 ) {
        wp_send_json_error(
            array( 'message' => 'Too many requests. Please try again in a few minutes.' ),
            429
        );
    }

    set_transient( $rate_key, $hits + 1, 15 * MINUTE_IN_SECONDS );

    $lead = array(
        'name'         => $name,
        'email'        => $email,
        'phone'        => $phone,
        'phone_pretty' => '+91 ' . substr( $phone, 0, 5 ) . ' ' . substr( $phone, 5 ),
        'slug'         => $slug,
        'template'     => $template['title'],
        'page_url'     => $page_url,
        'ip'           => $ip,
        'time'         => time(),
    );

    $mail_result = privu_tdl_send_emails( $lead, $template );

    wp_send_json_success(
        array(
            'message'      => 'Thanks! Your template is ready.',
            'template'     => $template['title'],
            'email'        => $email,
            'download_url' => privu_tdl_download_url( $slug ),
            'admin_mail'   => ! empty( $mail_result['admin'] ),
            'user_mail'    => ! empty( $mail_result['user'] ),
        )
    );
}
add_action( 'wp_ajax_privu_tdl_submit', 'privu_tdl_ajax_submit' );
add_action( 'wp_ajax_nopriv_privu_tdl_submit', 'privu_tdl_ajax_submit' );

/**
 * Serve a template file when the signed download URL is opened.
 */
function privu_tdl_maybe_download() {
    if ( empty( $_GET['privu_tdl'] ) ) {
        return;
    }

    $raw_slug  = sanitize_key( wp_unslash( $_GET['privu_tdl'] ) );
    $slug      = privu_tdl_resolve_slug( $raw_slug );
    $templates = privu_tdl_templates();

    if ( '' === $slug || ! isset( $templates[ $slug ] ) ) {
        return;
    }

    $provided_key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';

    if ( ! hash_equals( privu_tdl_download_key( $raw_slug ), $provided_key ) ) {
        wp_die(
            'This download link is invalid. Please open the Assessment Templates page and fill the form again.',
            'Download link invalid',
            array( 'response' => 403 )
        );
    }

    $path = privu_tdl_template_path( $templates[ $slug ] );

    if ( ! $path ) {
        wp_die(
            'This template is temporarily unavailable. Please contact contact@privu.tech.',
            'Template unavailable',
            array( 'response' => 404 )
        );
    }

    nocache_headers();

    header( 'Content-Description: File Transfer' );
    header( 'Content-Type: ' . privu_tdl_content_type( $path ) );
    header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Content-Length: ' . filesize( $path ) );
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Cache-Control: private, no-store, max-age=0' );

    readfile( $path );
    exit;
}
// Priority 25: ACF fields (Template Downloads screen) are registered by then.
add_action( 'init', 'privu_tdl_maybe_download', 25 );

/**
 * Send the lead notification and the visitor email.
 *
 * @param array $lead     Lead data.
 * @param array $template Template definition.
 * @return array{admin:bool,user:bool}
 */
function privu_tdl_send_emails( $lead, $template ) {
    $from_name = function () {
        return 'Privu';
    };
    add_filter( 'wp_mail_from_name', $from_name, 99 );

    // 1) Internal notification.
    $admin_recipients = apply_filters(
        'privu_tdl_admin_recipients',
        array( 'eric@privu.tech', 'contact@privu.tech' )
    );

    $admin_subject = sprintf(
        'New template download: %s – %s',
        $template['title'],
        $lead['name']
    );

    $reply_to_name = trim( str_replace( array( '"', "'", ',', ';', '<', '>' ), '', $lead['name'] ) );

    $admin_headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf( 'Reply-To: %s <%s>', $reply_to_name, $lead['email'] ),
    );

    $admin_sent = wp_mail(
        $admin_recipients,
        $admin_subject,
        privu_tdl_admin_email_body( $lead, $template ),
        $admin_headers
    );

    // 2) Visitor email with the template attached.
    $path       = privu_tdl_template_path( $template );
    $attachment = $path ? array( $path ) : array();

    $user_subject = sprintf( 'Your %s from Privu', $template['title'] );

    $user_headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: Privu <contact@privu.tech>',
    );

    $user_sent = wp_mail(
        $lead['email'],
        $user_subject,
        privu_tdl_user_email_body( $lead, $template ),
        $user_headers,
        $attachment
    );

    remove_filter( 'wp_mail_from_name', $from_name, 99 );

    if ( ! $admin_sent ) {
        error_log( '[Privu template download] Admin notification failed for lead: ' . $lead['email'] );
    }

    if ( ! $user_sent ) {
        error_log( '[Privu template download] Visitor email failed for lead: ' . $lead['email'] );
    }

    return array(
        'admin' => (bool) $admin_sent,
        'user'  => (bool) $user_sent,
    );
}

/**
 * Shared HTML shell for the emails.
 *
 * @param string $heading   Heading.
 * @param string $content   Inner HTML.
 * @param string $preheader Hidden preheader text.
 * @return string
 */
function privu_tdl_email_shell( $heading, $content, $preheader = '' ) {
    $year = wp_date( 'Y' );

    return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . esc_html( $heading ) . '</title></head>'
        . '<body style="margin:0;padding:0;background:#F3F7FA;font-family:Arial,Helvetica,sans-serif;color:#1A2B3C;">'
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;">' . esc_html( $preheader ) . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F7FA;padding:28px 12px;">'
        . '<tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #E5EBF1;">'
        . '<tr><td style="padding:22px 28px;background:#0092B8;background:linear-gradient(90deg,#0092B8 0%,#009689 100%);color:#ffffff;">'
        . '<span style="font-size:18px;font-weight:bold;letter-spacing:.4px;">Privu</span>'
        . '<span style="font-size:12px;opacity:.9;display:block;margin-top:2px;">Data privacy &amp; DPDP compliance</span>'
        . '</td></tr>'
        . '<tr><td style="padding:30px 28px 8px;">'
        . '<h1 style="margin:0 0 16px;font-size:22px;line-height:1.3;color:#101828;">' . esc_html( $heading ) . '</h1>'
        . $content
        . '</td></tr>'
        . '<tr><td style="padding:20px 28px 26px;border-top:1px solid #EDF1F5;">'
        . '<p style="margin:0;font-size:12px;line-height:1.6;color:#7B8A99;">Privu · <a href="https://www.privu.tech/" style="color:#0092B8;text-decoration:none;">www.privu.tech</a> · '
        . '<a href="mailto:contact@privu.tech" style="color:#0092B8;text-decoration:none;">contact@privu.tech</a><br>'
        . 'This email was sent because a template was requested on our website. &copy; ' . esc_html( $year ) . ' Privu.</p>'
        . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}

/**
 * Internal lead notification body.
 *
 * @param array $lead     Lead data.
 * @param array $template Template definition.
 * @return string
 */
function privu_tdl_admin_email_body( $lead, $template ) {
    $rows = array(
        'Name'         => esc_html( $lead['name'] ),
        'Work email'   => '<a href="mailto:' . esc_attr( $lead['email'] ) . '" style="color:#0092B8;">' . esc_html( $lead['email'] ) . '</a>',
        'Phone'        => '<a href="tel:+91' . esc_attr( $lead['phone'] ) . '" style="color:#0092B8;">' . esc_html( $lead['phone_pretty'] ) . '</a>',
        'Template'     => esc_html( $template['title'] ),
        'Requested on' => '<a href="' . esc_url( $lead['page_url'] ) . '" style="color:#0092B8;">' . esc_html( $lead['page_url'] ) . '</a>',
        'Date'         => esc_html( wp_date( 'd M Y, h:i A T', $lead['time'] ) ),
        'IP address'   => esc_html( $lead['ip'] ),
    );

    $table = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;line-height:1.5;">';

    foreach ( $rows as $label => $value ) {
        $table .= '<tr>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #EDF1F5;color:#5A6E82;width:38%;vertical-align:top;">' . esc_html( $label ) . '</td>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #EDF1F5;color:#1A2B3C;vertical-align:top;">' . $value . '</td>'
            . '</tr>';
    }

    $table .= '</table>';

    $content = '<p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#4A5565;">A visitor requested a template download from the website. Details below:</p>'
        . $table
        . '<p style="margin:20px 0 0;font-size:13px;line-height:1.6;color:#7B8A99;">Reply to this email to contact the lead directly. The download log is also available in the WordPress debug log if mail delivery fails.</p>';

    return privu_tdl_email_shell( 'New template download request', $content, 'New lead: ' . $lead['name'] . ' – ' . $template['title'] );
}

/**
 * Visitor email body (template is attached separately).
 *
 * @param array $lead     Lead data.
 * @param array $template Template definition.
 * @return string
 */
function privu_tdl_user_email_body( $lead, $template ) {
    $first_name = explode( ' ', $lead['name'] );
    $first_name = $first_name[0];
    $download   = privu_tdl_download_url( $lead['slug'] );

    $content = '<p style="margin:0 0 14px;font-size:15px;line-height:1.65;color:#4A5565;">Hi ' . esc_html( $first_name ) . ',</p>'
        . '<p style="margin:0 0 14px;font-size:15px;line-height:1.65;color:#4A5565;">Thank you for your interest. <strong style="color:#101828;">' . esc_html( $template['title'] ) . '</strong> is attached to this email, and you can also download it from the link below.</p>'
        . '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0;"><tr><td style="border-radius:9px;background:#0092B8;">'
        . '<a href="' . esc_url( $download ) . '" style="display:inline-block;padding:13px 24px;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:9px;background-color:#0092B8;background:linear-gradient(90deg,#0092B8 0%,#009689 100%);">Download template</a>'
        . '</td></tr></table>'
        . '<p style="margin:0 0 14px;font-size:14px;line-height:1.65;color:#4A5565;">If the attachment does not open, use the download link above. The file is an Excel workbook (.xlsx) and works with Microsoft Excel, Google Sheets and LibreOffice.</p>'
        . '<p style="margin:0 0 6px;font-size:14px;line-height:1.65;color:#4A5565;">Need help adapting this template to your organisation? Reply to this email or write to <a href="mailto:contact@privu.tech" style="color:#0092B8;text-decoration:none;">contact@privu.tech</a>.</p>'
        . '<p style="margin:20px 0 0;font-size:14px;line-height:1.65;color:#4A5565;">Regards,<br><strong style="color:#101828;">Team Privu</strong></p>';

    return privu_tdl_email_shell( 'Your ' . $template['title'], $content, 'Your template is attached and ready to download.' );
}
