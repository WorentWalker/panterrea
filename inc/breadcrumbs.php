<?php
function catalog_breadcrumbs()
{
    global $post;
    global $currentLang;
    $separator = ' <span class="separator"></span> ';
    $home_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20.5153 9.72843L13.0153 2.65218C13.0116 2.64898 13.0082 2.64554 13.005 2.64187C12.7289 2.39074 12.369 2.25159 11.9958 2.25159C11.6225 2.25159 11.2627 2.39074 10.9866 2.64187L10.9763 2.65218L3.48469 9.72843C3.33187 9.86895 3.20989 10.0397 3.12646 10.2298C3.04303 10.4199 2.99997 10.6252 3 10.8328V19.5C3 19.8978 3.15804 20.2793 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H9C9.39782 21 9.77936 20.842 10.0607 20.5607C10.342 20.2793 10.5 19.8978 10.5 19.5V15H13.5V19.5C13.5 19.8978 13.658 20.2793 13.9393 20.5607C14.2206 20.842 14.6022 21 15 21H19.5C19.8978 21 20.2794 20.842 20.5607 20.5607C20.842 20.2793 21 19.8978 21 19.5V10.8328C21 10.6252 20.957 10.4199 20.8735 10.2298C20.7901 10.0397 20.6681 9.86895 20.5153 9.72843ZM19.5 19.5H15V15C15 14.6022 14.842 14.2206 14.5607 13.9393C14.2794 13.658 13.8978 13.5 13.5 13.5H10.5C10.1022 13.5 9.72064 13.658 9.43934 13.9393C9.15804 14.2206 9 14.6022 9 15V19.5H4.5V10.8328L4.51031 10.8234L12 3.74999L19.4906 10.8216L19.5009 10.8309L19.5 19.5Z"/></svg>';

    $catalog_url = defined('URL_CATALOG') ? URL_CATALOG : home_url('/catalog');

    echo '<nav class="breadcrumbs">';
    echo '<a class="breadcrumbs__home" href="' . home_url() . '">' . $home_icon . '</a>' . $separator;

    if (is_post_type_archive('catalog_post')) {
        echo '<span class="breadcrumbs__title body2">' . esc_html__('Каталог', 'panterrea_v1') . '</span>';
    } elseif (is_tax('catalog_category')) {
        echo '<a class="breadcrumbs__link body2" href="' . esc_url($catalog_url) . '">' . esc_html__('Каталог', 'panterrea_v1') . '</a>' . $separator;
        $term = get_queried_object();
        if ($term->parent) {
            $parent_term = get_term($term->parent, 'catalog_category');
            $parent_link = function_exists('panterrea_get_catalog_category_link') ? panterrea_get_catalog_category_link($parent_term) : get_term_link($parent_term);
            $parent_name = $currentLang === 'en' && !empty(get_field('name_en', $parent_term)) ? get_field('name_en', $parent_term) : $parent_term->name;
            echo '<a class="breadcrumbs__link body2" href="' . esc_url($parent_link) . '">' . esc_html($parent_name) . '</a>' . $separator;
        }
        $term_name = $currentLang === 'en' && !empty(get_field('name_en', $term)) ? get_field('name_en', $term) : $term->name;
        echo '<span class="breadcrumbs__title body2">' . esc_html($term_name) . '</span>';
    } elseif (is_singular('catalog_post')) {
        echo '<a class="breadcrumbs__link body2" href="' . esc_url($catalog_url) . '">' . esc_html__('Каталог', 'panterrea_v1') . '</a>' . $separator;
        $terms = get_the_terms($post->ID, 'catalog_category');
        if ($terms && !is_wp_error($terms)) {
            $term = current($terms);
            if ($term->parent) {
                $parent_term = get_term($term->parent, 'catalog_category');
                $parent_link = function_exists('panterrea_get_catalog_category_link') ? panterrea_get_catalog_category_link($parent_term) : get_term_link($parent_term);
                $parent_name = $currentLang === 'en' && !empty(get_field('name_en', $parent_term)) ? get_field('name_en', $parent_term) : $parent_term->name;
                echo '<a class="breadcrumbs__link body2" href="' . esc_url($parent_link) . '">' . esc_html($parent_name) . '</a>' . $separator;
            }
            $term_link = function_exists('panterrea_get_catalog_category_link') ? panterrea_get_catalog_category_link($term) : get_term_link($term);
            $term_name = $currentLang === 'en' && !empty(get_field('name_en', $term)) ? get_field('name_en', $term) : $term->name;
            echo '<a class="breadcrumbs__link body2" href="' . esc_url($term_link) . '">' . esc_html($term_name) . '</a>' . $separator;
        }

        echo '<span class="breadcrumbs__title body2">' . esc_html(get_the_title()) . '</span>';
    } elseif (is_page_template('templates/ad-create.php')) {
        echo '<a class="breadcrumbs__link body2" href="' . esc_url($catalog_url) . '">' . esc_html__('Каталог', 'panterrea_v1') . '</a>' . $separator;
        echo '<span class="breadcrumbs__title body2">' . esc_html__('Створення оголошення', 'panterrea_v1') . '</span>';
    } elseif (is_page_template('templates/search.php')) {
        echo '<a class="breadcrumbs__link body2" href="' . esc_url($catalog_url) . '">' . esc_html__('Каталог', 'panterrea_v1') . '</a>' . $separator;
        echo '<span class="breadcrumbs__title body2">' . esc_html__('Пошук', 'panterrea_v1') . '</span>';
    }

    echo '</nav>';
}

function forum_breadcrumbs()
{
    global $currentLang;
    $separator = ' <span class="separator"></span> ';
    $home_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20.5153 9.72843L13.0153 2.65218C13.0116 2.64898 13.0082 2.64554 13.005 2.64187C12.7289 2.39074 12.369 2.25159 11.9958 2.25159C11.6225 2.25159 11.2627 2.39074 10.9866 2.64187L10.9763 2.65218L3.48469 9.72843C3.33187 9.86895 3.20989 10.0397 3.12646 10.2298C3.04303 10.4199 2.99997 10.6252 3 10.8328V19.5C3 19.8978 3.15804 20.2793 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H9C9.39782 21 9.77936 20.842 10.0607 20.5607C10.342 20.2793 10.5 19.8978 10.5 19.5V15H13.5V19.5C13.5 19.8978 13.658 20.2793 13.9393 20.5607C14.2206 20.842 14.6022 21 15 21H19.5C19.8978 21 20.2794 20.842 20.5607 20.5607C20.842 20.2793 21 19.8978 21 19.5V10.8328C21 10.6252 20.957 10.4199 20.8735 10.2298C20.7901 10.0397 20.6681 9.86895 20.5153 9.72843ZM19.5 19.5H15V15C15 14.6022 14.842 14.2206 14.5607 13.9393C14.2794 13.658 13.8978 13.5 13.5 13.5H10.5C10.1022 13.5 9.72064 13.658 9.43934 13.9393C9.15804 14.2206 9 14.6022 9 15V19.5H4.5V10.8328L4.51031 10.8234L12 3.74999L19.4906 10.8216L19.5009 10.8309L19.5 19.5Z"/></svg>';

    echo '<nav class="breadcrumbs">';
    echo '<a class="breadcrumbs__home" href="' . home_url() . '">' . $home_icon . '</a>' . $separator;

    if (is_single() || is_singular('post')) {
        echo '<a class="breadcrumbs__link body2" href="' . esc_url(home_url('/forum')) . '">' . esc_html__('Форум', 'panterrea_v1') . '</a>' . $separator;
        
        // Find blog page with template blog.php
        $blog_page = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'templates/blog.php',
            'number' => 1
        ));
        
        if (!empty($blog_page)) {
            $blog_page = $blog_page[0];
            echo '<a class="breadcrumbs__link body2" href="' . esc_url(get_permalink($blog_page->ID)) . '">' . esc_html($blog_page->post_title) . '</a>' . $separator;
        }
        
        echo '<span class="breadcrumbs__title body2">' . esc_html(get_the_title()) . '</span>';
    } elseif (is_page_template('templates/forum.php') || is_page_template('templates/blog.php')) {
        // On forum or blog page, just show "Home > Forum" without additional element
        echo '<a class="breadcrumbs__link body2" href="' . esc_url(home_url('/forum')) . '">' . esc_html__('Форум', 'panterrea_v1') . '</a>';
    } else {
        echo '<a class="breadcrumbs__link body2" href="' . esc_url(home_url('/forum')) . '">' . esc_html__('Форум', 'panterrea_v1') . '</a>' . $separator;
        
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_name = get_user_meta($user_id, 'name', true);
            if (!empty($user_name)) {
                echo '<span class="breadcrumbs__title body2">' . esc_html($user_name) . '</span>';
            } else {
                echo '<span class="breadcrumbs__title body2">' . esc_html__('Гість', 'panterrea_v1') . '</span>';
            }
        } else {
            echo '<span class="breadcrumbs__title body2">' . esc_html__('Гість', 'panterrea_v1') . '</span>';
        }
    }

    echo '</nav>';
}

function blog_breadcrumbs()
{
    global $post;
    $separator = ' <span class="separator"></span> ';
    $home_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20.5153 9.72843L13.0153 2.65218C13.0116 2.64898 13.0082 2.64554 13.005 2.64187C12.7289 2.39074 12.369 2.25159 11.9958 2.25159C11.6225 2.25159 11.2627 2.39074 10.9866 2.64187L10.9763 2.65218L3.48469 9.72843C3.33187 9.86895 3.20989 10.0397 3.12646 10.2298C3.04303 10.4199 2.99997 10.6252 3 10.8328V19.5C3 19.8978 3.15804 20.2793 3.43934 20.5607C3.72064 20.842 4.10218 21 4.5 21H9C9.39782 21 9.77936 20.842 10.0607 20.5607C10.342 20.2793 10.5 19.8978 10.5 19.5V15H13.5V19.5C13.5 19.8978 13.658 20.2793 13.9393 20.5607C14.2206 20.842 14.6022 21 15 21H19.5C19.8978 21 20.2794 20.842 20.5607 20.5607C20.842 20.2793 21 19.8978 21 19.5V10.8328C21 10.6252 20.957 10.4199 20.8735 10.2298C20.7901 10.0397 20.6681 9.86895 20.5153 9.72843ZM19.5 19.5H15V15C15 14.6022 14.842 14.2206 14.5607 13.9393C14.2794 13.658 13.8978 13.5 13.5 13.5H10.5C10.1022 13.5 9.72064 13.658 9.43934 13.9393C9.15804 14.2206 9 14.6022 9 15V19.5H4.5V10.8328L4.51031 10.8234L12 3.74999L19.4906 10.8216L19.5009 10.8309L19.5 19.5Z"/></svg>';

    echo '<nav class="breadcrumbs">';
    echo '<a class="breadcrumbs__home" href="' . home_url() . '">' . $home_icon . '</a>' . $separator;

    // Find blog page with template blog.php
    $blog_page = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'templates/blog.php',
        'number' => 1
    ));

    if (is_page_template('templates/blog.php')) {
        // On blog page, just show "Home > Blog"
        if (!empty($blog_page)) {
            $blog_page = $blog_page[0];
            echo '<span class="breadcrumbs__title body2">' . esc_html($blog_page->post_title) . '</span>';
        } else {
            echo '<span class="breadcrumbs__title body2">' . esc_html__('Блог', 'panterrea_v1') . '</span>';
        }
    } elseif (is_single() || is_singular('post')) {
        // On single post page: Home > Blog > [Category] > Post Title
        if (!empty($blog_page)) {
            $blog_page = $blog_page[0];
            echo '<a class="breadcrumbs__link body2" href="' . esc_url(get_permalink($blog_page->ID)) . '">' . esc_html($blog_page->post_title) . '</a>' . $separator;
        } else {
            echo '<a class="breadcrumbs__link body2" href="' . esc_url(home_url('/blog')) . '">' . esc_html__('Блог', 'panterrea_v1') . '</a>' . $separator;
        }

        // Get post categories (excluding Uncategorized)
        $default_category_id = get_option('default_category');
        $post_categories = get_the_category();
        
        if ($post_categories && !is_wp_error($post_categories)) {
            // Filter out Uncategorized
            $valid_categories = array_filter($post_categories, function($cat) use ($default_category_id) {
                return $cat->term_id != $default_category_id;
            });
            
            // Show first valid category if exists
            if (!empty($valid_categories)) {
                $category = reset($valid_categories);
                $category_link = get_category_link($category->term_id);
                echo '<a class="breadcrumbs__link body2" href="' . esc_url($category_link) . '">' . esc_html($category->name) . '</a>' . $separator;
            }
        }

        echo '<span class="breadcrumbs__title body2">' . esc_html(get_the_title()) . '</span>';
    } else {
        // Fallback: just show blog link
        if (!empty($blog_page)) {
            $blog_page = $blog_page[0];
            echo '<span class="breadcrumbs__title body2">' . esc_html($blog_page->post_title) . '</span>';
        } else {
            echo '<span class="breadcrumbs__title body2">' . esc_html__('Блог', 'panterrea_v1') . '</span>';
        }
    }

    echo '</nav>';
}