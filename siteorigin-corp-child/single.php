<?php
/**
 * The template for displaying all single posts
 *
 * @see https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @license GPL 2.0
 */
get_header(); ?>
<?php if (has_post_thumbnail()):

  $image_id = get_post_thumbnail_id();
  $image_url = wp_get_attachment_url($image_id);
  $image_alt = get_post_meta($image_id, "_wp_attachment_image_alt", true);
  $location = get_field("event_location");
  $event_date = get_the_date("d M Y");
  ?>
<div class="single-blog-banner">
    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(
  $image_alt,
); ?>" class="banner-image" />
        <div class="banner-content">
            <div class="container">
                <?php if ($event_date): ?>
                    <span class="event-date reveal"><?php
                    $categories = get_the_category();
                    if (!empty($categories)) {
                      echo '<span class="cat-name">' .
                        esc_html($categories[0]->name) .
                        "</span>";
                    }
                    ?></span>
                <?php endif; ?>                
                <h1 class="post-heading"><?php the_title(); ?></h1>
            <a href="/contact-us/" class="banner-cta-btn">
                Contact Us
            </a>             
        </div>
    </div>
</div>
<?php
endif; ?>
<div class="single-blog-row">
<div class="single-blog-content">
    <div class="single-float">

<?php if (is_single()): ?>
    <div class="extra-content">
        <?php the_content(); ?>
    </div>
    <aside class="blog-sidebar">
    <?php dynamic_sidebar('blog-sidebar'); ?>
    </aside>
   </div>
    <?php
    $categories = get_the_category();
    if ($categories) {
      $category_ids = [];
      foreach ($categories as $category) {
        $category_ids[] = $category->term_id;
      }
      $related_args = [
        "post_type" => "post",
        "posts_per_page" => 3,
        "post__not_in" => [get_the_ID()],
        "category__in" => $category_ids,
      ];
      $related_query = new WP_Query($related_args);
      if ($related_query->have_posts()): ?>
        
        <div class="related-posts-section">
            <h3>Related Posts</h3>
            <div class="related-posts-grid">

            <?php while ($related_query->have_posts()):
              $related_query->the_post(); ?>
                <div class="related-post-item">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) {
                          the_post_thumbnail("medium");
                        } ?>
                        <h4><?php the_title(); ?></h4>
                    </a>
                </div>
            <?php
            endwhile; ?>

            </div>

        </div>

    <?php endif;

      wp_reset_postdata();
    }
    ?>

<?php endif; ?>

</div>
</div>

<?php get_footer();