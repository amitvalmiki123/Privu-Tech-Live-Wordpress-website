/**
 * PRIVU - Team Section JS
 * File: assets/js/privu-team.js
 * 
 * Enqueue in functions.php:
 * wp_enqueue_script('privu-team', get_template_directory_uri() . '/assets/js/privu-team.js', [], '1.0', true);
 */

(function () {
    'use strict';

    /** Animate cards on scroll using IntersectionObserver */
    function initTeamAnimations() {
        var cards = document.querySelectorAll('.pts-founder-card, .pts-advisor-card');
        if (!cards.length) return;

        if (!('IntersectionObserver' in window)) {
            // Fallback: show all immediately
            cards.forEach(function (card) { card.classList.add('pts-animated'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var card = entry.target;
                    var delay = parseInt(card.getAttribute('data-delay') || '0', 10);
                    setTimeout(function () {
                        card.classList.add('pts-animated');
                    }, delay);
                    observer.unobserve(card);
                }
            });
        }, { threshold: 0.12 });

        cards.forEach(function (card) {
            observer.observe(card);
        });
    }

    /** Subtle mouse-parallax tilt on founder cards (desktop only) */
    function initCardTilt() {
        if (window.matchMedia('(hover: none)').matches) return;

        var founderCards = document.querySelectorAll('.pts-founder-card');
        founderCards.forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var rect = card.getBoundingClientRect();
                var x = (e.clientX - rect.left) / rect.width  - 0.5; // -0.5 to 0.5
                var y = (e.clientY - rect.top)  / rect.height - 0.5;
                card.style.transform = 'translateY(-6px) rotateX(' + (-y * 5) + 'deg) rotateY(' + (x * 5) + 'deg)';
            });
            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    }

    /** Animate header text on load */
    function initHeaderEntrance() {
        var heading = document.querySelector('.pts-main-heading');
        var badge   = document.querySelector('.pts-badge');
        var tagline = document.querySelector('.pts-tagline');

        if (heading) {
            heading.style.opacity = '0';
            heading.style.transform = 'translateY(20px)';
        }
        if (badge) {
            badge.style.opacity = '0';
            badge.style.transform = 'translateY(10px)';
        }
        if (tagline) {
            tagline.style.opacity = '0';
        }

        setTimeout(function () {
            if (badge) {
                badge.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                badge.style.opacity = '1';
                badge.style.transform = 'translateY(0)';
            }
        }, 100);

        setTimeout(function () {
            if (heading) {
                heading.style.transition = 'opacity 0.7s ease, transform 0.7s cubic-bezier(0.23,1,0.32,1)';
                heading.style.opacity = '1';
                heading.style.transform = 'translateY(0)';
            }
        }, 250);

        setTimeout(function () {
            if (tagline) {
                tagline.style.transition = 'opacity 0.6s ease';
                tagline.style.opacity = '1';
            }
        }, 480);
    }

    /** Init all */
    function init() {
        initHeaderEntrance();
        initTeamAnimations();
        initCardTilt();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
