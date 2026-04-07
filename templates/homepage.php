<?php

/**
 * Template Name: Homepage
 */

/*global $currentLang;*/

get_header();
?>

<main class="homepage firstSectionPadding">
    <section class="hero">
        <div class="hero__container container">
            <div class="hero__slider-wrapper">
                <?php
            $hero_slides = get_field('hero_slides');
            if ($hero_slides && is_array($hero_slides)) : ?>
                <div class="swiper hero__swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($hero_slides as $slide) : 
                            $bg_image = !empty($slide['background_image']) ? $slide['background_image'] : null;
                            $title = !empty($slide['title']) ? $slide['title'] : '';
                            $subtitle = !empty($slide['subtitle']) ? $slide['subtitle'] : '';
                        ?>
                        <div class="swiper-slide hero__slide">
                            <?php if ($bg_image) : ?>
                            <div class="hero__slide__bg"
                                style="background-image: url('<?php echo esc_url($bg_image['url']); ?>');"></div>
                            <?php endif; ?>
                            <div class="hero__slide__content container">
                                <?php if ($title) : ?>
                                <h1 class="hero__slide__title h3"><?php echo esc_html($title); ?></h1>
                                <?php endif; ?>
                                <?php if ($subtitle) : ?>
                                <div class="hero__slide__subtitle"><?php echo esc_html($subtitle); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Pagination -->
                </div>
            </div>
            <?php else : ?>
            <!-- Fallback if no slides -->
            <div class="hero__fallback">
                <div class="container">
                    <?php
                        $hero = get_field('hero');
                        if ($hero) : ?>
                    <?php if (!empty($hero['title'])) : ?>
                    <h1 class="hero__title h3"><?php echo esc_html($hero['title']); ?></h1>
                    <?php endif; ?>
                    <?php if (!empty($hero['subtitle'])) : ?>
                    <div class="hero__subtitle"><?php echo esc_html($hero['subtitle']); ?></div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($hero_slides && is_array($hero_slides) && count($hero_slides) > 0) : ?>
        <div class="swiper-pagination hero__swiper__pagination"></div>
        <?php endif; ?>
    </section>
    <section class="heroSearch">
        <div class="container">
            <div class="heroSearch__inner">
                <h1 class="heroSearch__title h5">Що вас цікавить?</h1>
            </div>
            <?php get_template_part('template-parts/search-input'); ?>
            <div class="hero__slider">
                <?php
                            $args = array(
                                'taxonomy' => 'catalog_category',
                                'hide_empty' => false,
                                'meta_key' => 'category_order',
                                'orderby' => 'meta_value_num',
                                'order' => 'ASC',
                                'parent' => 0
                            );

                            $categories = get_terms($args);

                            if ($categories && !is_wp_error($categories)) : ?>
                <div class="swiper categorySwiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($categories as $category) :
                                    $category_image = get_field('category_image', 'term_' . $category->term_id);
                                    $category_link = get_term_link($category);

                                    /*$category_name = ($currentLang === 'en') ? get_field('name_en', 'term_' . $category->term_id) : $category->name;*/
                                    $category_name = $category->name;

                                    if (!$category_name) {
                                        $category_name = $category->name;
                                    }
                                    ?>
                        <div class="swiper-slide">
                            <a href="<?php echo esc_url($category_link); ?>" class="hero__category">
                                <?php if ($category_image) : ?>
                                <img src="<?php echo esc_url($category_image['url']); ?>"
                                    alt="<?php echo esc_attr($category_name); ?>">
                                <?php endif; ?>
                                <div class="hero__category__title subtitle2"><?php echo esc_html($category_name); ?>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Pagination with arrows -->
                    <div class="categorySwiper__pagination">
                        <div class="categorySwiper__pagination__prev">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                <path
                                    d="M11.5247 15.8333C11.2726 15.8342 11.0336 15.7208 10.8747 15.525L6.84973 10.525C6.59697 10.2175 6.59697 9.77415 6.84973 9.46665L11.0164 4.46665C11.3109 4.11227 11.837 4.06377 12.1914 4.35832C12.5458 4.65287 12.5943 5.17893 12.2997 5.53332L8.57473 9.99998L12.1747 14.4667C12.3828 14.7164 12.4266 15.0644 12.287 15.358C12.1474 15.6515 11.8498 15.8371 11.5247 15.8333Z" />
                            </svg>
                        </div>
                        <div class="categorySwiper__pagination__next">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                <path
                                    d="M8.47722 4.16668C8.72938 4.16582 8.96837 4.27919 9.12722 4.47501L13.1522 9.47501C13.405 9.78252 13.405 10.2258 13.1522 10.5333L8.98556 15.5333C8.69101 15.8877 8.16494 15.9362 7.81056 15.6417C7.45617 15.3471 7.40767 14.8211 7.70222 14.4667L11.4272 10L7.82722 5.53335C7.61915 5.28358 7.57531 4.93561 7.71494 4.64204C7.85456 4.34846 8.15216 4.16288 8.47722 4.16668Z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/catalog', null, [
            'title' => __('Кращі пропозиції', 'panterrea_v1'),
            'subtitle' => __('Знаходьте товари, партнерів і перевірені рішення', 'panterrea_v1'),
            'show_sidebar' => false,
            'terms_args' => [
                'taxonomy' => 'catalog_category',
                'hide_empty' => false,
                'meta_key' => 'category_order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'parent' => 0
            ],
            'query_args' => [
                'post_type' => 'catalog_post',
                'posts_per_page' => 8,
                'meta_query' => [
                    [
                        'key' => '_is_active',
                        'value' => '1',
                        'compare' => '='
                    ]
                ],
                'orderby' => [
                    'meta_value_num' => 'DESC',
                    'date' => 'DESC'
                ],
                'meta_key' => '_is_boosted'
            ],
            'button' => [
                'text' => __('Показати всі', 'panterrea_v1'),
                'link' => URL_CATALOG,
            ],
        ]); ?>

    <?php get_template_part('template-parts/how-work'); ?>
    <?php get_template_part('template-parts/advantages'); ?>
    <?php get_template_part('template-parts/footer-form'); ?>

</main>

<?php
if (!isset($_COOKIE['forumBannerClosed'])) {
    get_template_part('template-parts/banner-forum');
}
?>

<?php
add_action('wp_footer', function () {
    ?>
    <script>
    (function() {
        function initCategorySwiper() {
            if (typeof Swiper === 'undefined') return;
            var el = document.querySelector('.categorySwiper');
            if (!el || el.swiper) return;
            new Swiper('.categorySwiper', {
                slidesPerView: 6,
                spaceBetween: 24,
                loop: false,
                speed: 600,
                observer: true,
                observeParents: true,
                breakpoints: { 320: { slidesPerView: 2, spaceBetween: 12 }, 480: { slidesPerView: 3, spaceBetween: 16 }, 768: { slidesPerView: 4, spaceBetween: 20 }, 1024: { slidesPerView: 5, spaceBetween: 24 }, 1260: { slidesPerView: 6, spaceBetween: 24 } },
                navigation: { nextEl: '.categorySwiper__pagination__next', prevEl: '.categorySwiper__pagination__prev' },
                pagination: false,
                keyboard: { enabled: true, onlyInViewport: true },
                a11y: { prevSlideMessage: 'Previous slide', nextSlideMessage: 'Next slide' }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCategorySwiper);
        } else {
            initCategorySwiper();
        }
        window.addEventListener('load', initCategorySwiper);
    })();
    </script>
    <?php
}, 20);
get_footer();