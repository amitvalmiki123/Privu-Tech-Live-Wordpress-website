<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @see https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @license GPL 2.0
 */
?>

		</div><!-- .corp-container -->
	</div><!-- #content -->

	<?php
	if ( siteorigin_page_setting( 'footer', true ) ) {
		do_action( 'siteorigin_corp_footer_before' );
		?>

		<footer id="colophon" class="site-footer">

			<?php do_action( 'siteorigin_corp_footer_top' ); ?>

			<?php if ( siteorigin_page_setting( 'footer_widgets' ) ) { ?>
				<div class="corp-container">
					<?php
					if ( is_active_sidebar( 'sidebar-footer' ) ) {
						$corp_sidebars = wp_get_sidebars_widgets();
						?>
						<div class="aos-zoom-in widgets widgets-<?php echo count( $corp_sidebars['sidebar-footer'] ); ?>" aria-label="<?php esc_attr_e( 'Footer Widgets', 'siteorigin-corp' ); ?>">
							<?php dynamic_sidebar( 'sidebar-footer' ); ?>
						</div>
						<?php
					}
					?>
				</div><!-- .corp-container -->
			<?php } ?>

            <div class="bottom-bar">
                <div class="footer-border"></div>
                <div class="corp-container">
            
                    <div class="site-info">
                        <span>
                            © <?php echo date("Y"); ?> Privu(Taxology). All rights reserved.
                        </span>
                    </div>
            
                    <div class="footer-menu">
                        <ul class="footer-links">
            
                            <?php
                            // Privacy Policy
                            $privacy_page = get_page_by_path("privacy-policy");
                            if ($privacy_page): ?>
                                <li><a href="<?php echo esc_url(get_permalink($privacy_page->ID)); ?>">
                                    Privacy Policy
                                </a></li>
                            <?php endif; ?>
            
                            <?php
                            // Terms of Service
                            $terms_page = get_page_by_path("terms-of-service");
                            if ($terms_page): ?>
                                <li><a href="<?php echo esc_url(get_permalink($terms_page->ID)); ?>">
                                    Terms of Service
                                </a></li>
                            <?php endif; ?>
            
                            <?php
                            // Cookie Policy
                            $cookie_page = get_page_by_path("cookie-policy");
                            if ($cookie_page): ?>
                                <li><a href="<?php echo esc_url(get_permalink($cookie_page->ID)); ?>">
                                    Cookie Policy
                                </a></li>
                            <?php endif; ?>
            
                        </ul>
                    </div>
            
                </div>
            </div>


			<?php do_action( 'siteorigin_corp_footer_bottom' ); ?>

		</footer><!-- #colophon -->
	<?php } ?>
</div><!-- #page -->

<?php if ( siteorigin_setting( 'navigation_scroll_to_top' ) ) { ?>
	<div id="scroll-to-top">
		<span class="screen-reader-text"><?php esc_html_e( 'Scroll to top', 'siteorigin-corp' ); ?></span>
		<?php siteorigin_corp_display_icon( 'up-arrow' ); ?>
	</div>
<?php } ?>

<?php wp_footer(); ?>
<?php do_action( 'siteorigin_corp_footer_after' ); ?>

<!-- header sticky js -->
<script>
document.addEventListener("scroll", function () {
    const header = document.getElementById("masthead");

    if (window.scrollY > 50) {
        if (!header.classList.contains("header-animate")) {
            header.classList.add("header-animate");
        }
    } else {
        header.classList.remove("header-animate");
    }
});

</script>

<script>
jQuery(document).ready(function($){
    $('#masthead #mobile-menu-button').click(function(){
        $('#mobile-menu-button').toggleClass('remove');
        $('body').toggleClass('remove');
        $('#mobile-navigation').toggleClass('open');
        $('.cancel').toggleClass('remove');
    });
    $('.cancel').click(function() {
        $('#mobile-navigation').removeClass('open');
        $('#mobile-menu-button').removeClass('remove');
        $('body').removeClass('remove');
        $('.cancel').removeClass('remove');
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("scroll-to-top");
    if (!btn) return;

    /* Inject LABSTICA style SVG */
    btn.insertAdjacentHTML("afterbegin", `
        <svg class="progress-circle" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>
        </svg>
    `);

    const path = btn.querySelector("path");
    const length = path.getTotalLength();

    path.style.strokeDasharray = length;
    path.style.strokeDashoffset = length;

    window.addEventListener("scroll", function () {
        const scrollTop = window.scrollY;
        const docHeight =
            document.documentElement.scrollHeight -
            document.documentElement.clientHeight;

        const progress = scrollTop / docHeight;
        path.style.strokeDashoffset =
            length - progress * length;

        if (scrollTop > 300) {
            btn.classList.add("show");
        } else {
            btn.classList.remove("show");
        }
    });

    btn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

});
</script>

<script>
// dpdp-blog.js
document.addEventListener('DOMContentLoaded', function () {

    const tabs = document.querySelectorAll('.topic-tab');
    const cards = document.querySelectorAll('.topic-card');

    if (!tabs.length || !cards.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.getAttribute('data-tab');

            tabs.forEach(t => t.classList.remove('active'));
            tabs.forEach(t => t.setAttribute('aria-selected','false'));
            this.classList.add('active');
            this.setAttribute('aria-selected','true');

            if (target === 'all-topics') {
                // show all cards
                cards.forEach(card => {
                    card.style.display = 'flex';
                });
                return;
            }

            cards.forEach(card => {
                if (card.getAttribute('data-tab-content') === target) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    const searchInput = document.getElementById('topicSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            cards.forEach(card => {
                const titleEl = card.querySelector('h3');
                const text = titleEl ? titleEl.innerText.toLowerCase() : '';
                // if search blank, respect tab active (if not all)
                if (!q) {
                    // find active tab
                    const activeTab = document.querySelector('.topic-tab.active');
                    const activeTarget = activeTab ? activeTab.getAttribute('data-tab') : 'all-topics';
                    if (activeTarget === 'all-topics' || card.getAttribute('data-tab-content') === activeTarget) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                } else {
                    card.style.display = text.includes(q) ? 'flex' : 'none';
                }
            });
        });
    }

});

</script>

<!-- search filter -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById('topicSearch');
    if (!searchInput) return;

    // LIVE FILTER (same page)
    searchInput.addEventListener('input', function () {

        const q = this.value.trim().toLowerCase();

        // Topic cards
        document.querySelectorAll(".topic-card").forEach(card => {
            const title = card.querySelector("h3")?.innerText.toLowerCase() || "";
            card.style.display = !q || title.includes(q) ? "flex" : "none";
        });

        // Index cards
        document.querySelectorAll(".topic-index-card").forEach(card => {
            const title = (card.dataset.title || "").toLowerCase();
            const category = (card.dataset.category || "").toLowerCase();

            card.style.display = !q || title.includes(q) || category.includes(q)
                ? "block"
                : "none";
        });

    });

});

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('topicSearch');
    const searchForm = searchInput ? searchInput.closest('form') : null;

    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            const query = searchInput.value.trim();
            
            if (query === "") {
                e.preventDefault(); // Redirect hone se rokta hai
                alert("Please enter something to search!"); // Optional alert
                return false;
            }
        });
    }
});

</script>

<script>
document.addEventListener('change', function(e) {
    if (e.target.name === 'cat') {
        if (e.target.value > 0) {
            window.location.href = "<?php echo home_url(); ?>/?cat=" + e.target.value;
        }
    }
});
</script>

<script>
jQuery(document).ready(function($) {

    $('.blog-layout-grid').addClass('owl-carousel owl-theme');

    // Initialize Owl Carousel
    $('.blog-layout-grid').owlCarousel({
        autoplay: false,
        autoplayTimeout: 3000,
        items: 3,
        margin:20,
        dots:false,
        nav: true,
        navText: [
            '<span class="owl-prev-btn"></span>',
            '<span class="owl-next-btn"></span>'
        ],
        responsive: {
            0: {
                items: 1
            },
            590: {
                items: 1
            },
              591: {
                items: 2
            },
            769: {
                items: 3
            }
        }
    });

});
</script>

<script>
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('tel-no-letters')) {
        e.target.value = e.target.value.replace(/[a-zA-Z]/g, '');
    }
});
</script>

<script>
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('email-lowercase')) {
        e.target.value = e.target.value.toLowerCase();
    }
});
</script>

<script>
    jQuery(document).ready(function($) {
    if ($(window).width() <= 767) {
        
        $('.wwd_col').first().addClass('is-active').find('.wwd_txt').show();

        $('.wwd_heading').on('click', function(e) {
            e.preventDefault();
            
            var $currentCol = $(this).closest('.wwd_col');
            var $currentTxt = $currentCol.find('.wwd_txt');
            if ($currentCol.hasClass('is-active')) {
                $currentTxt.slideUp(300);
                $currentCol.removeClass('is-active');
            } else {
                $('.wwd_col').removeClass('is-active');
                $('.wwd_txt').slideUp(300);
                $currentCol.addClass('is-active');
                $currentTxt.slideDown(300);
            }
        });
    }
});

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const aosMap = {
    "aos-fade-up": "fade-up",
    "aos-fade-down": "fade-down",
    "aos-left": "fade-left",
    "aos-right": "fade-right",
    "aos-zoom-in": "zoom-in",
    "aos-zoom-out": "zoom-out",
    "aos-flip-up": "flip-up",
    "aos-flip-left": "flip-left"
  };

  Object.keys(aosMap).forEach(cls => {
    document.querySelectorAll("." + cls).forEach(el => {
      el.setAttribute("data-aos", aosMap[cls]);
    });
  });

  AOS.init({
    duration: 900,
    easing: "ease-out-cubic",
    once: true,
    offset: 120
  });

  AOS.refreshHard();

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function(){

    const items = document.querySelectorAll(".hr-col");

    items.forEach(function(item){

        const heading = item.querySelector("h4");

        heading.addEventListener("click", function(e){

            e.stopPropagation();

            items.forEach(function(otherItem){

                if(otherItem !== item){
                    otherItem.classList.remove("active");
                }

            });

            item.classList.toggle("active");

        });

    });

});
</script>

</body>
</html>
