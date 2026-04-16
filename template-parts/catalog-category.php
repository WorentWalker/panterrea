<?php
/*global $currentLang;*/
?>

<section class="catalogCategory">
    <div class="container">
        <div class="catalogCategory__inner">

            <?php catalog_breadcrumbs(); ?>

            <div class="catalog__mobileBar">
                <?php get_template_part('template-parts/search-input', null, [ 'width' => 'full' ]); ?>
                <button type="button" class="catalog__filtersToggle js-catalogFiltersToggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                    <?php esc_html_e('Фільтри', 'panterrea_v1'); ?>
                </button>
            </div>
            <div class="catalogCategory__grid">
                <!-- <h1 class="catalogCategory__categories__title h3">
                    <?php the_title(); ?>
                </h1> -->

                <?php
                if (is_tax('catalog_category')) {
                    $current_category = get_queried_object();

                    if ($current_category) {
                        $args = array(
                            'taxonomy' => 'catalog_category',
                            'hide_empty' => false,
                            'meta_key' => 'category_order',
                            'orderby' => 'meta_value_num',
                            'order' => 'ASC',
                            'parent' => $current_category->term_id,
                        );

                        $subcategories = get_terms($args);
                        $subcategories = is_array($subcategories) && !is_wp_error($subcategories) ? $subcategories : [];

                        // Stable sort: category_order first, then name (fixes swap on back navigation)
                        usort($subcategories, function ($a, $b) {
                            $order_a = (int) get_field('category_order', 'term_' . $a->term_id);
                            $order_b = (int) get_field('category_order', 'term_' . $b->term_id);
                            if ($order_a !== $order_b) {
                                return $order_a <=> $order_b;
                            }
                            return strcasecmp($a->name, $b->name) ?: ($a->term_id <=> $b->term_id);
                        });

                        if (!empty($subcategories)) {
                            foreach ($subcategories as $subcategory) {
                                $subcategory_link = panterrea_get_catalog_category_link($subcategory);

                                $active_posts_count = new WP_Query([
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

                                $post_count = $active_posts_count->found_posts;

                                ?>
                <a href="<?php echo esc_url($subcategory_link); ?>" class="catalogCategory__subcategory">
                    <h3 class="catalogCategory__subcategory__title h6">
                        <?php
                                        /*$subcategory_name = ($currentLang === 'en' && ($name_en = get_field('name_en', $subcategory))) ? $name_en : $subcategory->name;*/
                                        $subcategory_name = $subcategory->name;
                                        echo esc_html($subcategory_name);
                                        ?>
                    </h3>
                    <div class="catalogCategory__subcategory__post-count label-text">
                        <?php echo $post_count; ?>
                    </div>
                </a>
                <?php
                            }
                        }
                    }
                }
                ?>

            </div>
        </div>
</section>