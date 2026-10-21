<?php
/**
 * ACF screen for the Assessment Template downloads.
 *
 * Registers a "Template Downloads" options page with a repeater, so the client
 * can add / edit / reorder / delete template cards and attach a file from the
 * media library — no code, no page-template editing.
 *
 * Requires Advanced Custom Fields PRO (the site already uses repeaters for the
 * Team Settings page). When ACF is not available the fallback screen in
 * inc/privu-template-admin.php is used instead.
 *
 * @package Privu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Does ACF (with repeater support) exist?
 *
 * @return bool
 */
function privu_tdl_acf_available() {
    return function_exists( 'acf_add_options_page' ) && function_exists( 'acf_add_local_field_group' );
}

/**
 * ACF options page + repeater field group.
 */
function privu_tdl_acf_register() {
    if ( ! privu_tdl_acf_available() ) {
        return;
    }

    acf_add_options_page(
        array(
            'page_title' => 'Template Downloads',
            'menu_title' => 'Template Downloads',
            'menu_slug'  => 'privu-template-downloads',
            'capability' => 'manage_options',
            'icon_url'   => 'dashicons-download',
            'position'   => '58.6',
            'redirect'   => false,
            'autoload'   => true,
        )
    );

    acf_add_local_field_group(
        array(
            'key'      => 'group_privu_tdl_templates',
            'title'    => 'Template cards',
            'fields'   => array(
                array(
                    'key'          => 'field_privu_tdl_message',
                    'label'        => '',
                    'name'         => '',
                    'type'         => 'message',
                    'message'      => '<strong>Ek row = ek template card.</strong><br>'
                        . '1. Pehle Assessment Templates page me card banayein (WP Bakery se koi card duplicate karke heading badal dein). Download link ka text “Download Template” hi rakhein.<br>'
                        . '2. Yahan <em>Add Row</em> karein — <strong>Template name</strong> me wahi heading likhein, aur <strong>Downloadable file</strong> me PDF / Word / Excel upload karein.<br>'
                        . '3. <strong>Card keywords</strong> me heading ke 2–3 words comma se likhein — isse popup ko pata chalta hai kaunsa card click hua.<br>'
                        . '4. Save karein aur page cache purge karein.<br><br>'
                        . 'Kisi bhi page par button lagane ke liye shortcode: <code>[privu_template_download slug="dpia" text="Download Template"]</code><br>'
                        . 'CF7 form edit karna ho to: <em>Contact → Contact Forms → “Privu Template Download”</em>.',
                    'new_lines'    => '',
                    'esc_html'     => 0,
                ),
                array(
                    'key'          => 'field_privu_tdl_templates',
                    'label'        => 'Templates',
                    'name'         => 'privu_templates',
                    'type'         => 'repeater',
                    'instructions' => 'Jo row upar hai wo pehle dikhegi.',
                    'required'     => 0,
                    'layout'       => 'block',
                    'button_label' => 'Add template',
                    'collapsed'    => 'field_privu_tdl_title',
                    'sub_fields'   => array(
                        array(
                            'key'          => 'field_privu_tdl_title',
                            'label'        => 'Template name',
                            'name'         => 'template_name',
                            'type'         => 'text',
                            'required'     => 1,
                            'instructions' => 'Page ke card ki heading jaisa hi (jaise “DPDP Readiness Assessment”).',
                            'placeholder'  => 'e.g. DPDP Readiness Assessment',
                        ),
                        array(
                            'key'          => 'field_privu_tdl_keywords',
                            'label'        => 'Card keywords',
                            'name'         => 'template_keywords',
                            'type'         => 'text',
                            'instructions' => 'Heading ke 2–3 words, comma se alag. Blank chhodein to Template name se detect hoga.',
                            'placeholder'  => 'readiness, assessment',
                        ),
                        array(
                            'key'          => 'field_privu_tdl_slug',
                            'label'        => 'Slug (optional)',
                            'name'         => 'template_slug',
                            'type'         => 'text',
                            'instructions' => 'Sirf tab bharein jab shortcode me use karna ho (jaise <code>dpia</code>). Purane templates ka slug na badlein.',
                            'placeholder'  => 'dpia',
                        ),
                        array(
                            'key'           => 'field_privu_tdl_file',
                            'label'         => 'Downloadable file',
                            'name'          => 'template_file',
                            'type'          => 'file',
                            'required'      => 1,
                            'return_format' => 'array',
                            'library'       => 'all',
                            'mime_types'    => 'xlsx,xls,csv,pdf,doc,docx,ppt,pptx,zip',
                            'instructions'  => 'Yehi file visitor ko email attachment aur “Download now” button se milegi.',
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'privu-template-downloads',
                    ),
                ),
            ),
            'menu_order'              => 0,
            'position'                => 'normal',
            'style'                   => 'default',
            'label_placement'         => 'top',
            'instruction_placement'   => 'label',
            'active'                  => true,
            'show_in_rest'            => 0,
        )
    );
}
add_action( 'acf/init', 'privu_tdl_acf_register' );

/**
 * Put the current 6 templates into the ACF repeater the first time, so the
 * client sees an editable list instead of an empty screen.
 */
function privu_tdl_acf_seed() {
    if ( ! privu_tdl_acf_available() || ! function_exists( 'update_field' ) ) {
        return;
    }

    // Already saved once (even empty) – never overwrite client edits.
    if ( null !== get_option( 'options_privu_templates', null ) ) {
        return;
    }

    $seed = array();

    // Fallback admin screen par pehle se saved rows hain to wahi seed karein.
    $saved = get_option( 'privu_tdl_templates' );

    if ( is_array( $saved ) && ! empty( $saved ) ) {
        foreach ( $saved as $slug => $row ) {
            if ( empty( $row['title'] ) ) {
                continue;
            }

            $seed[] = array(
                'template_name'     => $row['title'],
                'template_keywords' => ! empty( $row['keywords'] ) ? $row['keywords'] : '',
                'template_slug'     => sanitize_key( $slug ),
                'template_file'     => ! empty( $row['attachment_id'] ) ? (int) $row['attachment_id'] : '',
            );
        }
    } else {
        foreach ( privu_tdl_default_templates() as $slug => $template ) {
            $seed[] = array(
                'template_name'     => $template['title'],
                'template_keywords' => implode( ', ', $template['keywords'] ),
                'template_slug'     => $slug,
                'template_file'     => '',
            );
        }
    }

    if ( ! empty( $seed ) ) {
        update_field( 'privu_templates', $seed, 'option' );
    }
}
add_action( 'acf/init', 'privu_tdl_acf_seed', 20 );

/**
 * Add the live CF7 form ID to the instructions message.
 *
 * @param array $field Field settings.
 * @return array
 */
function privu_tdl_acf_message_field( $field ) {
    if ( ! isset( $field['key'] ) || 'field_privu_tdl_message' !== $field['key'] ) {
        return $field;
    }

    $form_id = function_exists( 'privu_tdl_cf7_form_id' ) ? (int) privu_tdl_cf7_form_id() : 0;

    if ( $form_id ) {
        $field['message'] .= '<br><br><strong>CF7 form:</strong> “Privu Template Download” (ID ' . $form_id . ') — '
            . '<a href="' . esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $form_id . '&action=edit' ) ) . '">form edit karein</a>. '
            . 'Shortcode page me lagane ki zarurat nahi — popup ke andar theme khud print karta hai.';
    }

    return $field;
}
add_filter( 'acf/load_field/key=field_privu_tdl_message', 'privu_tdl_acf_message_field' );

/**
 * Read the repeater and convert it to the internal template structure.
 *
 * @return array Templates keyed by slug, empty when ACF has no rows.
 */
function privu_tdl_templates_from_acf() {
    if ( ! function_exists( 'get_field' ) ) {
        return array();
    }

    $rows = get_field( 'privu_templates', 'option' );

    if ( ! is_array( $rows ) || empty( $rows ) ) {
        return array();
    }

    $templates = array();
    $used      = array();

    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        $title = isset( $row['template_name'] ) ? trim( (string) $row['template_name'] ) : '';

        if ( '' === $title ) {
            continue;
        }

        $slug = isset( $row['template_slug'] ) ? sanitize_key( $row['template_slug'] ) : '';

        if ( '' === $slug ) {
            $slug = sanitize_key( sanitize_title( $title ) );
        }

        if ( '' === $slug ) {
            $slug = 'template';
        }

        $base = $slug;
        $i    = 2;

        while ( isset( $used[ $slug ] ) ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        $used[ $slug ] = true;

        // Keywords.
        $keywords = array();

        if ( ! empty( $row['template_keywords'] ) ) {
            $raw = is_array( $row['template_keywords'] )
                ? $row['template_keywords']
                : preg_split( '/[,;]+/', (string) $row['template_keywords'] );

            foreach ( (array) $raw as $keyword ) {
                $keyword = trim( (string) $keyword );

                if ( '' !== $keyword ) {
                    $keywords[] = $keyword;
                }
            }
        }

        // File: ACF can return an array, an attachment ID or a URL.
        $attachment_id = 0;
        $file          = '';

        if ( ! empty( $row['template_file'] ) ) {
            $value = $row['template_file'];

            if ( is_array( $value ) ) {
                $attachment_id = ! empty( $value['ID'] ) ? (int) $value['ID'] : 0;
            } elseif ( is_numeric( $value ) ) {
                $attachment_id = (int) $value;
            } elseif ( is_string( $value ) ) {
                $file = $value;
            }
        }

        // File upload na ho to bundled file (jo pehle se chal rahi hai) use karein.
        if ( ! $attachment_id && '' === $file ) {
            $defaults = privu_tdl_default_templates();

            if ( isset( $defaults[ $slug ]['file'] ) ) {
                $file = $defaults[ $slug ]['file'];
            }
        }

        $templates[ $slug ] = array(
            'title'         => $title,
            'keywords'      => $keywords,
            'attachment_id' => $attachment_id,
            'file'          => $file,
        );
    }

    return $templates;
}
