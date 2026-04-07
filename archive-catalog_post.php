<?php

/**
 * Catalog Archive — category grid only
 */

// Set custom title and description for catalog archive
add_filter('document_title_parts', function($title) {
    if (is_post_type_archive('catalog_post')) {
        $title['title'] = 'Каталог оголошень для фермерів ВРХ | Товари, партнери, рішення — PanTerrea';
    }
    return $title;
}, 10, 1);

add_action('wp_head', function() {
    if (is_post_type_archive('catalog_post')) {
        echo '<meta name="description" content="' . esc_attr('Каталог PanTerrea — усі оголошення для фермерів ВРХ в одному місці. Товари, партнери та рішення від спільноти, якій можна довіряти.') . '">' . "\n";
    }
}, 1);

get_header();
?>

<main id="infiniteScroll" class="catalogArchive firstSectionPadding">
    <div class="container">
        <div class="catalogArchive__inner">
            <h1 class="catalogArchive__title h3">
                <?php echo esc_html__('Каталог', 'panterrea_v1'); ?>
            </h1>
            <div class="catalogArchive__subtitle">
                <?php echo esc_html__('Знаходьте товари, партнерів і перевірені рішення', 'panterrea_v1'); ?>
            </div>
            <div class="catalogArchive__breadcrumbs">
                <?php catalog_breadcrumbs(); ?>
            </div>
            <div class="catalogArchive__search">
                <?php get_template_part('template-parts/search-input', null, ['width' => 'full']); ?>
            </div>
        </div>
    </div>

    <?php
    // Get all parent categories
    $parent_categories_args = [
        'taxonomy' => 'catalog_category',
        'hide_empty' => false,
        'meta_key' => 'category_order',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'parent' => 0
    ];

    $parent_categories = get_terms($parent_categories_args);
    $parent_categories = is_array($parent_categories) && !is_wp_error($parent_categories) ? $parent_categories : [];

    // Stable sort: category_order first, then name (fixes swap on back navigation)
    usort($parent_categories, function ($a, $b) {
        $order_a = (int) get_field('category_order', 'term_' . $a->term_id);
        $order_b = (int) get_field('category_order', 'term_' . $b->term_id);
        if ($order_a !== $order_b) {
            return $order_a <=> $order_b;
        }
        return strcasecmp($a->name, $b->name) ?: ($a->term_id <=> $b->term_id);
    });

    if (!empty($parent_categories)) {
        ?>
    <section class="catalogArchive__categories">
        <div class="container">
            <h2 class="catalogArchive__categories__title h4">
                <?php echo esc_html__('Товари по категоріям', 'panterrea_v1'); ?>
            </h2>
            <div class="catalogArchive__categories__grid">
                <?php
                    foreach ($parent_categories as $category) {
                        $category_image = get_field('category_image', 'term_' . $category->term_id);
                        $category_link = panterrea_get_catalog_category_link($category);
                        
                        // Get subcategories
                        $subcategories_args = [
                            'taxonomy' => 'catalog_category',
                            'hide_empty' => false,
                            'meta_key' => 'category_order',
                            'orderby' => 'meta_value_num',
                            'order' => 'ASC',
                            'parent' => $category->term_id,
                        ];
                        $subcategories = get_terms($subcategories_args);
                        $subcategories = is_array($subcategories) && !is_wp_error($subcategories) ? $subcategories : [];
                        usort($subcategories, function ($a, $b) {
                            $order_a = (int) get_field('category_order', 'term_' . $a->term_id);
                            $order_b = (int) get_field('category_order', 'term_' . $b->term_id);
                            if ($order_a !== $order_b) return $order_a <=> $order_b;
                            return strcasecmp($a->name, $b->name) ?: ($a->term_id <=> $b->term_id);
                        });
                        
                        // Count active posts in this category (including subcategories)
                        $active_posts_count = new WP_Query([
                            'post_type' => 'catalog_post',
                            'posts_per_page' => -1,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'catalog_category',
                                    'field' => 'term_id',
                                    'terms' => $category->term_id,
                                    'include_children' => true,
                                ]
                            ],
                            'meta_query' => [
                                [
                                    'key'     => '_is_active',
                                    'value'   => '1',
                                    'compare' => '='
                                ]
                            ],
                            'fields' => 'ids'
                        ]);

                        $post_count = $active_posts_count->found_posts;
                        ?>
                <div class="catalogArchive__category">
                    <?php if ($category_image && isset($category_image['url'])) { ?>
                    <div class="catalogArchive__category__icon">
                        <img src="<?php echo esc_url($category_image['url']); ?>"
                            alt="<?php echo esc_attr($category->name); ?>" />
                    </div>
                    <?php } ?>
                    <div class="catalogArchive__category__content">
                        <h3 class="catalogArchive__category__title">
                            <?php echo esc_html($category->name); ?>
                        </h3>


                        <?php if ($subcategories && !is_wp_error($subcategories) && !empty($subcategories)) { ?>
                        <ul class="catalogArchive__category__subcategories">
                            <?php
                                        // Limit to 5 subcategories for display
                                        $display_subcategories = array_slice($subcategories, 0, 5);
                                        foreach ($display_subcategories as $subcategory) {
                                            $subcategory_link = panterrea_get_catalog_category_link($subcategory);
                                            
                                            $subcategory_posts_count = new WP_Query([
                                                'post_type' => 'catalog_post',
                                                'posts_per_page' => -1,
                                                'tax_query' => [
                                                    [
                                                        'taxonomy' => 'catalog_category',
                                                        'field' => 'term_id',
                                                        'terms' => $subcategory->term_id,
                                                        'include_children' => true,
                                                    ]
                                                ],
                                                'meta_query' => [
                                                    [
                                                        'key'     => '_is_active',
                                                        'value'   => '1',
                                                        'compare' => '='
                                                    ]
                                                ],
                                                'fields' => 'ids'
                                            ]);
                                            
                                            $subcategory_count = $subcategory_posts_count->found_posts;
                                            ?>
                            <li class="catalogArchive__category__subcategory">
                                <a href="<?php echo esc_url($subcategory_link); ?>"
                                    class="catalogArchive__category__subcategory__link">
                                    <span
                                        class="catalogArchive__category__subcategory__name"><?php echo esc_html($subcategory->name); ?></span>
                                    <span
                                        class="catalogArchive__category__subcategory__count"><?php echo $subcategory_count; ?></span>
                                </a>
                            </li>
                            <?php
                                        }
                                        ?>
                        </ul>
                        <?php } ?>

                        <a href="<?php echo esc_url($category_link); ?>" class="catalogArchive__category__show-all">
                            <?php echo esc_html__('Показати все', 'panterrea_v1'); ?>
                        </a>
                    </div>
                </div>
                <?php
                    }
                    ?>
            </div>
        </div>
    </section>
    <?php
    }
    ?>

</main>

<?php
get_footer();