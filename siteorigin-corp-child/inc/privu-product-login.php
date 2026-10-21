<?php
/**
 * Privu – Product Login CTA + ACF Options Screen ("Product Login") + CF7 Lead Gate.
 *
 * Everything is editable directly in WP Admin -> Product Login (ACF Options, with a native
 * WordPress admin fallback when ACF PRO is inactive), pre-filled out of the box:
 *  - Button text ("Login")
 *  - Destination / Redirect URL ("https://cms.privu.tech/")
 *  - Require Lead Form before redirect (ON by default; OFF links directly to Destination URL)
 *  - Popup CF7 form shortcode (auto-filled with [contact-form-7 id="..." title="Privu Product Login"])
 *  - Popup heading & subtitle
 *
 * @package Privu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRIVU_LOGIN_VERSION', '1.2.1' );
define( 'PRIVU_LOGIN_CF7_TITLE', 'Privu Product Login' );
define( 'PRIVU_LOGIN_DEFAULT_BUTTON_TEXT', 'Login' );
define( 'PRIVU_LOGIN_REDIRECT_URL', 'https://cms.privu.tech/' );
define( 'PRIVU_LOGIN_DEFAULT_MODAL_EYEBROW', 'Product Login' );
define( 'PRIVU_LOGIN_DEFAULT_MODAL_TITLE', 'Log in to Privu Platform' );
define( 'PRIVU_LOGIN_DEFAULT_MODAL_SUBTITLE', 'Enter your work details below to proceed directly to the Privu compliance portal.' );
define( 'PRIVU_LOGIN_DEFAULT_MODAL_FOOTER', 'Privu · Compliance Platform Portal' );

/* -------------------------------------------------------------------------
 * Editable settings getters (ACF Options / WP options -> pre-filled defaults)
 * ---------------------------------------------------------------------- */

/**
 * Header & Global Footer button label.
 *
 * @return string
 */
function privu_login_button_text() {
    $raw = get_option( 'options_privu_login_button_text', null );

    if ( null === $raw || '' === trim( (string) $raw ) ) {
        $text = PRIVU_LOGIN_DEFAULT_BUTTON_TEXT;
    } else {
        $text = trim( (string) $raw );
    }

    return (string) apply_filters( 'privu_login_button_text', $text );
}

/**
 * Target product URL after form submission (or directly on click when gate is disabled).
 *
 * @return string
 */
function privu_login_redirect_url() {
    $raw = get_option( 'options_privu_login_redirect_url', null );
    $url = ( null === $raw || '' === trim( (string) $raw ) ) ? PRIVU_LOGIN_REDIRECT_URL : trim( (string) $raw );
    $url = apply_filters( 'privu_login_redirect_url', $url );

    return esc_url_raw( $url ? $url : PRIVU_LOGIN_REDIRECT_URL );
}

/**
 * Whether clicking Login opens the Lead Form popup first (true) or opens Destination URL directly (false).
 *
 * @return bool
 */
function privu_login_is_gate_enabled() {
    $raw     = get_option( 'options_privu_login_enable_gate', null );
    $enabled = ( null === $raw || '' === (string) $raw ) ? true : ( '0' !== (string) $raw && false !== $raw );

    return (bool) apply_filters( 'privu_login_enable_gate', $enabled );
}

/**
 * Href output on Header & Global Footer Login links.
 *
 * @return string
 */
function privu_login_button_href() {
    return privu_login_is_gate_enabled() ? home_url( '/product-login/' ) : privu_login_redirect_url();
}

/**
 * Popup modal eyebrow badge text.
 *
 * @return string
 */
function privu_login_modal_eyebrow() {
    $raw = get_option( 'options_privu_login_modal_eyebrow', null );

    if ( null === $raw ) {
        return PRIVU_LOGIN_DEFAULT_MODAL_EYEBROW;
    }

    return trim( (string) $raw );
}

/**
 * Popup modal heading text.
 *
 * @return string
 */
function privu_login_modal_title() {
    $raw = get_option( 'options_privu_login_modal_title', null );

    if ( null === $raw || '' === trim( (string) $raw ) ) {
        return PRIVU_LOGIN_DEFAULT_MODAL_TITLE;
    }

    return trim( (string) $raw );
}

/**
 * Popup modal subtitle text.
 *
 * @return string
 */
function privu_login_modal_subtitle() {
    $raw = get_option( 'options_privu_login_modal_subtitle', null );

    if ( null === $raw ) {
        return PRIVU_LOGIN_DEFAULT_MODAL_SUBTITLE;
    }

    return trim( (string) $raw );
}

/**
 * Popup modal bottom footer text.
 *
 * @return string
 */
function privu_login_modal_footer() {
    $raw = get_option( 'options_privu_login_modal_footer', null );

    if ( null === $raw ) {
        return PRIVU_LOGIN_DEFAULT_MODAL_FOOTER;
    }

    return trim( (string) $raw );
}

/* -------------------------------------------------------------------------
 * Contact Form 7 form auto-provisioning & custom shortcode resolution
 * ---------------------------------------------------------------------- */

/**
 * Auto-created / stored default CF7 form ID before any custom shortcode override in ACF.
 *
 * @return int
 */
function privu_login_cf7_auto_form_id() {
    static $auto_id = null;

    if ( null !== $auto_id ) {
        return $auto_id;
    }

    $auto_id = 0;

    if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
        return $auto_id;
    }

    $stored = (int) get_option( 'privu_login_cf7_form_id' );

    if ( $stored && 'wpcf7_contact_form' === get_post_type( $stored ) && 'publish' === get_post_status( $stored ) ) {
        privu_login_cf7_maybe_clean_hint( $stored );
        $auto_id = $stored;

        return $auto_id;
    }

    $found = privu_login_cf7_find_form();

    if ( $found ) {
        privu_login_cf7_maybe_clean_hint( $found );
        update_option( 'privu_login_cf7_form_id', $found, false );
        $auto_id = $found;

        return $auto_id;
    }

    if ( ! get_transient( 'privu_login_cf7_create_lock' ) ) {
        $created = privu_login_cf7_create_form();

        if ( $created ) {
            update_option( 'privu_login_cf7_form_id', $created, false );
            $auto_id = $created;
        } else {
            set_transient( 'privu_login_cf7_create_lock', 1, 10 * MINUTE_IN_SECONDS );
        }
    }

    return $auto_id;
}

/**
 * Active [contact-form-7 ...] shortcode rendered inside the Product Login modal.
 *
 * Reads the editable ACF field first; falls back to the auto-generated "Privu Product Login" form.
 *
 * @return string
 */
function privu_login_cf7_shortcode() {
    $saved = trim( (string) get_option( 'options_privu_login_cf7_shortcode', '' ) );

    if ( '' !== $saved && false !== stripos( $saved, 'contact-form-7' ) ) {
        return $saved;
    }

    $auto_id = privu_login_cf7_auto_form_id();

    if ( ! $auto_id ) {
        return '';
    }

    if ( function_exists( 'privu_tdl_format_cf7_shortcode' ) ) {
        return privu_tdl_format_cf7_shortcode( $auto_id, PRIVU_LOGIN_CF7_TITLE );
    }

    return sprintf( '[contact-form-7 id="%d" title="%s"]', $auto_id, esc_attr( PRIVU_LOGIN_CF7_TITLE ) );
}

/**
 * Resolved post ID of whichever CF7 form is active in the Product Login popup.
 *
 * Supports numeric IDs, CF7 5.8+ hash shortcodes (e.g. [contact-form-7 id="41d9575" title="..."]),
 * and auto-provisioned defaults so validation + spam protection bind automatically.
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

    $custom_sc = trim( (string) get_option( 'options_privu_login_cf7_shortcode', '' ) );

    if ( '' !== $custom_sc && function_exists( 'privu_tdl_resolve_cf7_shortcode_id' ) ) {
        $resolved_custom = privu_tdl_resolve_cf7_shortcode_id( $custom_sc );

        if ( $resolved_custom > 0 ) {
            privu_login_cf7_maybe_clean_hint( $resolved_custom );
            $form_id = $resolved_custom;

            return $form_id;
        }
    }

    $form_id = privu_login_cf7_auto_form_id();

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
 * Remove the legacy phone subtext hint span from an already-created "Privu Product Login" CF7 form.
 *
 * @param int $id Contact form post ID.
 */
function privu_login_cf7_maybe_clean_hint( $id ) {
    if ( ! $id || ! method_exists( 'WPCF7_ContactForm', 'get_instance' ) ) {
        return;
    }

    try {
        $form = WPCF7_ContactForm::get_instance( $id );

        if ( ! $form || ! method_exists( $form, 'prop' ) || ! method_exists( $form, 'set_properties' ) || ! method_exists( $form, 'save' ) ) {
            return;
        }

        $markup = (string) $form->prop( 'form' );

        if ( false === strpos( $markup, 'ptdl-hint' ) ) {
            return;
        }

        $cleaned = preg_replace( '/\s*<span\s+class=(["\'])ptdl-hint\1>[^<]*<\/span>/i', '', $markup );

        if ( is_string( $cleaned ) && $cleaned !== $markup ) {
            $form->set_properties( array( 'form' => $cleaned ) );
            $form->save();
        }
    } catch ( Throwable $e ) {
        error_log( '[Privu product login] CF7 hint cleanup skipped: ' . $e->getMessage() );
    }
}

/**
 * Create the "Privu Product Login" CF7 form with valid tag syntax & lead notification mail.
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
            . 'Redirected to: ' . privu_login_redirect_url() . "\n"
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
    privu_login_cf7_auto_form_id();
}
add_action( 'admin_init', 'privu_login_cf7_bootstrap', 6 );
add_action( 'after_switch_theme', 'privu_login_cf7_bootstrap' );
add_action( 'wp', 'privu_login_cf7_bootstrap', 6 );

/* -------------------------------------------------------------------------
 * WP Admin -> Product Login (ACF Options Page + pre-filled fields + fallback)
 * ---------------------------------------------------------------------- */

/**
 * Register the "Product Login" ACF Options page and pre-filled fields.
 */
function privu_login_acf_register() {
    if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_options_page(
        array(
            'page_title' => 'Product Login',
            'menu_title' => 'Product Login',
            'menu_slug'  => 'privu-product-login',
            'capability' => 'manage_options',
            'icon_url'   => 'dashicons-lock',
            'position'   => '58.7',
            'redirect'   => false,
            'autoload'   => true,
        )
    );

    acf_add_local_field_group(
        array(
            'key'      => 'group_privu_product_login',
            'title'    => 'Product Login CTA & Lead Form Settings',
            'fields'   => array(
                array(
                    'key'      => 'field_privu_login_message',
                    'label'    => '',
                    'name'     => '',
                    'type'     => 'message',
                    'message'  => '<p style="margin:0;line-height:1.6">'
                        . 'Manage the <strong>Login</strong> button shown in the Header and Global Footer, its destination URL, and the Contact Form 7 shortcode rendered inside the popup.'
                        . '</p>',
                    'esc_html' => 0,
                ),
                array(
                    'key'           => 'field_privu_login_button_text',
                    'label'         => 'Button text',
                    'name'          => 'privu_login_button_text',
                    'type'          => 'text',
                    'default_value' => PRIVU_LOGIN_DEFAULT_BUTTON_TEXT,
                    'placeholder'   => 'Login',
                    'wrapper'       => array( 'width' => '34' ),
                    'instructions'  => 'Label shown on Header and Global Footer buttons.',
                ),
                array(
                    'key'           => 'field_privu_login_redirect_url',
                    'label'         => 'Destination / Redirect URL',
                    'name'          => 'privu_login_redirect_url',
                    'type'          => 'url',
                    'default_value' => PRIVU_LOGIN_REDIRECT_URL,
                    'placeholder'   => 'https://cms.privu.tech/',
                    'wrapper'       => array( 'width' => '66' ),
                    'instructions'  => 'URL opened immediately after lead form submission (or directly on click if the form gate is turned off).',
                ),
                array(
                    'key'           => 'field_privu_login_enable_gate',
                    'label'         => 'Require Lead Form before redirect',
                    'name'          => 'privu_login_enable_gate',
                    'type'          => 'true_false',
                    'ui'            => 1,
                    'ui_on_text'    => 'Yes',
                    'ui_off_text'   => 'No',
                    'default_value' => 1,
                    'instructions'  => 'Turn off to link the Header and Footer Login buttons directly to the Destination URL without opening a popup.',
                ),
                array(
                    'key'          => 'field_privu_login_cf7_shortcode',
                    'label'        => 'Popup CF7 form shortcode',
                    'name'         => 'privu_login_cf7_shortcode',
                    'type'         => 'text',
                    'placeholder'  => '[contact-form-7 id="1335" title="Privu Product Login"]',
                    'instructions' => 'Contact Form 7 shortcode rendered inside the Login popup. Paste any CF7 shortcode from Contact -> Contact Forms.',
                ),
                array(
                    'key'           => 'field_privu_login_modal_eyebrow',
                    'label'         => 'Popup eyebrow',
                    'name'          => 'privu_login_modal_eyebrow',
                    'type'          => 'text',
                    'default_value' => PRIVU_LOGIN_DEFAULT_MODAL_EYEBROW,
                    'wrapper'       => array( 'width' => '34' ),
                    'instructions'  => 'Small pill badge shown above the popup heading.',
                ),
                array(
                    'key'           => 'field_privu_login_modal_title',
                    'label'         => 'Popup heading',
                    'name'          => 'privu_login_modal_title',
                    'type'          => 'text',
                    'default_value' => PRIVU_LOGIN_DEFAULT_MODAL_TITLE,
                    'wrapper'       => array( 'width' => '66' ),
                    'instructions'  => 'Main heading shown at the top of the Login popup.',
                ),
                array(
                    'key'           => 'field_privu_login_modal_subtitle',
                    'label'         => 'Popup subtitle',
                    'name'          => 'privu_login_modal_subtitle',
                    'type'          => 'text',
                    'default_value' => PRIVU_LOGIN_DEFAULT_MODAL_SUBTITLE,
                    'wrapper'       => array( 'width' => '60' ),
                    'instructions'  => 'Short line displayed directly under the popup heading.',
                ),
                array(
                    'key'           => 'field_privu_login_modal_footer',
                    'label'         => 'Popup footer text',
                    'name'          => 'privu_login_modal_footer',
                    'type'          => 'text',
                    'default_value' => PRIVU_LOGIN_DEFAULT_MODAL_FOOTER,
                    'wrapper'       => array( 'width' => '40' ),
                    'instructions'  => 'Bottom line shown at the base of the Login popup.',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'privu-product-login',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
            'show_in_rest'          => 0,
        )
    );
}
add_action( 'acf/init', 'privu_login_acf_register' );

/**
 * Seed default values into options table once so every ACF field is pre-populated immediately.
 */
function privu_login_acf_seed() {
    if ( null === get_option( 'options_privu_login_button_text', null ) ) {
        update_option( 'options_privu_login_button_text', PRIVU_LOGIN_DEFAULT_BUTTON_TEXT, true );
    }

    if ( null === get_option( 'options_privu_login_redirect_url', null ) ) {
        update_option( 'options_privu_login_redirect_url', PRIVU_LOGIN_REDIRECT_URL, true );
    }

    if ( null === get_option( 'options_privu_login_enable_gate', null ) ) {
        update_option( 'options_privu_login_enable_gate', '1', true );
    }

    if ( null === get_option( 'options_privu_login_modal_eyebrow', null ) ) {
        update_option( 'options_privu_login_modal_eyebrow', PRIVU_LOGIN_DEFAULT_MODAL_EYEBROW, true );
    }

    if ( null === get_option( 'options_privu_login_modal_title', null ) ) {
        update_option( 'options_privu_login_modal_title', PRIVU_LOGIN_DEFAULT_MODAL_TITLE, true );
    }

    if ( null === get_option( 'options_privu_login_modal_subtitle', null ) ) {
        update_option( 'options_privu_login_modal_subtitle', PRIVU_LOGIN_DEFAULT_MODAL_SUBTITLE, true );
    }

    if ( null === get_option( 'options_privu_login_modal_footer', null ) ) {
        update_option( 'options_privu_login_modal_footer', PRIVU_LOGIN_DEFAULT_MODAL_FOOTER, true );
    }

    $current_sc = trim( (string) get_option( 'options_privu_login_cf7_shortcode', '' ) );

    if ( '' === $current_sc ) {
        $auto_sc = privu_login_cf7_shortcode();

        if ( '' !== $auto_sc ) {
            update_option( 'options_privu_login_cf7_shortcode', $auto_sc, true );
        }
    }
}
add_action( 'admin_init', 'privu_login_acf_seed', 12 );

/**
 * Guarantee the live CF7 shortcode displays inside the ACF field even before saving.
 *
 * @param mixed $value Current field value.
 * @return mixed
 */
function privu_login_acf_load_shortcode_value( $value ) {
    if ( is_string( $value ) && '' !== trim( $value ) ) {
        return $value;
    }

    return privu_login_cf7_shortcode();
}
add_filter( 'acf/load_value/key=field_privu_login_cf7_shortcode', 'privu_login_acf_load_shortcode_value' );

/**
 * Show a direct "Edit Form in Contact Form 7" link at the top of Admin -> Product Login.
 *
 * @param array $field ACF message field definition.
 * @return array
 */
function privu_login_acf_message_field( $field ) {
    if ( ! isset( $field['key'] ) || 'field_privu_login_message' !== $field['key'] ) {
        return $field;
    }

    $form_id = (int) privu_login_cf7_form_id();

    if ( $form_id > 0 ) {
        $form_title = get_the_title( $form_id );
        $form_title = $form_title ? $form_title : PRIVU_LOGIN_CF7_TITLE;
        $field['message'] .= '<p style="margin:4px 0 0;line-height:1.6;color:#50575e">'
            . 'Active CF7 form: <a href="' . esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $form_id . '&action=edit' ) ) . '">Edit “' . esc_html( $form_title ) . '” (#' . $form_id . ')</a>.'
            . '</p>';
    }

    return $field;
}
add_filter( 'acf/load_field/key=field_privu_login_message', 'privu_login_acf_message_field' );

/**
 * Native WordPress fallback screen when ACF PRO is not active.
 */
function privu_login_fallback_admin_menu() {
    if ( function_exists( 'acf_add_options_page' ) ) {
        return;
    }

    add_menu_page(
        'Product Login',
        'Product Login',
        'manage_options',
        'privu-product-login',
        'privu_login_fallback_admin_page',
        'dashicons-lock',
        58.7
    );
}
add_action( 'admin_menu', 'privu_login_fallback_admin_menu' );

/**
 * Render fallback admin settings page when ACF PRO is inactive.
 */
function privu_login_fallback_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $saved = false;

    if ( isset( $_POST['privu_login_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['privu_login_settings_nonce'] ) ), 'privu_login_save_settings' ) ) {
        $btn_text  = isset( $_POST['privu_login_button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['privu_login_button_text'] ) ) : PRIVU_LOGIN_DEFAULT_BUTTON_TEXT;
        $redir_url = isset( $_POST['privu_login_redirect_url'] ) ? esc_url_raw( wp_unslash( $_POST['privu_login_redirect_url'] ) ) : PRIVU_LOGIN_REDIRECT_URL;
        $gate_on   = ! empty( $_POST['privu_login_enable_gate'] ) ? '1' : '0';
        $shortcode = isset( $_POST['privu_login_cf7_shortcode'] ) ? sanitize_text_field( wp_unslash( $_POST['privu_login_cf7_shortcode'] ) ) : '';
        $m_eye     = isset( $_POST['privu_login_modal_eyebrow'] ) ? sanitize_text_field( wp_unslash( $_POST['privu_login_modal_eyebrow'] ) ) : PRIVU_LOGIN_DEFAULT_MODAL_EYEBROW;
        $m_title   = isset( $_POST['privu_login_modal_title'] ) ? sanitize_text_field( wp_unslash( $_POST['privu_login_modal_title'] ) ) : PRIVU_LOGIN_DEFAULT_MODAL_TITLE;
        $m_sub     = isset( $_POST['privu_login_modal_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['privu_login_modal_subtitle'] ) ) : PRIVU_LOGIN_DEFAULT_MODAL_SUBTITLE;
        $m_foot    = isset( $_POST['privu_login_modal_footer'] ) ? sanitize_text_field( wp_unslash( $_POST['privu_login_modal_footer'] ) ) : PRIVU_LOGIN_DEFAULT_MODAL_FOOTER;

        update_option( 'options_privu_login_button_text', $btn_text ? $btn_text : PRIVU_LOGIN_DEFAULT_BUTTON_TEXT, true );
        update_option( 'options_privu_login_redirect_url', $redir_url ? $redir_url : PRIVU_LOGIN_REDIRECT_URL, true );
        update_option( 'options_privu_login_enable_gate', $gate_on, true );
        update_option( 'options_privu_login_cf7_shortcode', $shortcode, true );
        update_option( 'options_privu_login_modal_eyebrow', $m_eye, true );
        update_option( 'options_privu_login_modal_title', $m_title ? $m_title : PRIVU_LOGIN_DEFAULT_MODAL_TITLE, true );
        update_option( 'options_privu_login_modal_subtitle', $m_sub, true );
        update_option( 'options_privu_login_modal_footer', $m_foot, true );
        $saved = true;
    }

    $form_id = (int) privu_login_cf7_form_id();
    ?>
    <div class="wrap">
        <h1>Product Login</h1>
        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Settings saved.</strong> Remember to purge the page cache after changes.</p></div>
        <?php endif; ?>
        <p>Manage the <strong>Login</strong> button shown in the Header and Global Footer, its destination URL, and the Contact Form 7 shortcode rendered inside the popup.
        <?php if ( $form_id > 0 ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $form_id . '&action=edit' ) ); ?>">Edit CF7 Form (#<?php echo (int) $form_id; ?>) &rarr;</a>
        <?php endif; ?>
        </p>

        <form method="post">
            <?php wp_nonce_field( 'privu_login_save_settings', 'privu_login_settings_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="pl-btn">Button text</label></th>
                    <td><input type="text" id="pl-btn" name="privu_login_button_text" class="regular-text" value="<?php echo esc_attr( privu_login_button_text() ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pl-url">Destination / Redirect URL</label></th>
                    <td><input type="url" id="pl-url" name="privu_login_redirect_url" class="large-text" value="<?php echo esc_attr( privu_login_redirect_url() ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row">Require Lead Form before redirect</th>
                    <td><label><input type="checkbox" name="privu_login_enable_gate" value="1" <?php checked( privu_login_is_gate_enabled() ); ?>> Open popup lead form before redirecting</label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pl-sc">Popup CF7 form shortcode</label></th>
                    <td><input type="text" id="pl-sc" name="privu_login_cf7_shortcode" class="large-text code" value="<?php echo esc_attr( privu_login_cf7_shortcode() ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pl-eye">Popup eyebrow</label></th>
                    <td><input type="text" id="pl-eye" name="privu_login_modal_eyebrow" class="regular-text" value="<?php echo esc_attr( privu_login_modal_eyebrow() ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pl-title">Popup heading</label></th>
                    <td><input type="text" id="pl-title" name="privu_login_modal_title" class="large-text" value="<?php echo esc_attr( privu_login_modal_title() ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pl-sub">Popup subtitle</label></th>
                    <td><input type="text" id="pl-sub" name="privu_login_modal_subtitle" class="large-text" value="<?php echo esc_attr( privu_login_modal_subtitle() ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="pl-foot">Popup footer text</label></th>
                    <td><input type="text" id="pl-foot" name="privu_login_modal_footer" class="large-text" value="<?php echo esc_attr( privu_login_modal_footer() ); ?>"></td>
                </tr>
            </table>
            <?php submit_button( 'Save Changes' ); ?>
        </form>
    </div>
    <?php
}

/* -------------------------------------------------------------------------
 * CF7 server-side validation & spam protection for Product Login
 * ---------------------------------------------------------------------- */

/**
 * Is the CF7 form currently being processed the active Product Login form?
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
 * Handle clean direct URL /product-login/ (opens modal when gate is ON, or redirects straight to Destination URL when OFF).
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

    if ( ! privu_login_is_gate_enabled() ) {
        wp_redirect( privu_login_redirect_url(), 302 );
        exit;
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
        $GLOBALS['privu_in_footer_sidebar']     = true;
        $GLOBALS['privu_footer_login_injected'] = false;
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
 * Append the configured Login link to the first Navigation Menu widget inside Global Footer (`sidebar-footer`).
 *
 * @param string $items HTML list items.
 * @return string
 */
function privu_login_inject_footer_menu_item( $items ) {
    if ( empty( $GLOBALS['privu_in_footer_sidebar'] ) || ! empty( $GLOBALS['privu_footer_login_injected'] ) ) {
        return $items;
    }

    $GLOBALS['privu_footer_login_injected'] = true;

    $btn_text = privu_login_button_text();

    if ( false !== stripos( $items, '>' . $btn_text . '<' ) || false !== stripos( $items, 'data-privu-login-trigger' ) ) {
        return $items;
    }

    $trigger_attr = privu_login_is_gate_enabled() ? ' data-privu-login-trigger="Footer Quick Links"' : '';

    $items .= '<li class="menu-item privu-footer-login-item">'
        . '<a href="' . esc_url( privu_login_button_href() ) . '"' . $trigger_attr . '>' . esc_html( $btn_text ) . '</a>'
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

    $gate_enabled = privu_login_is_gate_enabled();
    $cf7_form_id  = $gate_enabled ? (int) privu_login_cf7_form_id() : 0;

    if ( $gate_enabled && $cf7_form_id ) {
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
            'buttonText'  => privu_login_button_text(),
            'loginUrl'    => privu_login_button_href(),
            'redirectUrl' => privu_login_redirect_url(),
            'gateEnabled' => $gate_enabled,
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
 * Render the Product Login modal in the global site footer (when the Lead Form gate is enabled).
 */
function privu_login_print_modal() {
    if ( is_admin() || ! privu_login_is_gate_enabled() ) {
        return;
    }

    $cf7_form_id   = (int) privu_login_cf7_form_id();
    $cf7_shortcode = privu_login_cf7_shortcode();
    $rendered_cf7  = '';
    $cf7_mode      = 'native';
    $target_url    = privu_login_redirect_url();
    $target_host   = wp_parse_url( $target_url, PHP_URL_HOST );
    $target_label  = $target_host ? $target_host : $target_url;

    if ( $cf7_form_id && '' !== $cf7_shortcode ) {
        $rendered_cf7 = do_shortcode( $cf7_shortcode );

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
                <?php if ( '' !== privu_login_modal_eyebrow() ) : ?>
                    <span class="ptdl-badge"><?php echo esc_html( privu_login_modal_eyebrow() ); ?></span>
                <?php endif; ?>
                <h2 class="ptdl-title" id="plogin-title"><?php echo esc_html( privu_login_modal_title() ); ?></h2>
                <?php if ( '' !== privu_login_modal_subtitle() ) : ?>
                    <p class="ptdl-subtitle" id="plogin-subtitle"><?php echo esc_html( privu_login_modal_subtitle() ); ?></p>
                <?php endif; ?>
            </div>

            <?php if ( 'cf7' === $cf7_mode ) : ?>
                <div class="ptdl-cf7-wrap" data-plogin-form-wrap>
                    <?php echo $rendered_cf7; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered CF7 form markup from Admin -> Product Login. ?>
                </div>
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
                </form>
            <?php endif; ?>

            <div class="ptdl-success plogin-redirect-state" data-plogin-redirect hidden>
                <div class="ptdl-check" aria-hidden="true">
                    <span class="plogin-redirect-spinner"></span>
                </div>
                <h3 class="ptdl-success__title">Redirecting to Privu Platform…</h3>
                <p class="ptdl-success__text">Taking you to <strong><?php echo esc_html( $target_label ); ?></strong>. If you are not redirected automatically, click below.</p>
                <a class="ptdl-download" href="<?php echo esc_url( $target_url ); ?>" data-plogin-continue>
                    <span>Continue to <?php echo esc_html( $target_label ); ?> &rarr;</span>
                </a>
            </div>

            <?php if ( '' !== privu_login_modal_footer() ) : ?>
                <p class="ptdl-foot"><?php echo esc_html( privu_login_modal_footer() ); ?></p>
            <?php endif; ?>
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
