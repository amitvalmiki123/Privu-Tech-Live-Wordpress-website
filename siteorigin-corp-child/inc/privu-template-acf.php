<?php
/**
 * ACF screen for the Assessment Template downloads.
 *
 * Registers a "Template Downloads" options page with a repeater so an admin can
 * add, edit, reorder or delete template mappings and attach files from the
 * WordPress Media Library without touching code.
 *
 * Requires Advanced Custom Fields PRO. When ACF is inactive, the fallback table
 * in inc/privu-template-admin.php is used instead.
 *
 * @package Privu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Does ACF (with repeater + options support) exist?
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
            'page_title' => 'Templates & Checklists',
            'menu_title' => 'Templates & Checklists',
            'menu_slug'  => 'templates-checklists',
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
            'title'    => 'Templates & Checklists',
            'fields'   => array(
                array(
                    'key'      => 'field_privu_tdl_message',
                    'label'    => '',
                    'name'     => '',
                    'type'     => 'message',
                    'message'  => '<p style="margin:0;line-height:1.6">'
                        . 'Match each card on the Assessment Templates page by entering its heading in <strong>Template name</strong> and uploading its file (leave empty to use the bundled file).'
                        . '</p>',
                    'esc_html' => 0,
                ),
                array(
                    'key'          => 'field_privu_tdl_templates',
                    'label'        => 'Templates',
                    'name'         => 'privu_templates',
                    'type'         => 'repeater',
                    'instructions' => 'Drag rows to reorder. URL slugs and card matching update automatically from the template name.',
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
                            'instructions' => 'Enter the exact card heading from the Assessment Templates page.',
                            'placeholder'  => 'e.g. DPDP Act Compliance Checklist',
                        ),
                        array(
                            'key'           => 'field_privu_tdl_file',
                            'label'         => 'Downloadable file',
                            'name'          => 'template_file',
                            'type'          => 'file',
                            'required'      => 0,
                            'return_format' => 'array',
                            'library'       => 'all',
                            'mime_types'    => 'xlsx,xls,csv,pdf,doc,docx,ppt,pptx,zip',
                            'instructions'  => 'Upload PDF, Word or Excel, or leave empty to keep the bundled file.',
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'templates-checklists',
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
add_action( 'acf/init', 'privu_tdl_acf_register' );

/**
 * Redirect old admin page slug (?page=privu-template-downloads) to ?page=templates-checklists.
 */
function privu_tdl_redirect_old_admin_slug() {
    if ( is_admin() && isset( $_GET['page'] ) && 'privu-template-downloads' === $_GET['page'] ) {
        wp_safe_redirect( admin_url( 'admin.php?page=templates-checklists' ) );
        exit;
    }
}
add_action( 'admin_init', 'privu_tdl_redirect_old_admin_slug', 1 );

/**
 * Pre-fill the 6 default templates on first run so the admin screen is ready immediately.
 */
function privu_tdl_acf_seed() {
    if ( ! privu_tdl_acf_available() || ! function_exists( 'update_field' ) ) {
        return;
    }

    // Already saved once – never overwrite admin edits.
    if ( null !== get_option( 'options_privu_templates', null ) ) {
        return;
    }

    $seed  = array();
    $saved = get_option( 'privu_tdl_templates' );

    if ( is_array( $saved ) && ! empty( $saved ) ) {
        foreach ( $saved as $row ) {
            if ( empty( $row['title'] ) ) {
                continue;
            }

            $seed[] = array(
                'template_name' => $row['title'],
                'template_file' => ! empty( $row['attachment_id'] ) ? (int) $row['attachment_id'] : '',
            );
        }
    } else {
        foreach ( privu_tdl_default_templates() as $template ) {
            $seed[] = array(
                'template_name' => $template['title'],
                'template_file' => '',
            );
        }
    }

    if ( ! empty( $seed ) ) {
        update_field( 'privu_templates', $seed, 'option' );
    }
}
add_action( 'acf/init', 'privu_tdl_acf_seed', 20 );

/**
 * Append a short Contact Form 7 edit link to the top note.
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
        $field['message'] .= '<p style="margin:4px 0 0;line-height:1.6;color:#50575e">'
            . 'Popup form: <a href="' . esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $form_id . '&action=edit' ) ) . '">Edit “Privu Template Download” (#' . $form_id . ')</a>.'
            . '</p>';
    }

    return $field;
}
add_filter( 'acf/load_field/key=field_privu_tdl_message', 'privu_tdl_acf_message_field' );

/**
 * Read the ACF repeater and convert rows into the internal catalogue format.
 *
 * Slugs are always generated directly from `template_name` so renaming any
 * template automatically updates both card matching and its clean URL slug.
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

    $defaults     = privu_tdl_default_templates();
    $default_list = array_values( $defaults );
    $templates    = array();
    $used         = array();
    $row_index    = 0;

    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        $title = isset( $row['template_name'] ) ? trim( (string) $row['template_name'] ) : '';

        if ( '' === $title ) {
            $row_index++;
            continue;
        }

        $slug = sanitize_key( sanitize_title( $title ) );

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

        // Fall back to the matching bundled file (by slug or original row index 0..5).
        if ( ! $attachment_id && '' === $file ) {
            if ( isset( $defaults[ $slug ]['file'] ) ) {
                $file = $defaults[ $slug ]['file'];
            } elseif ( isset( $default_list[ $row_index ]['file'] ) ) {
                $file = $default_list[ $row_index ]['file'];
            }
        }

        $templates[ $slug ] = array(
            'title'         => $title,
            'keywords'      => array(),
            'attachment_id' => $attachment_id,
            'file'          => $file,
        );

        $row_index++;
    }

    return $templates;
}
