<?php
/**
 * Contact Form 7 integration for the Assessment Templates download modal.
 *
 * The popup's form is a real CF7 form, so it behaves exactly like the other
 * forms on the site:
 *  - entries / mail are handled by CF7 (and Flamingo, if active)
 *  - mail settings can be edited in Contact → Contact Forms
 *  - the theme only styles it and attaches the selected .xlsx template
 *
 * The form is created automatically the first time an administrator opens the
 * admin area. Its ID is stored in the `privu_tdl_cf7_form_id` option.
 *
 * @package Privu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PRIVU_TDL_CF7_TITLE', 'Privu Template Download' );

/**
 * Is Contact Form 7 available?
 *
 * @return bool
 */
function privu_tdl_cf7_active() {
    return class_exists( 'WPCF7_ContactForm' );
}

/**
 * ID of the CF7 form used by the popup (0 when CF7 is not available yet).
 *
 * A manually created form can be forced with:
 *   add_filter( 'privu_tdl_contact_form_id', fn() => 123 );
 * or by defining PRIVU_TDL_CF7_FORM_ID in wp-config.php.
 *
 * @return int
 */
function privu_tdl_cf7_form_id() {
    static $form_id = null;

    if ( null !== $form_id ) {
        return $form_id;
    }

    $form_id = 0;

    if ( defined( 'PRIVU_TDL_CF7_FORM_ID' ) && (int) PRIVU_TDL_CF7_FORM_ID ) {
        $form_id = (int) PRIVU_TDL_CF7_FORM_ID;

        return $form_id;
    }

    $override = (int) apply_filters( 'privu_tdl_contact_form_id', 0 );

    if ( $override ) {
        $form_id = $override;

        return $form_id;
    }

    if ( ! privu_tdl_cf7_active() ) {
        return $form_id;
    }

    $stored = (int) get_option( 'privu_tdl_cf7_form_id' );

    if ( $stored && 'wpcf7_contact_form' === get_post_type( $stored ) && 'publish' === get_post_status( $stored ) ) {
        privu_tdl_cf7_maybe_repair_form( $stored );
        $form_id = $stored;

        return $form_id;
    }

    $found = privu_tdl_cf7_find_form();

    if ( $found ) {
        privu_tdl_cf7_maybe_repair_form( $found );
        update_option( 'privu_tdl_cf7_form_id', $found, false );
        $form_id = $found;

        return $form_id;
    }

    // Only an administrator can create the form (and only once per hour on failure).
    if ( current_user_can( 'manage_options' ) && ! get_transient( 'privu_tdl_cf7_create_lock' ) ) {
        $created = privu_tdl_cf7_create_form();

        if ( $created ) {
            update_option( 'privu_tdl_cf7_form_id', $created, false );
            $form_id = $created;
        } else {
            set_transient( 'privu_tdl_cf7_create_lock', 1, HOUR_IN_SECONDS );
        }
    }

    return $form_id;
}

/**
 * Repair legacy CF7 tag option ordering on an already-created form without touching recipients.
 *
 * In Contact Form 7 syntax, options (e.g. maxlength:80, placeholder) must appear
 * before quoted string values ("...").
 *
 * @param int $id Contact form post ID.
 */
function privu_tdl_cf7_maybe_repair_form( $id ) {
    if ( ! $id || ! method_exists( 'WPCF7_ContactForm', 'get_instance' ) ) {
        return;
    }

    try {
        $form = WPCF7_ContactForm::get_instance( $id );

        if ( ! $form || ! method_exists( $form, 'prop' ) || ! method_exists( $form, 'set_properties' ) || ! method_exists( $form, 'save' ) ) {
            return;
        }

        $markup = (string) $form->prop( 'form' );
        $mail   = $form->prop( 'mail' );
        $dirty  = false;

        $fixed_markup = preg_replace(
            array(
                '/\[text\*\s+your-name\s+id:ptdl-cf7-name\s+placeholder\s+"([^"]*)"\s+maxlength:80\]/',
                '/\[email\*\s+your-email\s+id:ptdl-cf7-email\s+placeholder\s+"([^"]*)"\s+maxlength:120\]/',
                '/\[tel\*\s+your-phone\s+id:ptdl-cf7-phone\s+placeholder\s+"([^"]*)"(?:\s+minlength:\d+)?\s+maxlength:\d+\]/',
            ),
            array(
                '[text* your-name id:ptdl-cf7-name maxlength:80 placeholder "$1"]',
                '[email* your-email id:ptdl-cf7-email maxlength:120 placeholder "$1"]',
                '[tel* your-phone id:ptdl-cf7-phone minlength:10 maxlength:10 placeholder "9876543210"]',
            ),
            $markup
        );

        $props = array();

        if ( is_string( $fixed_markup ) && $fixed_markup !== $markup ) {
            $props['form'] = $fixed_markup;
            $dirty         = true;
        }

        if ( is_array( $mail ) && isset( $mail['additional_headers'] ) && false !== strpos( (string) $mail['additional_headers'], '<[your-email]>' ) ) {
            $mail['additional_headers'] = str_replace( 'Reply-To: [your-name] <[your-email]>', 'Reply-To: [your-email]', (string) $mail['additional_headers'] );
            $props['mail']              = $mail;
            $dirty                      = true;
        }

        if ( $dirty ) {
            $form->set_properties( $props );
            $form->save();
        }
    } catch ( Throwable $e ) {
        error_log( '[Privu template download] CF7 form repair skipped: ' . $e->getMessage() );
    }
}

/**
 * Find an existing form by title.
 *
 * @return int
 */
function privu_tdl_cf7_find_form() {
    if ( ! privu_tdl_cf7_active() ) {
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
            'title'               => PRIVU_TDL_CF7_TITLE,
        )
    );

    return $query->posts ? (int) $query->posts[0] : 0;
}

/**
 * Create the CF7 form (form markup + admin mail + visitor auto-reply).
 *
 * @return int Form ID, or 0 on failure.
 */
function privu_tdl_cf7_create_form() {
    if ( ! privu_tdl_cf7_active() || ! method_exists( 'WPCF7_ContactForm', 'get_template' ) ) {
        return 0;
    }

    try {
        $form = WPCF7_ContactForm::get_template(
            array(
                'title'  => PRIVU_TDL_CF7_TITLE,
                'locale' => 'en_US',
            )
        );

        if ( ! $form || ! method_exists( $form, 'set_properties' ) || ! method_exists( $form, 'save' ) || ! method_exists( $form, 'prop' ) ) {
            return 0;
        }

        $form_markup = '<div class="ptdl-cf7-fields">' . "\n"
            . '<p class="ptdl-field ptdl-field--name">' . "\n"
            . '<label for="ptdl-cf7-name">Full name <span aria-hidden="true">*</span></label>' . "\n"
            . '[text* your-name id:ptdl-cf7-name maxlength:80 placeholder "e.g. Priya Nair"]' . "\n"
            . '</p>' . "\n"
            . '<p class="ptdl-field ptdl-field--email">' . "\n"
            . '<label for="ptdl-cf7-email">Work email <span aria-hidden="true">*</span></label>' . "\n"
            . '[email* your-email id:ptdl-cf7-email maxlength:120 placeholder "you@company.com"]' . "\n"
            . '</p>' . "\n"
            . '<p class="ptdl-field ptdl-field--phone">' . "\n"
            . '<label for="ptdl-cf7-phone">Phone number <span aria-hidden="true">*</span></label>' . "\n"
            . '[tel* your-phone id:ptdl-cf7-phone minlength:10 maxlength:10 placeholder "9876543210"]' . "\n"
            . '<span class="ptdl-hint">10 digits, starting with 6, 7, 8 or 9.</span>' . "\n"
            . '</p>' . "\n"
            . '[hidden privu-template default:dpdp-act-compliance-checklist]' . "\n"
            . '[hidden privu-page-url]' . "\n"
            . '[submit class:ptdl-submit "Send me the template"]' . "\n"
            . '</div>';

        $mail = $form->prop( 'mail' );

        if ( ! is_array( $mail ) ) {
            $mail = array();
        }

        $mail['active']             = true;
        $mail['recipient']          = 'eric@privu.tech, contact@privu.tech';
        $mail['subject']            = 'New template download: [privu-template-name] - [your-name]';
        $mail['sender']             = 'Privu <contact@privu.tech>';
        $mail['additional_headers'] = 'Reply-To: [your-email]';
        $mail['use_html']           = false;
        $mail['exclude_blank']      = true;
        $mail['body']               = "New template download request\n\n"
            . "Name: [your-name]\n"
            . "Work email: [your-email]\n"
            . "Phone: [your-phone]\n"
            . "Template: [privu-template-name]\n"
            . "Page: [privu-page-url]\n"
            . "IP address: [_remote_ip]\n"
            . "Date: [_date] [_time]\n";

        $mail_2 = $form->prop( 'mail_2' );

        if ( ! is_array( $mail_2 ) ) {
            $mail_2 = array();
        }

        $mail_2['active']             = true;
        $mail_2['recipient']          = '[your-email]';
        $mail_2['subject']            = 'Your [privu-template-name] from Privu';
        $mail_2['sender']             = 'Privu <contact@privu.tech>';
        $mail_2['additional_headers'] = 'Reply-To: Privu <contact@privu.tech>';
        $mail_2['use_html']           = false;
        $mail_2['exclude_blank']      = true;
        $mail_2['body']               = "Hi [your-name],\n\n"
            . "Thank you for your interest. [privu-template-name] is attached to this email, and you can also download it here:\n"
            . "[privu-download-link]\n\n"
            . "If the attachment does not open, use the link above.\n\n"
            . "Regards,\n"
            . "Team Privu\n"
            . "contact@privu.tech\n";

        $messages = $form->prop( 'messages' );

        if ( ! is_array( $messages ) ) {
            $messages = array();
        }

        $messages['mail_sent_ok']      = 'Thank you. Your template is on its way.';
        $messages['mail_sent_ng']      = 'There was an error trying to send your message. Please try again later.';
        $messages['validation_error']  = 'Please check the highlighted fields and try again.';
        $messages['spam']              = 'There was an error trying to send your message. Please try again later.';
        $messages['invalid_required']  = 'Please fill out this field.';
        $messages['invalid_too_long']  = 'This field has too many characters.';
        $messages['invalid_too_short'] = 'This field is too short.';
        $messages['invalid_email']     = 'Please enter a valid email address.';
        $messages['invalid_tel']       = 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.';

        $form->set_properties(
            array(
                'form'      => $form_markup,
                'mail'      => $mail,
                'mail_2'    => $mail_2,
                'messages'  => $messages,
            )
        );

        $id = $form->save();

        return $id ? (int) $id : 0;
    } catch ( Throwable $e ) {
        error_log( '[Privu template download] CF7 form creation failed: ' . $e->getMessage() );

        return 0;
    }
}

/**
 * Create the CF7 form on the first admin dashboard visit...
 */
function privu_tdl_cf7_bootstrap() {
    if ( is_admin() && current_user_can( 'manage_options' ) ) {
        privu_tdl_cf7_form_id();
    }
}
add_action( 'admin_init', 'privu_tdl_cf7_bootstrap', 5 );
add_action( 'after_switch_theme', 'privu_tdl_cf7_bootstrap' );

/**
 * ...and on the first frontend page load so visitors also get a real form.
 * (Runs before the modal is printed in wp_footer.)
 */
function privu_tdl_cf7_ensure_frontend_form() {
    if ( ! is_admin() && function_exists( 'privu_tdl_cf7_form_id' ) ) {
        privu_tdl_cf7_form_id();
    }
}
add_action( 'wp', 'privu_tdl_cf7_ensure_frontend_form', 5 );

/**
 * Is the form currently being processed ours?
 *
 * @return bool
 */
function privu_tdl_cf7_is_our_form() {
    if ( ! function_exists( 'wpcf7_get_current_contact_form' ) ) {
        return false;
    }

    $contact_form = wpcf7_get_current_contact_form();

    return $contact_form && (int) $contact_form->id() === (int) privu_tdl_cf7_form_id();
}

/**
 * Template slug posted by the modal.
 *
 * @return string
 */
function privu_tdl_cf7_posted_slug() {
    if ( ! class_exists( 'WPCF7_Submission' ) ) {
        return '';
    }

    $submission = WPCF7_Submission::get_instance();

    if ( ! $submission ) {
        return '';
    }

    $raw = sanitize_key( $submission->get_posted_data( 'privu-template' ) );

    return function_exists( 'privu_tdl_resolve_slug' ) ? privu_tdl_resolve_slug( $raw ) : $raw;
}

/* -------------------------------------------------------------------------
 * Validation – work email + Indian mobile number
 * ---------------------------------------------------------------------- */

/**
 * Reject personal email domains (server side).
 *
 * @param WPCF7_Validation $result Validation result.
 * @param WPCF7_FormTag    $tag    Form tag.
 * @return WPCF7_Validation
 */
function privu_tdl_cf7_validate_work_email( $result, $tag ) {
    if ( ! privu_tdl_cf7_is_our_form() || 'your-email' !== $tag->name ) {
        return $result;
    }

    $email = isset( $_POST[ $tag->name ] ) && is_string( $_POST[ $tag->name ] )
        ? trim( wp_unslash( $_POST[ $tag->name ] ) )
        : '';

    if ( $email && privu_tdl_is_personal_email( $email ) ) {
        $result->invalidate(
            $tag,
            'Please use your work email. Gmail, Yahoo, Outlook and other personal addresses are not accepted.'
        );
    }

    return $result;
}
add_filter( 'wpcf7_validate_email', 'privu_tdl_cf7_validate_work_email', 20, 2 );
add_filter( 'wpcf7_validate_email*', 'privu_tdl_cf7_validate_work_email', 20, 2 );

/**
 * Ensure existing saved CF7 form instances also output strict 10-digit HTML attributes on the phone input.
 *
 * @param string $html Rendered form elements HTML.
 * @return string
 */
function privu_tdl_cf7_tune_phone_input( $html ) {
    if ( ! privu_tdl_cf7_is_our_form() ) {
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
add_filter( 'wpcf7_form_elements', 'privu_tdl_cf7_tune_phone_input' );

/**
 * Enforce a 10 digit Indian mobile number.
 *
 * @param WPCF7_Validation $result Validation result.
 * @param WPCF7_FormTag    $tag    Form tag.
 * @return WPCF7_Validation
 */
function privu_tdl_cf7_validate_phone( $result, $tag ) {
    if ( ! privu_tdl_cf7_is_our_form() || 'your-phone' !== $tag->name ) {
        return $result;
    }

    $phone = isset( $_POST[ $tag->name ] ) && is_string( $_POST[ $tag->name ] )
        ? trim( wp_unslash( $_POST[ $tag->name ] ) )
        : '';

    if ( $phone && '' === privu_tdl_normalize_phone( $phone ) ) {
        $result->invalidate(
            $tag,
            'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.'
        );
    }

    return $result;
}
add_filter( 'wpcf7_validate_tel', 'privu_tdl_cf7_validate_phone', 20, 2 );
add_filter( 'wpcf7_validate_tel*', 'privu_tdl_cf7_validate_phone', 20, 2 );

/* -------------------------------------------------------------------------
 * Spam rate-limiting + config validator clean-up
 * ---------------------------------------------------------------------- */

/**
 * Rate-limit CF7 template requests per IP (protects Mail 2 autoresponder without requiring CAPTCHA).
 *
 * @param bool $spam Current spam flag.
 * @return bool
 */
function privu_tdl_cf7_rate_limit( $spam ) {
    if ( $spam || ! privu_tdl_cf7_is_our_form() ) {
        return $spam;
    }

    $ip       = function_exists( 'privu_tdl_client_ip' ) ? privu_tdl_client_ip() : '0.0.0.0';
    $rate_key = 'privu_tdl_rl_' . md5( $ip );
    $hits     = (int) get_transient( $rate_key );

    if ( $hits >= 12 ) {
        return true;
    }

    set_transient( $rate_key, $hits + 1, 15 * MINUTE_IN_SECONDS );

    return false;
}
add_filter( 'wpcf7_spam', 'privu_tdl_cf7_rate_limit', 20 );

/**
 * Suppress CF7's generic "unsafe_email_without_protection" notice on our rate-limited autoresponder form.
 *
 * @param array             $error_codes Available validator error codes.
 * @param WPCF7_ContactForm $contact_form Contact form being validated.
 * @return array
 */
function privu_tdl_cf7_validator_codes( $error_codes, $contact_form ) {
    if ( $contact_form && method_exists( $contact_form, 'id' ) && (int) $contact_form->id() === (int) privu_tdl_cf7_form_id() ) {
        $error_codes = array_values( array_diff( (array) $error_codes, array( 'unsafe_email_without_protection' ) ) );
    }

    return $error_codes;
}
add_filter( 'wpcf7_config_validator_available_error_codes', 'privu_tdl_cf7_validator_codes', 10, 2 );

/* -------------------------------------------------------------------------
 * Attachment + custom mail tags
 * ---------------------------------------------------------------------- */

/**
 * Attach the selected template to the visitor auto-reply (mail_2).
 *
 * @param WPCF7_ContactForm $contact_form Form being sent.
 * @param bool              $abort        Abort flag (by reference).
 * @param WPCF7_Submission  $submission   Current submission.
 */
function privu_tdl_cf7_before_send_mail( $contact_form, &$abort, $submission ) {
    if ( ! $contact_form || (int) $contact_form->id() !== (int) privu_tdl_cf7_form_id() ) {
        return;
    }

    $raw_slug  = sanitize_key( $submission->get_posted_data( 'privu-template' ) );
    $slug      = function_exists( 'privu_tdl_resolve_slug' ) ? privu_tdl_resolve_slug( $raw_slug ) : $raw_slug;
    $templates = privu_tdl_templates();

    if ( '' === $slug || ! isset( $templates[ $slug ] ) ) {
        return;
    }

    $path = privu_tdl_template_path( $templates[ $slug ] );

    if ( ! $path ) {
        error_log( '[Privu template download] Missing CF7 attachment for template: ' . $slug );

        return;
    }

    $mail_2 = $contact_form->prop( 'mail_2' );

    if ( empty( $mail_2 ) || ! is_array( $mail_2 ) ) {
        return;
    }

    $existing_raw = isset( $mail_2['attachments'] ) ? $mail_2['attachments'] : '';

    if ( is_array( $existing_raw ) ) {
        $existing_raw = implode( "\n", $existing_raw );
    }

    $existing = trim( (string) $existing_raw );

    $mail_2['attachments'] = $existing ? $existing . "\n" . $path : $path;

    $contact_form->set_properties( array( 'mail_2' => $mail_2 ) );
}
add_action( 'wpcf7_before_send_mail', 'privu_tdl_cf7_before_send_mail', 10, 3 );

/**
 * Custom mail tags: [privu-template-name] and [privu-download-link].
 *
 * @param string $output   Replaced value.
 * @param string $name     Tag name.
 * @param bool   $html     HTML mode.
 * @param object $mail_tag Mail tag object.
 * @return string
 */
function privu_tdl_cf7_special_mail_tags( $output, $name, $html, $mail_tag ) {
    if ( ! in_array( $name, array( 'privu-template-name', 'privu-download-link' ), true ) ) {
        return $output;
    }

    $slug      = privu_tdl_cf7_posted_slug();
    $templates = privu_tdl_templates();

    if ( '' === $slug || ! isset( $templates[ $slug ] ) ) {
        return '';
    }

    if ( 'privu-template-name' === $name ) {
        return $templates[ $slug ]['title'];
    }

    return privu_tdl_download_url( $slug );
}
add_filter( 'wpcf7_special_mail_tags', 'privu_tdl_cf7_special_mail_tags', 10, 4 );
