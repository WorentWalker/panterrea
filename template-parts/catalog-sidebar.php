<?php
$terms = $args['terms'] ?? [];
$ad_type_tags = $args['ad_type_tags'] ?? [];
$condition_tags = $args['condition_tags'] ?? [];
$available_terms_slugs = $args['available_terms_slugs'] ?? [];
$available_ad_type_slugs = $args['available_ad_type_slugs'] ?? [];
$available_condition_slugs = $args['available_condition_slugs'] ?? [];
$current_category = $args['current_category'] ?? 'all';
$price_max = isset($args['price_max']) ? (int) $args['price_max'] : 1000000;
$price_max = $price_max > 0 ? $price_max : 1000000;
$price_max_formatted = $args['price_max_formatted'] ?? number_format($price_max, 0, ',', ' ');
?>

<aside class="catalog__sidebar">
    <div class="catalog__sidebar__header">
        <h3 class="catalog__sidebar__title h6"><?php esc_html_e('Фільтри', 'panterrea_v1'); ?></h3>
        <button type="button" class="catalog__sidebar__clear body2 js-catalogSidebarClear">
            <?php esc_html_e('Очистити все', 'panterrea_v1'); ?>
        </button>
    </div>

    <div id="catalogTagActive" class="catalog__tagActive catalog__sidebar__actives"></div>
    <!-- <a href="#" class="catalog__sidebar__showAll body2 js-catalogSidebarShowAll">
        <?php esc_html_e('Показати все', 'panterrea_v1'); ?>
    </a> -->

    <?php if (!empty($terms)) : ?>
    <div class="catalog__sidebar__section js-catalogSidebarSection" data-filter-section="category">
        <h4 class="catalog__sidebar__sectionTitle subtitle2">
            <?php esc_html_e('Категорії товарів', 'panterrea_v1'); ?>
        </h4>
        <div class="catalog__sidebar__checkboxes">
            <?php $catalog_base_url = defined('URL_CATALOG') ? URL_CATALOG : home_url('/catalog'); ?>
            <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="all" data-filter-section="category" data-available="1">
                <input type="checkbox" id="category-all" class="js-catalogFilter" data-category="all"
                    data-category-url="<?php echo esc_url($catalog_base_url); ?>"
                    <?php checked($current_category, 'all'); ?>>
                <label class="body2" for="category-all"><?php esc_html_e('Всі', 'panterrea_v1'); ?></label>
            </div>
            <?php foreach ($terms as $term) : ?>
            <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="<?php echo esc_attr($term->slug); ?>" data-filter-section="category" data-available="1">
                <input type="checkbox" id="category-<?php echo esc_attr($term->slug); ?>" class="js-catalogFilter"
                    data-category="<?php echo esc_attr($term->slug); ?>"
                    data-category-url="<?php echo esc_url(panterrea_get_catalog_category_link($term)); ?>"
                    <?php checked($current_category, $term->slug); ?>>
                <label class="body2" for="category-<?php echo esc_attr($term->slug); ?>">
                    <?php echo esc_html($term->name); ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="#" class="catalog__sidebar__showAll body2"><?php esc_html_e('Показати все', 'panterrea_v1'); ?></a>
    </div>
    <?php endif; ?>

    <?php if (!empty($ad_type_tags)) : ?>
    <div class="catalog__sidebar__section js-catalogSidebarSection<?php echo empty($available_ad_type_slugs) ? ' catalog__sidebar__section--empty' : ''; ?>" data-filter-section="ad_type">
        <h4 class="catalog__sidebar__sectionTitle subtitle2">
            <?php esc_html_e('Тип оголошення', 'panterrea_v1'); ?>
        </h4>
        <div class="catalog__sidebar__checkboxes">
            <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="tags-all" data-filter-section="ad_type" data-available="1">
                <input type="checkbox" id="tags-all" checked>
                <label class="body2" for="tags-all"><?php esc_html_e('Всі', 'panterrea_v1'); ?></label>
            </div>
            <?php foreach ($ad_type_tags as $tag) : ?>
            <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="<?php echo esc_attr($tag->slug); ?>" data-filter-section="ad_type" data-available="<?php echo in_array($tag->slug, $available_ad_type_slugs) ? '1' : '0'; ?>">
                <input type="checkbox" id="<?php echo esc_attr($tag->slug); ?>">
                <label class="body2" for="<?php echo esc_attr($tag->slug); ?>">
                    <?php echo esc_html($tag->name); ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="catalog__sidebar__section">
        <h4 class="catalog__sidebar__sectionTitle subtitle2">
            <?php esc_html_e('Ціна, грн', 'panterrea_v1'); ?>
        </h4>
        <div class="catalog__sidebar__price js-priceSlider" data-min="0" data-max="<?php echo esc_attr($price_max); ?>">
            <input type="range" class="js-priceRangeMin" style="display:none" min="0" max="<?php echo esc_attr($price_max); ?>" value="0" step="1">
            <input type="range" class="js-priceRangeMax" style="display:none" min="0" max="<?php echo esc_attr($price_max); ?>" value="<?php echo esc_attr($price_max); ?>" step="1">
            <div class="catalog__sidebar__priceInputs">
                <div class="catalog__sidebar__priceInput">
                    <label class="caption" for="priceMinInput">Min</label>
                    <input type="number" id="priceMinInput" class="js-priceMinInput" value="0" min="0"
                        max="<?php echo esc_attr($price_max); ?>">
                </div>
                <div class="catalog__sidebar__priceInput">
                    <label class="caption" for="priceMaxInput">Max</label>
                    <input type="number" id="priceMaxInput" class="js-priceMaxInput"
                        value="<?php echo esc_attr($price_max); ?>" min="0" max="<?php echo esc_attr($price_max); ?>">
                </div>
            </div>
            <div class="catalog__sidebar__checkbox">
                <input type="checkbox" id="noPrice" class="js-noPrice">
                <label class="body2" for="noPrice"><?php esc_html_e('Оголошення без ціни', 'panterrea_v1'); ?></label>
            </div>
        </div>
    </div>

    <?php if (!empty($condition_tags)) : ?>
    <div class="catalog__sidebar__section js-catalogSidebarSection<?php echo empty($available_condition_slugs) ? ' catalog__sidebar__section--empty' : ''; ?>" data-filter-section="condition">
        <h4 class="catalog__sidebar__sectionTitle subtitle2">
            <?php esc_html_e('Стан', 'panterrea_v1'); ?>
        </h4>
        <div class="catalog__sidebar__checkboxes">
            <?php if (empty($ad_type_tags)) : ?>
            <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="tags-all" data-filter-section="condition" data-available="1">
                <input type="checkbox" id="tags-all" checked>
                <label class="body2" for="tags-all"><?php esc_html_e('Всі', 'panterrea_v1'); ?></label>
            </div>
            <?php endif; ?>
            <?php foreach ($condition_tags as $tag) : ?>
            <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="<?php echo esc_attr($tag->slug); ?>" data-filter-section="condition" data-available="<?php echo in_array($tag->slug, $available_condition_slugs) ? '1' : '0'; ?>">
                <input type="checkbox" id="<?php echo esc_attr($tag->slug); ?>">
                <label class="body2" for="<?php echo esc_attr($tag->slug); ?>">
                    <?php echo esc_html($tag->name); ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</aside>