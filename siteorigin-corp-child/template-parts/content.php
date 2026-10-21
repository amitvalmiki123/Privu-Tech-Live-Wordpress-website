<?php
/**
 * Template part for displaying posts.
 *
 * @see https://codex.wordpress.org/Template_Hierarchy
 *
 * @license GPL 2.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( ! is_single() && ! siteorigin_corp_is_post_loop_widget() && siteorigin_setting( 'blog_archive_layout' ) == 'offset' || siteorigin_corp_is_post_loop_template( 'offset' ) ) { ?>
		<div class="entry-offset">
			<?php siteorigin_corp_offset_post_meta(); ?>
		</div>
	<?php } ?>

    <div class="event-item aos-zoom-in">
	    <?php if ( has_post_thumbnail() ) : ?>
	        <div class="event-thumb shine">
	            <a href="<?php the_permalink(); ?>">
	                <?php the_post_thumbnail( 'siteorigin-corp-720x480-crop' ); ?>
	            </a>
	        </div>
	    <?php endif; ?>

	</div>	

	<div class="corp-content-wrapper aos-zoom-in">
        <div class="event-category">
        
            <?php 
            $categories = get_the_category(); 
            if ( ! empty( $categories ) ) {
                $cat_link = get_category_link( $categories[0]->term_id );
                echo '<a class="cat-name" href="' . esc_url( $cat_link ) . '">'
                        . esc_html( $categories[0]->name ) .
                     '</a>';
            }
        
            $author_id = get_the_author_meta('ID');
            $author_image = get_field('author', 'user_' . $author_id);
            ?>
        
            <span class="author-box">
                <?php if($author_image): ?>
                    <img src="<?php echo esc_url($author_image['url']); ?>" class="author-img" alt="<?php the_author(); ?>">
                <?php else: ?>
                    <?php echo get_avatar($author_id, 24); ?>
                <?php endif; ?>
        
                <span class="author-name"><?php the_author(); ?></span>
            </span>
        
        </div>
    

		<?php
		$title = siteorigin_corp_get_post_title();

		if (
			! is_single() &&
			! siteorigin_corp_is_post_loop_widget() &&
			siteorigin_setting( 'blog_archive_layout' ) == 'offset'
			|| siteorigin_corp_is_post_loop_template( 'offset' )
		) {
			$meta = siteorigin_corp_posted_on( false );
		} else {
			$meta = siteorigin_corp_post_meta( true, '', false );
		}
		?>

		<?php if ( ! empty( $title ) || ! empty( $entry_meta ) ) { ?>
			<header class="entry-header">
				<?php
				if ( ! empty( $title ) ) {
					echo $title;
				}
				?>
				<?php if ( 'post' === get_post_type() ) { ?>
					<div class="entry-meta">
							<?php echo $meta; ?>
					</div><!-- .entry-meta -->
				<?php } ?>
			</header><!-- .entry-header -->
		<?php } ?>

		<div class="entry-content">
			<?php
			if ( is_single() || ( siteorigin_setting( 'blog_archive_content' ) == 'full' ) ) {
				the_content();
			} else {
				siteorigin_corp_excerpt();
			}

			wp_link_pages( array(
				'before' => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'siteorigin-corp' ) . '</span>',
				'after'  => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
			) );
			?>

			<div class="entry-bottom">
		    <span class="entry-date">
		        <?php echo get_the_date(); ?>
		    </span>
		    <a href="<?php the_permalink(); ?>" class="read-more-btn">Read</a>
		</div>
		</div><!-- .entry-content -->

		
	</div><!-- .corp-content-wrapper -->

	<?php siteorigin_corp_entry_footer(); ?>
</article><!-- #post-## -->
