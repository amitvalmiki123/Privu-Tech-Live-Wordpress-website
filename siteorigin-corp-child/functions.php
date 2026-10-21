<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'siteorigin-corp-style','siteorigin-corp-style','siteorigin-corp-icons' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

function privu_enqueue_team_assets() {
    wp_enqueue_style(
        'privu-team',
        get_stylesheet_directory_uri() . '/css/privu-team.css',
        [], '1.0.0'
    );
    wp_enqueue_script(
        'privu-team',
        get_stylesheet_directory_uri() . '/js/privu-team.js',
        [], '1.0.0', true
    );
}
add_action( 'wp_enqueue_scripts', 'privu_enqueue_team_assets' );

function privu_team_js() {
    wp_enqueue_script(
        'privu-team',
        get_stylesheet_directory_uri() . '/js/privu-team.js',
        [], '1.0.0', true
    );
}
add_action( 'wp_enqueue_scripts', 'privu_team_js' );

// END ENQUEUE PARENT ACTION



register_sidebar(
    array(
            'name'          => __( 'Contact Widget ', 'SiteOrigin Corp' ),
            'id'            => 'contact_widget',
            'before_widget' => '',
            'after_widget'  => '',
            'before_title'  => '',
            'after_title'   => '',
    )
);

//custom blog sidebar
function custom_blog_sidebar() {
    register_sidebar(array(
        'name'          => 'Blog Sidebar',
        'id'            => 'blog-sidebar',
        'before_widget' => '<div class="sidebar-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'custom_blog_sidebar');

add_filter( 'request', function( $query_vars ) {
    if ( isset( $_GET['s'] ) && empty( $_GET['s'] ) ) {
        $query_vars['s'] = ' '; // Ek space add kar deta hai taaki WordPress redirect na kare
    }
    return $query_vars;
});

/* =============================
   GLOBAL ORGANIZATION + WEBSITE
============================= */

add_action('wp_head','privu_global_schema');

function privu_global_schema(){

if(is_front_page()){
?>

<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@graph":[

  {
   "@type":"Organization",
   "name":"Privu",
   "url":"https://privu.tech",
   "logo":"https://privu.tech/wp-content/uploads/2025/01/privu-logo.png"
  },

  {
   "@type":"WebSite",
   "url":"https://privu.tech",
   "name":"Privu",
   "potentialAction":{
    "@type":"SearchAction",
    "target":"https://privu.tech/?s={search_term_string}",
    "query-input":"required name=search_term_string"
   }
  }

 ]
}
</script>

<?php
}

}

/* ===============================
   AUTO BLOGPOSTING SCHEMA
================================ */

add_action('wp_head','privu_blog_schema');

function privu_blog_schema(){

if(is_single()){

global $post;

$schema = [
"@context" => "https://schema.org",
"@type" => "BlogPosting",
"headline" => get_the_title(),
"description" => get_the_excerpt(),
"image" => get_the_post_thumbnail_url($post->ID,'full'),
"author" => [
 "@type" => "Person",
 "name" => get_the_author()
],
"publisher" => [
 "@type" => "Organization",
 "name" => "Privu",
 "logo" => [
  "@type" => "ImageObject",
  "url" => "https://privu.tech/wp-content/uploads/2025/01/privu-logo.png"
 ]
],
"datePublished" => get_the_date('c'),
"dateModified" => get_the_modified_date('c'),
"mainEntityOfPage" => [
 "@type" => "WebPage",
 "@id" => get_permalink()
]
];

echo '<script type="application/ld+json">'.json_encode($schema).'</script>';

}

}

/* ===============================
   BREADCRUMB SCHEMA (ALL PAGES)
================================ */

add_action('wp_head','privu_breadcrumb_schema');

function privu_breadcrumb_schema(){

if(is_singular()){

?>
<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@type":"BreadcrumbList",
 "itemListElement":[
  {
   "@type":"ListItem",
   "position":1,
   "name":"Home",
   "item":"https://privu.tech"
  },
  {
   "@type":"ListItem",
   "position":2,
   "name":"<?php echo get_the_title(); ?>",
   "item":"<?php echo get_permalink(); ?>"
  }
 ]
}
</script>
<?php

}

}

/* Post page schema*/
add_action('wp_head', 'privu_custom_schema_override', 1);

function privu_custom_schema_override() {

    if (!is_single()) return; // sirf posts ke liye

    global $post;

    $schema = get_field('custom_schema', $post->ID);

    if (!empty($schema)) {

        add_filter('rank_math/json_ld', '__return_empty_array');

        $schema = trim($schema);
        $decoded = json_decode($schema);

        if (json_last_error() === JSON_ERROR_NONE) {
            echo '<script type="application/ld+json">' . $schema . '</script>';
        }
    }
}

/* ===============================
   COLLECTION PAGE SCHEMA
================================ */

add_action('wp_head','privu_template_schema_pro');

function privu_template_schema_pro(){

if(is_page('assessment-templates')){
?>

<script type="application/ld+json">
{
 "@context":"https://schema.org",
 "@type":"CollectionPage",
 "name":"Assessment Templates",
 "url":"https://privu.tech/assessment-templates/"
}
</script>

<?php
}

}

// ================================================================
// PRIVU TEAM SECTION
// Options Page
if ( function_exists('acf_add_options_page') ) {
    acf_add_options_page([
        'page_title' => 'Team Settings',
        'menu_title' => 'Team Settings',
        'menu_slug'  => 'theme-settings',
        'redirect'   => false,
        'menu_icon'  => 'dashicons-groups',
    ]);
    acf_add_options_sub_page([
        'page_title'  => 'Team Section',
        'menu_title'  => 'Team Section',
        'parent_slug' => 'theme-settings',
    ]);
}

// Helper: Naam se Initials
if ( ! function_exists('privu_get_initials') ) {
    function privu_get_initials( $name ) {
        $words    = explode( ' ', trim( $name ) );
        $initials = '';
        foreach ( array_slice( $words, 0, 2 ) as $w ) {
            $initials .= strtoupper( $w[0] ?? '' );
        }
        return $initials;
    }
}

// Shortcode: [privu_team]
function privu_team_shortcode() {

    $section_heading = get_field( 'section_heading', 'option' ) ?: 'Our Team';
    $section_tagline = get_field( 'section_tagline', 'option' ) ?: 'Strategy, business acumen &amp; technical depth — built to deliver outcomes, not just reports.';
    $cofounders      = get_field( 'cofounders', 'option' );
    $advisors        = get_field( 'advisors',   'option' );

    ob_start();
    ?>

    <section class="privu-team-section" id="privu-team">

        <div class="pts-header">
            <div class="pts-header-inner">
                <div class="pts-badge">Meet the People</div>
                <h2 class="pts-main-heading"><?php echo esc_html( $section_heading ); ?></h2>
                <p class="pts-tagline"><?php echo wp_kses_post( $section_tagline ); ?></p>
            </div>
            <div class="pts-header-deco" aria-hidden="true">
                <span class="pts-deco-ring pts-deco-ring--1"></span>
                <span class="pts-deco-ring pts-deco-ring--2"></span>
                <span class="pts-deco-ring pts-deco-ring--3"></span>
            </div>
        </div>

        <?php if ( ! empty( $cofounders ) ) : ?>
        <div class="pts-block pts-block--founders">
            <div class="pts-block-label">
                <span class="pts-label-line"></span>
                <span class="pts-label-text">Co-Founders</span>
                <span class="pts-label-line"></span>
            </div>
            <div class="pts-founders-grid">
                <?php foreach ( $cofounders as $i => $founder ) :
                    $name      = esc_html( $founder['founder_name']         ?? '' );
                    $role_tag  = esc_html( $founder['founder_role_tag']     ?? '' );
                    $image_url = esc_url(  $founder['founder_image']['url'] ?? '' );
                    $image_alt = esc_attr( $founder['founder_image']['alt'] ?? $name );
                    $pts_raw   = $founder['founder_points'] ?? '';
                    $points    = array_filter( array_map( 'trim', explode( "\n", $pts_raw ) ) );
                    $initials  = privu_get_initials( $name );
                    $delay     = $i * 120;
                ?>
                <div class="pts-founder-card" data-delay="<?php echo $delay; ?>">
                    <div class="pts-card-glow" aria-hidden="true"></div>
                    <div class="pts-founder-top">
                        <div class="pts-avatar-wrap">
                            <?php if ( $image_url ) : ?>
                                <img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>" class="pts-avatar-img" />
                            <?php else : ?>
                                <div class="pts-avatar-initials"><?php echo esc_html( $initials ); ?></div>
                            <?php endif; ?>
                            <div class="pts-avatar-ring" aria-hidden="true"></div>
                        </div>
                        <div class="pts-founder-meta">
                            <h3 class="pts-member-name"><?php echo $name; ?></h3>
                            <span class="pts-role-tag"><?php echo $role_tag; ?></span>
                        </div>
                    </div>
                    <div class="pts-card-divider"></div>
                    <?php if ( ! empty( $points ) ) : ?>
                    <ul class="pts-points-list">
                        <?php foreach ( $points as $pt ) : ?>
                        <li class="pts-point-item">
                            <span class="pts-point-dot" aria-hidden="true"></span>
                            <span><?php echo esc_html( $pt ); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $advisors ) ) : ?>
        <div class="pts-block pts-block--advisors">
            <div class="pts-block-label pts-block-label--teal">
                <span class="pts-label-line"></span>
                <span class="pts-label-text">Advisors</span>
                <span class="pts-label-line"></span>
            </div>
            <div class="pts-advisors-grid">
                <?php
                $colors = [ 'blue', 'teal', 'navy' ];
                foreach ( $advisors as $i => $advisor ) :
                    $name        = esc_html( $advisor['advisor_name']          ?? '' );
                    $role_tag    = esc_html( $advisor['advisor_role_tag']      ?? '' );
                    $image_url   = esc_url(  $advisor['advisor_image']['url']  ?? '' );
                    $image_alt   = esc_attr( $advisor['advisor_image']['alt']  ?? $name );
                    $description = esc_html( $advisor['advisor_description']   ?? '' );
                    $initials    = privu_get_initials( $name );
                    $color       = $colors[ $i % count( $colors ) ];
                    $delay       = $i * 100;
                ?>
                <div class="pts-advisor-card pts-advisor-card--<?php echo $color; ?>" data-delay="<?php echo $delay; ?>">
                    <div class="pts-advisor-accent" aria-hidden="true"></div>
                    <div class="pts-advisor-avatar-wrap">
                        <?php if ( $image_url ) : ?>
                            <img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>" class="pts-avatar-img pts-avatar-img--advisor" />
                        <?php else : ?>
                            <div class="pts-avatar-initials pts-avatar-initials--advisor"><?php echo esc_html( $initials ); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="pts-advisor-info">
                        <h3 class="pts-member-name pts-member-name--advisor"><?php echo $name; ?></h3>
                        <span class="pts-advisor-role"><?php echo $role_tag; ?></span>
                        <?php if ( $description ) : ?>
                        <p class="pts-advisor-desc"><?php echo $description; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </section>

    <?php
    return ob_get_clean();
}
add_shortcode( 'privu_team', 'privu_team_shortcode' );

add_filter('the_content', 'insert_form_before_faq_wrapper');

function insert_form_before_faq_wrapper($content) {
    // Sirf single blog post par kaam kare
    if (is_single()) {
        
        // Aapka contact form HTML
        $contact_form_html = '
        <div id="blog-form-section" class="blog-contact-form contact_row">
            <div class="right-row">' 
                . do_shortcode('[contact-form-7 id="41d9575" title="Contact form"]') . 
            '</div>
        </div>';

        // Aapki FAQ wrapper class
        $faq_class = '<div class="privu-faq-wrapper">';

        // Agar post content mein ye class milti hai, toh usse pehle form add kar do
        if (strpos($content, $faq_class) !== false) {
            $content = str_replace($faq_class, $contact_form_html . $faq_class, $content);
        }
    }
    return $content;
}


// ================================================================
// PRIVU HR DPDP PAGE
// ================================================================

// 1. DPDP Page CSS Enqueue
add_action( 'wp_enqueue_scripts', function() {
    if ( is_page_template( 'page-privu-hr-dpdp.php' ) ) {
        wp_enqueue_style(
            'privu-hr-dpdp',
            get_stylesheet_directory_uri() . '/css/privu-hr-dpdp.css',
            array(),
            '1.0.0'
        );
    }
});

// 2. ACF Field Group Load
$privu_dpdp_acf = get_stylesheet_directory() . '/inc/acf-dpdp-fields.php';
if ( file_exists( $privu_dpdp_acf ) ) {
    require_once $privu_dpdp_acf;
}

// 3. CTA Form Handler (sirf tab kaam karta hai jab CF7 use na ho)
add_action( 'init', function() {
    if (
        isset( $_POST['pdpdp_nonce'] ) &&
        wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pdpdp_nonce'] ) ), 'pdpdp_cta_form' )
    ) {
        $name  = sanitize_text_field( isset( $_POST['pdpdp_name']  ) ? $_POST['pdpdp_name']  : '' );
        $org   = sanitize_text_field( isset( $_POST['pdpdp_org']   ) ? $_POST['pdpdp_org']   : '' );
        $email = sanitize_email(      isset( $_POST['pdpdp_email'] ) ? $_POST['pdpdp_email'] : '' );

        if ( $name && $org && is_email( $email ) ) {
            $to      = 'contact@privu.tech';
            $subject = 'New DPDP Consultation Request from ' . $name;
            $body    = "Name: $name\nOrganization: $org\nEmail: $email";
            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $email,
            );
            wp_mail( $to, $subject, $body, $headers );
        }

        wp_safe_redirect( add_query_arg( 'dpdp_sent', '1', get_permalink() ) );
        exit;
    }
});

// ================================================================
// ASSESSMENT TEMPLATE DOWNLOADS
// Lead form + gated download + email delivery.
// Implementation lives in inc/privu-template-downloads.php so this
// file stays untouched apart from this include.
// ================================================================
$privu_tdl_include = get_stylesheet_directory() . '/inc/privu-template-downloads.php';
if ( file_exists( $privu_tdl_include ) ) {
    require_once $privu_tdl_include;
}

// ================================================================
// PRODUCT LOGIN LEAD GATE (Header + Global Footer -> CF7 -> cms.privu.tech)
// ================================================================
$privu_login_include = get_stylesheet_directory() . '/inc/privu-product-login.php';
if ( file_exists( $privu_login_include ) ) {
    require_once $privu_login_include;
}
