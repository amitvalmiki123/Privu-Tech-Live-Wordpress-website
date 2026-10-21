<?php
/**
 * Privu – Product Login Lead Gate (Header + Global Footer → Lead Form → cms.privu.tech).
 *
 * Flow:
 *  1. Visitor clicks "Login" in the Header (next to "Get Started") or Global Footer.
 *  2. A Contact Form 7 lead modal ("Privu Product Login") opens on the current page
 *     with Full Name, Work Email, and +91 10-digit Mobile Number.
 *  3. Same strict work-email + [6-9] 10-digit mobile checks run client-side & server-side.
 *  4. Lead details are emailed to eric@privu.tech and contact@privu.tech.
 *  5. Visitor is immediately redirected to https://cms.privu.tech/.
 *
 * @package Privu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRIVU_LOGIN_VERSION', '1.0.0' );
define( 'PRIVU_LOGIN_CF7_TITLE', 'Privu Product Login' );
define( 'PRIVU_LOGIN_REDIRECT_URL', 'https://cms.privu.tech/' );

/**
 * Target product URL after lead form submission (filterable if ever changed).
 *
 * @return string
 */
function privu_login_redirect_url() {
    $url = apply_filters( 'privu_login_redirect_url', PRIVU_LOGIN_REDIRECT_URL );

    return esc_url_raw( $url ? $url : PRIVU_LOGIN_REDIRECT_URL );
}

/* -------------------------------------------------------------------------
 * Contact Form 7 form auto-provisioning ("Privu Product Login")
 * ---------------------------------------------------------------------- */

/**
 * ID of the CF7 form used by the Product Login modal (0 when CF7 is inactive).
 *
 * @return int
 */
function privu_login_cf7_form_id() {
    static $form_id = null;

    if ( null !== $form_id ) {
        return $form_id;
    }

    $form_id = 0;

    if ( defined( 'PRIVU_LOGIN_CF7_FORM_ID' ) && (int) PRIVU_LOGIN_CF7_FORM_ID ) {
        $form_id = (int) PRIVU_LOGIN_CF7_FORM_ID;

        return $form_id;
    }

    $override = (int) apply_filters( 'privu_login_contact_form_id', 0 );

    if ( $override ) {
        $form_id = $override;

        return $form_id;
    }

    if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
        return $form_id;
    }

    $stored = (int) get_option( 'privu_login_cf7_form_id' );

    if ( $stored && 'wpcf7_contact_form' === get_post_type( $stored ) && 'publish' === get_post_status( $stored ) ) {
        $form_id = $stored;

        return $form_id;
    }

    $found = privu_login_cf7_find_form();

    if ( $found ) {
        update_option( 'privu_login_cf7_form_id', $found, false );
        $form_id = $found;

        return $form_id;
    }

    if ( ! get_transient( 'privu_login_cf7_create_lock' ) ) {
        $created = privu_login_cf7_create_form();

        if ( $created ) {
            update_option( 'privu_login_cf7_form_id', $created, false );
            $form_id = $created;
        } else {
            set_transient( 'privu_login_cf7_create_lock', 1, 10 * MINUTE_IN_SECONDS );
        }
    }

    return $form_id;
}

/**
 * Find an existing "Privu Product Login" CF7 form by title.
 *
 * @return int
 */
function privu_login_cf7_find_form() {
    if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
        return 0;
    }

    $query = new WP_Query(
        array(
            'post_type'           => 'wpcf7_contact_form',
            'post_status'         => 'publish',
            'posts_per_page'      => 1,
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'fields'              => 'ids',
            'title'               => PRIVU_LOGIN_CF7_TITLE,
        )
    );

    return $query->posts ? (int) $query->posts[0] : 0;
}

/**
 * Create the "Privu Product Login" CF7 form with valid tag syntax & lead notification mail.
 *
 * Note: All CF7 options (`id:...`, `minlength:...`, `maxlength:...`, `placeholder`)
 * appear strictly before quoted strings so WPCF7_FormTagsManager::parse_atts()
 * and WPCF7 Config Validator pass with zero warnings.
 *
 * @return int
 */
function privu_login_cf7_create_form() {
    if ( ! class_exists( 'WPCF7_ContactForm' ) || ! method_exists( 'WPCF7_ContactForm', 'get_template' ) ) {
        return 0;
    }

    try {
        $form = WPCF7_ContactForm::get_template(
            array(
                'title'  => PRIVU_LOGIN_CF7_TITLE,
                'locale' => 'en_US',
            )
        );

        if ( ! $form || ! method_exists( $form, 'set_properties' ) || ! method_exists( $form, 'save' ) || ! method_exists( $form, 'prop' ) ) {
            return 0;
        }

        $form_markup = '<div class="ptdl-cf7-fields">' . "\n"
            . '<p class="ptdl-field ptdl-field--name">' . "\n"
            . '<label for="plogin-cf7-name">Full name <span aria-hidden="true">*</span></label>' . "\n"
            . '[text* your-name id:plogin-cf7-name maxlength:80 placeholder "e.g. Priya Nair"]' . "\n"
            . '</p>' . "\n"
            . '<p class="ptdl-field ptdl-field--email">' . "\n"
            . '<label for="plogin-cf7-email">Work email <span aria-hidden="true">*</span></label>' . "\n"
            . '[email* your-email id:plogin-cf7-email maxlength:120 placeholder "you@company.com"]' . "\n"
            . '</p>' . "\n"
            . '<p class="ptdl-field ptdl-field--phone">' . "\n"
            . '<label for="plogin-cf7-phone">Phone number <span aria-hidden="true">*</span></label>' . "\n"
            . '[tel* your-phone id:plogin-cf7-phone minlength:10 maxlength:10 placeholder "9876543210"]' . "\n"
            . '<span class="ptdl-hint">10 digits, starting with 6, 7, 8 or 9.</span>' . "\n"
            . '</p>' . "\n"
            . '[hidden privu-source default:Header]' . "\n"
            . '[hidden privu-page-url]' . "\n"
            . '[submit class:ptdl-submit "Continue to Login"]' . "\n"
            . '</div>';

        $mail = $form->prop( 'mail' );

        if ( ! is_array( $mail ) ) {
            $mail = array();
        }

        $mail['active']             = true;
        $mail['recipient']          = 'eric@privu.tech, contact@privu.tech';
        $mail['subject']            = 'New Product Login Lead: [your-name] ([your-email])';
        $mail['sender']             = 'Privu <contact@privu.tech>';
        $mail['additional_headers'] = 'Reply-To: [your-email]';
        $mail['use_html']           = false;
        $mail['exclude_blank']      = true;
        $mail['body']               = "New Product Login lead submission\n\n"
            . "Name: [your-name]\n"
            . "Work email: [your-email]\n"
            . "Phone: +91 [your-phone]\n"
            . "CTA source: [privu-source]\n"
            . "Page: [privu-page-url]\n"
            . "Redirected to: https://cms.privu.tech/\n"
            . "IP address: [_remote_ip]\n"
            . "Date: [_date] [_time]\n";

        $mail_2 = $form->prop( 'mail_2' );

        if ( ! is_array( $mail_2 ) ) {
            $mail_2 = array();
        }

        $mail_2['active'] = false;

        $messages = $form->prop( 'messages' );

        if ( ! is_array( $messages ) ) {
            $messages = array();
        }

        $messages['mail_sent_ok']      = 'Verified! Redirecting to Privu Platform…';
        $messages['mail_sent_ng']      = 'There was an error trying to submit your details. Please try again.';
        $messages['validation_error']  = 'Please check the highlighted fields and try again.';
        $messages['spam']              = 'Too many requests. Please try again in a few minutes.';
        $messages['invalid_required']  = 'Please fill out this field.';
        $messages['invalid_too_long']  = 'This field has too many characters.';
        $messages['invalid_too_short'] = 'This field is too short.';
        $messages['invalid_email']     = 'Please enter a valid work email address.';
        $messages['invalid_tel']       = 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.';

        $form->set_properties(
            array(
                'form'     => $form_markup,
                'mail'     => $mail,
                'mail_2'   => $mail_2,
                'messages' => $messages,
            )
        );

        $id = $form->save();

        return $id ? (int) $id : 0;
    } catch ( Throwable $e ) {
        error_log( '[Privu product login] CF7 form creation failed: ' . $e->getMessage() );

        return 0;
    }
}

/**
 * Ensure the Product Login CF7 form exists on both admin and frontend requests.
 */
function privu_login_cf7_bootstrap() {
    privu_login_cf7_form_id();
}
add_action( 'admin_init', 'privu_login_cf7_bootstrap', 6 );
add_action( 'after_switch_theme', 'privu_login_cf7_bootstrap' );
add_action( 'wp', 'privu_login_cf7_bootstrap', 6 );

/**
 * Is the CF7 form currently being processed the Product Login form?
 *
 * @return bool
 */
function privu_login_cf7_is_our_form() {
    if ( ! function_exists( 'wpcf7_get_current_contact_form' ) ) {
        return false;
    }

    $contact_form = wpcf7_get_current_contact_form();
    $login_id     = (int) privu_login_cf7_form_id();

    return $login_id > 0 && $contact_form && (int) $contact_form->id() === $login_id;
}

/* -------------------------------------------------------------------------
 * CF7 server-side validation & spam protection for Product Login
 * ---------------------------------------------------------------------- */

/**
 * Reject personal / disposable email domains on the Product Login CF7 form.
 *
 * @param WPCF7_Validation $result Validation result.
 * @param WPCF7_FormTag    $tag    Form tag.
 * @return WPCF7_Validation
 */
function privu_login_cf7_validate_work_email( $result, $tag ) {
    if ( ! privu_login_cf7_is_our_form() || 'your-email' !== $tag->name ) {
        return $result;
    }

    $email = isset( $_POST[ $tag->name ] ) && is_string( $_POST[ $tag->name ] )
        ? trim( wp_unslash( $_POST[ $tag->name ] ) )
        : '';

    if ( $email && function_exists( 'privu_tdl_is_personal_email' ) && privu_tdl_is_personal_email( $email ) ) {
        $result->invalidate(
            $tag,
            'Please use your work email. Gmail, Yahoo, Outlook and other personal addresses are not accepted.'
        );
    }

    return $result;
}
add_filter( 'wpcf7_validate_email', 'privu_login_cf7_validate_work_email', 20, 2 );
add_filter( 'wpcf7_validate_email*', 'privu_login_cf7_validate_work_email', 20, 2 );

/**
 * Enforce strict 10-digit [6-9] Indian mobile number on the Product Login CF7 form.
 *
 * @param WPCF7_Validation $result Validation result.
 * @param WPCF7_FormTag    $tag    Form tag.
 * @return WPCF7_Validation
 */
function privu_login_cf7_validate_phone( $result, $tag ) {
    if ( ! privu_login_cf7_is_our_form() || 'your-phone' !== $tag->name ) {
        return $result;
    }

    $phone = isset( $_POST[ $tag->name ] ) && is_string( $_POST[ $tag->name ] )
        ? trim( wp_unslash( $_POST[ $tag->name ] ) )
        : '';

    if ( $phone && function_exists( 'privu_tdl_normalize_phone' ) && '' === privu_tdl_normalize_phone( $phone ) ) {
        $result->invalidate(
            $tag,
            'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.'
        );
    }

    return $result;
}
add_filter( 'wpcf7_validate_tel', 'privu_login_cf7_validate_phone', 20, 2 );
add_filter( 'wpcf7_validate_tel*', 'privu_login_cf7_validate_phone', 20, 2 );

/**
 * Add HTML5 numeric/pattern attributes to the Product Login CF7 phone input.
 *
 * @param string $html Rendered form elements HTML.
 * @return string
 */
function privu_login_cf7_tune_phone_input( $html ) {
    if ( ! privu_login_cf7_is_our_form() ) {
        return $html;
    }

    return preg_replace_callback(
        '/<input\b[^>]*\bname=(["\'])your-phone\1[^>]*>/i',
        function ( $matches ) {
            $tag = $matches[0];
            $tag = preg_replace( '/\s*\b(maxlength|minlength|inputmode|pattern|placeholder)=(["\'])[^"\']*\2/i', '', $tag );
            $tag = preg_replace(
                '/\s*\/?>$/',
                ' minlength="10" maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}" placeholder="9876543210">',
                $tag
            );
            return $tag;
        },
        $html
    );
}
add_filter( 'wpcf7_form_elements', 'privu_login_cf7_tune_phone_input' );

/**
 * Light per-IP rate limit on Product Login CF7 submissions.
 *
 * @param bool $spam Current spam flag.
 * @return bool
 */
function privu_login_cf7_rate_limit( $spam ) {
    if ( $spam || ! privu_login_cf7_is_our_form() ) {
        return $spam;
    }

    $ip       = function_exists( 'privu_tdl_client_ip' ) ? privu_tdl_client_ip() : '0.0.0.0';
    $rate_key = 'privu_login_rl_' . md5( $ip );
    $hits     = (int) get_transient( $rate_key );

    if ( $hits >= 15 ) {
        return true;
    }

    set_transient( $rate_key, $hits + 1, 15 * MINUTE_IN_SECONDS );

    return false;
}
add_filter( 'wpcf7_spam', 'privu_login_cf7_rate_limit', 20 );

/* -------------------------------------------------------------------------
 * Clean /product-login/ URL support + Global Footer Quick Links injection
 * ---------------------------------------------------------------------- */

/**
 * Handle clean direct URL /product-login/ by redirecting to the home page with the login modal open.
 */
function privu_login_handle_clean_url() {
    if ( is_admin() || empty( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }

    $req_path  = trim( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ), '/' );
    $home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

    if ( '' !== $home_path && 0 === strpos( $req_path, $home_path . '/' ) ) {
        $req_path = substr( $req_path, strlen( $home_path ) + 1 );
    }

    if ( 'product-login' !== $req_path ) {
        return;
    }

    wp_safe_redirect(
        add_query_arg( 'privu_login', '1', home_url( '/' ) ),
        302
    );
    exit;
}
add_action( 'template_redirect', 'privu_login_handle_clean_url', 1 );

/**
 * Track when WordPress is rendering the Global Footer widget area (`sidebar-footer`).
 *
 * @param string $index Sidebar ID.
 */
function privu_login_footer_sidebar_start( $index ) {
    if ( 'sidebar-footer' === $index ) {
        $GLOBALS['privu_in_footer_sidebar']      = true;
        $GLOBALS['privu_footer_login_injected']  = false;
    }
}
add_action( 'dynamic_sidebar_before', 'privu_login_footer_sidebar_start' );

/**
 * Reset sidebar tracking after `sidebar-footer` finishes rendering.
 *
 * @param string $index Sidebar ID.
 */
function privu_login_footer_sidebar_end( $index ) {
    if ( 'sidebar-footer' === $index ) {
        $GLOBALS['privu_in_footer_sidebar'] = false;
    }
}
add_action( 'dynamic_sidebar_after', 'privu_login_footer_sidebar_end' );

/**
 * Append a "Login" link to the first Navigation Menu widget inside Global Footer (`sidebar-footer`).
 *
 * @param string $items HTML list items.
 * @return string
 */
function privu_login_inject_footer_menu_item( $items ) {
    if ( empty( $GLOBALS['privu_in_footer_sidebar'] ) || ! empty( $GLOBALS['privu_footer_login_injected'] ) ) {
        return $items;
    }

    $GLOBALS['privu_footer_login_injected'] = true;

    if ( false !== stripos( $items, '>Login<' ) || false !== stripos( $items, 'data-privu-login-trigger' ) ) {
        return $items;
    }

    $items .= '<li class="menu-item privu-footer-login-item">'
        . '<a href="' . esc_url( home_url( '/product-login/' ) ) . '" data-privu-login-trigger="Footer Quick Links">Login</a>'
        . '</li>';

    return $items;
}
add_filter( 'wp_nav_menu_items', 'privu_login_inject_footer_menu_item', 20, 1 );

/* -------------------------------------------------------------------------
 * Global frontend assets + Product Login modal markup
 * ---------------------------------------------------------------------- */

/**
 * Enqueue header/footer Login CTA styles + Product Login modal script on all frontend pages.
 */
function privu_login_enqueue_assets() {
    if ( is_admin() ) {
        return;
    }

    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    $modal_css_rel = '/css/privu-template-downloads.css';
    $login_css_rel = '/css/privu-product-login.css';
    $login_js_rel  = '/js/privu-product-login.js';

    $modal_css_ver = file_exists( $dir . $modal_css_rel ) ? filemtime( $dir . $modal_css_rel ) : PRIVU_LOGIN_VERSION;
    $login_css_ver = file_exists( $dir . $login_css_rel ) ? filemtime( $dir . $login_css_rel ) : PRIVU_LOGIN_VERSION;
    $login_js_ver  = file_exists( $dir . $login_js_rel ) ? filemtime( $dir . $login_js_rel ) : PRIVU_LOGIN_VERSION;

    wp_enqueue_style( 'privu-tdl', $uri . $modal_css_rel, array(), $modal_css_ver );
    wp_enqueue_style( 'privu-product-login', $uri . $login_css_rel, array( 'privu-tdl' ), $login_css_ver );
    wp_enqueue_script( 'privu-product-login', $uri . $login_js_rel, array(), $login_js_ver, true );

    $cf7_form_id = (int) privu_login_cf7_form_id();

    if ( $cf7_form_id ) {
        if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
            wpcf7_enqueue_styles();
        }

        if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
            wpcf7_enqueue_scripts();
        }
    }

    $blocked = function_exists( 'privu_tdl_blocked_email_domains' ) ? privu_tdl_blocked_email_domains() : array();

    wp_localize_script(
        'privu-product-login',
        'PrivuProductLogin',
        array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'loginUrl'    => home_url( '/product-login/' ),
            'redirectUrl' => privu_login_redirect_url(),
            'mode'        => $cf7_form_id ? 'cf7' : 'native',
            'formId'      => $cf7_form_id,
            'blocked'     => $blocked,
            'i18n'        => array(
                'submit'       => 'Continue to Login',
                'submitting'   => 'Verifying…',
                'nameError'    => 'Please enter your full name.',
                'emailError'   => 'Please enter a valid work email address.',
                'personal'     => 'Please use your work email. Gmail, Yahoo, Outlook and other personal addresses are not accepted.',
                'phoneError'   => 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.',
                'genericError' => 'Something went wrong. Please try again.',
                'networkError' => 'Network error. Please check your connection and try again.',
            ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'privu_login_enqueue_assets', 22 );

/**
 * Prevent JS delay/minification plugins from breaking instant click handling on the Login button.
 *
 * @param string $tag    Script HTML tag.
 * @param string $handle Script handle.
 * @return string
 */
function privu_login_script_attributes( $tag, $handle ) {
    if ( 'privu-product-login' !== $handle ) {
        return $tag;
    }

    return str_replace(
        '<script ',
        '<script data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false" ',
        $tag
    );
}
add_filter( 'script_loader_tag', 'privu_login_script_attributes', 10, 2 );

/**
 * Render the Product Login modal in the global site footer.
 */
function privu_login_print_modal() {
    if ( is_admin() ) {
        return;
    }

    $cf7_form_id  = (int) privu_login_cf7_form_id();
    $rendered_cf7 = '';
    $cf7_mode     = 'native';
    $target_url   = privu_login_redirect_url();

    if ( $cf7_form_id ) {
        $rendered_cf7 = do_shortcode( '[contact-form-7 id="' . $cf7_form_id . '" title="Privu Product Login"]' );

        if ( '' !== trim( strip_tags( $rendered_cf7 ) ) && ! preg_match( '/\[(text|email|tel|hidden|submit)[* ]/', $rendered_cf7 ) ) {
            $cf7_mode = 'cf7';
        }
    }
    ?>
    <div class="ptdl-overlay" id="privu-login-modal" hidden data-privu-login-mode="<?php echo esc_attr( $cf7_mode ); ?>">
        <div class="ptdl-modal" role="dialog" aria-modal="true" aria-labelledby="plogin-title" aria-describedby="plogin-subtitle">
            <button type="button" class="ptdl-close" data-plogin-close aria-label="Close login dialog">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
                    <path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="ptdl-modal__head">
                <span class="ptdl-badge">Product Login</span>
                <h2 class="ptdl-title" id="plogin-title">Log in to Privu Platform</h2>
                <p class="ptdl-subtitle" id="plogin-subtitle">Enter your work details below to proceed directly to the Privu compliance portal.</p>
            </div>

            <?php if ( 'cf7' === $cf7_mode ) : ?>
                <div class="ptdl-cf7-wrap" data-plogin-form-wrap>
                    <?php echo $rendered_cf7; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered CF7 form markup. ?>
                </div>
                <p class="ptdl-privacy" data-plogin-privacy>You will be redirected to <strong>cms.privu.tech</strong> right after verification.</p>
            <?php else : ?>
                <form class="ptdl-form" data-plogin-native-form novalidate>
                    <div class="ptdl-field">
                        <label for="plogin-name">Full name <span aria-hidden="true">*</span></label>
                        <input type="text" id="plogin-name" name="name" autocomplete="name" placeholder="e.g. Priya Nair" maxlength="80" required>
                        <p class="ptdl-error" data-ptdl-error="name" role="alert"></p>
                    </div>

                    <div class="ptdl-field">
                        <label for="plogin-email">Work email <span aria-hidden="true">*</span></label>
                        <input type="email" id="plogin-email" name="email" autocomplete="email" placeholder="you@company.com" maxlength="120" required>
                        <p class="ptdl-error" data-ptdl-error="email" role="alert"></p>
                    </div>

                    <div class="ptdl-field">
                        <label for="plogin-phone">Phone number <span aria-hidden="true">*</span></label>
                        <div class="ptdl-phone-wrap">
                            <span class="ptdl-phone-prefix" aria-hidden="true">+91</span>
                            <input type="tel" id="plogin-phone" name="phone" inputmode="numeric" autocomplete="tel" placeholder="9876543210" minlength="10" maxlength="10" pattern="[6-9][0-9]{9}" required>
                        </div>
                        <p class="ptdl-hint">10 digits, starting with 6, 7, 8 or 9.</p>
                        <p class="ptdl-error" data-ptdl-error="phone" role="alert"></p>
                    </div>

                    <div class="ptdl-hp" aria-hidden="true">
                        <label for="plogin-website">Website</label>
                        <input type="text" id="plogin-website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <button type="submit" class="ptdl-submit" data-plogin-submit>
                        <span class="ptdl-spinner" aria-hidden="true"></span>
                        <span class="ptdl-submit__label">Continue to Login</span>
                    </button>

                    <p class="ptdl-form-error" data-plogin-form-error role="alert"></p>
                    <p class="ptdl-privacy" data-plogin-privacy>You will be redirected to <strong>cms.privu.tech</strong> right after verification.</p>
                </form>
            <?php endif; ?>

            <div class="ptdl-success plogin-redirect-state" data-plogin-redirect hidden>
                <div class="ptdl-check" aria-hidden="true">
                    <span class="plogin-redirect-spinner"></span>
                </div>
                <h3 class="ptdl-success__title">Redirecting to Privu Platform…</h3>
                <p class="ptdl-success__text">Taking you to <strong>cms.privu.tech</strong>. If you are not redirected automatically, click below.</p>
                <a class="ptdl-download" href="<?php echo esc_url( $target_url ); ?>" data-plogin-continue>
                    <span>Continue to cms.privu.tech &rarr;</span>
                </a>
            </div>

            <p class="ptdl-foot">Privu &middot; Compliance Platform Portal</p>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'privu_login_print_modal', 6 );

/* -------------------------------------------------------------------------
 * Native AJAX fallback (used only when Contact Form 7 is not active)
 * ---------------------------------------------------------------------- */

/**
 * Runtime nonce for cached pages (native fallback mode).
 */
function privu_login_ajax_nonce() {
    wp_send_json_success(
        array(
            'nonce' => wp_create_nonce( 'privu_login_submit' ),
        )
    );
}
add_action( 'wp_ajax_privu_login_nonce', 'privu_login_ajax_nonce' );
add_action( 'wp_ajax_nopriv_privu_login_nonce', 'privu_login_ajax_nonce' );

/**
 * Validate lead details, notify admins, and return redirect URL (native fallback mode).
 */
function privu_login_ajax_submit() {
    if ( ! check_ajax_referer( 'privu_login_submit', 'nonce', false ) ) {
        wp_send_json_error(
            array(
                'code'    => 'bad_nonce',
                'message' => 'Your session expired. Please try again.',
            ),
            403
        );
    }

    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_error(
            array( 'message' => 'Something went wrong. Please try again.' ),
            400
        );
    }

    $name_raw  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $email_raw = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $phone_raw = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $source    = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'Website';
    $page_url  = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : home_url( '/' );

    $errors = array();
    $name   = trim( preg_replace( '/\s+/', ' ', $name_raw ) );

    if ( strlen( $name ) < 2 || strlen( $name ) > 80 || ! preg_match( '/\p{L}/u', $name ) || preg_match( '/[<>]|https?:/i', $name ) ) {
        $errors['name'] = 'Please enter your full name.';
    }

    $email = strtolower( trim( $email_raw ) );

    if ( ! is_email( $email ) ) {
        $errors['email'] = 'Please enter a valid work email address.';
    } elseif ( function_exists( 'privu_tdl_is_personal_email' ) && privu_tdl_is_personal_email( $email ) ) {
        $errors['email'] = 'Please use your work email. Gmail, Yahoo, Outlook and other personal addresses are not accepted.';
    }

    $phone = function_exists( 'privu_tdl_normalize_phone' ) ? privu_tdl_normalize_phone( $phone_raw ) : '';

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

    $ip       = function_exists( 'privu_tdl_client_ip' ) ? privu_tdl_client_ip() : '0.0.0.0';
    $rate_key = 'privu_login_rl_' . md5( $ip );
    $hits     = (int) get_transient( $rate_key );

    if ( $hits >= 15 ) {
        wp_send_json_error(
            array( 'message' => 'Too many requests. Please try again in a few minutes.' ),
            429
        );
    }

    set_transient( $rate_key, $hits + 1, 15 * MINUTE_IN_SECONDS );

    $recipients = apply_filters(
        'privu_login_admin_recipients',
        array( 'eric@privu.tech', 'contact@privu.tech' )
    );

    $subject = sprintf( 'New Product Login Lead: %s (%s)', $name, $email );
    $body    = "New Product Login lead submission\n\n"
        . 'Name: ' . $name . "\n"
        . 'Work email: ' . $email . "\n"
        . 'Phone: +91 ' . $phone . "\n"
        . 'CTA source: ' . $source . "\n"
        . 'Page: ' . $page_url . "\n"
        . 'Redirected to: ' . privu_login_redirect_url() . "\n"
        . 'IP address: ' . $ip . "\n"
        . 'Date: ' . wp_date( 'd M Y, h:i A T' ) . "\n";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $email,
    );

    wp_mail( $recipients, $subject, $body, $headers );

    wp_send_json_success(
        array(
            'redirect_url' => privu_login_redirect_url(),
        )
    );
}
add_action( 'wp_ajax_privu_login_submit', 'privu_login_ajax_submit' );
add_action( 'wp_ajax_nopriv_privu_login_submit', 'privu_login_ajax_submit' );
