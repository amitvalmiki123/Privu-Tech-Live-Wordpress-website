<?php
/**
 * Admin screen for the Assessment Template downloads.
 *
 * Lets the client add, edit, reorder and delete template cards without
 * touching code: name, detection keywords, slug and the downloadable file
 * (uploaded through the normal WordPress media library).
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
 * Register the admin menu.
 */
function privu_tdl_admin_menu() {
    // ACF PRO ka apna "Template Downloads" page hai – tab ye fallback screen na dikhaye.
    if ( function_exists( 'acf_add_options_page' ) ) {
        return;
    }

    add_menu_page(
        'Template Downloads',
        'Template Downloads',
        'manage_options',
        'privu-template-downloads',
        'privu_tdl_admin_page',
        'dashicons-download',
        58.6
    );
}
add_action( 'admin_menu', 'privu_tdl_admin_menu' );

/**
 * Current templates in the admin format (slug => title/keywords/attachment_id/file).
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
            'keywords'      => implode( ', ', $template['keywords'] ),
            'attachment_id' => 0,
            'file'          => $template['file'],
        );
    }

    return $rows;
}

/**
 * Human readable file label for a row.
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
 * Render the admin screen.
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
        <h1>Template Downloads</h1>

        <?php if ( 'saved' === $updated ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Templates saved.</strong> Cache purge karna na bhoolein.</p></div>
        <?php elseif ( 'reset' === $updated ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Default templates restored.</strong></p></div>
        <?php endif; ?>

        <p class="ptdl-admin-intro">
            Ye list popup ke saare template cards control karti hai. Naya card page me add karne ke baad,
            yahan uske liye ek row add karein aur file upload kar dein — code change karne ki zarurat nahi.
        </p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ptdl-admin-form">
            <input type="hidden" name="action" value="privu_tdl_save_templates">
            <?php wp_nonce_field( 'privu_tdl_save_templates' ); ?>

            <table class="widefat striped ptdl-admin-table">
                <thead>
                    <tr>
                        <th style="width:22%">Template name</th>
                        <th style="width:26%">Keywords (auto-detect)</th>
                        <th style="width:12%">Slug</th>
                        <th style="width:30%">Downloadable file</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody id="ptdl-admin-rows">
                    <?php
                    if ( empty( $rows ) ) {
                        $rows = array( '' => array( 'title' => '', 'keywords' => '', 'attachment_id' => 0, 'file' => '' ) );
                    }

                    $index = 0;

                    foreach ( $rows as $slug => $row ) :
                        $attachment_id = isset( $row['attachment_id'] ) ? (int) $row['attachment_id'] : 0;
                        $file          = isset( $row['file'] ) ? $row['file'] : '';
                        $label         = privu_tdl_admin_file_label( $row );
                        ?>
                        <tr class="ptdl-admin-row">
                            <td>
                                <input type="text" class="widefat" name="templates[<?php echo (int) $index; ?>][title]" value="<?php echo esc_attr( $row['title'] ); ?>" placeholder="e.g. DPDP Act Compliance Checklist">
                            </td>
                            <td>
                                <input type="text" class="widefat" name="templates[<?php echo (int) $index; ?>][keywords]" value="<?php echo esc_attr( $row['keywords'] ); ?>" placeholder="checklist, dpdp act">
                                <p class="description">Card ke heading me aane wale 2–3 words, comma se alag.</p>
                            </td>
                            <td>
                                <input type="text" class="widefat" name="templates[<?php echo (int) $index; ?>][slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="auto">
                                <p class="description">Purane slug na badlein.</p>
                            </td>
                            <td>
                                <input type="hidden" class="ptdl-attachment-id" name="templates[<?php echo (int) $index; ?>][attachment_id]" value="<?php echo esc_attr( $attachment_id ); ?>">
                                <input type="hidden" class="ptdl-attachment-file" name="templates[<?php echo (int) $index; ?>][file]" value="<?php echo esc_attr( $file ); ?>">
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

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ptdl-admin-reset" onsubmit="return confirm('Default templates restore karein? Aapke saare custom rows hat jayenge.');">
            <input type="hidden" name="action" value="privu_tdl_reset_templates">
            <?php wp_nonce_field( 'privu_tdl_reset_templates' ); ?>
            <button type="submit" class="button-link">Restore default templates</button>
        </form>

        <hr>

        <h2>Contact Form 7</h2>

        <?php if ( $cf7_form_id ) : ?>
            <p>
                Popup ka form: <strong>Privu Template Download</strong> (ID <code><?php echo (int) $cf7_form_id; ?></code>)<br>
                <a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $cf7_form_id . '&action=edit' ) ); ?>">CF7 form edit karein</a>
            </p>
            <p class="description">
                Form page par manually lagane ki zarurat nahi — theme popup ke andar khud print karta hai.
                Kisi aur page par form chahiye to shortcode:
                <code>[contact-form-7 id="<?php echo (int) $cf7_form_id; ?>" title="Privu Template Download"]</code>
            </p>
        <?php else : ?>
            <p>Contact Form 7 active nahi hai ya form abhi create nahi hua. Admin page refresh karein.</p>
        <?php endif; ?>

        <hr>

        <h2>Naya template card kaise add karein?</h2>
        <ol class="ptdl-admin-steps">
            <li><strong>Page me card banayein</strong> — Assessment Templates page ko WP Bakery se edit karein, koi bhi card duplicate karein aur uska heading badal dein (jaise “DPDP Readiness Assessment”). Download link ka text “Download Template” hi rakhein.</li>
            <li><strong>Yahan row add karein</strong> — upar <em>+ Add template</em> par click karein, wahi heading/naam likhein.</li>
            <li><strong>Keywords</strong> — card ke heading ke 2–3 words comma se likhein. Isse popup ko pata chalta hai kaunsa card click hua.</li>
            <li><strong>File upload</strong> — <em>Choose file</em> se PDF/Word/Excel upload karein. Yehi file visitor ko email attachment aur download button se milegi.</li>
            <li><strong>Save</strong> karein, phir page cache purge karein aur ek baar test karein.</li>
        </ol>

        <h2>Kisi bhi page par download button (optional)</h2>
        <p>Shortcode se kahin bhi download button laga sakte hain — popup wahi khulega:</p>
        <p><code>[privu_template_download slug="dpia" text="Download Template"]</code></p>
    </div>

    <script type="text/html" id="tmpl-ptdl-admin-row">
        <tr class="ptdl-admin-row">
            <td><input type="text" class="widefat" data-field="title" placeholder="e.g. DPDP Readiness Assessment"></td>
            <td>
                <input type="text" class="widefat" data-field="keywords" placeholder="readiness, assessment">
                <p class="description">Card ke heading me aane wale 2–3 words, comma se alag.</p>
            </td>
            <td>
                <input type="text" class="widefat" data-field="slug" placeholder="auto">
                <p class="description">Khali chhodein to naam se ban jayega.</p>
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
        .ptdl-admin { max-width: 1180px; }
        .ptdl-admin-intro { max-width: 780px; font-size: 14px; line-height: 1.6; }
        .ptdl-admin-table { border-collapse: collapse; margin-top: 12px; }
        .ptdl-admin-table th { font-weight: 600; }
        .ptdl-admin-table td { vertical-align: top; padding: 12px 10px; }
        .ptdl-admin-table .description { margin: 4px 0 0; font-size: 11px; color: #646970; }
        .ptdl-attachment-name { display: block; font-size: 12px; color: #2271b1; margin-bottom: 6px; word-break: break-all; }
        .ptdl-attachment-actions { display: flex; gap: 8px; align-items: center; }
        .ptdl-admin-actions { display: flex; gap: 10px; align-items: center; margin: 16px 0 6px; }
        .ptdl-admin-reset { margin-top: 10px; }
        .ptdl-admin-steps { max-width: 780px; }
        .ptdl-admin-steps li { margin-bottom: 8px; line-height: 1.6; }
    </style>

    <script>
    (function () {
        var table = document.querySelector('.ptdl-admin-table');
        var tbody = document.getElementById('ptdl-admin-rows');
        var tmpl = document.getElementById('tmpl-ptdl-admin-row');

        if (!table || !tbody || !tmpl) {
            return;
        }

        if (window.wp && wp.media) {
            // media library already available through wp_enqueue_media()
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
                window.alert('Media library load nahi hui. Page refresh karein.');
                return;
            }

            var frame = wp.media({
                title: 'Template file choose karein',
                button: { text: 'Use this file' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();

                row.querySelector('.ptdl-attachment-id').value = attachment.id || 0;
                row.querySelector('.ptdl-attachment-file').value = '';
                row.querySelector('.ptdl-attachment-name').textContent = attachment.filename || attachment.title || '';
                row.querySelector('[data-field="title"]').value = row.querySelector('[data-field="title"]').value;
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

    $raw  = isset( $_POST['templates'] ) && is_array( $_POST['templates'] ) ? wp_unslash( $_POST['templates'] ) : array();
    $rows = array();
    $used = array();

    foreach ( $raw as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        $title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';

        if ( '' === trim( $title ) ) {
            continue;
        }

        $slug = isset( $row['slug'] ) ? sanitize_key( $row['slug'] ) : '';

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

        $rows[ $slug ] = array(
            'title'         => $title,
            'keywords'      => isset( $row['keywords'] ) ? sanitize_text_field( $row['keywords'] ) : '',
            'attachment_id' => isset( $row['attachment_id'] ) ? absint( $row['attachment_id'] ) : 0,
            'file'          => isset( $row['file'] ) ? sanitize_file_name( $row['file'] ) : '',
        );
    }

    if ( empty( $rows ) ) {
        delete_option( 'privu_tdl_templates' );
    } else {
        update_option( 'privu_tdl_templates', $rows, false );
    }

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'         => 'privu-template-downloads',
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
                'page'         => 'privu-template-downloads',
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
    if ( 'toplevel_page_privu-template-downloads' !== $hook || function_exists( 'acf_add_options_page' ) ) {
        return;
    }

    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'privu_tdl_admin_assets' );
