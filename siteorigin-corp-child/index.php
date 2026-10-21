<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @see https://codex.wordpress.org/Template_Hierarchy
 *
 * @license GPL 2.0
 */
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php get_template_part( 'loops/loop', 'blog-' . siteorigin_setting( 'blog_archive_layout' ) ); ?>

            <section class="trend-post-kes">
                <div class="blog-filter-bar aos-fade-up">
                
                    <form role="search" method="get" class="blog-search-form" action="<?php echo home_url('/'); ?>">
                        <input type="search" 
                               placeholder="Search articles..." 
                               value="<?php echo get_search_query(); ?>" 
                               name="s" />
                
                        <?php if ( isset($_GET['cat']) && $_GET['cat'] ) : ?>
                            <input type="hidden" name="cat" value="<?php echo esc_attr($_GET['cat']); ?>">
                        <?php endif; ?>
                
                        <button type="submit">Search</button>
                    </form>
                
                </div>

                <div class="trending-header aos-fade-up">
                    <h2 class="custom-heading">Trending Now</h2>
                </div>
                <div class="trending-wrapper aos-zoom-in">
                
                <?php
                $trending_query = new WP_Query(array(
                    'posts_per_page' => 1,
                    'post_status' => 'publish',
                    'category_name' => 'news'
                ));
                
                if ($trending_query->have_posts()) :
                    while ($trending_query->have_posts()) : $trending_query->the_post();
                ?>
                    <article class="trending-card">
                
                        <a href="<?php the_permalink(); ?>" class="trending-thumb shine">
                            <?php the_post_thumbnail('large'); ?>
                        </a>
                
                        <div class="trending-content">
                
                            <div class="trending-meta">
                
                                <!-- CATEGORY BADGE -->
                                <?php 
                                $cat = get_the_category();
                                if ( ! empty( $cat ) ) :
                                    $cat_link = get_category_link( $cat[0]->term_id );
                                ?>
                                    <a href="<?php echo esc_url( $cat_link ); ?>" class="trend-badge">
                                        <?php echo esc_html( $cat[0]->name ); ?>
                                    </a>
                                <?php endif; ?>
                
                                <!-- DATE -->
                                <span class="trend-date">
                                    <?php echo get_the_date(); ?>
                                </span>
                
                            </div>
                
                            <h3 class="entry-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                
                            <p class="trend-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
                            </p>
                
                            <a href="<?php the_permalink(); ?>" class="read-more-btn">Read More</a>
                
                        </div>
                    </article>
                
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                
                </div>
            </section>            

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_sidebar();
get_footer();


