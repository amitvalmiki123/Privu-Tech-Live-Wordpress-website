<?php
/**
 * The theme header.
 *
 * This is the template that displays all of the <head> section and everything up until <div class="corp-container">.
 *
 * @see https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @license GPL 2.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>


</head>

<body <?php body_class(); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
}
do_action( 'siteorigin_corp_body_top' );
?>

<!--  -->
<?php if ( is_front_page() ) : ?>
  <?php get_template_part('consent-section'); ?>
<?php endif; ?>
<!--  -->

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'siteorigin-corp' ); ?></a>

	<?php if ( class_exists( 'Woocommerce' ) && is_store_notice_showing() ) { ?>
		<div id="topbar">
			<?php siteorigin_corp_woocommerce_demo_store(); ?>
		</div><!-- #topbar -->
	<?php
	}
	do_action( 'siteorigin_corp_header_before' );

	if ( siteorigin_page_setting( 'header', true ) ) {
		$header_classes = '';
		if ( siteorigin_setting( 'header_layout' ) == 'centered' ) {
			$header_classes .= ' centered';
		}

		if ( siteorigin_setting( 'header_sticky' ) ) {
			$header_classes .= ' sticky';
		}

		if ( siteorigin_setting( 'navigation_mobile_menu' ) ) {
			$header_classes .= ' mobile-menu';
		} 

		?>
		<header id="masthead" class="site-header<?php echo $header_classes; ?>" <?php if ( siteorigin_setting( 'header_scales' ) ) echo 'data-scale-logo="true"'; ?> >

			<div class="corp-container">

				<div class="site-header-inner" data-aos="flip-up" >

					<div class="site-branding">
						<?php siteorigin_corp_display_logo(); ?>
						<?php if ( siteorigin_setting( 'header_site_description' ) ) { ?>
							<p class="site-description"><?php bloginfo( 'description' ); ?></p>
						<?php } ?>
					</div><!-- .site-branding -->

					<?php $mega_menu_active = function_exists( 'ubermenu' ) || function_exists( 'max_mega_menu_is_enabled' ) && max_mega_menu_is_enabled( 'menu-1' ); ?>

					<?php 
					$nav_classes = '';
					if ( siteorigin_setting( 'navigation_menu_link_hover_underline' ) ) {
						$nav_classes .= ' link-underline ';
					}

					if ( $mega_menu_active ) {
						$nav_classes .= ' mega-menu';
					}

					?>

					<nav id="site-navigation" class="main-navigation<?php echo $nav_classes; ?>">

						<?php
						if ( siteorigin_setting( 'navigation_header_menu' ) ) {
							wp_nav_menu( array( 'theme_location' => 'menu-1', 'menu_id' => 'primary-menu' ) );
						}
						?>

						<?php
						if ( function_exists( 'is_woocommerce' ) && siteorigin_setting( 'woocommerce_mini_cart' ) && ! $mega_menu_active ) {
							siteorigin_corp_woocommerce_mini_cart();
						}
						?>

						<?php if ( siteorigin_setting( 'navigation_header_menu' ) && siteorigin_setting( 'navigation_mobile_menu' ) && ! $mega_menu_active ) { ?>
							<a href="#menu" id="mobile-menu-button">
								<div class="sidemenu">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>	
							</a>
						<?php } ?>
					        <?php
					        $privu_hdr_login_text = function_exists( 'privu_login_button_text' ) ? privu_login_button_text() : 'Login';
					        $privu_hdr_login_href = function_exists( 'privu_login_button_href' ) ? privu_login_button_href() : home_url( '/product-login/' );
					        $privu_hdr_login_gate = ! function_exists( 'privu_login_is_gate_enabled' ) || privu_login_is_gate_enabled();
					        ?>
					        <div class="contact-row">
					            <a href="<?php echo esc_url( $privu_hdr_login_href ); ?>" class="privu-header-login-btn"<?php echo $privu_hdr_login_gate ? ' data-privu-login-trigger="Header"' : ''; ?>><?php echo esc_html( $privu_hdr_login_text ); ?></a>
					            <a href="/contact-us/" class="privu-header-cta-btn">Get Started</a>
					        </div>
					</nav><!-- #site-navigation -->

					<?php if ( siteorigin_setting( 'navigation_menu_search' ) ) { ?>
						<div id="fullscreen-search">
							<div class="corp-container">
								<span><?php esc_html_e( 'Type and press enter to search', 'siteorigin-corp' ); ?></span>
								<form id="fullscreen-search-form" method="get" action="<?php echo esc_url( home_url() ); ?>">
									<input type="search" name="s" placeholder="" aria-label="<?php esc_attr_e( 'Search for', 'siteorigin-corp' ); ?>" value="<?php echo get_search_query(); ?>" />
									<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'siteorigin-corp' ); ?>">
										<?php siteorigin_corp_display_icon( 'search' ); ?>
									</button>
								</form>
							</div>
							<button id="search-close-button" class="search-close-button" aria-label="<?php esc_attr_e( 'Close search', 'siteorigin-corp' ); ?>">
								<span class="close"><?php siteorigin_corp_display_icon( 'close' ); ?></span>
							</button>
						</div><!-- #header-search -->
					<?php } ?>

				</div><!-- .site-header-inner -->

			</div><!-- .corp-container -->

		</header><!-- #masthead -->
	<?php
	}
	do_action( 'siteorigin_corp_content_before' );
	?>

	<div id="content" class="site-content">

	<?php 
// Hero Section
if (!function_exists('make_safe_url')) {
    function make_safe_url($url) {
        if (!$url) return '#';
        if (strpos($url, '/') === 0 || strpos($url, '#') === 0) return esc_url($url);
        if (!preg_match('/^https?:\/\//', $url)) return esc_url(home_url($url));
        return esc_url($url);
    }
}

if (is_front_page()) : 

$hero_subheading = get_field('hero_subheading');
$hero_heading = get_field('hero_heading');
$hero_description = get_field('hero_description');
$primary_btn_text = get_field('primary_btn_text');
$primary_btn_link = get_field('primary_btn_link');
$secondary_btn_text = get_field('secondary_btn_text');
$secondary_btn_link = get_field('secondary_btn_link');
$hero_image = get_field('hero_image');
?>

<section class="custom-hero-section">
    <div class="hero-container">
        
        <div class="hero-left" data-aos="zoom-in-right">
            <?php if($hero_subheading): ?>
                <span class="hero-subheading"><?php echo $hero_subheading; ?></span>
            <?php endif; ?>

            <?php if($hero_heading): ?>
                <h1 class="hero-heading"><?php echo $hero_heading; ?></h1>
            <?php endif; ?>

            <?php if($hero_description): ?>
                <p class="hero-description"><?php echo $hero_description; ?></p>
            <?php endif; ?>

            <div class="hero-buttons">
                <?php if($primary_btn_text): ?>
                    <a href="<?php echo make_safe_url($primary_btn_link); ?>" class="btn primary-btn">
                        <?php echo $primary_btn_text; ?>
                    </a>
                <?php endif; ?>

                <?php if($secondary_btn_text): ?>
                    <a href="<?php echo make_safe_url($secondary_btn_link); ?>" class="btn secondary-btn">
                        <?php echo $secondary_btn_text; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="hero-right" data-aos="zoom-in-left">
            <?php if($hero_image): ?>
                <img src="<?php echo esc_url($hero_image['url']); ?>" alt="strategic-compliance-partner-privu">
            <?php endif; ?>
        </div>

    </div>
</section>

<?php endif; ?>

<?php
/*
|--------------------------------------------------------------------------
| ONLY FOR PAGE : DPDP Compliance Staffing Firm
|--------------------------------------------------------------------------
| Separate Hero Section (Independent from Home Banner)
|--------------------------------------------------------------------------
*/

if (
    is_page(array(
        'dpdp-compliance-staffing-firm',
        'privu-hr-dpdp'
    ))
) :

    $page_id = get_the_ID();

    // Separate ACF fields for this page
    $dpdp_tagline          = get_field('dpdp_tagline', $page_id);
    $dpdp_heading          = get_field('dpdp_heading', $page_id);
    $dpdp_description      = get_field('dpdp_description', $page_id);
    $dpdp_primary_btn_text = get_field('dpdp_primary_btn_text', $page_id);
    $dpdp_primary_btn_link = get_field('dpdp_primary_btn_link', $page_id);
    $dpdp_second_btn_text  = get_field('dpdp_second_btn_text', $page_id);
    $dpdp_second_btn_link  = get_field('dpdp_second_btn_link', $page_id);
?>

<section class="dpdp-staffing-hero custom-hero-section">
    <div class="dpdp-container hero-container">

        <!-- LEFT CONTENT -->
        <div class="dpdp-left hero-left" data-aos="zoom-in-down">

            <?php if ($dpdp_tagline): ?>
                <div class="dpdp-tagline hero-subheading" data-aos="zoom-in-down">
                    <?php echo $dpdp_tagline; ?>
                </div>
            <?php endif; ?>

            <?php if ($dpdp_heading): ?>
                <h1 class="dpdp-heading hero-heading" data-aos="zoom-in-down">
                    <?php echo $dpdp_heading; ?>
                </h1>
            <?php endif; ?>

            <?php if ($dpdp_description): ?>
                <p class="dpdp-description hero-description" data-aos="zoom-in-down">
                    <?php echo $dpdp_description; ?>
                </p>
            <?php endif; ?>

            <div class="dpdp-buttons hero-buttons" data-aos="zoom-in-down">

               <?php if ($dpdp_primary_btn_text): ?>
                    <a href="#ctaform" class="dpdp-btn primary-btn btn primary-btn">
                        <?php echo $dpdp_primary_btn_text; ?>
                    </a>
                <?php endif; ?>

                <?php if ($dpdp_second_btn_text): ?>
                    <a href="<?php echo esc_url($dpdp_second_btn_link); ?>" class="dpdp-btn secondary-btn btn secondary-btn">
                        <?php echo $dpdp_second_btn_text; ?>
                    </a>
                <?php endif; ?>

            </div>

        </div>


        <!-- RIGHT GRID BOXES -->
        <div class="dpdp-right" data-aos="zoom-in-left">

            <div class="dpdp-box">
                <h3>₹250 Cr</h3>
                <p>Max penalty — breach linked to non-compliance</p>
            </div>

            <div class="dpdp-box">
                <h3>₹200 Cr</h3>
                <p>Biometric or health data without valid consent</p>
            </div>

            <div class="dpdp-box">
                <h3>May 2027</h3>
                <p>Full compliance deadline — all 23 rules</p>
            </div>

            <div class="dpdp-box">
                <h3>Now</h3>
                <p>Rules 1, 2, 17–21 already in force since Nov 2025</p>
            </div>

        </div>

    </div>
</section>

<?php endif; ?>

<!-- inner page banner -->
<?php
$subheading      = get_field('subheading');
$subheading_logo = get_field('subheading_logo');
$heading         = get_field('heading');
$description     = get_field('description');
$button_text     = get_field('button_text');
$button_link     = get_field('button_link');
$bg_image        = get_field('bg_image');
$banner_search_placeholder = get_field('banner_search_placeholder');
?>


<?php if( $heading ) : ?>
<section class="inner-hero" 
  style="<?php echo $bg_image ? 'background-image:url('.esc_url($bg_image['url']).');' : ''; ?>">
  
  <div class="container">

    <?php if($subheading || $subheading_logo): ?>
    <div class="hero-sub-wrapper" data-aos="zoom-in-down">
        
        <?php if($subheading_logo): ?>
            <img class="hero-sub-logo" 
                 src="<?php echo esc_url($subheading_logo['url']); ?>" 
                 alt="<?php echo esc_attr($subheading_logo['alt']); ?>">
        <?php endif; ?>

        <?php if($subheading): ?>
            <span class="hero-sub"><?php echo esc_html($subheading); ?></span>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <h1 class="hero-title" data-aos="zoom-in-down"><?php echo wp_kses_post($heading); ?></h1>

    <?php if ($description): ?>
      <p class="hero-desc" data-aos="zoom-in-down"><?php echo esc_html($description); ?></p>
    <?php endif; ?>

    <?php if ( $banner_search_placeholder ) : ?>
        <form class="banner-search" data-aos="zoom-in-down" id="topicSearchForm" action="<?php echo esc_url( home_url('/') ); ?>" method="get">
         
            <input 
                type="search"
                id="topicSearch"
                name="s"
                placeholder="<?php echo esc_attr( $banner_search_placeholder ); ?>"
            >
 
            <button type="submit" id="searchBtn" class="search-btn" aria-label="Search"></button>
 
        </form>
    <?php endif; ?>

    <?php if ($button_text && $button_link): ?>
      <a href="<?php echo esc_url($button_link['url']); ?>"
         target="<?php echo esc_attr($button_link['target']); ?>"
         class="hero-btn" data-aos="zoom-in-down">
         <?php echo esc_html($button_text); ?>
      </a>
    <?php endif; ?>

  </div>
</section>
<?php endif; ?>

<!-- blog banner-->
<?php if ( is_home() || is_archive() ) : ?>

<?php
$subheading      = get_field('subheading', 'option');
$subheading_logo = get_field('subheading_logo', 'option');
$heading         = get_field('heading', 'option');
$description     = get_field('description', 'option');
$button_text     = get_field('button_text', 'option');
$button_link     = get_field('button_link', 'option');
$bg_image        = get_field('bg_image', 'option');
?>

<?php if( $heading ) : ?>
<section class="inner-hero" 
  style="<?php echo $bg_image ? 'background-image:url('.esc_url($bg_image['url']).');' : ''; ?>">
  
  <div class="container">

    <?php if($subheading || $subheading_logo): ?>
    <div class="hero-sub-wrapper" data-aos="zoom-in-down">
        
        <?php if($subheading_logo): ?>
            <img class="hero-sub-logo" 
                 src="<?php echo esc_url($subheading_logo['url']); ?>" 
                 alt="<?php echo esc_attr($subheading_logo['alt']); ?>">
        <?php endif; ?>

        <?php if($subheading): ?>
            <span class="hero-sub"><?php echo esc_html($subheading); ?></span>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <h1 class="hero-title" data-aos="zoom-in-down"><?php echo esc_html($heading); ?></h1>

    <?php if ($description): ?>
      <p class="hero-desc" data-aos="zoom-in-down"><?php echo esc_html($description); ?></p>
    <?php endif; ?>

    <?php if ($button_text && $button_link): ?>
      <a href="<?php echo esc_url($button_link['url']); ?>"
         target="<?php echo esc_attr($button_link['target']); ?>"
         class="hero-btn" data-aos="zoom-in-down">
         <?php echo esc_html($button_text); ?>
      </a>
    <?php endif; ?>

  </div>
</section>
<?php endif; ?>

<?php endif; ?>

		<div class="corp-container">

			<?php do_action( 'siteorigin_corp_content_top' ); ?>

