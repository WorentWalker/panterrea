<?php
/*global $currentLang;*/

$terms_args = $args['terms_args'] ?? null;
if ($terms_args) {
    // Remove meta_key/orderby - they exclude terms without category_order. We sort in PHP instead.
    unset($terms_args['meta_key'], $terms_args['orderby'], $terms_args['order']);
    $terms = get_terms($terms_args);
    $terms = is_array($terms) && !is_wp_error($terms) ? $terms : [];
} else {
    $terms = [];
}

if (empty($terms) && is_tax('catalog_category')) {
    $current_term = get_queried_object();
    if ($current_term && $current_term->parent) {
        $terms = get_terms([
            'taxonomy' => 'catalog_category',
            'hide_empty' => false,
            'parent' => $current_term->parent,
        ]);
        $terms = is_array($terms) && !is_wp_error($terms) ? $terms : [];
    }
}

// Sort by category_order in PHP (terms without it get 0, then by name, then term_id for stability)
usort($terms, function ($a, $b) {
    $order_a = (int) get_field('category_order', 'term_' . $a->term_id);
    $order_b = (int) get_field('category_order', 'term_' . $b->term_id);
    if ($order_a !== $order_b) {
        return $order_a <=> $order_b;
    }
    $by_name = strcasecmp($a->name, $b->name);
    return $by_name !== 0 ? $by_name : ($a->term_id <=> $b->term_id);
});

// Don't filter by count: for parent categories, WordPress count only includes direct posts,
// not posts in subcategories. So we'd hide parent categories that have posts only in children.
$query_args = $args['query_args'] ?? [
        'post_type' => 'catalog_post',
        'posts_per_page' => 12,
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
    ];
$title = $args['title'] ?? false;
$subtitle = $args['subtitle'] ?? false;
$breadcrumbs = $args['breadcrumbs'] ?? false;
$sort = $args['sort'] ?? false;
$button = $args['button'] ?? false;
$current_category_slug = $args['category'] ?? 'all';
$author_id = $args['author_id'] ?? false;
$search = $args['search'] ?? false;
$show_sidebar_override = $args['show_sidebar'] ?? null;

$favorites = [];
if (is_user_logged_in()) {
    $favorites = get_user_meta(get_current_user_id(), 'favorite_posts', true);
    if (!$favorites) {
        $favorites = [];
    }
}

$custom_query = new WP_Query($query_args);
$total_author_posts = 0;
$catalog_found_count = $custom_query->found_posts;
if ($author_id) {
    $total_author_posts = $custom_query->found_posts;
}
?>

<section class="catalog">
    <div class="container">
        <?php $show_sidebar = $show_sidebar_override !== null ? (bool) $show_sidebar_override : ($breadcrumbs || $sort || (!empty($terms) && ($button ?? false))); ?>
        <div class="catalog__inner<?php echo $show_sidebar ? ' catalog__inner--withSidebar' : ''; ?>">

            <?php if ($show_sidebar) : ?>
            <?php
            $all_tags = get_terms(['taxonomy' => 'catalog_tag', 'hide_empty' => true]);
            $all_tags = !empty($all_tags) && !is_wp_error($all_tags) ? $all_tags : [];
            $condition_slugs = ['new', 'used', 'novyy', 'vzhyvanyy'];
            $ad_type_tags_all = array_values(array_filter($all_tags, fn($t) => !in_array($t->slug, $condition_slugs)));
            $condition_tags_all = array_values(array_filter($all_tags, fn($t) => in_array($t->slug, $condition_slugs)));
            $post_ids = array_map(fn($p) => $p->ID, $custom_query->posts);
            $available_ad_type = $ad_type_tags_all;
            $available_condition = $condition_tags_all;
            if (function_exists('get_available_filters_for_posts') && !empty($post_ids)) {
                $filtered = get_available_filters_for_posts($post_ids, $terms, $ad_type_tags_all, $condition_tags_all);
                $available_ad_type = $filtered['ad_type_tags'];
                $available_condition = $filtered['condition_tags'];
            }
            $available_terms_slugs = array_map(fn($t) => $t->slug, $terms);
            $available_ad_type_slugs = array_map(fn($t) => $t->slug, $available_ad_type);
            $available_condition_slugs = array_map(fn($t) => $t->slug, $available_condition);
            $price_max = function_exists('get_catalog_max_price') ? get_catalog_max_price($current_category_slug) : 1000000;
            $price_max = (int) $price_max;
            if ($price_max <= 0) $price_max = 1000000;
            $price_max_formatted = number_format($price_max, 0, ',', ' ');
            ?>
            <?php get_template_part('template-parts/catalog-sidebar', null, [
                'terms' => $terms,
                'ad_type_tags' => $ad_type_tags_all,
                'condition_tags' => $condition_tags_all,
                'available_terms_slugs' => $available_terms_slugs,
                'available_ad_type_slugs' => $available_ad_type_slugs,
                'available_condition_slugs' => $available_condition_slugs,
                'current_category' => $current_category_slug,
                'price_max' => $price_max,
                'price_max_formatted' => $price_max_formatted,
            ]); ?>
            <?php endif; ?>

            <div class="catalog__content">
                <?php if (is_front_page() && ($title || $subtitle)) : ?>
                <div class="catalog__header">
                    <?php if ($title) : ?>
                    <h2 class="catalog__title h3"><?= esc_html($title); ?></h2>
                    <?php endif; ?>
                    <?php if ($subtitle) : ?>
                    <div class="catalog__subtitle body1"><?= esc_html($subtitle); ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (is_front_page() && !empty($terms)) : ?>
                <div class="catalog__filters">
                    <div class="catalog__filter body2 active js-catalogFilter" data-category="all"><?= esc_html__('Всі', 'panterrea_v1'); ?></div>
                    <?php foreach ($terms as $term) : ?>
                    <div class="catalog__filter body2 js-catalogFilter" data-category="<?= esc_attr($term->slug); ?>"><?= esc_html($term->name); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($author_id) :
                get_template_part('template-parts/author-info', null, ['author_id' => $author_id]);
            endif; ?>

                <?php if ($breadcrumbs || $sort) : ?>
                <div class="catalog__navigation">
                    <?php if ($breadcrumbs) : ?>
                    <?php catalog_breadcrumbs(); ?>
                    <?php endif; ?>

                    <p class="catalog__count body2 js-catalogCount">
                        <?= esc_html__('Знайдено', 'panterrea_v1') . ' <strong><span class="js-catalogCountNum">' . (int) $catalog_found_count . '</span></strong> ' . esc_html__('оголошень', 'panterrea_v1'); ?>
                    </p>

                    <?php if ($sort) : ?>
                    <div class="catalog__navigation__inner">
                        <div class="catalog__tagsFilter">
                            <div class="catalog__sort">
                                <div class="catalog__dropdown">
                                    <div class="catalog__dropdown__title sortDrop button-medium">
                                        <?= esc_html__('Сортувати', 'panterrea_v1'); ?>
                                    </div>
                                    <div class="catalog__dropdown__list dropdown">
                                        <div class="dropdown__item">
                                            <input type="radio" name="sort" id="novelty" value="novelty" checked>
                                            <label class="body2"
                                                for="novelty"><span><?= esc_html__('Спочатку новіші', 'panterrea_v1'); ?></span></label>
                                        </div>
                                        <div class="dropdown__item">
                                            <input type="radio" name="sort" id="price_asc_uah" value="price_asc_uah">
                                            <label class="body2"
                                                for="price_asc_uah"><span><?= esc_html__('Спочатку дешевші', 'panterrea_v1'); ?></span></label>
                                        </div>
                                        <div class="dropdown__item">
                                            <input type="radio" name="sort" id="price_desc_uah" value="price_desc_uah">
                                            <label class="body2"
                                                for="price_desc_uah"><span><?= esc_html__('Спочатку дорожчі', 'panterrea_v1'); ?></span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div id="catalogItems" class="catalog__items">

                    <?php
                if ($custom_query->have_posts()) :
                    while ($custom_query->have_posts()) : $custom_query->the_post();
                        get_template_part('template-parts/catalog-item', null, [
                            'favorites' => $favorites,
                            'search' => $search
                        ]);
                    endwhile;
                    wp_reset_postdata();
                else :
                    get_template_part('template-parts/empty-folder', null, [
                        'info' => esc_html__('Немає оголошень по запиту', 'panterrea_v1')
                    ]);
                endif;
                ?>

                </div>

                <?php if ($button && !empty($button['text']) && !empty($button['link'])) : ?>
                <a href="<?= esc_url($button['link']); ?>" class="btn btn__showAll button-large">
                    <?= esc_html($button['text']); ?>
                </a>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>