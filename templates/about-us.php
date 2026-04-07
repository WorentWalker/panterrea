<?php

/**
 * Template Name: About Us
 */

get_header();?>
<section class="aboutUs__hero">
    <div class="container">
        <div class="aboutUs__hero__content">
            <h1 class="aboutUs__hero__title">
                <?php the_field( 'about_hero_title' ); ?>
            </h1>
            <div class="aboutUs__hero__info p">
                <?php echo wpautop( get_field( 'about_hero_subtitle' ) ); ?>
            </div>
        </div>
        <?php if ( have_rows( 'quote_' ) ) : ?>
        <?php while ( have_rows( 'quote_' ) ) : the_row(); ?>
        <div class="about__hero__quote">
            <div class="quote__text">
                <blockquote>
                    <p>
                        <?php the_sub_field( 'text' ); ?>
                    </p>
                </blockquote>
                <div class="quote__author">
                    <div class="quote__author__name">
                        <?php the_sub_field( 'author' ); ?>
                    </div>
                </div>
            </div>
            <?php if ( get_sub_field( 'image' ) ) : ?>
            <div class="quote__image">
                <img src="<?php the_sub_field( 'image' ); ?>" alt="Quote Image">
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
    <div class="container">
        <?php if ( have_rows( 'steps' ) ) : ?>
        <div class="aboutUs__hero__steps">
            <?php 
                $step_counter = 1;
                while ( have_rows( 'steps' ) ) : the_row(); 
                ?>
            <div class="aboutUs__hero__steps__item">
                <div class="aboutUs__hero__steps__item__number">
                    <span><?php echo str_pad( $step_counter, 2, '0', STR_PAD_LEFT ); ?></span>
                </div>
                <div class="aboutUs__hero__steps__item__title">
                    <?php the_sub_field( 'text' ); ?>
                </div>
            </div>
            <?php 
                    $step_counter++;
                endwhile; 
                ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<section class="aboutUs__advantages">
    <div class="container">
        <div class="aboutUs__advantages__title h3">
            <?php the_field( 'advantages_title' ); ?>
        </div>
        <div class="aboutUs__advantages__subtitle h4">
            <?php the_field( 'advantages_subtitle' ); ?>
        </div>

        <?php if ( have_rows( 'advantages_list' ) ) : ?>
        <div class="aboutUs__advantages__cards-wrapper">
            <div class="aboutUs__advantages__cards">
                <?php while ( have_rows( 'advantages_list' ) ) : the_row(); ?>
                <?php $icon = get_sub_field( 'icon' ); ?>
                <div class="aboutUs__advantages__card">
                    <?php if ( $icon ) : ?>
                    <div class="aboutUs__advantages__card__icon">
                        <img src="<?php echo esc_url( $icon['url'] ); ?>"
                            alt="<?php echo esc_attr( $icon['alt'] ); ?>" />
                    </div>
                    <?php endif; ?>
                    <div class="aboutUs__advantages__card__text body1">
                        <?php the_sub_field( 'text' ); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="aboutUs__advantages__caption body1">
            <?php the_field( 'caption' ); ?>
        </div>
    </div>
</section>
<section class="aboutUs__hero aboutUs__hero__second">
    <div class="container">
        <div class="aboutUs__hero__content">
            <h2 class="h3">
                <?php the_field( 'quote_title' ); ?>
            </h2>
            <div class="aboutUs__hero__info p">
                <?php echo wpautop( get_field( 'quote_subtitle' ) ); ?>
            </div>
        </div>
        <?php if ( have_rows( 'quote_block' ) ) : ?>
        <?php while ( have_rows( 'quote_block' ) ) : the_row(); ?>
        <div class="about__hero__quote">
            <div class="quote__text">
                <blockquote>
                    <p>
                        <?php the_sub_field( 'text' ); ?>
                    </p>
                </blockquote>
                <div class="quote__author">
                    <div class="quote__author__name">
                        <?php the_sub_field( 'author' ); ?>
                    </div>
                </div>
            </div>
            <?php if ( get_sub_field( 'image' ) ) : ?>
            <div class="quote__image">
                <img src="<?php the_sub_field( 'image' ); ?>" alt="Quote Image">
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>
<section class="aboutUs__founders">
    <div class="container">
        <div class="aboutUs__founders__title">
            <?php the_field( 'founders_title' ); ?>
        </div>
        <p class="aboutUs__founders__subtitle">
            <?php the_field( 'founders_subtitle' ); ?>
        </p>
        <?php if ( have_rows( 'founders_block' ) ) : ?>
        <div class="aboutUs__founders__content">
            <?php while ( have_rows( 'founders_block' ) ) : the_row(); ?>
            <div class="aboutUs__founders__item">
                <?php if ( get_sub_field( 'image' ) ) : ?>
                <div class="aboutUs__founders__item__image">
                    <img src="<?php the_sub_field( 'image' ); ?>" alt="Founder Image">
                </div>
                <?php endif; ?>
                <div class="aboutUs__founders__item__content">
                    <h3 class="aboutUs__founders__item__title">
                        <?php the_sub_field( 'title' ); ?>
                    </h3>
                    <?php if ( get_sub_field( 'subtitle' ) ) : ?>
                    <?php echo wpautop( get_sub_field( 'subtitle' ) ); ?>
                    <?php endif; ?>
                    <?php if ( have_rows( 'awards' ) ) : ?>
                    <div class="aboutUs__founders__item__blocks">
                        <?php while ( have_rows( 'awards' ) ) : the_row(); ?>
                        <?php $image = get_sub_field( 'image' ); ?>
                        <div class="aboutUs__founders__item__block">
                            <?php if ( $image ) : ?>
                            <div class="aboutUs__founders__item__block__icon">
                                <img src="<?php echo esc_url( $image['url'] ); ?>"
                                    alt="<?php echo esc_attr( $image['alt'] ); ?>" />
                            </div>
                            <?php endif; ?>
                            <div class="aboutUs__founders__item__block__text">
                                <?php the_sub_field( 'text' ); ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<section class="aboutUs__team">
    <div class="container">
        <div class="aboutUs__team__title">
            <?php the_field( 'team_title' ); ?>
        </div>
        <div class="aboutUs__team__content">
            <div class="aboutUs__team__text">
                <?php echo wpautop( get_field( 'team_list' ) ); ?>
            </div>
            <?php if ( get_field( 'team_image' ) ) : ?>
            <div class="aboutUs__team__image">
                <img src="<?php the_field( 'team_image' ); ?>" alt="Team Image">
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Scroll direction detection for cards animation (fallback for browsers without scroll-driven animations)
(function() {
    'use strict';

    // Check if browser supports scroll-driven animations
    if (CSS.supports('animation-timeline', 'scroll()')) {
        return; // Use CSS-only solution
    }

    const section = document.querySelector('.aboutUs__advantages');
    const cards = document.querySelector('.aboutUs__advantages__cards');

    if (!section || !cards) return;

    let lastScrollY = window.scrollY;
    let scrollDirection = 'down';
    let ticking = false;

    // Smooth easing function for more fluid animation
    function easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    }

    function easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    }

    function updateCardsPosition() {
        const rect = section.getBoundingClientRect();
        const sectionTop = rect.top + window.scrollY;
        const viewportHeight = window.innerHeight;
        const scrollY = window.scrollY;

        // Animation should complete earlier - before section fully enters viewport
        // Start animation when section is 1 viewport height above, end when it's 0.5 viewport height above viewport top
        const animationStart = sectionTop - viewportHeight;
        const animationEnd = sectionTop - (viewportHeight * 0.5);
        const animationRange = animationEnd - animationStart;
        const currentScroll = scrollY - animationStart;

        // Determine scroll direction
        if (scrollY > lastScrollY) {
            scrollDirection = 'down';
        } else if (scrollY < lastScrollY) {
            scrollDirection = 'up';
        }
        lastScrollY = scrollY;

        // Calculate card offset - animation completes when section reaches viewport
        if (currentScroll >= 0 && currentScroll <= animationRange) {
            const rawProgress = currentScroll / animationRange;
            const progress = Math.max(0, Math.min(1, rawProgress));

            // Apply smooth easing to progress for more fluid movement
            const easedProgress = easeInOutCubic(progress);

            const cardsWidth = cards.scrollWidth;
            const wrapperWidth = section.querySelector('.aboutUs__advantages__cards-wrapper').offsetWidth;
            const maxOffset = Math.max(0, cardsWidth - wrapperWidth);

            // Move cards based on scroll direction and progress (reversed direction)
            const offset = scrollDirection === 'down' ?
                -(1 - easedProgress) * maxOffset :
                -easedProgress * maxOffset;

            cards.style.setProperty('--scroll-offset', offset + 'px');
            section.setAttribute('data-scrolling', scrollDirection);
        } else if (currentScroll > animationRange) {
            // Animation completed - keep cards in final position
            const cardsWidth = cards.scrollWidth;
            const wrapperWidth = section.querySelector('.aboutUs__advantages__cards-wrapper').offsetWidth;
            const maxOffset = Math.max(0, cardsWidth - wrapperWidth);
            cards.style.setProperty('--scroll-offset', '0px');
            section.setAttribute('data-scrolling', 'completed');
        } else {
            // Before animation starts - keep cards in initial position
            const cardsWidth = cards.scrollWidth;
            const wrapperWidth = section.querySelector('.aboutUs__advantages__cards-wrapper').offsetWidth;
            const maxOffset = Math.max(0, cardsWidth - wrapperWidth);
            cards.style.setProperty('--scroll-offset', -maxOffset + 'px');
        }

        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(updateCardsPosition);
            ticking = true;
        }
    }, {
        passive: true
    });

    // Initial update
    updateCardsPosition();
})();
</script>
</main>

<?php
get_footer();