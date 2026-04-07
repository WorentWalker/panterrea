<section class="heroSearch">
    <div class="container">
        <div class="heroSearch__inner">
            <h1 class="heroSearch__title h3">Перший Український онлайн базар</h1>
            <div class="heroSearch__subtitle">Продавай. Купуй. Орендуй. Господарюй!</div>

            <div class="heroSearch__blockSearch">
                <input type="text" name="search" placeholder="Що вас цікавить ?" class="input input__search body1"
                       aria-label="Search">
                <div class="btn__search"></div>
            </div>

            <div class="heroSearch__categories">

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

                if ($categories && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $category_image = get_field('category_image', 'term_' . $category->term_id);
                        $category_link = get_term_link($category);
                        ?>
                        <a href="<?php echo esc_url($category_link); ?>" class="heroSearch__category">
                            <?php
                            if ($category_image) {
                                echo '<img src="' . esc_url($category_image['url']) . '" alt="' . esc_attr($category->name) . '">';
                            } ?>
                            <div class="heroSearch__category__title subtitle2"><?php echo esc_html($category->name); ?></div>
                        </a>
                        <?php
                    }
                } ?>

            </div>

            <a href="#" class="btn btn__showAll button-large">Показати всі</a>

        </div>
    </div>
</section>