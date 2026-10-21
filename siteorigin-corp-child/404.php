<?php get_header(); ?>

<div class="error-404">
    <div class="error-inner">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>
            Sorry, the page you are looking for does not exist or has been moved.
        </p>

        <div class="error-actions">
            <a href="<?php echo home_url(); ?>" class="btn-home">Go to Homepage</a>
            <a href="javascript:history.back()" class="btn-back">Go Back</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>