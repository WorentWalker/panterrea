<?php
/**
 * Forum filters sidebar.
 *
 * @var array|null $args {
 *     @type WP_Term[] $categories
 *     @type int[]     $selected_cat_ids
 *     @type bool      $only_my   Legacy mirror of sort === mine.
 *     @type string    $forum_sort all|popular|mine
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$categories = (isset($args) && is_array($args) && isset($args['categories'])) ? $args['categories'] : [];
$selected_cat_ids = (isset($args) && is_array($args) && isset($args['selected_cat_ids'])) ? $args['selected_cat_ids'] : [];
$forum_sort = 'all';
if (isset($args) && is_array($args) && isset($args['forum_sort'])) {
    $forum_sort = panterrea_forum_sanitize_sort((string) $args['forum_sort']);
}
$selected_cat_ids = array_map('intval', (array) $selected_cat_ids);
$all_categories_selected = ($selected_cat_ids === []);

$categories_visible = array_slice($categories, 0, 4);
$categories_extra = array_slice($categories, 4);
$has_extra_categories = count($categories_extra) > 0;
?>

<aside class="forum__sidebar" aria-label="<?php echo esc_attr__('Фільтри форуму', 'panterrea_v1'); ?>">

    <div class="forum__sidebar__section forum__sidebar__section--search">
        <span class="forum__sidebar__label subtitle1"><?php esc_html_e('Пошук', 'panterrea_v1'); ?></span>
        <div class="forum__sidebar__search">
            <span class="forum__sidebar__searchIcon" aria-hidden="true"></span>
            <input id="searchInputForum" type="search" name="search" inputmode="search" autocomplete="off"
                placeholder="<?php esc_attr_e('Пошук', 'panterrea_v1'); ?>" class="forum__sidebar__searchInput js-forumSearchInput body2"
                aria-label="<?php esc_attr_e('Пошук у стрічці форуму', 'panterrea_v1'); ?>"
                value="<?php echo isset($_GET['q']) ? esc_attr(sanitize_text_field(wp_unslash($_GET['q']))) : ''; ?>">
            <button type="button" class="forum__sidebar__searchSubmit"
                aria-label="<?php esc_attr_e('Шукати', 'panterrea_v1'); ?>"></button>
        </div>
    </div>

    <div class="forum__sidebar__section">
        <span class="forum__sidebar__label subtitle1"><?php esc_html_e('Сортування', 'panterrea_v1'); ?></span>
        <div class="forum__sidebar__sortRadios dropdown" role="radiogroup"
            aria-label="<?php esc_attr_e('Сортування дописів', 'panterrea_v1'); ?>">
            <div class="dropdown__item">
                <input type="radio" name="forum_feed_sort" id="forum-sort-all" value="all"
                    <?php checked($forum_sort, 'all'); ?>>
                <label class="subtitle2" for="forum-sort-all"><?php esc_html_e('Всі дописи', 'panterrea_v1'); ?></label>
            </div>
            <div class="dropdown__item">
                <input type="radio" name="forum_feed_sort" id="forum-sort-popular" value="popular"
                    <?php checked($forum_sort, 'popular'); ?>>
                <label class="subtitle2"
                    for="forum-sort-popular"><?php esc_html_e('Популярні', 'panterrea_v1'); ?></label>
            </div>

            <?php if (is_user_logged_in()) : ?>
            <div class="dropdown__item">
                <input type="radio" name="forum_feed_sort" id="forum-sort-mine" value="mine"
                    <?php checked($forum_sort, 'mine'); ?>>
                <label class="subtitle2"
                    for="forum-sort-mine"><?php esc_html_e('Мої дописи', 'panterrea_v1'); ?></label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($categories)) : ?>
    <div class="forum__sidebar__section forum__sidebar__section--categories">
        <span class="forum__sidebar__label subtitle1"><?php esc_html_e('Категорії', 'panterrea_v1'); ?></span>
        <div class="catalog__sidebar__checkboxes">
            <div class="catalog__sidebar__checkbox dropdown__item">
                <input type="checkbox" id="forum-cat-all" class="js-forumCategoryAll">
                <label class="body2" for="forum-cat-all"><?php esc_html_e('Всі категорії', 'panterrea_v1'); ?></label>
            </div>
            <?php $rendered_ids = []; ?>
            <?php foreach ($categories_visible as $term) : ?>
            <?php
                if (!$term instanceof WP_Term) continue;
                $tid = (int) $term->term_id;
                if (in_array($tid, $rendered_ids, true)) continue;
                $rendered_ids[] = $tid;
                $slug = $term->slug;
                $fid  = 'forum-cat-' . $tid;
                ?>
            <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="<?php echo esc_attr($slug); ?>"
                data-filter-section="forum_category" data-available="1">
                <input type="checkbox" id="<?php echo esc_attr($fid); ?>" class="js-forumCategoryFilter"
                    value="<?php echo esc_attr((string) $tid); ?>" data-category="<?php echo esc_attr($slug); ?>"
                    <?php checked(in_array($tid, $selected_cat_ids, true)); ?>>
                <label class="body2" for="<?php echo esc_attr($fid); ?>"><?php echo esc_html($term->name); ?></label>
            </div>
            <?php endforeach; ?>

            <?php if ($has_extra_categories) : ?>
            <div class="forum__sidebar__categoryExtras is-collapsed">
                <?php foreach ($categories_extra as $term) : ?>
                <?php
                    if (!$term instanceof WP_Term) continue;
                    $tid = (int) $term->term_id;
                    if (in_array($tid, $rendered_ids, true)) continue;
                    $rendered_ids[] = $tid;
                    $slug = $term->slug;
                    $fid  = 'forum-cat-' . $tid;
                    ?>
                <div class="catalog__sidebar__checkbox dropdown__item" data-filter-slug="<?php echo esc_attr($slug); ?>"
                    data-filter-section="forum_category" data-available="1">
                    <input type="checkbox" id="<?php echo esc_attr($fid); ?>" class="js-forumCategoryFilter"
                        value="<?php echo esc_attr((string) $tid); ?>" data-category="<?php echo esc_attr($slug); ?>"
                        <?php checked(in_array($tid, $selected_cat_ids, true)); ?>>
                    <label class="body2"
                        for="<?php echo esc_attr($fid); ?>"><?php echo esc_html($term->name); ?></label>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="forum__sidebar__catsToggle body2 js-forumCatsToggle"
                data-label-show="<?php echo esc_attr__('Показати всі', 'panterrea_v1'); ?>"
                data-label-hide="<?php echo esc_attr__('Сховати', 'panterrea_v1'); ?>" aria-expanded="false">
                <?php esc_html_e('Показати всі', 'panterrea_v1'); ?>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="forum__sidebar__section forum__sidebar__section--hideBtn">
        <button type="button" class="forum__filtersHide js-forumFiltersHide">
            <?php esc_html_e('Сховати фільтри', 'panterrea_v1'); ?>
        </button>
    </div>
</aside>