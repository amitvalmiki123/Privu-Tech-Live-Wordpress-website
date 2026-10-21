<?php
/*
 Template Name: DPDP Blog & Resources
*/

get_header(); ?>

<section class="topics-cards-section">
    <div class="topics-cards-wrapper">

        <!-- LEFT SIDE -->
        <div class="blog-main">

            <?php 
            $query = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => -1
            ]);

            if($query->have_posts()):
                while($query->have_posts()): $query->the_post();

                // Only keep ACF if really needed
                $topic_points = get_field('topic_points'); // optional
            ?>

            <article class="topic-card aos-fade-down">

                <!-- IMAGE -->
                <div class="topic-image">
                    <?php if(has_post_thumbnail()): ?>
                        <?php the_post_thumbnail('medium'); ?>
                    <?php endif; ?>
                </div>

                <!-- CONTENT -->
                <div class="topic-content">

                    <h3><?php the_title(); ?></h3>

                    <div class="topic-meta">
                    
                        <?php 
                        $author_id = get_the_author_meta('ID');
                        $author_image = get_field('author', 'user_' . $author_id);
                        ?>
                    
                        <span class="author-box">
                    
                            <?php if($author_image): ?>
                                <img src="<?php echo esc_url($author_image['url']); ?>" alt="<?php the_author(); ?>" class="author-img">
                            <?php else: ?>
                                <?php echo get_avatar($author_id, 28); ?>
                            <?php endif; ?>
                    
                            <span class="author-name">By <?php the_author(); ?></span>
                    
                        </span>
                    
                        <span>• <?php echo get_the_date('F j, Y'); ?></span>
                    
                    </div>

                    <!-- DESCRIPTION -->
                    <p>
                        <?php 
                        if(has_excerpt()){
                            the_excerpt();
                        } else {
                            echo wp_trim_words(get_the_content(), 20);
                        }
                        ?>
                    </p>

                    <!-- OPTIONAL POINTS (ACF) -->
                    <?php if($topic_points): ?>
                        <ul class="topic-points">
                            <?php foreach($topic_points as $point): ?>
                                <li><?php echo esc_html($point['point']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <a href="<?php the_permalink(); ?>" class="btn topic-btn">
                        Read More
                    </a>

                </div>

            </article>

            <?php endwhile; wp_reset_postdata(); endif; ?>

        </div>

        <!-- SIDEBAR -->
        <aside class="blog-sidebar">
            <?php dynamic_sidebar('blog-sidebar'); ?>
        </aside>

    </div>
</section>

<?php
$index_heading = get_field('index_heading');
$index_description = get_field('index_description');
?>

<section class="topic-index">
    <div class="topic-index-container">

        <?php if($index_heading): ?>
            <h2 class="custom-heading aos-fade-up"><?php echo esc_html($index_heading); ?></h2>
        <?php endif; ?>

        <?php if($index_description): ?>
            <p class="topic-index-description aos-fade-up"><?php echo esc_html($index_description); ?></p>
        <?php endif; ?>

        <div class="topic-index-grid" id="topicIndexGrid">

            <?php if(have_rows('topic_index')): ?>
                <?php while(have_rows('topic_index')): the_row(); 
                    $icon = get_sub_field('index_icon');
                    $title = get_sub_field('index_title');
                    $category = get_sub_field('index_category');
                ?>
                
                <div class="topic-index-card aos-flip-up"
                     data-title="<?php echo strtolower(esc_attr($title)); ?>"
                     data-category="<?php echo strtolower(esc_attr($category)); ?>">

                    <div class="index-card-icon">
                        <?php if($icon): ?>
                            <img src="<?php echo esc_url($icon['url']); ?>" alt="">
                        <?php endif; ?>
                    </div>

                    <h3 class="index-card-title"><?php echo esc_html($title); ?></h3>

                    <span class="index-card-category"><?php echo esc_html($category); ?></span>
                </div>

                <?php endwhile; ?>
            <?php endif; ?>

        </div>

    </div>
</section>

<section class="topics-tabs-section">
    <div class="tabs-container">
        <ul class="topics-tabs" role="tablist">
            <li role="tab" aria-selected="true" class="topic-tab active" data-tab="all-topics">
                All Topics
            </li>

            <?php if(have_rows('topic_tabs')): $i = 0; while(have_rows('topic_tabs')): the_row();
                $tab_title = get_sub_field('tab_title');
                $tab_slug  = sanitize_title($tab_title);
            ?>
                <li role="tab" aria-selected="false" 
                    class="topic-tab aos-flip-left" 
                    data-tab="<?php echo esc_attr($tab_slug); ?>">
                    <?php echo esc_html($tab_title); ?>
                </li>
            <?php $i++; endwhile; endif; ?>
        </ul>
    </div>
</section>

<?php
$news_heading = get_field('newsletter_heading');
$news_desc = get_field('newsletter_description');
$newsletter_shortcode = '[newsletter_form]';
?>

<section class="newsletter-fullwidth-strip">
    <div class="corp-container newsletter-inner">

        <?php if($news_heading): ?>
            <h2 class="custom-heading aos-fade-up"><?php echo esc_html($news_heading); ?></h2>
        <?php endif; ?>

        <?php if($news_desc): ?>
            <p class="newsletter-subtitle aos-fade-up"><?php echo esc_html($news_desc); ?></p>
        <?php endif; ?>

        <div class="newsletter-form-wrapper aos-fade-down">
            <?php echo do_shortcode($newsletter_shortcode); ?>
        </div>

    </div>
</section>

<section class="contact_div non-bg mb0 pt0">
    <div class="contact_section">

        <h2 class="custom-heading aos-fade-up">Need Expert Guidance?</h2>
        <p class="aos-fade-up">Our compliance experts are ready to help you navigate DPDP requirements</p>

        <div class="contact_row">

            <!-- LEFT SIDE -->
            <div class="left-row aos-right">
               <ul>
 	               <li>Email<br>
                       <a href="mailto:contact@privu.tech">contact@privu.tech</a>
				   </li>
 	               <li>Phone<br>
                       <a href="tel:919971735924">+91 9971735924</a>
				   </li>
               </ul>
            </div>

            <!-- RIGHT SIDE FORM -->
            <div class="right-row aos-left">
                <?php echo do_shortcode('[contact-form-7 id="41d9575" title="Contact form"]'); ?>
            </div>

        </div>

    </div>
</section>


<?php get_footer(); ?>