<?php
/**
 * Fallback admin screen for the Assessment Template downloads (used only when ACF PRO is inactive).
 *
 * Stored in the `privu_tdl_templates` option. When the option is empty the
 * built-in defaults from privu_tdl_default_templates() are used.
 *
 * @package Privu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the admin menu when ACF PRO is not active.
 */
function privu_tdl_admin_menu() {
    if ( function_exists( 'acf_add_options_page' ) ) {
        return;
    }

    add_menu_page(
        'Templates & Checklists',
        'Templates & Checklists',
        'manage_options',
        'templates-checklists',
        'privu_tdl_admin_page',
        'dashicons-download',
        58.6
    );
}
add_action( 'admin_menu', 'privu_tdl_admin_menu' );

/**
 * Current templates in the admin format.
 *
 * @return array
 */
function privu_tdl_admin_rows() {
    $saved = get_option( 'privu_tdl_templates' );

    if ( is_array( $saved ) && ! empty( $saved ) ) {
        return $saved;
    }

    $rows = array();

    foreach ( privu_tdl_default_templates() as $slug => $template ) {
        $rows[ $slug ] = array(
            'title'         => $template['title'],
            'keywords'      => '',
            'attachment_id' => 0,
            'file'          => $template['file'],
        );
    }

    return $rows;
}

/**
 * Human-readable file label for a row.
 *
 * @param array $row Row data.
 * @return string
 */
function privu_tdl_admin_file_label( $row ) {
    if ( ! empty( $row['attachment_id'] ) ) {
        $url = wp_get_attachment_url( (int) $row['attachment_id'] );

        return $url ? basename( $url ) : 'Missing attachment';
    }

    if ( ! empty( $row['file'] ) ) {
        return $row['file'] . ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'assets/templates/' . $row['file'] ) ? '' : ' (bundled file)' );
    }

    return '';
}

/**
 * Render the fallback admin screen.
 */
function privu_tdl_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $rows        = privu_tdl_admin_rows();
    $cf7_form_id = function_exists( 'privu_tdl_cf7_form_id' ) ? (int) privu_tdl_cf7_form_id() : 0;
    $updated     = isset( $_GET['ptdl-updated'] ) ? sanitize_text_field( wp_unslash( $_GET['ptdl-updated'] ) ) : '';
    ?>
    <div class="wrap ptdl-admin">
        <h1>Templates &amp; Checklists</h1>

        <?php if ( 'saved' === $updated ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Templates saved.</strong> Remember to purge the page cache after changes.</p></div>
        <?php elseif ( 'reset' === $updated ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Default templates restored.</strong></p></div>
        <?php endif; ?>

        <p class="ptdl-admin-intro">
            Match each card on the Assessment Templates page by entering its exact heading in <strong>Template name</strong> and choosing its downloadable file. Card matching and button URL slugs update automatically from the template name.
        </p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ptdl-admin-form">
            <input type="hidden" name="action" value="privu_tdl_save_templates">
            <?php wp_nonce_field( 'privu_tdl_save_templates' ); ?>

            <table class="form-table" role="presentation" style="margin-top:0;">
                <tr>
                    <th scope="row"><label for="ptdl-cf7-shortcode">Popup CF7 form shortcode</label></th>
                    <td>
                        <input type="text" id="ptdl-cf7-shortcode" name="privu_tdl_cf7_shortcode" class="large-text code" value="<?php echo esc_attr( function_exists( 'privu_tdl_cf7_shortcode' ) ? privu_tdl_cf7_shortcode() : '' ); ?>">
                        <p class="description">Contact Form 7 shortcode rendered inside the download popup.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ptdl-eyebrow">Popup eyebrow</label></th>
                    <td><input type="text" id="ptdl-eyebrow" name="privu_tdl_modal_eyebrow" class="regular-text" value="<?php echo esc_attr( function_exists( 'privu_tdl_modal_eyebrow' ) ? privu_tdl_modal_eyebrow() : '' ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ptdl-heading">Popup heading</label></th>
                    <td><input type="text" id="ptdl-heading" name="privu_tdl_modal_title" class="large-text" value="<?php echo esc_attr( function_exists( 'privu_tdl_modal_title' ) ? privu_tdl_modal_title() : '' ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ptdl-subtitle">Popup subtitle</label></th>
                    <td><input type="text" id="ptdl-subtitle" name="privu_tdl_modal_subtitle" class="large-text" value="<?php echo esc_attr( function_exists( 'privu_tdl_modal_subtitle' ) ? privu_tdl_modal_subtitle() : '' ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ptdl-footer">Popup footer text</label></th>
                    <td><input type="text" id="ptdl-footer" name="privu_tdl_modal_footer" class="large-text" value="<?php echo esc_attr( function_exists( 'privu_tdl_modal_footer' ) ? privu_tdl_modal_footer() : '' ); ?>"></td>
                </tr>
            </table>

            <table class="widefat striped ptdl-admin-table">
                <thead>
                    <tr>
                        <th style="width:48%">Template name</th>
                        <th style="width:42%">Downloadable file</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody id="ptdl-admin-rows">
                    <?php
                    if ( empty( $rows ) ) {
                        $rows = array( '' => array( 'title' => '', 'keywords' => '', 'attachment_id' => 0, 'file' => '' ) );
                    }

                    $index = 0;

                    foreach ( $rows as $row ) :
                        $attachment_id = isset( $row['attachment_id'] ) ? (int) $row['attachment_id'] : 0;
                        $file          = isset( $row['file'] ) ? $row['file'] : '';
                        $label         = privu_tdl_admin_file_label( $row );
                        ?>
                        <tr class="ptdl-admin-row">
                            <td>
                                <input type="text" class="widefat" data-field="title" name="templates[<?php echo (int) $index; ?>][title]" value="<?php echo esc_attr( $row['title'] ); ?>" placeholder="e.g. DPDP Act Compliance Checklist">
                                <p class="description">Exact card heading from the Assessment Templates page.</p>
                            </td>
                            <td>
                                <input type="hidden" class="ptdl-attachment-id" data-field="attachment_id" name="templates[<?php echo (int) $index; ?>][attachment_id]" value="<?php echo esc_attr( $attachment_id ); ?>">
                                <input type="hidden" class="ptdl-attachment-file" data-field="file" name="templates[<?php echo (int) $index; ?>][file]" value="<?php echo esc_attr( $file ); ?>">
                                <span class="ptdl-attachment-name"><?php echo esc_html( $label ); ?></span>
                                <span class="ptdl-attachment-actions">
                                    <button type="button" class="button button-small ptdl-select-file">Choose file</button>
                                    <button type="button" class="button-link ptdl-clear-file">Remove</button>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="button-link ptdl-remove-row" style="color:#b32d2e">Delete</button>
                            </td>
                        </tr>
                        <?php
                        $index++;
                    endforeach;
                    ?>
                </tbody>
            </table>

            <p class="ptdl-admin-actions">
                <button type="button" class="button" id="ptdl-admin-add-row">+ Add template</button>
                <button type="submit" class="button button-primary">Save templates</button>
            </p>
        </form>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ptdl-admin-reset" onsubmit="return confirm('Restore the 6 default templates? Your custom changes will be replaced.');">
            <input type="hidden" name="action" value="privu_tdl_reset_templates">
            <?php wp_nonce_field( 'privu_tdl_reset_templates' ); ?>
            <button type="submit" class="button-link">Restore default templates</button>
        </form>

        <?php if ( $cf7_form_id ) : ?>
            <hr>
            <p>
                Popup form: <strong>Privu Template Download</strong> (ID <code><?php echo (int) $cf7_form_id; ?></code>) —
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $cf7_form_id . '&action=edit' ) ); ?>">Edit Contact Form 7 form</a>
            </p>
        <?php endif; ?>
    </div>

    <script type="text/html" id="tmpl-ptdl-admin-row">
        <tr class="ptdl-admin-row">
            <td>
                <input type="text" class="widefat" data-field="title" placeholder="e.g. DPDP Readiness Assessment">
                <p class="description">Exact card heading from the Assessment Templates page.</p>
            </td>
            <td>
                <input type="hidden" class="ptdl-attachment-id" data-field="attachment_id" value="0">
                <input type="hidden" class="ptdl-attachment-file" data-field="file" value="">
                <span class="ptdl-attachment-name"></span>
                <span class="ptdl-attachment-actions">
                    <button type="button" class="button button-small ptdl-select-file">Choose file</button>
                    <button type="button" class="button-link ptdl-clear-file">Remove</button>
                </span>
            </td>
            <td><button type="button" class="button-link ptdl-remove-row" style="color:#b32d2e">Delete</button></td>
        </tr>
    </script>

    <style>
        .ptdl-admin { max-width: 980px; }
        .ptdl-admin-intro { max-width: 780px; font-size: 14px; line-height: 1.6; }
        .ptdl-admin-table { border-collapse: collapse; margin-top: 12px; }
        .ptdl-admin-table th { font-weight: 600; }
        .ptdl-admin-table td { vertical-align: top; padding: 12px 10px; }
        .ptdl-admin-table .description { margin: 4px 0 0; font-size: 11px; color: #646970; }
        .ptdl-attachment-name { display: block; font-size: 12px; color: #2271b1; margin-bottom: 6px; word-break: break-all; }
        .ptdl-attachment-actions { display: flex; gap: 8px; align-items: center; }
        .ptdl-admin-actions { display: flex; gap: 10px; align-items: center; margin: 16px 0 6px; }
        .ptdl-admin-reset { margin-top: 10px; }
    </style>

    <script>
    (function () {
        var table = document.querySelector('.ptdl-admin-table');
        var tbody = document.getElementById('ptdl-admin-rows');
        var tmpl = document.getElementById('tmpl-ptdl-admin-row');

        if (!table || !tbody || !tmpl) {
            return;
        }

        function reindex() {
            tbody.querySelectorAll('.ptdl-admin-row').forEach(function (row, index) {
                row.querySelectorAll('[data-field]').forEach(function (input) {
                    input.name = 'templates[' + index + '][' + input.getAttribute('data-field') + ']';
                });
            });
        }

        function openMedia(row) {
            if (!window.wp || !wp.media) {
                window.alert('Media Library could not be loaded. Please refresh the page.');
                return;
            }

            var frame = wp.media({
                title: 'Select template file',
                button: { text: 'Use this file' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();

                row.querySelector('.ptdl-attachment-id').value = attachment.id || 0;
                row.querySelector('.ptdl-attachment-file').value = '';
                row.querySelector('.ptdl-attachment-name').textContent = attachment.filename || attachment.title || '';
            });

            frame.open();
        }

        table.addEventListener('click', function (event) {
            var row = event.target.closest('.ptdl-admin-row');

            if (!row) {
                return;
            }

            if (event.target.classList.contains('ptdl-select-file')) {
                event.preventDefault();
                openMedia(row);
            }

            if (event.target.classList.contains('ptdl-clear-file')) {
                event.preventDefault();
                row.querySelector('.ptdl-attachment-id').value = '0';
                row.querySelector('.ptdl-attachment-file').value = '';
                row.querySelector('.ptdl-attachment-name').textContent = '';
            }

            if (event.target.classList.contains('ptdl-remove-row')) {
                event.preventDefault();
                row.parentNode.removeChild(row);
                reindex();
            }
        });

        document.getElementById('ptdl-admin-add-row').addEventListener('click', function () {
            var html = tmpl.innerHTML.trim();
            var wrapper = document.createElement('tbody');
            wrapper.innerHTML = html;
            tbody.appendChild(wrapper.firstElementChild);
            reindex();
        });
    })();
    </script>
    <?php
}

/**
 * Save the template rows.
 */
function privu_tdl_admin_save() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized', 403 );
    }

    check_admin_referer( 'privu_tdl_save_templates' );

    $raw          = isset( $_POST['templates'] ) && is_array( $_POST['templates'] ) ? wp_unslash( $_POST['templates'] ) : array();
    $defaults     = privu_tdl_default_templates();
    $default_list = array_values( $defaults );
    $rows         = array();
    $used         = array();
    $row_index    = 0;

    foreach ( $raw as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        $title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';

        if ( '' === trim( $title ) ) {
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

        $attachment_id = isset( $row['attachment_id'] ) ? absint( $row['attachment_id'] ) : 0;
        $file          = isset( $row['file'] ) ? sanitize_file_name( $row['file'] ) : '';

        if ( ! $attachment_id && '' === $file ) {
            if ( isset( $defaults[ $slug ]['file'] ) ) {
                $file = $defaults[ $slug ]['file'];
            } elseif ( isset( $default_list[ $row_index ]['file'] ) ) {
                $file = $default_list[ $row_index ]['file'];
            }
        }

        $rows[ $slug ] = array(
            'title'         => $title,
            'keywords'      => '',
            'attachment_id' => $attachment_id,
            'file'          => $file,
        );

        $row_index++;
    }

    if ( isset( $_POST['privu_tdl_cf7_shortcode'] ) ) {
        $sc = sanitize_text_field( wp_unslash( $_POST['privu_tdl_cf7_shortcode'] ) );
        update_option( 'options_privu_tdl_cf7_shortcode', $sc, true );
    }

    if ( isset( $_POST['privu_tdl_modal_eyebrow'] ) ) {
        update_option( 'options_privu_tdl_modal_eyebrow', sanitize_text_field( wp_unslash( $_POST['privu_tdl_modal_eyebrow'] ) ), true );
    }

    if ( isset( $_POST['privu_tdl_modal_title'] ) ) {
        $t = sanitize_text_field( wp_unslash( $_POST['privu_tdl_modal_title'] ) );
        update_option( 'options_privu_tdl_modal_title', $t ? $t : PRIVU_TDL_DEFAULT_MODAL_TITLE, true );
    }

    if ( isset( $_POST['privu_tdl_modal_subtitle'] ) ) {
        update_option( 'options_privu_tdl_modal_subtitle', sanitize_text_field( wp_unslash( $_POST['privu_tdl_modal_subtitle'] ) ), true );
    }

    if ( isset( $_POST['privu_tdl_modal_footer'] ) ) {
        update_option( 'options_privu_tdl_modal_footer', sanitize_text_field( wp_unslash( $_POST['privu_tdl_modal_footer'] ) ), true );
    }

    if ( empty( $rows ) ) {
        delete_option( 'privu_tdl_templates' );
    } else {
        update_option( 'privu_tdl_templates', $rows, false );
    }

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'         => 'templates-checklists',
                'ptdl-updated' => 'saved',
            ),
            admin_url( 'admin.php' )
        )
    );
    exit;
}
add_action( 'admin_post_privu_tdl_save_templates', 'privu_tdl_admin_save' );

/**
 * Restore the built-in defaults.
 */
function privu_tdl_admin_reset() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized', 403 );
    }

    check_admin_referer( 'privu_tdl_reset_templates' );

    delete_option( 'privu_tdl_templates' );

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'         => 'templates-checklists',
                'ptdl-updated' => 'reset',
            ),
            admin_url( 'admin.php' )
        )
    );
    exit;
}
add_action( 'admin_post_privu_tdl_reset_templates', 'privu_tdl_admin_reset' );

/**
 * Load the media library only on our screen.
 *
 * @param string $hook Current admin page hook.
 */
function privu_tdl_admin_assets( $hook ) {
    if ( 'toplevel_page_templates-checklists' !== $hook || function_exists( 'acf_add_options_page' ) ) {
        return;
    }

    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'privu_tdl_admin_assets' );
