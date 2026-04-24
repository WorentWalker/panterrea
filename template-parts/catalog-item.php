<?php
/*global $currentLang;
if (empty($currentLang) && isset($args['currentLang'])) {
    $currentLang = $args['currentLang'];
}*/
?>

<div class="catalogItem">

    <?php
    $catalog_data = get_field('catalog_post');
    $featured_image_mini = $catalog_data['featured_image_mini'] ?? '';
    $featured_image = $catalog_data['featured_image'] ?? '';
    $fallback_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');

    $image_url = $featured_image_mini ?: ($featured_image ?: $fallback_image);

    $categories = get_the_terms(get_the_ID(), 'catalog_category');
    $blank_image_url = '';

    if ($categories && !is_wp_error($categories)) {
        $primary_category = $categories[0];

        // Определяем родительскую категорию
        if ($primary_category->parent) {
            $parent_category = get_term($primary_category->parent, 'catalog_category');
        } else {
            // Если у категории нет родителя, используем саму категорию как родительскую
            $parent_category = $primary_category;
        }

        // Получаем поле blank_image только из родительской категории
        if ($parent_category && !is_wp_error($parent_category)) {
            $blank_image = get_field('blank_image', 'term_' . $parent_category->term_id);
            
            if ($blank_image) {
                // Если это массив с url
                if (is_array($blank_image) && isset($blank_image['url'])) {
                    $blank_image_url = $blank_image['url'];
                }
                // Если это ID изображения
                elseif (is_numeric($blank_image)) {
                    $blank_image_url = wp_get_attachment_image_url($blank_image, 'full');
                }
                // Если это строка (URL)
                elseif (is_string($blank_image)) {
                    $blank_image_url = $blank_image;
                }
            }
        }
    }
    ?>

    <?php if (!empty($image_url)) : ?>
    <a href="<?php the_permalink(); ?>">
        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title(); ?>" loading="lazy" decoding="async">
    </a>
    <?php else : ?>
    <a href="<?php the_permalink(); ?>">
        <img src="<?php echo esc_url($blank_image_url ?: get_template_directory_uri() . '/src/svg/logo_green.svg'); ?>"
            alt="<?php the_title(); ?>" loading="lazy" decoding="async">
    </a>
    <?php endif; ?>

    <?php if (get_post_meta(get_the_ID(), '_is_boosted', true) === '1') : ?>
    <div class="catalogItem__mark label-text boost">
        <?php echo esc_html__( 'Рекламується', 'panterrea_v1' ); ?>
    </div>
    <?php endif; ?>

    <?php
    $favorites = $args['favorites'] ?? null;
    $is_favorite = in_array(get_the_ID(), $favorites);
    $favorites_class = $is_favorite ? 'active' : '';
    ?>
    <div class="btn btn__favorites js-favorites catalogItem__favorites <?php echo esc_attr($favorites_class); ?>"
        data-post-id="<?php echo esc_attr(get_the_ID()); ?>"></div>

    <div class="catalogItem__desc">
        <?php
        $tags = get_the_terms(get_the_ID(), 'catalog_tag');
        if (!empty($tags) || get_post_meta(get_the_ID(), '_is_boosted', true) === '1') : ?>
        <div class="catalogItem__marks">
            <?php if (get_post_meta(get_the_ID(), '_is_boosted', true) === '1') : ?>
            <div class="catalogItem__mark label-text boost boost__mobile">
                <?php echo esc_html__( 'Рекламується', 'panterrea_v1' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($tags) && !is_wp_error($tags)) : foreach ($tags as $tag) : ?>
            <div class="catalogItem__mark label-text <?php echo esc_html($tag->slug); ?>">
                <?php
                $tag_name = $tag->name;
                echo esc_html($tag_name);
                ?>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php endif; ?>

        <a href="<?php the_permalink(); ?>" class="catalogItem__title subtitle1">
            <h3> <?php 
                $search_query = $args['search'] ?? '';
                if (!empty($search_query)) {
                    $highlight_terms = function_exists('panterrea_search_get_highlight_terms')
                        ? panterrea_search_get_highlight_terms($search_query)
                        : [$search_query];
                    echo highlight_search_query(get_the_title(), $highlight_terms ?: $search_query);
                } else {
                    the_title();
                }
            ?></h3>
        </a>
        <div class="catalogItem__descRow">
            <h4 class="catalogItem__price body2">
                <?php
                [$price, $currency] = panterrea_get_post_price_pair(get_the_ID(), $catalog_data);
                echo panterrea_format_price_display($price, $currency);
                ?>
            </h4>
        </div>
        <a href="<?php the_permalink(); ?>" class="catalogItem__detailsBtn btn btn__green">
            <?php esc_html_e('Детальніше', 'panterrea_v1'); ?>
        </a>
    </div>
</div>