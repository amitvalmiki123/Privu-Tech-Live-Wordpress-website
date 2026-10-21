<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @see https://codex.wordpress.org/Template_Hierarchy
 *
 * @license GPL 2.0
 */

get_header();

// FULL-WIDTH HERO
if ( !is_front_page() ) {
    get_template_part('template-parts/hero-inner');
}
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php
		while ( have_posts() ) {
			the_post();

			get_template_part( 'template-parts/content', 'page' );

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		} 
		?>

	</main>
</div>

<?php
get_sidebar();
get_footer();

