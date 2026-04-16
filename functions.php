<?php

/**
 * Constants
 */

define('URL_LOGIN', home_url('/login'));
define('URL_REGISTER', home_url('/register'));
define('URL_FORGOTPASS', home_url('/forgot-password'));
define('URL_RESETPASS', home_url('/reset-password'));
define('URL_PROFILE', home_url('/profile'));
define('URL_FAVORITES', home_url('/favorites'));
define('URL_CATALOG', home_url('/catalog'));
define('URL_CREATEAD', home_url('/create-ad'));
define('URL_AUTHORAD', home_url('/author-ad'));
define('URL_USERAD', home_url('/user-ad'));
define('URL_EDITAD', home_url('/edit-ad'));
define('URL_BOOSTAD', home_url('/boost-ad'));
define('URL_MESSAGES', home_url('/user-messages'));
define('URL_FORUM', home_url('/forum'));

/**
 * Forum feed: sanitize category term IDs from request values.
 *
 * @param array<int|string> $ids
 * @return int[]
 */
function panterrea_forum_sanitize_cat_ids($ids)
{
    $out = [];
    foreach ((array) $ids as $id) {
        $i = (int) $id;
        if ($i > 0) {
            $out[] = $i;
        }
    }

    return array_values(array_unique($out));
}

/**
 * @return int[]
 */
function panterrea_forum_get_cat_ids_from_request($request_key = 'forum_cat')
{
    if (empty($_GET[$request_key])) {
        return [];
    }
    $raw = wp_unslash($_GET[$request_key]);
    if (is_array($raw)) {
        return panterrea_forum_sanitize_cat_ids($raw);
    }

    return panterrea_forum_sanitize_cat_ids(explode(',', (string) $raw));
}

/**
 * @param int[] $cat_ids
 * @return array<int, array<string, mixed>>
 */
function panterrea_forum_tax_query_for_categories(array $cat_ids)
{
    $cat_ids = panterrea_forum_sanitize_cat_ids($cat_ids);
    if ($cat_ids === []) {
        return [];
    }

    return [
        [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => $cat_ids,
            'operator' => 'IN',
        ],
    ];
}

/**
 * @return int[]
 */
function panterrea_forum_cat_ids_from_post_param()
{
    if (empty($_POST['forum_cats'])) {
        return [];
    }
    $raw = wp_unslash($_POST['forum_cats']);
    if (!is_array($raw)) {
        $raw = [$raw];
    }

    return panterrea_forum_sanitize_cat_ids($raw);
}

/**
 * Forum list sort: all (chronological), popular, mine.
 *
 * @return string all|popular|mine
 */
function panterrea_forum_sanitize_sort($value)
{
    $value = is_string($value) ? strtolower(sanitize_key($value)) : '';
    if ($value === 'popular') {
        return 'popular';
    }
    if ($value === 'recent') {
        return 'recent';
    }
    if ($value === 'mine' && is_user_logged_in()) {
        return 'mine';
    }

    return 'all';
}

/**
 * @return string all|popular|mine
 */
function panterrea_forum_get_sort_from_request()
{
    if (!empty($_GET['forum_sort'])) {
        return panterrea_forum_sanitize_sort(wp_unslash($_GET['forum_sort']));
    }
    if (!empty($_GET['only_my']) && $_GET['only_my'] === '1' && is_user_logged_in()) {
        return 'mine';
    }

    return 'all';
}

function panterrea_forum_sync_like_count($post_id)
{
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return;
    }
    $likes = get_post_meta($post_id, '_forum_likes', true);
    $likes = is_array($likes) ? $likes : [];
    update_post_meta($post_id, '_forum_like_count', count($likes));
}

function panterrea_forum_get_like_count($post_id)
{
    $post_id = (int) $post_id;
    $stored = get_post_meta($post_id, '_forum_like_count', true);
    if ($stored !== '' && $stored !== false && $stored !== null) {
        return (int) $stored;
    }
    $likes = get_post_meta($post_id, '_forum_likes', true);

    return is_array($likes) ? count($likes) : 0;
}

/**
 * ORDER BY likes meta, then comment_count, then date.
 */
function panterrea_forum_clauses_popular_sort($clauses, $query)
{
    if (empty($GLOBALS['panterrea_forum_popular_order'])) {
        return $clauses;
    }
    if (!$query instanceof WP_Query) {
        return $clauses;
    }
    global $wpdb;
    $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS panterrea_flc ON ({$wpdb->posts}.ID = panterrea_flc.post_id AND panterrea_flc.meta_key = '_forum_like_count') ";
    $clauses['orderby'] = 'COALESCE(CAST(panterrea_flc.meta_value AS UNSIGNED), 0) DESC, ' . $wpdb->posts . '.comment_count DESC, ' . $wpdb->posts . '.post_date DESC';

    return $clauses;
}
add_filter('posts_clauses', 'panterrea_forum_clauses_popular_sort', 10, 2);

/**
 * Ensure the 8 forum categories exist on every request.
 * Runs once per day via transient cache.
 */
function panterrea_ensure_forum_categories()
{
    if (get_transient('panterrea_forum_cats_created')) {
        return;
    }
    $names = [
        'ВРХ', 'Бізнес та партнерства', 'Ветеринарія', 'Відео',
        'Досвід', 'Знання та аналітика', 'Свинарство', 'Утримання ВРХ',
    ];
    foreach ($names as $name) {
        if (!get_cat_ID($name)) {
            wp_insert_term($name, 'category');
        }
    }
    set_transient('panterrea_forum_cats_created', 1, DAY_IN_SECONDS);
}
add_action('init', 'panterrea_ensure_forum_categories');

define('EMAIL_TEMPLATE_CONFIRMATION', __DIR__ . '/template-emails/confirmation-email.php');
define('EMAIL_TEMPLATE_RESETPASS', __DIR__ . '/template-emails/reset-password-email.php');
define('EMAIL_TEMPLATE_NOTIFICATION', __DIR__ . '/template-emails/notification-email.php');

define('BOOST_PRICE', 9000); // amount in cents

/**
 * API Keys - ВАЖЛИВО: Додайте свої реальні ключі!
 * Рекомендується винести ці константи у wp-config.php для безпеки
 */

// Stripe API Keys
if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_publishable_key_here');
}
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', 'sk_test_your_secret_key_here');
}

// AWS S3 Keys
if (!defined('S3_KEY')) {
    define('S3_KEY', 'your_aws_access_key_here');
}
if (!defined('S3_SECRET')) {
    define('S3_SECRET', 'your_aws_secret_key_here');
}
if (!defined('S3_BUCKET')) {
    define('S3_BUCKET', 'your_s3_bucket_name_here');
}

// Cloudflare Turnstile Keys
if (!defined('TURNSTILE_SITE_KEY')) {
    define('TURNSTILE_SITE_KEY', '0x4AAAAAACb0K9RtyT_sCD8L');
}
if (!defined('TURNSTILE_SECRET_KEY')) {
    define('TURNSTILE_SECRET_KEY', '0x4AAAAAACb0K_5MgmXTvAw_7imhXPEK3OY');
}

/**
 * Include
 */

require_once get_template_directory() . '/inc/formValidator.php';
require_once get_template_directory() . '/inc/CPT.php';
require_once get_template_directory() . '/inc/adminCustomView.php';
require_once get_template_directory() . '/inc/moderationPanel.php';
require_once get_template_directory() . '/inc/breadcrumbs.php';
require_once get_template_directory() . '/inc/cron.php';
require_once get_template_directory() . '/inc/stripeAPI/init.php';
require_once get_template_directory() . '/inc/awsSDK/aws-autoloader.php';

use Aws\S3\S3Client;

function panterrea_setup()
{
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('menus');
}

add_action('after_setup_theme', 'panterrea_setup');

function ptRegisterNavMenus()
{
    register_nav_menus([
        'header_menu' => __('Header Menu'),
        'footer_menu_columns' => __('Footer Columns'),
        'footer_menu_columns_en' => __('Footer Columns (EN)')
    ]);
}

add_action('after_setup_theme', 'ptRegisterNavMenus');

if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title' => 'Theme General Settings',
        'menu_title' => 'Theme Settings',
        'menu_slug' => 'theme-general-settings',
    ]);
}

/**
 * Set Language Cookie
 */
/*
function set_language_cookie() {
    if (!isset($_COOKIE['lang'])) {
        setcookie('lang', 'uk', time() + (10 * 365 * 24 * 60 * 60), '/');
        $_COOKIE['lang'] = 'uk';
    }

    if (!isset($_COOKIE['lang'])) {
        setcookie('lang', 'uk', time() + (10 * 365 * 24 * 60 * 60), '/');
        $_COOKIE['lang'] = 'uk';
    }

    global $currentLang;
    $currentLang = $_COOKIE['lang'] ?? 'uk';
}

add_action('init', 'set_language_cookie');
*/

add_action('init', function () {
    if (isset($_COOKIE['lang'])) {
        setcookie('lang', '', time() - 3600, '/');
        unset($_COOKIE['lang']);
    }
});

// Remove XML-RPC RSD and WLW manifest links from head to avoid 403/404 external links
add_action('init', function () {
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
});

/**
 * Set Global Variables
 */
function set_global_variables()
{
    global $actionTemplate;

    $actionTemplates = array('templates/login.php', 'templates/register.php', 'templates/forgot-password.php', 'templates/reset-password.php');
    $actionTemplate = in_array(get_page_template_slug(), $actionTemplates);

    global $whiteBg;

    $whiteBgTemplates = array('templates/user-profile.php', 'templates/user-favorites.php', 'templates/ad-create.php', 'templates/user-ad.php', 'templates/ad-edit.php', 'templates/ad-boost.php', 'templates/user-messages.php', 'templates/forum.php');
    $whiteBg = in_array(get_page_template_slug(), $whiteBgTemplates);

    if (is_singular('catalog_post')) {
        $post_id = get_the_ID();
        $author_id = (int)get_post_field('post_author', $post_id);

        if ($author_id === get_current_user_id()) {
            $whiteBg = true;
        }
    }
}

add_action('wp_head', 'set_global_variables');
// Колонка "Registered" (Дата регистрации) в Users list
add_filter('manage_users_columns', function($columns) {
  $columns['registered'] = 'Registered';
  return $columns;
});

add_filter('manage_users_custom_column', function($value, $column_name, $user_id) {
  if ($column_name === 'registered') {
    $user = get_userdata($user_id);
    if (!$user || empty($user->user_registered)) return '—';

    // формат: 15.01.2026 14:30 (по часовому поясу сайта)
    $timestamp = strtotime($user->user_registered . ' UTC');
    return wp_date('d.m.Y H:i', $timestamp);
  }
  return $value;
}, 10, 3);

// Сделать колонку сортируемой
add_filter('manage_users_sortable_columns', function($columns) {
  $columns['registered'] = 'registered';
  return $columns;
});

// Сортировка по user_registered
add_action('pre_get_users', function($query) {
  if (!is_admin()) return;
  $orderby = $query->get('orderby');
  if ($orderby === 'registered') {
    $query->set('orderby', 'registered');
    $query->set('order', $query->get('order') ?: 'DESC');
  }
});
/**
 * Language
 */
/*
function load_theme_language() {
    if (isset($_COOKIE['lang'])) {
        $currentLang = $_COOKIE['lang'];
    } else {
        $currentLang = 'uk';
    }

    load_textdomain('panterrea_v1', get_template_directory() . "/languages/panterrea-{$currentLang}.mo");
}
add_action('after_setup_theme', 'load_theme_language');
*/

/**
 * Detect current language from URL path
 * Returns 'uk' for Ukrainian pages, 'en' for English pages
 */
function panterrea_get_current_language() {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // Check if URL contains /uk/ or /en/ path segment at the start
    // This matches patterns like /uk/... or /en/... or /uk or /en
    if (preg_match('#^/(uk|en)(/|$|\?)#', $request_uri, $matches)) {
        return $matches[1];
    }
    
    // Also check if language appears anywhere in the path (fallback)
    if (preg_match('#/(uk|en)(/|$|\?)#', $request_uri, $matches)) {
        return $matches[1];
    }
    
    // Default to Ukrainian if no language detected
    return 'uk';
}

/**
 * Filter language attributes to set correct lang attribute based on URL
 */
function panterrea_filter_language_attributes($output, $doctype) {
    $current_lang = panterrea_get_current_language();
    
    // Map language codes to proper lang attribute values
    $lang_map = [
        'uk' => 'uk',
        'en' => 'en'
    ];
    
    $lang_code = isset($lang_map[$current_lang]) ? $lang_map[$current_lang] : 'uk';
    
    // Build new language attributes
    $attributes = array();
    
    // Check if RTL is needed (Ukrainian and English are both LTR, but keeping for future)
    if (function_exists('is_rtl') && is_rtl()) {
        $attributes[] = 'dir="rtl"';
    }
    
    // Add lang attribute
    if ('text/html' === get_option('html_type') || 'html' === $doctype) {
        $attributes[] = 'lang="' . esc_attr($lang_code) . '"';
    }
    
    if ('text/html' !== get_option('html_type') || 'xhtml' === $doctype) {
        $attributes[] = 'xml:lang="' . esc_attr($lang_code) . '"';
    }
    
    return implode(' ', $attributes);
}
add_filter('language_attributes', 'panterrea_filter_language_attributes', 10, 2);

/**
 * Disable SEO plugin canonical tags
 */
function panterrea_disable_seo_plugin_canonicals() {
    // Disable All in One SEO Pack canonical
    add_filter('aioseo_canonical_url', '__return_empty_string', 999);
    
    // Disable Rank Math canonical
    add_filter('rank_math/frontend/canonical', '__return_empty_string', 999);
    
    // Disable default WordPress canonical
    remove_action('wp_head', 'rel_canonical');
}
add_action('init', 'panterrea_disable_seo_plugin_canonicals', 999);

/**
 * Add self-referencing canonical tags for all URLs
 * 
 * Rules:
 * - /author-ad/ URLs: Keep author_id parameter, remove all other parameters
 * - /catalog/ URLs with category parameter: Convert to /catalog/{category-slug}/
 * - All other URLs: Remove all query parameters
 * - Properly handle TranslatePress language prefixes to avoid duplication
 */
function panterrea_add_canonical_tag() {
    // Get current URL
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // Parse URL
    $parsed_url = parse_url($current_url);
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '/';
    $query = isset($parsed_url['query']) ? $parsed_url['query'] : '';
    
    // Parse query string
    parse_str($query, $query_params);
    
    // Remove language prefix from path if it exists (to avoid duplication with home_url())
    // TranslatePress adds language prefix via home_url() filter, so we need to remove it from path first
    $path_without_lang = $path;
    if (preg_match('#^/(uk|en)(/.*)?$#', $path, $lang_matches)) {
        // Remove the language prefix from the path
        $path_without_lang = isset($lang_matches[2]) ? $lang_matches[2] : '/';
        // Ensure path starts with / if it's not empty
        if ($path_without_lang === '') {
            $path_without_lang = '/';
        }
    }
    
    // Build canonical URL using path without language prefix
    // home_url() will add the correct language prefix via TranslatePress filters
    $canonical_url = home_url($path_without_lang);
    
    // Handle /author-ad/ URLs - keep author_id parameter, remove all others
    if (preg_match('#^/author-ad/?$#', $path_without_lang)) {
        if (isset($query_params['author_id']) && !empty($query_params['author_id'])) {
            $canonical_url = add_query_arg('author_id', $query_params['author_id'], $canonical_url);
        }
    }
    // Handle /catalog/ and /catalog/{path}/ — remove query params for canonical
    elseif (preg_match('#^/catalog(/.*)?$#', $path_without_lang)) {
        $canonical_url = home_url($path_without_lang ? rtrim($path_without_lang, '/') . '/' : '/catalog/');
    }
    // For all other URLs, remove all query parameters
    // (canonical_url already set to path without query params above)
    
    // Ensure trailing slash for directory-like URLs (except root and files)
    // Skip if we've already set a canonical URL with trailing slash
    if ($path_without_lang !== '/' && !preg_match('/\.[a-zA-Z0-9]+$/', $path_without_lang)) {
        // Only add trailing slash if URL doesn't already have one
        if (substr($canonical_url, -1) !== '/') {
            $canonical_url = trailingslashit($canonical_url);
        }
    }
    
    // Output canonical tag
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";
}

// Add our custom canonical tag with high priority to ensure it runs first
add_action('wp_head', 'panterrea_add_canonical_tag', 1);

/**
 * SEO for catalog category (taxonomy) pages: title, meta description
 */
add_filter('document_title_parts', function($title) {
    if (is_tax('catalog_category')) {
        $term = get_queried_object();
        if ($term && !is_wp_error($term)) {
            $title['title'] = $term->name . ' | ' . __('Каталог', 'panterrea_v1') . ' — PanTerrea';
        }
    }
    return $title;
}, 10, 1);

add_action('wp_head', function() {
    if (is_tax('catalog_category')) {
        $term = get_queried_object();
        $name = $term && !is_wp_error($term) ? $term->name : '';
        $desc = $name ? sprintf(__('Оголошення в категорії %s — каталог PanTerrea для фермерів ВРХ. Товари, партнери та рішення.', 'panterrea_v1'), $name) : __('Каталог оголошень PanTerrea для фермерів ВРХ.', 'panterrea_v1');
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    }
}, 2);

/**
 * Remove noindex and nofollow from all pages
 */
function panterrea_remove_noindex_nofollow($robots) {
    // Remove noindex and nofollow directives
    unset($robots['noindex']);
    unset($robots['nofollow']);
    
    return $robots;
}
add_filter('wp_robots', 'panterrea_remove_noindex_nofollow', 999);

/**
 * Remove WordPress core robots filters that add noindex
 */
function panterrea_remove_core_robots_filters() {
    remove_filter('wp_robots', 'wp_robots_noindex');
    remove_filter('wp_robots', 'wp_robots_noindex_embeds');
    remove_filter('wp_robots', 'wp_robots_noindex_search');
}
add_action('init', 'panterrea_remove_core_robots_filters', 999);

/**
 * Remove X-Robots-Tag headers that contain noindex or nofollow
 */
function panterrea_remove_robots_headers() {
    // Remove any X-Robots-Tag headers that might be set
    if (!headers_sent()) {
        // Get all headers
        $headers = headers_list();
        
        // Remove X-Robots-Tag headers
        foreach ($headers as $header) {
            if (stripos($header, 'X-Robots-Tag') !== false) {
                // Try to remove it (this might not work if headers already sent)
                header_remove('X-Robots-Tag');
            }
        }
    }
}
add_action('template_redirect', 'panterrea_remove_robots_headers', 999);

/**
 * Remove robots meta tags that plugins output directly
 */
function panterrea_remove_robots_meta_output() {
    // Remove any direct output of robots meta tags
    ob_start(function($buffer) {
        // Remove meta robots tags containing noindex or nofollow
        $buffer = preg_replace('/<meta\s+name=["\']robots["\']\s+content=["\'][^"\']*(noindex|nofollow)[^"\']*["\']\s*\/?>/i',
'', $buffer);
return $buffer;
});
}
add_action('template_redirect', 'panterrea_remove_robots_meta_output', 1);

/**
 * Mark theme scripts as functional so Complianz never blocks them.
 * Required for catalog filters, price slider, and core functionality.
 * Priority 1 = run before Complianz and other plugins.
 */
add_filter('script_loader_tag', function ($tag, $handle, $src) {
    $functional_handles = ['main', 'filters', 'sliders', 'hero-swiper', 'slick-js'];
    if (in_array($handle, $functional_handles, true)) {
        return str_replace('<script ', '<script data-category="functional" ', $tag);
    }
    return $tag;
}, 1, 3);

/**
 * Whitelist catalog scripts in Complianz so they are never blocked.
 * Belt-and-suspenders alongside data-category="functional".
 */
add_filter('cmplz_whitelisted_script_tags', function ($tags) {
    $tags[] = 'filters-js';
    $tags[] = 'main-js';
    $tags[] = 'sliders-js';
    $tags[] = 'filters.js';
    $tags[] = 'main.js';
    return $tags;
}, 10, 1);

/**
* Register styles and scripts.
*/
add_action('wp_enqueue_scripts', function () {
// Google Fonts - Montserrat and Roboto with all weights
wp_enqueue_style('google-fonts',
'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap',
array(), null);

// Styles
$main_css_path = get_template_directory() . '/src/css/main.css';
$main_css_version = file_exists($main_css_path) ? filemtime($main_css_path) : '2025.07.16';
wp_enqueue_style('main', get_template_directory_uri() . '/src/css/main.css', array('google-fonts'), $main_css_version);

// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('cookie', get_template_directory_uri() . '/src/js/cookie.js', null, null, true);

/*
$lang = isset($_COOKIE['lang']) ? sanitize_text_field($_COOKIE['lang']) : 'uk';
$json_path = get_template_directory() . "/languages/json/translations_{$lang}.json";
if (file_exists($json_path)) {
$translations = file_get_contents($json_path);
} else {
$translations = json_encode([]);
}
*/
wp_enqueue_script('lang', get_template_directory_uri() . '/src/js/lang.js', null, '2025.07.08', true);
/*
wp_localize_script('lang', 'translations', json_decode($translations, true));
*/

wp_enqueue_script('message', get_template_directory_uri() . '/src/js/message.js', null, null, true);

$email_confirmed = is_user_logged_in() && get_user_meta(get_current_user_id(), 'email_verified', true);
$post_id = is_singular('catalog_post') ? get_the_ID() : null;

$main_js_path = get_template_directory() . '/src/js/main.js';
$main_js_version = file_exists($main_js_path) ? filemtime($main_js_path) : '2025.07.16';
wp_enqueue_script('main', get_template_directory_uri() . '/src/js/main.js', null, $main_js_version, true);
wp_localize_script('main', 'mainObject', [
'ajax_url' => admin_url('admin-ajax.php'),
'loggedIn' => is_user_logged_in() ? 'true' : 'false',
'emailConfirmed' => $email_confirmed ? 'true' : 'false',
'phone_nonce' => wp_create_nonce('show_phone_nonce'),
'statusAd_nonce' => wp_create_nonce('statusAd_nonce'),
'favorites_nonce' => wp_create_nonce('favorites_nonce'),
'deleteAd_nonce' => wp_create_nonce('deleteAd_nonce'),
'notification_nonce' => wp_create_nonce('notification_nonce'),
'loginURL' => URL_LOGIN,
'userAdURL' => URL_USERAD,
'postID' => $post_id
]);
wp_enqueue_script('validator', get_template_directory_uri() . '/src/js/validator.js', null, null, true);
wp_localize_script('validator', 'securityObject', [
'register_nonce' => wp_create_nonce('register_user_nonce'),
'resend_nonce' => wp_create_nonce('resend_email_nonce'),
'login_nonce' => wp_create_nonce('login_user_nonce'),
'forgot_nonce' => wp_create_nonce('forgot_password_nonce'),
'reset_nonce' => wp_create_nonce('reset_password_nonce'),
'edit_nonce' => wp_create_nonce('edit_profile_nonce'),
'change_nonce' => wp_create_nonce('change_password_nonce'),
'adCreate_nonce' => wp_create_nonce('adCreate_nonce'),
'adEdit_nonce' => wp_create_nonce('adEdit_nonce'),
'contact_nonce' => wp_create_nonce('contact_nonce'),
]);

$current_category = get_queried_object();
$current_category_slug = '';

if (!empty($_GET['category'])) {
$current_category_slug = sanitize_text_field($_GET['category']);
} elseif ($current_category && isset($current_category->taxonomy) && $current_category->taxonomy === 'catalog_category')
{
$current_category_slug = $current_category->slug;
}

$filters_ver = file_exists(get_template_directory() . '/src/js/filters.js')
    ? filemtime(get_template_directory() . '/src/js/filters.js') : null;
wp_enqueue_script('filters', get_template_directory_uri() . '/src/js/filters.js', array('main'), $filters_ver, true);
$catalog_base_url = defined('URL_CATALOG') ? URL_CATALOG : home_url('/catalog');
wp_localize_script('filters', 'filtersObject', [
'filters_nonce' => wp_create_nonce('filters_nonce'),
'currentCategory' => $current_category_slug,
'catalogBaseUrl' => $catalog_base_url,
]);

wp_enqueue_script('adCreate', get_template_directory_uri() . '/src/js/adCreate.js', null, '2025.07.14', true);
wp_localize_script('adCreate', 'adCreateObject', [
'getSubcategories_nonce' => wp_create_nonce('getSubcategories_nonce')
]);

wp_enqueue_script('tabs', get_template_directory_uri() . '/src/js/tabs.js', null, null, true);

wp_enqueue_script('search', get_template_directory_uri() . '/src/js/search.js', null, null, true);
wp_localize_script('search', 'searchObject', [
'search_nonce' => wp_create_nonce('search_nonce')
]);

$sliderTemplates = array('templates/homepage.php', 'templates/ad-create.php', 'templates/ad-edit.php',
'templates/forum.php');
if (is_singular('catalog_post') || is_page_template($sliderTemplates)) {
wp_enqueue_style('slick-css', get_template_directory_uri() . '/inc/slick/slick.css');
wp_enqueue_style('slick-theme-css', get_template_directory_uri() . '/inc/slick/slick-theme.css');

wp_enqueue_script('slick-js', get_template_directory_uri() . '/inc/slick/slick.min.js', array('jquery'), null, true);
wp_enqueue_script('sliders', get_template_directory_uri() . '/src/js/sliders.js', null, null, true);
}

// Swiper.js for hero slider
if (is_page_template('templates/homepage.php')) {
wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0');
wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);
wp_enqueue_script('hero-swiper', get_template_directory_uri() . '/src/js/hero-swiper.js', array('swiper-js'), '1.0.0',
true);
wp_enqueue_script('how-work', get_template_directory_uri() . '/src/js/how-work.js', array(), '1.0.0', true);
}

// Swiper.js for related posts on single post pages
if (is_single()) {
wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0');
wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);
}

if (is_singular('catalog_post') || is_page_template('templates/user-messages.php')) {
wp_enqueue_script('chat', get_template_directory_uri() . '/src/js/chat.js', null, null, true);
wp_localize_script('chat', 'chatObject', [
'chat_nonce' => wp_create_nonce('chat_nonce')
]);
}

$stripeTemplates = array('templates/ad-boost.php', 'templates/ad-create.php');
if (is_page_template($stripeTemplates)) {
wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', null, null, true);
wp_enqueue_script('adBoost', get_template_directory_uri() . '/src/js/adBoost.js', null, null, true);
wp_localize_script('adBoost', 'adBoostObject', [
'stripe_publishable_key' => STRIPE_PUBLISHABLE_KEY,
'boost_nonce' => wp_create_nonce('boost_nonce')
]);
}

$maskTemplates = array('templates/register.php', 'templates/user-profile.php', 'templates/about-us.php',
'templates/homepage.php');
if (is_page_template($maskTemplates) || is_front_page()) {
wp_enqueue_script('imask-js', get_template_directory_uri() . '/inc/imask/imask.min.js', null, null, true);
wp_enqueue_script('masks', get_template_directory_uri() . '/src/js/masks.js', null, null, true);
}

$forumTemplates = array('templates/forum.php');
if (is_page_template($forumTemplates)) {
wp_enqueue_style('quill-css', 'https://cdn.quilljs.com/1.3.6/quill.snow.css');
wp_enqueue_script('quill-js', 'https://cdn.quilljs.com/1.3.6/quill.min.js', array(), null, true);

    $forum_js_path = get_template_directory() . '/src/js/forum.js';
    $forum_js_version = file_exists($forum_js_path) ? filemtime($forum_js_path) : '2026.04.09';
    wp_enqueue_script('forum', get_template_directory_uri() . '/src/js/forum.js', null, $forum_js_version, true);
wp_localize_script('forum', 'forumObject', [
'forum_nonce' => wp_create_nonce('forum_nonce'),
'str_show_all' => __('Показати всі', 'panterrea_v1'),
'str_hide' => __('Сховати', 'panterrea_v1'),
'str_show_all_comments' => __('Показати всі', 'panterrea_v1'),
'str_hide_comments' => __('Сховати', 'panterrea_v1'),
]);
}
});

function custom_mailer_settings($phpmailer)
{
$phpmailer->isSMTP();
$phpmailer->Host = SMTP_HOST;
$phpmailer->SMTPAuth = false;
$phpmailer->SMTPSecure = SMTP_SECURE;
$phpmailer->Port = SMTP_PORT;
$phpmailer->From = SMTP_FROM_EMAIL;
$phpmailer->FromName = SMTP_FROM_NAME;

$phpmailer->Timeout = 10;

/*$phpmailer->SMTPDebug = 2;
$phpmailer->Debugoutput = function ($str, $level) {
if (defined('WP_DEBUG') && WP_DEBUG) {
error_log("SMTP Debug [$level]: $str");
}
};*/
}

add_action('phpmailer_init', 'custom_mailer_settings');

/**
* Cloudflare Turnstile Verification
*/

function verify_turnstile_token($token) {
    if (empty($token)) {
        return false;
    }
    
    $secret_key = TURNSTILE_SECRET_KEY;
    $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    
    $data = [
        'secret' => $secret_key,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'body' => $data,
        'timeout' => 10
    ];
    
    $response = wp_remote_post($verify_url, $options);
    
    if (is_wp_error($response)) {
        error_log('Turnstile verification error: ' . $response->get_error_message());
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    return isset($result['success']) && $result['success'] === true;
}

/**
* Register
*/

add_action('wp_ajax_register_form', 'registration_user');
add_action('wp_ajax_nopriv_register_form', 'registration_user');

/**
* @throws Exception
*/
function registration_user()
{
$security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
$formDataRaw = sanitize_text_field(filter_input(INPUT_POST, 'formData'));
$turnstile_token = sanitize_text_field(filter_input(INPUT_POST, 'turnstile_token'));

if (!$security || !wp_verify_nonce($security, 'register_user_nonce')) {
wp_send_json_error(['message' => 'Invalid request (nonce).']);
}

// Перевірка Cloudflare Turnstile
if (!verify_turnstile_token($turnstile_token)) {
wp_send_json_error([
'message' => 'Turnstile verification failed.',
'errors' => ['turnstile' => __('Будь ласка, підтвердіть, що ви не робот.', 'panterrea_v1')]
]);
}

if (!$formDataRaw) {
wp_send_json_error(['message' => 'No data are available.']);
}

$formData = json_decode(stripslashes($formDataRaw), true);

if (json_last_error() !== JSON_ERROR_NONE) {
error_log('Error JSON: ' . json_last_error_msg());
wp_send_json_error(['message' => 'Incorrect data.']);
}

$requiredFields = ['name', 'city', 'email', 'phone', 'password'];
foreach ($requiredFields as $field) {
if (!array_key_exists($field, $formData)) {
wp_send_json_error(['message' => "The {$field} field is required."]);
}
}

$validator = new FormValidator();

$validator->addRules('name', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isAlpha']]);
/*$validator->addRules('surname', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isAlpha']]);*/
$validator->addRules('city', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isAlpha']]);
$validator->addRules('email', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isEmail']]);
/*$validator->addRules('phone', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isPhone']]);*/
$validator->addRules('profession', [FormValidator::rules()['isOptionalAlpha']]);
$validator->addRules('password', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['minLength'](8),
FormValidator::rules()['isStrongPassword']]);

$validationResult = $validator->validate($formData);

if (!$validationResult['success']) {
wp_send_json_error($validationResult);
}

$formData = sanitize_form_data($formData);

if (email_exists($formData['email'])) {
wp_send_json_error([
'message' => 'A user with this email already exists.',
'errors' => ['email' => __('Цей email вже зареєстрований.', 'panterrea_v1')]
]);
}

global $wpdb;
$existing_user = $wpdb->get_var(
$wpdb->prepare(
"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'phone' AND meta_value = %s",
$formData['phone']
)
);

if ($existing_user) {
wp_send_json_error([
'message' => 'A user with this phone already exists.',
'errors' => ['phone' => __('Цей номер телефону вже зареєстрований.', 'panterrea_v1')]
]);
}

try {
$user_id = wp_create_user($formData['email'], $formData['password'], $formData['email']);
if (is_wp_error($user_id)) {
throw new Exception($user_id->get_error_message());
}

update_user_meta($user_id, 'name', $formData['name']);
/*update_user_meta($user_id, 'surname', $formData['surname']);*/
update_user_meta($user_id, 'city', $formData['city']);
update_user_meta($user_id, 'phone', $formData['phone']);
update_user_meta($user_id, 'profession', $formData['profession']);

$utms = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
foreach ($utms as $utm) {
    $cookie_key = 'panterrea_' . $utm;
    if (isset($_COOKIE[$cookie_key])) {
        update_user_meta($user_id, 'reg_' . $utm, sanitize_text_field($_COOKIE[$cookie_key]));
    }
}

$notification_types = [
'password_reset',
'create_ad',
'delete_ad',
'boost_ad',
'new_message',
'boost_expiring',
'boost_expired'
];
foreach ($notification_types as $type) {
update_user_meta($user_id, 'email_notify_' . $type, '1');
}

send_confirmation_email($user_id);

add_notification($user_id, 'user_registered', __('Вітаємо! Ви успішно зареєструвались на платформі PanTerrea.',
'panterrea_v1'));

wp_send_json_success(['message' => 'User successfully registered!']);
} catch (Exception $e) {
error_log('User registration error: ' . $e->getMessage());
wp_send_json_error(['message' => 'Error creating a user.']);
}
}

function sanitize_form_data(array $formData): array
{
$sanitizedData = [];

foreach ($formData as $key => $value) {
$value = trim($value);

if ($key === 'email') {
$sanitizedData[$key] = sanitize_email($value);
} else {
$sanitizedData[$key] = sanitize_text_field($value);
}
}

return $sanitizedData;
}

/**
* @throws Exception
*/
function send_confirmation_email($user_id)
{
try {
$user = get_user_by('id', $user_id);

if (!$user) {
throw new Exception('User not found.');
}

$email = $user->user_email;

if (!is_email($email)) {
throw new Exception('Invalid email.');
}

$token = hash_hmac('sha256', random_bytes(16), wp_salt('nonce'));

update_user_meta($user_id, 'email_confirmation_token', $token);

$confirmation_url = add_query_arg([
'action' => 'confirm_email',
'token' => $token,
'user_id' => $user_id
], site_url());

$message = render_template(EMAIL_TEMPLATE_CONFIRMATION, [
'display_name' => get_user_meta($user_id, 'name', true),
'confirmation_url' => $confirmation_url,
]);

$subject = __('Panterrea: Підтвердження вашої електронної пошти', 'panterrea_v1');
$headers = [
'Content-Type: text/html; charset=UTF-8',
'From: <customer@panterrea.com>'
    ];

    if (!wp_mail($email, $subject, $message, $headers)) {
    throw new Exception('Unable to send a confirmation email.');
    }
    } catch (Exception $e) {
    error_log('Error sending a confirmation email: ' . $e->getMessage());
    }
    }

    function confirm_email()
    {
    $action = sanitize_text_field(filter_input(INPUT_GET, 'action'));
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
    $token = sanitize_text_field(filter_input(INPUT_GET, 'token'));

    if (isset($action) && $action === 'confirm_email') {

    $user = get_user_by('id', $user_id);
    if (!$user) {
    setMessageCookies('error', __('Користувач не знайдений.', 'panterrea_v1'));
    wp_redirect(site_url());
    exit;
    }

    if (get_user_meta($user_id, 'email_verified', true)) {
    setMessageCookies('success', __('Email успішно підтверджено.', 'panterrea_v1'));
    wp_redirect(site_url());
    exit;
    }

    $saved_token = get_user_meta($user_id, 'email_confirmation_token', true);

    if (!$saved_token || $saved_token !== $token) {
    error_log("Email confirmation failed for user ID {$user_id}. Token mismatch or missing.");
    setMessageCookies('error', __('Ключ відсутній або не дійсний.', 'panterrea_v1'));
    wp_redirect(site_url());
    exit;
    }

    update_user_meta($user_id, 'email_verified', true);
    delete_user_meta($user_id, 'email_confirmation_token');

    add_notification($user_id, 'email_verified', __('Email успішно підтверджено.', 'panterrea_v1'));

    setMessageCookies('success', __('Email успішно підтверджено.', 'panterrea_v1'));
    wp_redirect(site_url());
    exit;
    }
    }

    add_action('init', 'confirm_email');

    /**
    * @throws Exception
    */
    function render_template($template_path, $variables = []): bool|string
    {
    try {
    if (!file_exists($template_path)) {
    throw new Exception('Template not found: ' . $template_path);
    }

    extract($variables);

    ob_start();
    include $template_path;
    return ob_get_clean();
    } catch (Exception $e) {
    error_log('Error rendering template: ' . $e->getMessage());
    return false;
    }
    }

    add_action('wp_ajax_resend_confirmation_email', 'resend_confirmation_email');
    add_action('wp_ajax_nopriv_resend_confirmation_email', 'resend_confirmation_email');

    /**
    * @throws Exception
    */
    function resend_confirmation_email()
    {
    $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
    if (!isset($security) || !wp_verify_nonce($security, 'resend_email_nonce')) {
    wp_send_json_error(['message' => 'Invalid nonce.']);
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!$email || !is_email($email)) {
    wp_send_json_error(['message' => 'Invalid email.']);
    }

    $user = get_user_by('email', $email);

    if ($user) {

    $last_resend_time = get_user_meta($user->ID, 'last_resend_time', true);
    if ($last_resend_time && time() - $last_resend_time < 60) { wp_send_json_error(['message'=> 'Please try again in a
        few seconds.']);
        }
        update_user_meta($user->ID, 'last_resend_time', time());

        try {
        send_confirmation_email($user->ID);
        wp_send_json_success(['message' => 'The email was sent successfully.']);
        } catch (Exception $e) {
        wp_send_json_error(['message' => 'The email could not be sent. Please try again.']);
        }
        } else {
        wp_send_json_error(['message' => 'No user with this email was found.']);
        }
        }

        /**
        * Login
        */

        add_action('wp_ajax_login_user', 'user_login');
        add_action('wp_ajax_nopriv_login_user', 'user_login');

        function user_login()
        {
        $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
        $formDataRaw = sanitize_text_field(filter_input(INPUT_POST, 'formData'));

        if (!$security || !wp_verify_nonce($security, 'login_user_nonce')) {
        wp_send_json_error(['message' => 'Invalid request (nonce).']);
        }

        if (!$formDataRaw) {
        wp_send_json_error(['message' => 'No data are available.']);
        }

        $formData = json_decode(stripslashes($formDataRaw), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Error JSON: ' . json_last_error_msg());
        wp_send_json_error(['message' => 'Incorrect data.']);
        }

        $requiredFields = ['email', 'password'];
        foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $formData)) {
        wp_send_json_error(['message' => "The {$field} field is required."]);
        }
        }

        $validator = new FormValidator();

        $validator->addRules('email', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isEmail']]);
        $validator->addRules('password', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['minLength'](8),
        FormValidator::rules()['isStrongPassword']]);

        $validationResult = $validator->validate($formData);

        if (!$validationResult['success']) {
        wp_send_json_error($validationResult);
        }

        $email = $formData['email'];
        $password = $formData['password'];

        $user = get_user_by('email', $email);
        if (!$user) {
        wp_send_json_error([
        'message' => 'A user with this email does not exist.',
        'errors' => ['email' => __('Цей email не зареєстрований.', 'panterrea_v1')]
        ]);
        }

        $auth = wp_authenticate($email, $password);
        if (is_wp_error($auth)) {
        wp_send_json_error([
        'message' => 'Incorrect password.',
        'errors' => ['password' => __('Невірний пароль.', 'panterrea_v1')]
        ]);
        }

        /*if (!get_user_meta($auth->ID, 'email_verified', true)) {
        wp_send_json_error([
        'message' => 'Your email address has not been verified.',
        'errors' => ['email' => 'Email не підтверджений.']
        ]);
        }*/

        wp_set_current_user($auth->ID);
        wp_set_auth_cookie($auth->ID, true, is_ssl());

        $redirect_url = home_url();
        if (!empty($formData['redirect_to'])) {
            $requested = esc_url_raw($formData['redirect_to']);
            $home = home_url();
            if ($requested && strpos($requested, $home) === 0 && strpos($requested, 'wp-admin') === false && strpos($requested, 'wp-login') === false) {
                $redirect_url = $requested;
            }
        }
        wp_send_json_success([
        'message' => 'Login successful.',
        'redirect_url' => $redirect_url
        ]);
        }

        /**
        * Social Login - Initialize OAuth flow
        */
        add_action('wp_ajax_social_login_init', 'social_login_init');
        add_action('wp_ajax_nopriv_social_login_init', 'social_login_init');

        function social_login_init()
        {
        $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
        $provider = sanitize_text_field(filter_input(INPUT_POST, 'provider'));

        if (!$security || !wp_verify_nonce($security, 'login_user_nonce')) {
        wp_send_json_error(['message' => 'Invalid request (nonce).']);
        }

        if (!in_array($provider, ['google', 'facebook'])) {
        wp_send_json_error(['message' => 'Invalid provider.']);
        }

        // Option 1: Use Nextend Social Login plugin if installed
        if (class_exists('NextendSocialLogin')) {
        $auth_url = NextendSocialLogin::getLoginUrl($provider);
        if ($auth_url) {
        wp_send_json_success([
        'message' => 'Redirecting to ' . $provider . '...',
        'auth_url' => $auth_url
        ]);
        return;
        }
        }

        // Option 2: Custom OAuth implementation
        // You need to configure these in wp-config.php or theme options
        $client_id = '';
        $redirect_uri = home_url('/wp-admin/admin-ajax.php?action=social_login_callback&provider=' . $provider);

        if ($provider === 'google') {
        $client_id = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
        if (empty($client_id)) {
        wp_send_json_error(['message' => 'Google OAuth не настроен. Установите плагин Nextend Social Login или настройте
        GOOGLE_CLIENT_ID.']);
        return;
        }

        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
        ]);
        } elseif ($provider === 'facebook') {
        $client_id = defined('FACEBOOK_APP_ID') ? FACEBOOK_APP_ID : '';
        if (empty($client_id)) {
        wp_send_json_error(['message' => 'Facebook OAuth не настроен. Установите плагин Nextend Social Login или
        настройте FACEBOOK_APP_ID.']);
        return;
        }

        $auth_url = 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'email,public_profile'
        ]);
        }

        wp_send_json_success([
        'message' => 'Redirecting to ' . $provider . '...',
        'auth_url' => $auth_url
        ]);
        }

        /**
        * Social Login - OAuth Callback
        */
        add_action('wp_ajax_social_login_callback', 'social_login_callback');
        add_action('wp_ajax_nopriv_social_login_callback', 'social_login_callback');

        function social_login_callback()
        {
        $provider = sanitize_text_field(filter_input(INPUT_GET, 'provider'));
        $code = sanitize_text_field(filter_input(INPUT_GET, 'code'));
        $error = sanitize_text_field(filter_input(INPUT_GET, 'error'));

        if ($error) {
        wp_redirect(home_url('/login/?error=' . urlencode('Ошибка авторизации: ' . $error)));
        exit;
        }

        if (!$code || !in_array($provider, ['google', 'facebook'])) {
        wp_redirect(home_url('/login/?error=' . urlencode('Неверный запрос')));
        exit;
        }

        // This is a simplified example - you'll need to implement token exchange
        // For production, use Nextend Social Login plugin or implement full OAuth flow

        wp_redirect(home_url('/login/?error=' . urlencode('Для полноценной работы установите плагин Nextend Social
        Login')));
        exit;
        }

        /**
        * Override redirect URL for social login (Google & Facebook)
        * Redirects to home page instead of /wp-admin
        */
        add_filter('nsl_google_login_redirect_url', function($redirect_url, $provider) {
        return home_url();
        }, 10, 2);

        add_filter('nsl_facebook_login_redirect_url', function($redirect_url, $provider) {
        return home_url();
        }, 10, 2);

        add_filter('nsl_google_default_login_redirect_url', function($redirect_url, $provider) {
        return home_url();
        }, 10, 2);

        add_filter('nsl_facebook_default_login_redirect_url', function($redirect_url, $provider) {
        return home_url();
        }, 10, 2);

        add_filter('nsl_googledefault_last_location_redirect', function($redirect_to, $requested_redirect_to) {
        // Если редирект ведет на /wp-admin, /wp-login.php или это пусто, редиректим на главную
        if (empty($redirect_to) ||
        strpos($redirect_to, '/wp-admin') !== false ||
        strpos($redirect_to, '/wp-login.php') !== false ||
        strpos($redirect_to, 'wp-login.php') !== false) {
        return home_url();
        }
        return $redirect_to;
        }, 10, 2);

        add_filter('nsl_facebookdefault_last_location_redirect', function($redirect_to, $requested_redirect_to) {
        // Если редирект ведет на /wp-admin, /wp-login.php или это пусто, редиректим на главную
        if (empty($redirect_to) ||
        strpos($redirect_to, '/wp-admin') !== false ||
        strpos($redirect_to, '/wp-login.php') !== false ||
        strpos($redirect_to, 'wp-login.php') !== false) {
        return home_url();
        }
        return $redirect_to;
        }, 10, 2);

        add_filter('nsl_google_last_location_redirect', function($redirect_to, $requested_redirect_to) {
        // Если редирект ведет на /wp-admin, /wp-login.php или это пусто, редиректим на главную
        if (empty($redirect_to) ||
        strpos($redirect_to, '/wp-admin') !== false ||
        strpos($redirect_to, '/wp-login.php') !== false ||
        strpos($redirect_to, 'wp-login.php') !== false) {
        return home_url();
        }
        return $redirect_to;
        }, 10, 2);

        add_filter('nsl_facebook_last_location_redirect', function($redirect_to, $requested_redirect_to) {
        // Если редирект ведет на /wp-admin, /wp-login.php или это пусто, редиректим на главную
        if (empty($redirect_to) ||
        strpos($redirect_to, '/wp-admin') !== false ||
        strpos($redirect_to, '/wp-login.php') !== false ||
        strpos($redirect_to, 'wp-login.php') !== false) {
        return home_url();
        }
        return $redirect_to;
        }, 10, 2);

        /**
        * Override WordPress login_redirect filter to prevent redirect to /wp-admin or /wp-login.php
        * This is a fallback filter that catches any login redirects
        */
        add_filter('login_redirect', function($redirect_to, $requested_redirect_to, $user) {
        // Если редирект ведет на /wp-admin, /wp-login.php или admin_url, перенаправляем на главную
        if (empty($redirect_to) ||
        strpos($redirect_to, '/wp-admin') !== false ||
        strpos($redirect_to, '/wp-login.php') !== false ||
        strpos($redirect_to, 'wp-login.php') !== false ||
        $redirect_to === admin_url()) {
        return home_url();
        }
        return $redirect_to;
        }, 10, 3);

        /**
        * Hook into NSL before login to set redirect URL
        * This runs before the redirect is determined
        */
        add_action('nsl_before_wp_login', function() {
        // Устанавливаем редирект на главную страницу через GET параметр
        // Это будет использовано плагином при определении редиректа
        if (!isset($_GET['redirect'])) {
        $_GET['redirect'] = home_url();
        }
        });

        /**
        * Hook into NSL login action to override redirect
        */
        add_action('nsl_login', function($user_id, $provider) {
        // Перехватываем редирект после успешного логина через социальные сети
        add_filter('nsl_' . $provider->getId() . 'last_location_redirect', function($redirect_to,
        $requested_redirect_to) {
        // Если редирект ведет на /wp-admin или /wp-login.php, редиректим на главную
        if (empty($redirect_to) ||
        strpos($redirect_to, '/wp-admin') !== false ||
        strpos($redirect_to, '/wp-login.php') !== false ||
        strpos($redirect_to, 'wp-login.php') !== false) {
        return home_url();
        }
        return $redirect_to;
        }, 999, 2);
        }, 10, 2);

        /**
        * Override wp_safe_redirect_fallback to prevent redirect to /wp-admin
        * This filter is used by validateRedirect() in Nextend Social Login
        */
        add_filter('wp_safe_redirect_fallback', function($fallback_url, $status) {
        // Если fallback ведет на /wp-admin, используем главную страницу
        if (strpos($fallback_url, '/wp-admin') !== false || $fallback_url === admin_url()) {
        return home_url();
        }
        return $fallback_url;
        }, 10, 2);

        /**
        * Intercept wp_redirect to prevent redirects to /wp-login.php after social login
        * This is a last resort filter that catches any redirects
        */
        add_filter('wp_redirect', function($location, $status) {
        // Проверяем, что это редирект после социального логина
        // Если редирект ведет на /wp-login.php и пользователь залогинен, заменяем на главную
        if ((strpos($location, '/wp-login.php') !== false || strpos($location, 'wp-login.php') !== false) &&
        is_user_logged_in()) {
        // Это редирект после успешного логина, перенаправляем на главную
        return home_url();
        }
        // Если редирект на /wp-admin и пользователь не в админке, также перенаправляем на главную
        if (strpos($location, '/wp-admin') !== false && !is_admin() && is_user_logged_in()) {
        return home_url();
        }
        return $location;
        }, 999, 2);

        /**
        * Forgot Password
        */

        add_action('wp_ajax_forgot_pass', 'forgot_password');
        add_action('wp_ajax_nopriv_forgot_pass', 'forgot_password');

        function forgot_password()
        {
        $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));

        if (!$security || !wp_verify_nonce($security, 'forgot_password_nonce')) {
        wp_send_json_error(['message' => 'Invalid request (nonce).']);
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        if (!$email || !is_email($email)) {
        wp_send_json_error(['message' => 'Invalid email.']);
        }

        $user = get_user_by('email', $email);
        if (!$user) {
        wp_send_json_error([
        'message' => 'A user with this email does not exist.',
        'errors' => ['email' => __('Цей email не зареєстрований.', 'panterrea_v1')]
        ]);
        }

        try {
        send_forgot_email($user->ID, $email);
        wp_send_json_success(['message' => 'An email has been sent with password reset instructions.']);
        } catch (Exception $e) {
        error_log('Error when resetting password: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Error when resetting password.']);
        }

        }

        /**
        * @throws Exception
        */
        function send_forgot_email($user_id, $email)
        {
        try {
        $user = get_user_by('id', $user_id);

        if (!$user) {
        throw new Exception('User not found.');
        }

        if (!is_email($email)) {
        throw new Exception('Invalid email.');
        }

        $reset_token = wp_generate_password(20, false);
        update_user_meta($user_id, 'password_reset_token', $reset_token);
        update_user_meta($user_id, 'password_reset_token_expiration', time() + HOUR_IN_SECONDS);

        $base_url = site_url() . '/reset-password';
        $reset_url = add_query_arg([
        'action' => 'reset_password',
        'token' => $reset_token,
        'user_id' => $user_id
        ], $base_url);

        $message = render_template(EMAIL_TEMPLATE_RESETPASS, [
        'display_name' => get_user_meta($user_id, 'name', true),
        'reset_url' => $reset_url,
        ]);

        $subject = __('Panterrea: Скидання паролю', 'panterrea_v1');
        $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Panterrea <noreply@panterrea.com>'
            ];

            if (!wp_mail($email, $subject, $message, $headers)) {
            throw new Exception('Unable to send a forgot password email.');
            }
            } catch (Exception $e) {
            error_log('Error sending a forgot password email: ' . $e->getMessage());
            }
            }

            /**
            * Reset Password
            */

            add_action('wp_ajax_reset_password', 'reset_user_password');
            add_action('wp_ajax_nopriv_reset_password', 'reset_user_password');

            function reset_user_password()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            $formDataRaw = sanitize_text_field(filter_input(INPUT_POST, 'formData'));

            if (!$security || !wp_verify_nonce($security, 'reset_password_nonce')) {
            wp_send_json_error(['message' => 'Invalid request (nonce).']);
            }

            if (!$formDataRaw) {
            wp_send_json_error(['message' => 'No data are available.']);
            }

            $formData = json_decode(stripslashes($formDataRaw), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            wp_send_json_error(['message' => 'Invalid form data.']);
            }

            $requiredFields = ['password', 'confirmPassword', 'user_id', 'token'];
            foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
            wp_send_json_error(['message' => "The {$field} field is required."]);
            }
            }

            $validator = new FormValidator();

            $validator->addRules('password', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['minLength'](8), FormValidator::rules()['isStrongPassword']]);

            $validationResult = $validator->validate($formData);

            if (!$validationResult['success']) {
            wp_send_json_error($validationResult);
            }

            $formData = sanitize_form_data($formData);

            if ($formData['password'] !== $formData['confirmPassword']) {
            wp_send_json_error([
            'message' => 'A user with this email does not exist.',
            'errors' => ['confirmPassword' => __('Паролі не збігаються.', 'panterrea_v1')]
            ]);
            }

            $user_id = (int)$formData['user_id'];
            $token = sanitize_text_field($formData['token']);
            $stored_token = get_user_meta($user_id, 'password_reset_token', true);
            $token_expiration = get_user_meta($user_id, 'password_reset_token_expiration', true);

            if (!$stored_token || $stored_token !== $token) {
            wp_send_json_error(['message' => 'Invalid reset token.']);
            }

            if (!$token_expiration || time() > $token_expiration) {
            delete_user_meta($user_id, 'password_reset_token');
            delete_user_meta($user_id, 'password_reset_token_expiration');
            wp_send_json_error(['message' => 'The reset token has expired.']);
            }

            $new_password = $formData['password'];
            wp_set_password($new_password, $user_id);

            delete_user_meta($user_id, 'password_reset_token');
            delete_user_meta($user_id, 'password_reset_token_expiration');

            add_notification($user_id, 'password_reset', __('Пароль успішно відновлено.', 'panterrea_v1'));

            $redirect_url = home_url();
            wp_send_json_success([
            'message' => 'Password successfully reset.',
            'redirect_url' => $redirect_url
            ]);
            }

            /**
            * Edit Profile
            */

            add_action('wp_ajax_edit_profile', 'edit_user_profile');

            /**
            * @throws Exception
            */
            function edit_user_profile()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            $formDataRaw = sanitize_text_field(filter_input(INPUT_POST, 'formData'));

            if (!$security || !wp_verify_nonce($security, 'edit_profile_nonce')) {
            wp_send_json_error(['message' => 'Invalid request (nonce).']);
            }

            if (!$formDataRaw) {
            wp_send_json_error(['message' => 'No data are available.']);
            }

            $formData = json_decode(stripslashes($formDataRaw), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Error JSON: ' . json_last_error_msg());
            wp_send_json_error(['message' => 'Incorrect data.']);
            }

            $requiredFields = ['name', 'city', 'phone', 'user_id'];
            foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $formData)) {
            wp_send_json_error(['message' => "The {$field} field is required."]);
            }
            }

            $validator = new FormValidator();

            $validator->addRules('name', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isAlpha']]);
            $validator->addRules('city', [FormValidator::rules()['isNotEmpty'], FormValidator::rules()['isAlpha']]);
            $validator->addRules('profession', [FormValidator::rules()['isOptionalAlpha']]);
            /*$validator->addRules('phone', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['isPhone']]);*/

            $validationResult = $validator->validate($formData);

            if (!$validationResult['success']) {
            wp_send_json_error($validationResult);
            }

            $formData = sanitize_form_data($formData);

            $user_id = (int)$formData['user_id'];
            $user = get_user_by('id', $user_id);
            if (!$user) {
            wp_send_json_error(['message' => 'Incorrect user ID.']);
            }

            try {
            update_user_meta($user_id, 'name', $formData['name']);
            update_user_meta($user_id, 'city', $formData['city']);
            update_user_meta($user_id, 'phone', $formData['phone']);
            update_user_meta($user_id, 'profession', $formData['profession']);

            wp_send_json_success(['message' => 'User data successfully edit!']);
            } catch (Exception $e) {
            error_log('User data edit error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Error edit a user data.']);
            }
            }

            /**
            * Change Password
            */

            add_action('wp_ajax_change_password', 'change_user_password');

            function change_user_password()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            $formDataRaw = sanitize_text_field(filter_input(INPUT_POST, 'formData'));

            if (!$security || !wp_verify_nonce($security, 'change_password_nonce')) {
            wp_send_json_error(['message' => 'Invalid request (nonce).']);
            }

            if (!$formDataRaw) {
            wp_send_json_error(['message' => 'No data are available.']);
            }

            $formData = json_decode(stripslashes($formDataRaw), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            wp_send_json_error(['message' => 'Invalid form data.']);
            }

            $requiredFields = ['password', 'confirmPassword', 'oldPassword', 'user_id'];
            foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
            wp_send_json_error(['message' => "The {$field} field is required."]);
            }
            }

            $validator = new FormValidator();

            $validator->addRules('password', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['minLength'](8), FormValidator::rules()['isStrongPassword']]);

            $validationResult = $validator->validate($formData);

            if (!$validationResult['success']) {
            wp_send_json_error($validationResult);
            }

            $formData = sanitize_form_data($formData);

            $user_id = (int)$formData['user_id'];
            $user = get_user_by('id', $user_id);
            if (!$user) {
            wp_send_json_error(['message' => 'Incorrect user ID.']);
            }

            $old_password = $formData['oldPassword'];
            if (!wp_check_password($old_password, $user->user_pass, $user_id)) {
            wp_send_json_error([
            'message' => 'The current password is incorrect.',
            'errors' => ['oldPassword' => __('Некоректний існуючий пароль.', 'panterrea_v1')]
            ]);
            }

            if ($formData['password'] !== $formData['confirmPassword']) {
            wp_send_json_error([
            'message' => 'Passwords do not match.',
            'errors' => ['confirmPassword' => __('Паролі не збігаються.', 'panterrea_v1')]
            ]);
            }

            $new_password = $formData['password'];
            wp_set_password($new_password, $user_id);

            wp_send_json_success(['message' => 'Password successfully reset.']);
            }

            add_action('admin_init', function () {
            if (current_user_can('subscriber') && !defined('DOING_AJAX')) {
            wp_redirect(home_url());
            exit;
            }
            });

            add_action('after_setup_theme', function () {
            if (current_user_can('subscriber')) {
            show_admin_bar(false);
            }
            });

            function restrict_pages_for_logged_in_users()
            {
            $restricted_templates = [
            'templates/login.php',
            'templates/register.php',
            'templates/forgot-password.php',
            'templates/reset-password.php'
            ];

            $redirect_url = home_url();

            if (is_user_logged_in() && is_page_template($restricted_templates)) {
            if (current_user_can('administrator')) {
            return;
            }

            setMessageCookies('warning', __('Ви уже авторизовані.', 'panterrea_v1'));

            wp_redirect($redirect_url);
            exit;
            }
            }

            add_action('template_redirect', 'restrict_pages_for_logged_in_users');

            function setMessageCookies($messageType, $messageText, $durationInSeconds = 60)
            {
            $expiryTime = time() + $durationInSeconds;
            setcookie('message_type', $messageType, $expiryTime, '/');
            setcookie('message_text', $messageText, $expiryTime, '/');
            }

            function get_catalog_post_author_info($author_id): ?array
            {

            if (!$author_id) {
            return null;
            }

            $user_info = get_userdata($author_id);

            if (!$user_info) {
            return null;
            }

            $first_name = get_user_meta($author_id, 'name', true);
            $city = get_user_meta($author_id, 'city', true);

            return [
            'name' => $first_name,
            'city' => $city,
            ];
            }

            /**
            * Author Phone
            */

            add_action('wp_ajax_get_author_phone', 'get_author_phone');

            function get_author_phone()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'show_phone_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!is_user_logged_in()) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Авторизуйтесь для доступу.', 'panterrea_v1')
            ]);
            }

            $email_confirmed = get_user_meta(get_current_user_id(), 'email_verified', true);

            if (!$email_confirmed) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1')
            ]);
            }

            $author_id = (int)sanitize_text_field(filter_input(INPUT_POST, 'author_id'));
            if (!$author_id) {
            wp_send_json_error(['message' => 'Invalid author ID.']);
            }

            $phone = get_user_meta($author_id, 'phone', true);
            if (!$phone) {
            wp_send_json_error(['message' => 'Phone number not found.']);
            }

            wp_send_json_success(['phone' => $phone]);
            }

            function custom_transliterate($text): string
            {
            $transliteration_table = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g',
            'д' => 'd', 'е' => 'e', 'є' => 'ye', 'ж' => 'zh', 'з' => 'z',
            'и' => 'y', 'і' => 'i', 'ї' => 'yi', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
            'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
            'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
            'ь' => '', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'H', 'Ґ' => 'G',
            'Д' => 'D', 'Е' => 'E', 'Є' => 'Ye', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'Y', 'І' => 'I', 'Ї' => 'Yi', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
            'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
            'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
            'Ь' => '', 'Ю' => 'Yu', 'Я' => 'Ya',
            ' ' => '-', '_' => '-',
            ];

            $text = strtr($text, $transliteration_table);
            $text = preg_replace('/[^a-zA-Z0-9\-]/', '', $text);
            $text = preg_replace('/-+/', '-', $text);

            return strtolower(trim($text, '-'));
            }

            /**
             * Get filter options that have at least one post in the given set.
             * FacetWP-style: only show filters that exist in current results.
             */
            function get_available_filters_for_posts(array $post_ids, array $terms, array $ad_type_tags, array $condition_tags): array
            {
                if (empty($post_ids)) {
                    return ['terms' => [], 'ad_type_tags' => [], 'condition_tags' => []];
                }
                $cat_term_ids = [];
                $tag_term_ids = [];
                foreach ($post_ids as $pid) {
                    $cats = wp_get_object_terms($pid, 'catalog_category');
                    if (!is_wp_error($cats)) {
                        foreach ($cats as $t) {
                            $cat_term_ids[$t->term_id] = true;
                        }
                    }
                    $tags = wp_get_object_terms($pid, 'catalog_tag');
                    if (!is_wp_error($tags)) {
                        foreach ($tags as $t) {
                            $tag_term_ids[$t->term_id] = true;
                        }
                    }
                }
                $terms = array_values(array_filter($terms, fn($t) => !empty($cat_term_ids[$t->term_id])));
                $ad_type_tags = array_values(array_filter($ad_type_tags, fn($t) => !empty($tag_term_ids[$t->term_id])));
                $condition_tags = array_values(array_filter($condition_tags, fn($t) => !empty($tag_term_ids[$t->term_id])));
                return ['terms' => $terms, 'ad_type_tags' => $ad_type_tags, 'condition_tags' => $condition_tags];
            }

            /**
            * Get max price from active catalog posts (optionally filtered by category)
            */
            function get_catalog_max_price($category_slug = 'all')
            {
            $args = [
            'post_type' => 'catalog_post',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_query' => [
            ['key' => '_is_active', 'value' => '1', 'compare' => '='],
            ['key' => 'catalog_post_currency', 'value' => 'грн', 'compare' => '='],
            ['key' => 'catalog_post_price', 'compare' => 'EXISTS'],
            ['key' => 'catalog_post_price', 'value' => '', 'compare' => '!='],
            ['key' => 'catalog_post_price', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC'],
            ],
            'orderby' => 'meta_value_num',
            'meta_key' => 'catalog_post_price',
            'order' => 'DESC',
            'fields' => 'ids',
            ];
            if ($category_slug && $category_slug !== 'all') {
            $args['tax_query'] = [
            ['taxonomy' => 'catalog_category', 'field' => 'slug', 'terms' => $category_slug],
            ];
            }
            $q = new WP_Query($args);
            $post_id = $q->posts[0] ?? null;
            if (!$post_id) {
            return 1000000;
            }
            $price = (int) get_post_meta($post_id, 'catalog_post_price', true);
            return $price > 0 ? $price : 1000000;
            }

            /**
            * Filter Catalog Posts
            */

            add_action('wp_ajax_filter_posts', 'filter_posts_callback');
            add_action('wp_ajax_nopriv_filter_posts', 'filter_posts_callback');

            function filter_posts_callback()
            {
            /*$currentLang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'uk';*/

            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'filters_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            $clicked_category = sanitize_text_field($_POST['category'] ?? false);
            $current_page_category = sanitize_text_field($_POST['current_page_category'] ?? '');
            $tags = isset($_POST['tags']) ? json_decode(stripslashes($_POST['tags'])) : [];
            $sort = sanitize_text_field(filter_input(INPUT_POST, 'sort'));
            $price_min = isset($_POST['price_min']) ? absint($_POST['price_min']) : 0;
            $price_max = isset($_POST['price_max']) ? absint($_POST['price_max']) : 1000000;
            $no_price = isset($_POST['no_price']) && $_POST['no_price'] === '1';
            $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
            $scroll = isset($_POST['scroll']) && $_POST['scroll'] === 'true';
            $scrollPage = sanitize_text_field($_POST['scrollPage'] ?? 'catalog');

            $favorites = [];
            if (is_user_logged_in()) {
            $favorites = get_user_meta(get_current_user_id(), 'favorite_posts', true) ?: [];
            }

            $query_args = [
            'post_type' => 'catalog_post',
            'posts_per_page' => 9,
            'paged' => $page,
            'tax_query' => [],
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

            if ($clicked_category) {
            $query_args['posts_per_page'] = 9;

            if ($clicked_category !== "all") {
            $cat_slugs = array_values(array_filter(array_map('sanitize_key', explode(',', $clicked_category))));
            if (!empty($cat_slugs)) {
            $query_args['tax_query'] = [
            [
            'taxonomy' => 'catalog_category',
            'field' => 'slug',
            'terms' => $cat_slugs,
            'operator' => 'IN',
            ],
            ];
            }
            }
            }

            if (!$clicked_category || $clicked_category === "all") {
            if ($current_page_category) {
            $current_category_term = get_term_by('slug', $current_page_category, 'catalog_category');
            if ($current_category_term) {
            $query_args['tax_query'] = [
            [
            'taxonomy' => 'catalog_category',
            'field' => 'term_id',
            'terms' => $current_category_term->term_id,
            'include_children' => true,
            ],
            ];
            }
            }
            }

            if (!empty($tags) && !in_array('tags-all', (array)$tags)) {
            $query_args['tax_query'][] = [
            'taxonomy' => 'catalog_tag',
            'field' => 'slug',
            'terms' => $tags,
            'operator' => 'IN',
            ];
            }

            if ($price_min > 0 || $price_max < 1000000 || $no_price) {
            $price_in_range = [
            'key' => 'catalog_post_price',
            'value' => [$price_min, $price_max],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN',
            ];
            if ($no_price) {
            $query_args['meta_query'][] = [
            'relation' => 'OR',
            [
            'relation' => 'OR',
            ['key' => 'catalog_post_price', 'compare' => 'NOT EXISTS'],
            ['key' => 'catalog_post_price', 'value' => '', 'compare' => '='],
            ['key' => 'catalog_post_price', 'value' => 0, 'compare' => '=', 'type' => 'NUMERIC'],
            ],
            $price_in_range,
            ];
            } else {
            $query_args['meta_query'][] = $price_in_range;
            }
            }

            if (!empty($sort)) {
            if (str_contains($sort, 'price')) {
            $currency = str_contains($sort, 'uah') ? 'грн' : '$';
            $order = str_contains($sort, 'asc') ? 'ASC' : 'DESC';

            $query_args['meta_query'][] = [
            'key' => 'catalog_post_currency',
            'value' => $currency,
            'compare' => '=',
            ];
            $query_args['orderby'] = [
            'meta_value_num' => $order,
            'date' => 'DESC'
            ];
            $query_args['meta_key'] = 'catalog_post_price';
            }
            }

            if ($scrollPage === 'favorites') {
            $query_args['post__in'] = $favorites;
            }

            if (preg_match('/^author-(\d+)$/', $scrollPage, $matches)) {
            $author_id = intval($matches[1]);
            $query_args['author'] = $author_id;
            }

            if (preg_match('/^search-(.+)$/', $scrollPage, $matches)) {
            $search_string = sanitize_text_field($matches[1]);
            $query_args['s'] = $search_string;
            }

            $query = new WP_Query($query_args);
            $has_more_posts = $query->found_posts > $query_args['posts_per_page'] * $page;
            $found_count = $query->found_posts;

            ob_start();
            if ($query->have_posts()) {
            while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/catalog-item', null, [
            'favorites' => $favorites,
            /*'currentLang' => $currentLang*/
            ]);
            }

            } else {
            if ($scroll && !$has_more_posts) {
            ob_end_clean();
            wp_send_json_error([['message' => 'No more posts.']]);
            } else {
            get_template_part('template-parts/empty-folder', null, [
            'info' => __('Немає оголошень по запиту', 'panterrea_v1')
            ]);
            }
            }

            $html = ob_get_clean();
            wp_reset_postdata();

            $available_filters_json = '{}';
            if (function_exists('get_available_filters_for_posts')) {
            $post_ids = array_map(fn($p) => $p->ID, $query->posts);
            $terms = [];
            if ($current_page_category && $current_page_category !== 'all') {
            $current_term = get_term_by('slug', $current_page_category, 'catalog_category');
            if ($current_term) {
            $parent_id = $current_term->parent ? $current_term->parent : $current_term->term_id;
            $terms = get_terms(['taxonomy' => 'catalog_category', 'hide_empty' => false, 'parent' => $parent_id]);
            }
            } else {
            $terms = get_terms(['taxonomy' => 'catalog_category', 'hide_empty' => false, 'parent' => 0]);
            }
            $terms = is_array($terms) && !is_wp_error($terms) ? $terms : [];
            $all_tags = get_terms(['taxonomy' => 'catalog_tag', 'hide_empty' => true]);
            $all_tags = is_array($all_tags) && !is_wp_error($all_tags) ? $all_tags : [];
            $condition_slugs = ['new', 'used', 'novyy', 'vzhyvanyy'];
            $ad_type_tags = array_values(array_filter($all_tags, fn($t) => !in_array($t->slug, $condition_slugs)));
            $condition_tags = array_values(array_filter($all_tags, fn($t) => in_array($t->slug, $condition_slugs)));
            $filtered = get_available_filters_for_posts($post_ids, $terms, $ad_type_tags, $condition_tags);
            $available_filters = [
            'terms' => array_map(fn($t) => $t->slug, $terms),
            'ad_type' => array_map(fn($t) => $t->slug, $filtered['ad_type_tags']),
            'condition' => array_map(fn($t) => $t->slug, $filtered['condition_tags']),
            ];
            $available_filters_json = wp_json_encode($available_filters);
            }

            echo '<div data-catalog-count="' . esc_attr($found_count) . '" data-available-filters="' . esc_attr($available_filters_json) . '">' . $html . '</div>';
            wp_die();
            }

            /**
            * Manage Favorites
            */

            add_action('wp_ajax_manage_favorites', 'manage_favorites');

            function manage_favorites()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'favorites_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!is_user_logged_in()) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Авторизуйтесь для доступу.', 'panterrea_v1')
            ]);
            }

            $user_id = get_current_user_id();

            $email_confirmed = get_user_meta($user_id, 'email_verified', true);
            if (!$email_confirmed) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1')
            ]);
            }

            $post_id = (int)sanitize_text_field(filter_input(INPUT_POST, 'post_id'));
            if (!$post_id) {
            wp_send_json_error(['message' => 'Incorrect ID.']);
            }

            $favorites = get_user_meta($user_id, 'favorite_posts', true);
            if (!$favorites) {
            $favorites = [];
            }

            $action = sanitize_text_field(filter_input(INPUT_POST, 'action_type'));

            if ($action === 'add') {
            if (!in_array($post_id, $favorites)) {
            $favorites[] = $post_id;
            update_user_meta($user_id, 'favorite_posts', $favorites);
            }
            } elseif ($action === 'remove') {
            if (($key = array_search($post_id, $favorites)) !== false) {
            unset($favorites[$key]);
            update_user_meta($user_id, 'favorite_posts', $favorites);
            }
            } else {
            wp_send_json_error(['message' => 'Invalid action type.']);
            }

            wp_send_json_success();
            }

            /**
            * Get Subcategories
            */

            add_action('wp_ajax_get_subcategories', 'get_subcategories');

            function get_subcategories()
            {
            /*$currentLang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'uk';*/

            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'getSubcategories_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }
            if (!isset($_POST['category_id'])) {
            wp_send_json_error(['message' => 'Категорія не вибрана']);
            return;
            }

            $category_id = intval($_POST['category_id']);
            $args = [
            'taxonomy' => 'catalog_category',
            'hide_empty' => false,
            'meta_key' => 'category_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'parent' => $category_id,
            ];

            $subcategories = get_terms($args);

            if (is_wp_error($subcategories) || empty($subcategories)) {
            wp_send_json_success(['subcategories' => []]);
            return;
            }

            $response = [];
            foreach ($subcategories as $subcategory) {
            /*$translated_name = ($currentLang === 'en' && ($name_en = get_field('name_en', $subcategory))) ? $name_en :
            $subcategory->name;*/

            $translated_name = $subcategory->name;
            $response[] = [
            'id' => $subcategory->term_id,
            'name' => $translated_name,
            ];
            }

            wp_send_json_success(['subcategories' => $response]);
            }

            function optimize_image_with_imagick($filePath)
            {
            if (!class_exists('Imagick')) {
            error_log('Imagick not available.');
            return $filePath;
            }

            try {
            $imagick = new Imagick($filePath);
            $mime = $imagick->getImageMimeType();

            $imagick->stripImage();
            $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality(80);

            $format = $mime === 'image/png' ? 'png' : 'jpeg';
            $imagick->setImageFormat($format);

            $optimizedPath = tempnam(sys_get_temp_dir(), 'opt_img_');
            $imagick->writeImage($optimizedPath);
            $imagick->clear();
            $imagick->destroy();

            return $optimizedPath;

            } catch (Exception $e) {
            error_log("Optimization failed: " . $e->getMessage());
            return $filePath;
            }
            }

            function create_mini_thumbnail($sourcePath, $width = 270, $height = 270)
            {
            if (!class_exists('Imagick')) {
            error_log('Imagick not available.');
            return $sourcePath;
            }

            try {
            $image = new Imagick($sourcePath);
            $image->cropThumbnailImage($width, $height);

            $format = $image->getImageFormat();
            $image->setImageFormat($format);

            $miniPath = tempnam(sys_get_temp_dir(), 'mini_');
            $image->writeImage($miniPath);
            $image->clear();
            $image->destroy();

            return $miniPath;

            } catch (Exception $e) {
            error_log("Mini thumbnail failed: " . $e->getMessage());
            return $sourcePath;
            }
            }

            /**
            * Create Ad
            */

            add_action('wp_ajax_create_ad', 'handle_create_ad');

            function handle_create_ad()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'adCreate_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (empty($_POST['formData'])) {
            wp_send_json_error(['message' => 'Дані не передані']);
            }

            $formData = json_decode(stripslashes($_POST['formData']), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(['message' => 'Невірний формат JSON']);
            }

            $validator = new FormValidator();

            $validator->addRules('adName', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['maxLength'](100)]);
            $validator->addRules('adCategory', [FormValidator::rules()['isNotEmpty']]);
            $validator->addRules('adType', [FormValidator::rules()['isNotEmpty']]);
            /*$validator->addRules('adPrice', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['isNumber']]);*/
            $validator->addRules('adCurrency', [FormValidator::rules()['isNotEmpty']]);
            $validator->addRules('adDesc', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['maxLength'](1000)]);
            $validator->addRules('adCondition', [FormValidator::rules()['isNotEmpty']]);

            $validationResult = $validator->validate($formData);

            if (!$validationResult['success']) {
            wp_send_json_error($validationResult);
            }

            $formData = sanitize_form_data($formData);

            $taxonomy = 'catalog_category';
            $categoryExists = false;
            $categoryIdFromRequest = isset($formData['adCategoryId']) ? (int)$formData['adCategoryId'] : 0;
            if ($categoryIdFromRequest > 0) {
            $categoryExists = term_exists($categoryIdFromRequest, $taxonomy);
            }
            if (!$categoryExists) {
            $rawCategory = $formData['adCategory'];
            $categoryParts = explode(' / ', $rawCategory);
            $lastCategory = trim(end($categoryParts));
            $categoryExists = term_exists($lastCategory, $taxonomy);
            }
            if (!$categoryExists) {
            wp_send_json_error(['message' => 'Обрана категорія не існує.']);
            }

            $acf_field = acf_get_field('field_675b3326e8fb5');
            if (!$acf_field || empty($acf_field['choices'])) {
            wp_send_json_error(['message' => 'Помилка в налаштуваннях ACF для Currency']);
            }

            if ($formData['adCurrency'] === 'UAH') {
            $formData['adCurrency'] = 'грн';
            }

            if (!array_key_exists($formData['adCurrency'], $acf_field['choices'])) {
            wp_send_json_error(['message' => 'Недійсне значення для Currency']);
            }

            $taxonomyTag = 'catalog_tag';
            $fieldsToCheck = ['adType'];

            $categoriesToCheck = ['Техніка', 'Обладнання', 'Земля'];
            $allMachineryCategories = [];

            foreach ($categoriesToCheck as $categoryName) {
            $category = term_exists($categoryName, 'catalog_category');
            if (!$category) {
            wp_send_json_error(['message' => "Категорія '{$categoryName}' не знайдена."]);
            }
            $categoryId = (int)$category['term_id'];
            $allMachineryCategories = array_merge($allMachineryCategories, get_term_children($categoryId,
            'catalog_category'));
            $allMachineryCategories[] = $categoryId;
            }

            if (in_array((int)$categoryExists['term_id'], $allMachineryCategories, true)) {
            $fieldsToCheck[] = 'adCondition';
            }

            foreach ($fieldsToCheck as $field) {
            $tags = is_string($formData[$field]) ? array_map('trim', explode(',', $formData[$field])) :
            (array)$formData[$field];
            foreach ($tags as $tag) {
            $tagExists = term_exists($tag, $taxonomyTag);
            if (!$tagExists) {
            wp_send_json_error(['message' => "Значення '{$tag}' у полі '{$field}' не існує у таксономії
            {$taxonomyTag}."]);
            }
            }
            }

            $currentUserId = get_current_user_id();
            if (!$currentUserId) {
            wp_send_json_error(['message' => 'Користувач не авторизований.']);
            }

            $postId = wp_insert_post([
            'post_title' => sanitize_text_field($formData['adName']),
            'post_status' => 'pending',
            'post_type' => 'catalog_post',
            'post_author' => $currentUserId,
            'post_content' => sanitize_text_field($formData['adRegion'])
            ]);

            if (is_wp_error($postId)) {
            wp_send_json_error(['message' => 'Не вдалося створити пост.']);
            }

            $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-central-1',
            'credentials' => [
            'key' => S3_KEY,
            'secret' => S3_SECRET,
            ],
            ]);

            $bucket = S3_BUCKET;
            $folderPath = "ad/$currentUserId/$postId/";

            $imageUrls = [];
            $featuredImageMiniUrl = '';

            if (!empty($_FILES['files'])) {

            foreach ($_FILES['files']['tmp_name'] as $index => $tmpFilePath) {
            if (is_uploaded_file($tmpFilePath)) {
            $originalPath = $tmpFilePath;
            $tmpFilePath = optimize_image_with_imagick($originalPath);

            $miniPath = create_mini_thumbnail($tmpFilePath);

            $fileExt = pathinfo($_FILES['files']['name'][$index], PATHINFO_EXTENSION);
            $fileName = time() . '-' . uniqid('', true) . '-' . bin2hex(random_bytes(4)) . '.' . $fileExt;

            $keyOriginal = $folderPath . $fileName;
            $miniFileName = 'mini-' . $fileName;
            $keyMini = $folderPath . $miniFileName;

            try {
            $resultOriginal = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $keyOriginal,
            'SourceFile' => $tmpFilePath,
            'ContentType' => mime_content_type($tmpFilePath),
            ]);
            $imageUrls[] = $resultOriginal['ObjectURL'];

            $resultMini = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $keyMini,
            'SourceFile' => $miniPath,
            'ContentType' => mime_content_type($miniPath),
            ]);
            $miniUrls[] = $resultMini['ObjectURL'];

            if ($index === 0) {
            $featuredImageMiniUrl = $resultMini['ObjectURL'];
            }
            } catch (Exception $e) {
            wp_send_json_error(['message' => 'Помилка завантаження у S3: ' . $e->getMessage()]);
            }

            if (file_exists($miniPath)) {
            unlink($miniPath);
            }
            if ($tmpFilePath !== $originalPath && file_exists($tmpFilePath)) {
            unlink($tmpFilePath);
            }
            }
            }
            }

            wp_set_post_terms($postId, [(int)$categoryExists['term_id']], $taxonomy);

            foreach ($fieldsToCheck as $field) {
            $tags = is_string($formData[$field]) ? array_map('trim', explode(',', $formData[$field])) :
            (array)$formData[$field];
            wp_set_post_terms($postId, $tags, $taxonomyTag, true);
            }

            $cleanUrls = array_map(fn($url) => str_replace('s3.eu-central-1.amazonaws.com/', '', $url), $imageUrls);
            $featuredImageUrl = array_shift($cleanUrls);
            $cleanMiniUrl = isset($featuredImageMiniUrl)
            ? str_replace('s3.eu-central-1.amazonaws.com/', '', $featuredImageMiniUrl)
            : '';

            $formData['adPrice'] = isset($formData['adPrice']) && is_numeric($formData['adPrice'])
            ? (float)$formData['adPrice']
            : 0;

            $updateData = [
            'price' => $formData['adPrice'],
            'currency' => $formData['adCurrency'],
            'info' => $formData['adDesc'],
            'featured_image' => $featuredImageUrl,
            'featured_image_mini' => $cleanMiniUrl
            ];

            if (!empty($cleanUrls)) {
            $updateData['gallery'] = array_map(fn($url) => ['image' => $url], $cleanUrls);
            }

            update_field('field_675a2873d04e6', $updateData, $postId);

            update_post_meta($postId, '_is_active', '0');

            $ad_name = sanitize_text_field($formData['adName']);
            $max_length = 40;
            if (mb_strlen($ad_name) > $max_length) {
            $ad_name = mb_substr($ad_name, 0, $max_length) . '...';
            }
            add_notification($currentUserId, 'ad_pending', sprintf(__('Оголошення %s очікує модерації. Після перевірки Ви отримаєте повідомлення.',
            'panterrea_v1'), $ad_name));

            wp_send_json_success(['message' => 'Оголошення успішно створено і очікує модерації', 'postId' => $postId]);
            }

            /**
            * Edit Ad
            */

            add_action('wp_ajax_edit_ad', 'handle_edit_ad');

            function handle_edit_ad()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'adEdit_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (empty($_POST['formData'])) {
            wp_send_json_error(['message' => 'Дані не передані']);
            }

            $formData = json_decode(stripslashes($_POST['formData']), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(['message' => 'Невірний формат JSON']);
            }

            $validator = new FormValidator();

            $validator->addRules('adName', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['maxLength'](100)]);
            $validator->addRules('adCategory', [FormValidator::rules()['isNotEmpty']]);
            $validator->addRules('adType', [FormValidator::rules()['isNotEmpty']]);
            /*$validator->addRules('adPrice', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['isNumber']]);*/
            $validator->addRules('adCurrency', [FormValidator::rules()['isNotEmpty']]);
            $validator->addRules('adDesc', [FormValidator::rules()['isNotEmpty'],
            FormValidator::rules()['maxLength'](1000)]);
            $validator->addRules('adCondition', [FormValidator::rules()['isNotEmpty']]);

            $validationResult = $validator->validate($formData);

            if (!$validationResult['success']) {
            wp_send_json_error($validationResult);
            }

            $formData = sanitize_form_data($formData);

            $taxonomy = 'catalog_category';
            $categoryExists = false;
            $categoryIdFromRequest = isset($formData['adCategoryId']) ? (int)$formData['adCategoryId'] : 0;
            if ($categoryIdFromRequest > 0) {
            $categoryExists = term_exists($categoryIdFromRequest, $taxonomy);
            }
            if (!$categoryExists) {
            $rawCategory = $formData['adCategory'];
            $categoryParts = explode(' / ', $rawCategory);
            $lastCategory = trim(end($categoryParts));
            $categoryExists = term_exists($lastCategory, $taxonomy);
            }
            if (!$categoryExists) {
            wp_send_json_error(['message' => 'Обрана категорія не існує.']);
            }

            $acf_field = acf_get_field('field_675b3326e8fb5');
            if (!$acf_field || empty($acf_field['choices'])) {
            wp_send_json_error(['message' => 'Помилка в налаштуваннях ACF для Currency']);
            }

            if ($formData['adCurrency'] === 'UAH') {
            $formData['adCurrency'] = 'грн';
            }

            if (!array_key_exists($formData['adCurrency'], $acf_field['choices'])) {
            wp_send_json_error(['message' => 'Недійсне значення для Currency']);
            }

            $taxonomyTag = 'catalog_tag';
            $fieldsToCheck = ['adType'];

            $categoriesToCheck = ['Техніка', 'Обладнання', 'Земля'];
            $allMachineryCategories = [];

            foreach ($categoriesToCheck as $categoryName) {
            $category = term_exists($categoryName, 'catalog_category');
            if (!$category) {
            wp_send_json_error(['message' => "Категорія '{$categoryName}' не знайдена."]);
            }
            $categoryId = (int)$category['term_id'];
            $allMachineryCategories = array_merge($allMachineryCategories, get_term_children($categoryId,
            'catalog_category'));
            $allMachineryCategories[] = $categoryId;
            }

            if (in_array((int)$categoryExists['term_id'], $allMachineryCategories, true)) {
            $fieldsToCheck[] = 'adCondition';
            }

            foreach ($fieldsToCheck as $field) {
            $tags = is_string($formData[$field]) ? array_map('trim', explode(',', $formData[$field])) :
            (array)$formData[$field];
            foreach ($tags as $tag) {
            $tagExists = term_exists($tag, $taxonomyTag);
            if (!$tagExists) {
            wp_send_json_error(['message' => "Значення '{$tag}' у полі '{$field}' не існує у таксономії
            {$taxonomyTag}."]);
            }
            }
            }

            $postId = intval($_POST['postId']);

            if (!$postId || get_post_type($postId) !== 'catalog_post') {
            wp_send_json_error(['message' => 'Некоректне оголошення.']);
            }

            $post_author_id = (int)get_post_field('post_author', $postId);
            $currentUserId = get_current_user_id();

            if (!$currentUserId) {
            wp_send_json_error(['message' => 'Користувач не авторизований.']);
            }

            if ($post_author_id !== $currentUserId) {
            wp_send_json_error(['message' => 'Ви не є автором цього поста']);
            }

            update_post_meta($postId, '_skip_slug_update', '1');

            $postData = [
            'ID' => $postId,
            'post_title' => sanitize_text_field($formData['adName']),
            'post_content' => sanitize_text_field($formData['adRegion'])
            ];
            $current_is_active = get_post_meta($postId, '_is_active', true);
            $current_is_boosted = get_post_meta($postId, '_is_boosted', true);
            $current_boost_expiration = get_post_meta($postId, '_boost_expiration', true);

            wp_update_post($postData);

            delete_post_meta($postId, '_skip_slug_update');

            $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-central-1',
            'credentials' => [
            'key' => S3_KEY,
            'secret' => S3_SECRET,
            ],
            ]);

            $bucket = S3_BUCKET;
            $folderPath = "ad/$currentUserId/$postId/";

            $existingImages = isset($_POST['existingImages'])
            ? (is_array($_POST['existingImages']) ? $_POST['existingImages'] : [$_POST['existingImages']])
            : [];
            $newImages = $_FILES['newImages'] ?? null;

            $existingFiles = [];
            try {
            $objects = $s3->listObjects([
            'Bucket' => $bucket,
            'Prefix' => $folderPath,
            ]);

            if (!empty($objects['Contents'])) {
            foreach ($objects['Contents'] as $object) {
            $existingFiles[] = $object['Key'];
            }
            }
            } catch (Exception $e) {
            wp_send_json_error(['message' => 'Помилка отримання файлів S3: ' . $e->getMessage()]);
            }

            $filesToKeep = [];
            $imageUrls = [];

            foreach ($existingImages as $imageUrl) {
            $parsedUrl = parse_url($imageUrl);
            if (!isset($parsedUrl['path'])) continue;

            $key = ltrim($parsedUrl['path'], '/');

            if (in_array($key, $existingFiles)) {
            $filesToKeep[] = $key;

            $pathInfo = pathinfo($key);
            $miniKey = $pathInfo['dirname'] . '/mini-' . $pathInfo['basename'];
            if (in_array($miniKey, $existingFiles)) {
            $filesToKeep[] = $miniKey;
            }

            $imageUrls[] = $imageUrl;
            } else {
            error_log("Файл не знайдено в S3: $key");
            }
            }

            if (!empty($_FILES['newImages']) && is_array($_FILES['newImages']['tmp_name'])) {
            foreach ($newImages['tmp_name'] as $index => $tmpFilePath) {
            if (!is_uploaded_file($tmpFilePath)) {
            continue;
            }

            $originalPath = $tmpFilePath;
            $tmpFilePath = optimize_image_with_imagick($originalPath);

            $miniPath = create_mini_thumbnail($tmpFilePath);

            $fileExt = pathinfo($newImages['name'][$index], PATHINFO_EXTENSION);
            $fileName = time() . '-' . uniqid('', true) . '-' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $key = $folderPath . $fileName;
            $miniFileName = 'mini-' . $fileName;
            $keyMini = $folderPath . $miniFileName;

            try {
            $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $tmpFilePath,
            'ContentType' => mime_content_type($tmpFilePath),
            ]);

            $resultMini = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $keyMini,
            'SourceFile' => $miniPath,
            'ContentType' => mime_content_type($miniPath),
            ]);

            $filesToKeep[] = $key;
            $filesToKeep[] = $keyMini;

            $imageUrls[] = $result['ObjectURL'];

            if ($tmpFilePath !== $originalPath && file_exists($tmpFilePath)) {
            unlink($tmpFilePath);
            }
            if (file_exists($miniPath)) {
            unlink($miniPath);
            }

            } catch (Exception $e) {
            wp_send_json_error(['message' => 'Помилка завантаження у S3: ' . $e->getMessage()]);
            }
            }
            }

            $filesToDelete = array_diff($existingFiles, $filesToKeep);
            if (!empty($filesToDelete)) {
            foreach ($filesToDelete as $fileKey) {
            try {
            $s3->deleteObject([
            'Bucket' => $bucket,
            'Key' => $fileKey,
            ]);
            } catch (Exception $e) {
            error_log('Помилка видалення з S3: ' . $e->getMessage());
            }
            }
            }

            wp_set_post_terms($postId, [(int)$categoryExists['term_id']], $taxonomy);

            wp_set_post_terms($postId, [], $taxonomyTag, false);

            foreach ($fieldsToCheck as $field) {
            $tags = is_string($formData[$field]) ? array_map('trim', explode(',', $formData[$field])) :
            (array)$formData[$field];
            wp_set_post_terms($postId, $tags, $taxonomyTag, true);
            }

            $cleanUrls = array_map(fn($url) => str_replace('s3.eu-central-1.amazonaws.com/', '', $url), $imageUrls);
            $featuredImageUrl = array_shift($cleanUrls);

            $formData['adPrice'] = isset($formData['adPrice']) && is_numeric($formData['adPrice'])
            ? (float)$formData['adPrice']
            : 0;

            $featuredImageMiniUrl = '';
            if (!empty($featuredImageUrl)) {
            $pathParts = pathinfo($featuredImageUrl);
            $featuredImageMiniUrl = $pathParts['dirname'] . '/mini-' . $pathParts['basename'];
            }

            // For old ads without thumbnails

            $currentCatalogPostFields = get_field('catalog_post', $postId);
            $currentFeaturedImageUrl = $currentCatalogPostFields['featured_image'] ?? null;
            $currentFeaturedImageMiniUrl = $currentCatalogPostFields['featured_image_mini'] ?? null;

            if ($currentFeaturedImageUrl && !$currentFeaturedImageMiniUrl) {
            $parsedUrl = parse_url($currentFeaturedImageUrl);
            if (!empty($parsedUrl['path'])) {
            $featuredImageKey = ltrim($parsedUrl['path'], '/');
            $tempFilePath = sys_get_temp_dir() . '/' . basename($featuredImageKey);

            try {
            $s3->getObject([
            'Bucket' => $bucket,
            'Key' => $featuredImageKey,
            'SaveAs' => $tempFilePath,
            ]);

            if (file_exists($tempFilePath)) {
            $miniPath = create_mini_thumbnail($tempFilePath);

            $pathInfo = pathinfo($featuredImageKey);
            $miniKey = $pathInfo['dirname'] . '/mini-' . $pathInfo['basename'];

            $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $miniKey,
            'SourceFile' => $miniPath,
            'ContentType' => mime_content_type($miniPath),
            ]);

            unlink($tempFilePath);
            unlink($miniPath);
            }
            } catch (Exception $e) {
            error_log('Помилка створення мініатюри при редагуванні: ' . $e->getMessage());
            }
            }
            }

            // For old ads without thumbnails

            if (empty($imageUrls)) {
            $featuredImageUrl = '';
            $featuredImageMiniUrl = '';
            }

            $updateData = [
            'price' => $formData['adPrice'],
            'currency' => $formData['adCurrency'],
            'info' => $formData['adDesc'],
            'featured_image' => $featuredImageUrl,
            'featured_image_mini' => $featuredImageMiniUrl,
            'gallery' => []
            ];

            if (!empty($cleanUrls)) {
            $updateData['gallery'] = array_map(fn($url) => ['image' => $url], $cleanUrls);
            }

            update_field('field_675a2873d04e6', $updateData, $postId);
            update_post_meta($postId, '_is_active', $current_is_active);
            update_post_meta($postId, '_is_boosted', $current_is_boosted);
            update_post_meta($postId, '_boost_expiration', $current_boost_expiration);

            $postUrl = get_permalink($postId);
            // Ensure trailing slash for catalog URLs
            if (strpos($postUrl, '/catalog/') !== false) {
            $postUrl = rtrim($postUrl, '/') . '/';
            }
            wp_send_json_success(['message' => 'Оголошення успішно відредаговано', 'postUrl' => $postUrl]);
            }

            /**
            * Search - Ukrainian Language Support
            */

            add_action('wp_ajax_search_suggestions', 'search_suggestions');
            add_action('wp_ajax_nopriv_search_suggestions', 'search_suggestions');

            /**
             * Ukrainian word stemming - removes common endings to find word roots
             */
            function ukrainian_stem($word) {
                $word = mb_strtolower(trim($word), 'UTF-8');
                
                // Don't stem very short words
                if (mb_strlen($word, 'UTF-8') <= 3) {
                    return $word;
                }
                
                // Common Ukrainian word endings (nouns, adjectives, verbs)
                $endings = [
                    // Noun endings (all cases, singular and plural)
                    'ами', 'ами', 'ові', 'еві', 'ями', 'ях', 'ам', 'ах', 'ів', 'ем', 'ою', 'єю',
                    'ом', 'ою', 'ім', 'ою', 'єю', 'ою', 'ів', 'ам', 'ах', 'ам',
                    'ов', 'ев', 'ів', 'ою', 'єю', 'ам', 'ах', 'ох', 'ах',
                    'ою', 'єю', 'ом', 'ем', 'ім', 'ою', 'єю',
                    'и', 'і', 'у', 'ю', 'а', 'я', 'ою', 'єю', 'о', 'е', 'є'
                ];
                
                // Try to remove endings from longest to shortest
                usort($endings, function($a, $b) {
                    return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8');
                });
                
                foreach ($endings as $ending) {
                    $ending_len = mb_strlen($ending, 'UTF-8');
                    $word_len = mb_strlen($word, 'UTF-8');
                    
                    // Keep at least 3 characters in the stem
                    if ($word_len - $ending_len >= 3) {
                        $word_ending = mb_substr($word, -$ending_len, null, 'UTF-8');
                        if ($word_ending === $ending) {
                            return mb_substr($word, 0, $word_len - $ending_len, 'UTF-8');
                        }
                    }
                }
                
                return $word;
            }

            /**
             * Calculate Levenshtein distance for Ukrainian strings (for typo detection)
             */
            function ukrainian_levenshtein($str1, $str2) {
                $str1 = mb_strtolower($str1, 'UTF-8');
                $str2 = mb_strtolower($str2, 'UTF-8');
                
                $len1 = mb_strlen($str1, 'UTF-8');
                $len2 = mb_strlen($str2, 'UTF-8');
                
                if ($len1 == 0) return $len2;
                if ($len2 == 0) return $len1;
                
                $matrix = array();
                for ($i = 0; $i <= $len1; $i++) {
                    $matrix[$i] = array($i);
                }
                for ($j = 0; $j <= $len2; $j++) {
                    $matrix[0][$j] = $j;
                }
                
                for ($i = 1; $i <= $len1; $i++) {
                    for ($j = 1; $j <= $len2; $j++) {
                        $cost = (mb_substr($str1, $i - 1, 1, 'UTF-8') === mb_substr($str2, $j - 1, 1, 'UTF-8')) ? 0 : 1;
                        $matrix[$i][$j] = min(
                            $matrix[$i - 1][$j] + 1,      // deletion
                            $matrix[$i][$j - 1] + 1,      // insertion
                            $matrix[$i - 1][$j - 1] + $cost // substitution
                        );
                    }
                }
                
                return $matrix[$len1][$len2];
            }

            /**
             * Normalize Ukrainian keyboard layout mistakes (Russian/Ukrainian mixed)
             */
            function normalize_ukrainian_keyboard($text) {
                // Common Russian letters that might be mistyped instead of Ukrainian
                $replacements = [
                    'ы' => 'и', 'э' => 'е', 'ъ' => '', 'ё' => 'е',
                ];
                
                return str_replace(array_keys($replacements), array_values($replacements), mb_strtolower($text, 'UTF-8'));
            }

            /**
             * Advanced search with Ukrainian language support
             */
            function search_suggestions()
            {
                if (isset($_POST['query'])) {
                    $query = sanitize_text_field($_POST['query']);

                    $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                    if (!$security || !wp_verify_nonce($security, 'search_nonce')) {
                        wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    // Normalize keyboard layout mistakes
                    $query = normalize_ukrainian_keyboard($query);
                    
                    // Get stem for grammatical form matching
                    $stem = ukrainian_stem($query);
                    
                    // Build custom SQL query for advanced matching
                    global $wpdb;
                    
                    $search_terms = esc_sql($wpdb->esc_like($query));
                    $stem_terms = esc_sql($wpdb->esc_like($stem));
                    
                    // Custom query that searches in title, content, and excerpt
                    // Supports: 1) Partial match, 2) Stem match (grammatical forms), 3) Fuzzy match
                    $post_ids_query = "
                        SELECT DISTINCT p.ID,
                            CASE
                                WHEN p.post_title LIKE '%{$search_terms}%' THEN 100
                                WHEN p.post_content LIKE '%{$search_terms}%' THEN 80
                                WHEN p.post_excerpt LIKE '%{$search_terms}%' THEN 70
                                WHEN p.post_title LIKE '%{$stem_terms}%' THEN 60
                                WHEN p.post_content LIKE '%{$stem_terms}%' THEN 50
                                WHEN p.post_excerpt LIKE '%{$stem_terms}%' THEN 40
                                ELSE 10
                            END as relevance
                        FROM {$wpdb->posts} p
                        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                        WHERE p.post_type = 'catalog_post'
                        AND p.post_status = 'publish'
                        AND pm.meta_key = '_is_active'
                        AND pm.meta_value = '1'
                        AND (
                            p.post_title LIKE '%{$search_terms}%'
                            OR p.post_content LIKE '%{$search_terms}%'
                            OR p.post_excerpt LIKE '%{$search_terms}%'
                            OR p.post_title LIKE '%{$stem_terms}%'
                            OR p.post_content LIKE '%{$stem_terms}%'
                            OR p.post_excerpt LIKE '%{$stem_terms}%'
                        )
                        ORDER BY relevance DESC, p.post_date DESC
                        LIMIT 10
                    ";
                    
                    $post_ids = $wpdb->get_results($post_ids_query);
                    
                    $suggestions = array();
                    
                    if ($post_ids) {
                        foreach ($post_ids as $post_data) {
                            $post = get_post($post_data->ID);
                            
                            // Get boost status
                            $is_boosted = get_post_meta($post->ID, '_is_boosted', true);
                            
                            $categories = get_the_terms($post->ID, 'catalog_category');
                            $category_path = '';

                            if ($categories && !is_wp_error($categories)) {
                                $category = $categories[0];
                                $ancestors = get_ancestors($category->term_id, 'catalog_category');

                                $ancestor_names = array_reverse(array_map(function ($ancestor_id) {
                                    return get_term($ancestor_id)->name;
                                }, $ancestors));

                                $ancestor_names[] = $category->name;
                                $category_path = implode('/', $ancestor_names);
                            }

                            $permalink = get_permalink($post->ID);
                            // Ensure trailing slash for catalog URLs
                            if (strpos($permalink, '/catalog/') !== false) {
                                $permalink = rtrim($permalink, '/') . '/';
                            }

                            $suggestions[] = array(
                                'title' => $post->post_title,
                                'url' => $permalink,
                                'category' => $category_path,
                                'relevance' => $post_data->relevance,
                                'is_boosted' => $is_boosted
                            );
                        }
                        
                        // Sort by boosted status first, then by relevance
                        usort($suggestions, function($a, $b) {
                            if ($a['is_boosted'] != $b['is_boosted']) {
                                return $b['is_boosted'] - $a['is_boosted'];
                            }
                            return $b['relevance'] - $a['relevance'];
                        });
                        
                        // Limit to 5 results
                        $suggestions = array_slice($suggestions, 0, 5);
                        
                        // Remove internal fields before sending
                        foreach ($suggestions as &$suggestion) {
                            unset($suggestion['relevance']);
                            unset($suggestion['is_boosted']);
                        }
                    }

                    wp_send_json_success($suggestions);
                }

                wp_die();
            }

            /**
             * AJAX endpoint for testing Ukrainian stem function (for test-ukrainian-search.php)
             */
            add_action('wp_ajax_test_ukrainian_stem', 'test_ukrainian_stem_ajax');
            add_action('wp_ajax_nopriv_test_ukrainian_stem', 'test_ukrainian_stem_ajax');

            function test_ukrainian_stem_ajax() {
                $word = isset($_POST['word']) ? sanitize_text_field($_POST['word']) : '';
                
                if (empty($word)) {
                    wp_send_json_error('No word provided');
                }
                
                $normalized = normalize_ukrainian_keyboard($word);
                $stem = ukrainian_stem($normalized);
                
                wp_send_json_success([
                    'original' => $word,
                    'normalized' => $normalized,
                    'stem' => $stem
                ]);
            }

            /**
             * Custom search query filter for Ukrainian language support on search page
             */
            add_filter('posts_search', 'ukrainian_search_filter', 10, 2);
            add_filter('posts_orderby', 'ukrainian_search_orderby', 10, 2);

            function ukrainian_search_filter($search, $wp_query) {
                global $wpdb;
                
                // Only apply on main query with search parameter
                if (empty($search) || !$wp_query->is_main_query() || is_admin()) {
                    return $search;
                }
                
                $search_term = $wp_query->get('s');
                if (empty($search_term)) {
                    return $search;
                }
                
                // Normalize and stem the search term
                $search_term = normalize_ukrainian_keyboard($search_term);
                $stem = ukrainian_stem($search_term);
                
                $search_terms = esc_sql($wpdb->esc_like($search_term));
                $stem_terms = esc_sql($wpdb->esc_like($stem));
                
                // Build custom search query with relevance scoring
                $search = " AND (
                    ({$wpdb->posts}.post_title LIKE '%{$search_terms}%')
                    OR ({$wpdb->posts}.post_content LIKE '%{$search_terms}%')
                    OR ({$wpdb->posts}.post_excerpt LIKE '%{$search_terms}%')
                    OR ({$wpdb->posts}.post_title LIKE '%{$stem_terms}%')
                    OR ({$wpdb->posts}.post_content LIKE '%{$stem_terms}%')
                    OR ({$wpdb->posts}.post_excerpt LIKE '%{$stem_terms}%')
                ) ";
                
                // Store search terms for ordering
                $wp_query->set('_search_term_normalized', $search_terms);
                $wp_query->set('_search_stem', $stem_terms);
                
                return $search;
            }

            function ukrainian_search_orderby($orderby, $wp_query) {
                global $wpdb;
                
                // Only apply on main query with search parameter
                if (!$wp_query->is_main_query() || is_admin()) {
                    return $orderby;
                }
                
                $search_terms = $wp_query->get('_search_term_normalized');
                $stem_terms = $wp_query->get('_search_stem');
                
                if (empty($search_terms)) {
                    return $orderby;
                }
                
                // Add relevance scoring to orderby
                $relevance_orderby = "
                    CASE
                        WHEN {$wpdb->posts}.post_title LIKE '%{$search_terms}%' THEN 100
                        WHEN {$wpdb->posts}.post_content LIKE '%{$search_terms}%' THEN 80
                        WHEN {$wpdb->posts}.post_excerpt LIKE '%{$search_terms}%' THEN 70
                        WHEN {$wpdb->posts}.post_title LIKE '%{$stem_terms}%' THEN 60
                        WHEN {$wpdb->posts}.post_content LIKE '%{$stem_terms}%' THEN 50
                        WHEN {$wpdb->posts}.post_excerpt LIKE '%{$stem_terms}%' THEN 40
                        ELSE 10
                    END DESC
                ";
                
                // Prepend relevance to existing orderby (which includes boost and date)
                if (!empty($orderby)) {
                    return $relevance_orderby . ', ' . $orderby;
                }
                
                return $relevance_orderby;
            }

            /**
            * Highlight search query in text
            */
            function highlight_search_query($text, $query) {
            if (empty($query) || empty($text)) {
            return esc_html($text);
            }

            if (is_array($query)) {
            $query = implode(' ', $query);
            }
            $query = sanitize_text_field((string) $query);
            if ($query === '') {
            return esc_html($text);
            }

            $escaped_text = esc_html($text);
            $escaped_query = preg_quote($query, '/');
            $pattern = '/(' . $escaped_query . ')/iu';
            $highlighted = preg_replace($pattern, '<strong>$1</strong>', $escaped_text);

            return $highlighted;
            }

            /**
            * Toggle Ad Status
            */

            add_action('wp_ajax_toggle_catalog_post_status', 'toggle_catalog_post_status');

            function toggle_catalog_post_status()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'statusAd_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!is_user_logged_in()) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Авторизуйтесь для доступу.', 'panterrea_v1')
            ]);
            }

            $email_confirmed = get_user_meta(get_current_user_id(), 'email_verified', true);

            if (!$email_confirmed) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1')
            ]);
            }

            $post_id = intval($_POST['post_id']);
            $post_author_id = (int)get_post_field('post_author', $post_id);
            $current_user_id = get_current_user_id();

            if ($post_author_id !== $current_user_id) {
            wp_send_json_error(['message' => 'Ви не є автором цього поста']);
            }

            $current_status = get_post_meta($post_id, '_is_active', true);
            $is_boosted = get_post_meta($post_id, '_is_boosted', true);

            if ($current_status === '1') {
            update_post_meta($post_id, '_is_active', '0');

            if ($is_boosted) {
            $boost_expiration = get_post_meta($post_id, '_boost_expiration', true);

            if ($boost_expiration && $boost_expiration > time()) {
            $time_remaining = $boost_expiration - time();
            update_post_meta($post_id, '_boost_remaining_time', $time_remaining);
            }

            delete_post_meta($post_id, '_boost_expiration');
            }

            wp_send_json_success([
            'is_active' => '0',
            'message' => __('deactivate_ad', 'panterrea_v1')
            ]);
            } else {
            update_post_meta($post_id, '_is_active', '1');

            if ($is_boosted) {
            $boost_remaining_time = get_post_meta($post_id, '_boost_remaining_time', true);

            if ($boost_remaining_time) {
            $new_expiration = time() + (int)$boost_remaining_time;
            update_post_meta($post_id, '_boost_expiration', $new_expiration);
            delete_post_meta($post_id, '_boost_remaining_time');
            }
            }

            wp_send_json_success([
            'is_active' => '1',
            'message' => __('activate_ad', 'panterrea_v1')
            ]);
            }
            }

            /**
            * Delete Ad
            */

            add_action('wp_ajax_delete_catalog_post', 'delete_catalog_post');

            function delete_catalog_post()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'deleteAd_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!is_user_logged_in()) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Авторизуйтесь для доступу.', 'panterrea_v1')
            ]);
            }

            $email_confirmed = get_user_meta(get_current_user_id(), 'email_verified', true);

            if (!$email_confirmed) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1')
            ]);
            }

            $post_id = intval($_POST['post_id']);
            $post_author_id = (int)get_post_field('post_author', $post_id);
            $current_user_id = get_current_user_id();

            if ($post_author_id !== $current_user_id) {
            wp_send_json_error(['message' => 'Ви не є автором цього поста']);
            }

            if (!$post_id || get_post_type($post_id) !== 'catalog_post') {
            wp_send_json_error(['message' => 'Некоректне оголошення.']);
            }

            $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-central-1',
            'credentials' => [
            'key' => S3_KEY,
            'secret' => S3_SECRET,
            ],
            ]);

            $bucket = S3_BUCKET;
            $folderPath = "ad/$current_user_id/$post_id/";

            try {
            $objects = $s3->listObjectsV2([
            'Bucket' => $bucket,
            'Prefix' => $folderPath,
            ]);

            if (!empty($objects['Contents'])) {
            $keysToDelete = [];
            foreach ($objects['Contents'] as $object) {
            $keysToDelete[] = ['Key' => $object['Key']];
            }

            $s3->deleteObjects([
            'Bucket' => $bucket,
            'Delete' => ['Objects' => $keysToDelete],
            ]);
            }
            } catch (AwsException $e) {
            wp_send_json_error(['message' => 'Помилка видалення файлів з S3: ' . $e->getMessage()]);
            }

            $ad_name = get_the_title($post_id);
            $max_length = 40;
            if (mb_strlen($ad_name) > $max_length) {
            $ad_name = mb_substr($ad_name, 0, $max_length) . '...';
            }

            $result = wp_delete_post($post_id, true);

            if ($result) {
            add_notification($post_author_id, 'delete_ad', sprintf(__('Оголошення %s успішно видалено.',
            'panterrea_v1'), $ad_name));

            wp_send_json_success(['message' => __('Оголошення успішно видалено.', 'panterrea_v1')]);
            } else {
            wp_send_json_error(['message' => __('Не вдалося видалити оголошення.', 'panterrea_v1')]);
            }
            }

            /**
            * Moderate Catalog Post (Admin)
            * Модерація оголошень адміністратором
            */

            add_action('wp_ajax_moderate_catalog_post', 'moderate_catalog_post_handler');

            function moderate_catalog_post_handler()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'moderate_post_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('У вас немає прав для модерації.', 'panterrea_v1')]);
            }

            $post_id = intval($_POST['post_id']);
            $moderate_action = sanitize_text_field($_POST['moderate_action']);

            if (!$post_id || get_post_type($post_id) !== 'catalog_post') {
            wp_send_json_error(['message' => __('Некоректне оголошення.', 'panterrea_v1')]);
            }

            $post = get_post($post_id);
            if (!$post) {
            wp_send_json_error(['message' => __('Оголошення не знайдено.', 'panterrea_v1')]);
            }

            $author_id = (int)get_post_field('post_author', $post_id);
            $ad_name = get_the_title($post_id);
            $max_length = 40;
            if (mb_strlen($ad_name) > $max_length) {
            $ad_name = mb_substr($ad_name, 0, $max_length) . '...';
            }

            switch ($moderate_action) {
            case 'approve':
            // Approve and publish the post
            $result = wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish'
            ]);

            if (!is_wp_error($result)) {
            update_post_meta($post_id, '_is_active', '1');
            
            // Send notification to user
            add_notification(
            $author_id,
            'ad_approved',
            sprintf(__('Ваше оголошення %s було схвалено і опубліковано.', 'panterrea_v1'), $ad_name)
            );

            wp_send_json_success([
            'message' => __('Оголошення схвалено і опубліковано.', 'panterrea_v1')
            ]);
            } else {
            wp_send_json_error(['message' => __('Помилка при схваленні оголошення.', 'panterrea_v1')]);
            }
            break;

            case 'reject':
            // Move to trash (reject)
            $result = wp_trash_post($post_id);

            if ($result) {
            update_post_meta($post_id, '_is_active', '0');
            
            // Send notification to user
            add_notification(
            $author_id,
            'ad_rejected',
            sprintf(__('Ваше оголошення %s було відхилено модератором.', 'panterrea_v1'), $ad_name)
            );

            wp_send_json_success([
            'message' => __('Оголошення відхилено.', 'panterrea_v1')
            ]);
            } else {
            wp_send_json_error(['message' => __('Помилка при відхиленні оголошення.', 'panterrea_v1')]);
            }
            break;

            case 'unpublish':
            // Move published post back to pending
            $result = wp_update_post([
            'ID' => $post_id,
            'post_status' => 'pending'
            ]);

            if (!is_wp_error($result)) {
            update_post_meta($post_id, '_is_active', '0');
            
            // Send notification to user
            add_notification(
            $author_id,
            'ad_pending',
            sprintf(__('Ваше оголошення %s було знято з публікації і очікує повторної модерації.', 'panterrea_v1'), $ad_name)
            );

            wp_send_json_success([
            'message' => __('Оголошення знято з публікації.', 'panterrea_v1')
            ]);
            } else {
            wp_send_json_error(['message' => __('Помилка при зняті з публікації.', 'panterrea_v1')]);
            }
            break;

            case 'restore':
            // Restore from trash to pending
            $result = wp_untrash_post($post_id);

            if ($result) {
            // Set status to pending for re-moderation
            wp_update_post([
            'ID' => $post_id,
            'post_status' => 'pending'
            ]);

            update_post_meta($post_id, '_is_active', '0');
            
            // Send notification to user
            add_notification(
            $author_id,
            'ad_pending',
            sprintf(__('Ваше оголошення %s було відновлено і очікує модерації.', 'panterrea_v1'), $ad_name)
            );

            wp_send_json_success([
            'message' => __('Оголошення відновлено і очікує модерації.', 'panterrea_v1')
            ]);
            } else {
            wp_send_json_error(['message' => __('Помилка при відновленні оголошення.', 'panterrea_v1')]);
            }
            break;

            default:
            wp_send_json_error(['message' => __('Невідома дія модерації.', 'panterrea_v1')]);
            }
            }

            /**
            * Boost Ad
            */

            add_action('wp_ajax_create_payment_intent', 'handle_create_payment_intent');

            function handle_create_payment_intent()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'boost_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!is_user_logged_in()) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Авторизуйтесь для доступу.', 'panterrea_v1')
            ]);
            }

            $email_confirmed = get_user_meta(get_current_user_id(), 'email_verified', true);

            if (!$email_confirmed) {
            wp_send_json_error([
            'message' => 'Unauthorized access.',
            'errors' => __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1')
            ]);
            }

            $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

            $post_author_id = (int)get_post_field('post_author', $post_id);
            $current_user_id = get_current_user_id();

            if ($post_author_id !== $current_user_id) {
            wp_send_json_error(['message' => 'Ви не є автором цього поста']);
            }

            if (!$post_id || get_post_type($post_id) !== 'catalog_post') {
            wp_send_json_error(['message' => 'Некоректне оголошення.']);
            }

            $is_active = get_post_meta($post_id, '_is_active', true);
            if (!$is_active) {
            wp_send_json_error(['message' => 'Виберіть активне оголошення.']);
            }

            $is_boosted = get_post_meta($post_id, '_is_boosted', true);
            if ($is_boosted) {
            wp_send_json_error(['message' => 'Оголошення уже рекламується.']);
            }

            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

            $amount = BOOST_PRICE;

            $currency = 'uah';

            try {
            $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ['card'],
            ]);

            wp_send_json_success(['clientSecret' => $paymentIntent->client_secret]);
            } catch (Exception $e) {
            wp_send_json_error(['error' => $e->getMessage()]);
            }

            wp_die();
            }

            add_action('wp_ajax_successful_payment', 'handle_successful_payment');

            function handle_successful_payment()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'boost_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

            $post_author_id = (int)get_post_field('post_author', $post_id);
            $current_user_id = get_current_user_id();

            if ($post_author_id !== $current_user_id) {
            wp_send_json_error(['message' => 'Ви не є автором цього поста']);
            }

            if (!$post_id || get_post_type($post_id) !== 'catalog_post') {
            wp_send_json_error(['message' => 'Некоректне оголошення.']);
            }

            $is_active = get_post_meta($post_id, '_is_active', true);
            if (!$is_active) {
            wp_send_json_error(['message' => 'Виберіть активне оголошення.']);
            }

            $is_boosted = get_post_meta($post_id, '_is_boosted', true);
            if ($is_boosted) {
            wp_send_json_error(['message' => 'Оголошення уже рекламується.']);
            }

            $boost_duration = 30 * DAY_IN_SECONDS;
            /*$boost_duration = 10 * MINUTE_IN_SECONDS;*/
            $boost_expiration = time() + $boost_duration;
            $current_status = get_post_meta($post_id, '_is_active', true);

            remove_action('save_post', 'save_boost_meta_box');

            $current_time = current_time('mysql');
            $post_data = [
            'ID' => $post_id,
            'post_date' => $current_time,
            'post_date_gmt' => get_gmt_from_date($current_time),
            ];
            wp_update_post($post_data);

            add_action('save_post', 'save_boost_meta_box');

            update_post_meta($post_id, '_is_active', $current_status);
            $updated = update_post_meta($post_id, '_is_boosted', true);

            update_post_meta($post_id, '_boost_expiration', $boost_expiration);

            if ($updated) {

            $ad_name = get_the_title($post_id);
            $max_length = 40;
            if (mb_strlen($ad_name) > $max_length) {
            $ad_name = mb_substr($ad_name, 0, $max_length) . '...';
            }
            add_notification($post_author_id, 'boost_ad', sprintf(__('Оголошення %s тепер рекламується.',
            'panterrea_v1'), $ad_name));

            wp_send_json_success(['message' => __('Оплата успішна! Тепер оголошення рекламується.', 'panterrea_v1')]);
            } else {
            wp_send_json_error(['message' => __('Помилка при оновленні оголошення.', 'panterrea_v1')]);
            }
            }

            /**
            * Chat
            */

            add_action('after_switch_theme', 'create_chat_tables');
            function create_chat_tables()
            {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            $table_sessions = $wpdb->prefix . 'chat_sessions';
            $sql_sessions = "CREATE TABLE IF NOT EXISTS $table_sessions (
            chat_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user1_id BIGINT UNSIGNED NOT NULL,
            user2_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_chat (user1_id, user2_id, post_id)
            ) $charset_collate;";

            $table_messages = $wpdb->prefix . 'chat_messages';
            $sql_messages = "CREATE TABLE IF NOT EXISTS $table_messages (
            message_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            chat_id BIGINT UNSIGNED NOT NULL,
            sender_id BIGINT UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chat_id) REFERENCES $table_sessions(chat_id) ON DELETE CASCADE
            ) $charset_collate;";

            $table_blocklist = $wpdb->prefix . 'chat_blocklist';
            $sql_blocklist = "CREATE TABLE IF NOT EXISTS $table_blocklist (
            block_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            blocker_id BIGINT UNSIGNED NOT NULL,
            blocked_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_block (blocker_id, blocked_id)
            ) $charset_collate;";

            $table_deleted = $wpdb->prefix . 'chat_deleted';
            $sql_deleted = "CREATE TABLE IF NOT EXISTS $table_deleted (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            chat_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY chat_user (chat_id, user_id)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql_sessions);
            dbDelta($sql_messages);
            dbDelta($sql_blocklist);
            dbDelta($sql_deleted);
            }

            add_action('wp_ajax_save_chat_message', 'save_chat_message');

            function save_chat_message()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'chat_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
            $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
            $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

            if ($user_id == 0 || $recipient_id == 0 || ($post_id == 0 && empty($message) && empty($_FILES['images']))) {
            wp_send_json_error(['message' => 'Невірні дані або повідомлення порожнє']);
            }

            if (!get_user_by('ID', $user_id)) {
            wp_send_json_error(['message' => 'Користувач не знайдений']);
            }
            if (!get_user_by('ID', $recipient_id)) {
            wp_send_json_error(['message' => 'Отримувач не знайдений']);
            }

            global $wpdb;

            $blocked = $wpdb->get_var(
            $wpdb->prepare(
            "SELECT 1 FROM {$wpdb->prefix}chat_blocklist
            WHERE blocker_id = %d AND blocked_id = %d",
            $recipient_id, $user_id
            )
            );

            if ($blocked) {
            wp_send_json_error([
            'message' => __('Користувач заблокував вас.', 'panterrea_v1'),
            'error' => 'blocked'
            ]);
            }

            $utcTime = current_time('mysql', true);

            $user1_id = min($user_id, $recipient_id);
            $user2_id = max($user_id, $recipient_id);

            $chat_id = $wpdb->get_var(
            $wpdb->prepare(
            "SELECT chat_id FROM {$wpdb->prefix}chat_sessions
            WHERE user1_id = %d AND user2_id = %d AND post_id = %d",
            $user1_id, $user2_id, $post_id
            )
            );

            if (!$chat_id) {
            $inserted = $wpdb->insert(
            "{$wpdb->prefix}chat_sessions",
            [
            'user1_id' => $user1_id,
            'user2_id' => $user2_id,
            'post_id' => $post_id,
            'created_at' => current_time('mysql'),
            ]
            );

            if (!$inserted) {
            wp_send_json_error(['message' => 'Не вдалося створити чат']);
            }

            $chat_id = $wpdb->insert_id;
            }

            $uploaded_images = [];
            if (!empty($_FILES['images']['name'][0])) {

            $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-central-1',
            'credentials' => [
            'key' => S3_KEY,
            'secret' => S3_SECRET,
            ],
            ]);

            $bucket = S3_BUCKET;
            $folderPath = "chats/$chat_id/";

            foreach ($_FILES['images']['name'] as $key => $name) {
            $fileTmpName = $_FILES['images']['tmp_name'][$key];
            $fileType = $_FILES['images']['type'][$key];
            $fileSize = $_FILES['images']['size'][$key];
            $fileExt = pathinfo($name, PATHINFO_EXTENSION);

            if (!in_array($fileType, ['image/jpeg', 'image/png'])) continue;
            if ($fileSize > 5 * 1024 * 1024) continue;

            $fileName = time() . '-' . uniqid('', true) . '-' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $filePath = $folderPath . $fileName;

            try {
            $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $filePath,
            'SourceFile' => $fileTmpName,
            'ContentType' => mime_content_type($fileTmpName),
            ]);

            $uploaded_images[] = $result['ObjectURL'];
            } catch (Exception $e) {
            continue;
            }
            }

            $cleanUrls = array_map(fn($url) => str_replace('s3.eu-central-1.amazonaws.com/', '', $url),
            $uploaded_images);

            }

            $final_message = !empty($cleanUrls) ? json_encode($cleanUrls, JSON_UNESCAPED_SLASHES) : $message;

            $inserted_message = $wpdb->insert(
            "{$wpdb->prefix}chat_messages",
            [
            'chat_id' => $chat_id,
            'sender_id' => $user_id,
            'message' => $final_message,
            'is_read' => false,
            'timestamp' => $utcTime
            ]
            );

            if (!$inserted_message) {
            wp_send_json_error(['message' => 'Не вдалося зберегти повідомлення']);
            }

            $table_name = $wpdb->prefix . 'notifications';
            $existing_notification = $wpdb->get_var($wpdb->prepare("
            SELECT id FROM $table_name
            WHERE user_id = %d
            AND type = 'new_message'
            AND status = 'unread'
            LIMIT 1
            ", $recipient_id));

            if (!$existing_notification) {
            add_notification($recipient_id, 'new_message', __("Нові повідомлення у чаті.", 'panterrea_v1'));
            }

            wp_send_json_success([
            'chatId' => $chat_id,
            'message' => $final_message
            ]);
            }

            add_action('wp_ajax_get_chat_history', 'get_chat_history');
            function get_chat_history()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'chat_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!isset($_POST['user_id']) || !isset($_POST['recipient_id']) || !isset($_POST['post_id'])) {
            wp_send_json_error(['error' => 'Недостатньо даних']);
            }

            $user_id = intval($_POST['user_id']);
            $recipient_id = intval($_POST['recipient_id']);
            $post_id = intval($_POST['post_id']);

            if ($user_id == 0 || $recipient_id == 0 || $post_id == 0) {
            wp_send_json_error(['message' => 'Невірні дані']);
            }

            if (!get_user_by('ID', $user_id)) {
            wp_send_json_error(['message' => 'Користувач не знайдений']);
            }
            if (!get_user_by('ID', $recipient_id)) {
            wp_send_json_error(['message' => 'Отримувач не знайдений']);
            }

            $user_name = get_user_meta($user_id, 'name', true);

            $user_initials = (!empty($user_name))
            ? mb_substr($user_name, 0, 1)
            : 'Невідомий';

            $recipient_name = get_user_meta($recipient_id, 'name', true);

            $recipient_full_name = trim("$recipient_name");
            $recipient_initials = (!empty($recipient_name))
            ? mb_substr($recipient_name, 0, 1)
            : 'Невідомий';

            global $wpdb;

            $chat_id = $wpdb->get_var($wpdb->prepare(
            "SELECT chat_id FROM {$wpdb->prefix}chat_sessions
            WHERE ((user1_id = %d AND user2_id = %d) OR (user1_id = %d AND user2_id = %d))
            AND post_id = %d",
            $user_id, $recipient_id, $recipient_id, $user_id, $post_id
            ));

            if (!$chat_id) {
            wp_send_json_success([
            'user_initials' => $user_initials,
            'recipient_name' => [
            'full_name' => !empty($recipient_full_name) ? $recipient_full_name : 'Невідомий',
            'initials' => $recipient_initials
            ],
            'messages' => []
            ]);
            }

            $deleted_at = $wpdb->get_var($wpdb->prepare(
            "SELECT deleted_at FROM {$wpdb->prefix}chat_deleted
            WHERE chat_id = %d AND user_id = %d",
            $chat_id, $user_id
            ));

            $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}chat_messages
            SET is_read = 1
            WHERE chat_id = %d AND sender_id = %d AND is_read = 0",
            $chat_id, $recipient_id
            ));

            if ($deleted_at) {
            $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT sender_id, message, timestamp, is_read
            FROM {$wpdb->prefix}chat_messages
            WHERE chat_id = %d AND timestamp > %s
            ORDER BY timestamp ASC",
            $chat_id, $deleted_at
            ), ARRAY_A);
            } else {
            $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT sender_id, message, timestamp, is_read
            FROM {$wpdb->prefix}chat_messages
            WHERE chat_id = %d ORDER BY timestamp ASC",
            $chat_id
            ), ARRAY_A);
            }

            $formatted_messages = [];
            foreach ($messages as $msg) {
            $sender_id = $msg['sender_id'];
            $sender = ($sender_id == $user_id) ? $user_initials : $recipient_initials;

            $formatted_messages[] = [
            'sender_id' => $sender_id,
            'sender' => $sender,
            'content' => esc_html($msg['message']),
            'timestamp' => $msg['timestamp'],
            'is_read' => (bool)$msg['is_read']
            ];
            }

            wp_send_json_success([
            'user_initials' => $user_initials,
            'recipient_name' => [
            'full_name' => !empty($recipient_full_name) ? $recipient_full_name : 'Невідомий',
            'initials' => $recipient_initials
            ],
            'messages' => $formatted_messages
            ]);
            }

            add_action('wp_ajax_mark_messages_read', 'mark_messages_read');
            function mark_messages_read()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'chat_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!isset($_POST['sender_id']) || !isset($_POST['post_id'])) {
            wp_send_json_error(['error' => 'Недостатньо даних']);
            }

            $user_id = get_current_user_id();
            $post_id = intval($_POST['post_id']);
            $sender_id = intval($_POST['sender_id']);

            if ($sender_id == 0 || $post_id == 0) {
            wp_send_json_error(['message' => 'Невірні дані']);
            }

            if (!get_user_by('ID', $sender_id)) {
            wp_send_json_error(['message' => 'Відправник не знайдений']);
            }

            if (get_post_status($post_id) === false) {
            wp_send_json_error(['message' => 'Оголошення не знайдено']);
            }

            global $wpdb;

            $chat_id = $wpdb->get_var($wpdb->prepare(
            "SELECT chat_id FROM {$wpdb->prefix}chat_sessions
            WHERE post_id = %d AND (user1_id = %d AND user2_id = %d OR user1_id = %d AND user2_id = %d)",
            $post_id, $user_id, $sender_id, $sender_id, $user_id
            ));

            if (!$chat_id) {
            wp_send_json_error(['message' => 'Чат не знайдено']);
            }

            $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}chat_messages
            SET is_read = 1
            WHERE chat_id = %d AND sender_id = %d AND is_read = 0",
            $chat_id, $sender_id
            ));

            wp_send_json_success(['message' => 'Повідомлення позначено як прочитані']);
            }

            add_action('wp_ajax_block_chat_user', 'block_chat_user');
            function block_chat_user()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'chat_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!isset($_POST['blocked_id'])) {
            wp_send_json_error(['error' => 'Недостатньо даних']);
            }

            global $wpdb;
            $blocker_id = get_current_user_id();
            $blocked_id = isset($_POST['blocked_id']) ? intval($_POST['blocked_id']) : 0;

            if (!$blocked_id || $blocker_id === $blocked_id) {
            wp_send_json_error(['message' => 'Некоректний користувач.']);
            }

            $table_name = "{$wpdb->prefix}chat_blocklist";

            $is_blocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE blocker_id = %d AND blocked_id = %d",
            $blocker_id, $blocked_id
            ));

            if ($is_blocked) {
            $wpdb->delete($table_name, ['blocker_id' => $blocker_id, 'blocked_id' => $blocked_id], ['%d', '%d']);

            if ($wpdb->last_error) {
            wp_send_json_error(['message' => 'Помилка БД при розблокуванні.']);
            }

            wp_send_json_success([
            'message' => __("Користувач розблокований.", 'panterrea_v1'),
            'is_blocked' => false
            ]);
            } else {
            $wpdb->insert($table_name, [
            'blocker_id' => $blocker_id,
            'blocked_id' => $blocked_id
            ], ['%d', '%d']);

            if ($wpdb->last_error) {
            wp_send_json_error(['message' => 'Помилка БД при блокуванні.']);
            }

            wp_send_json_success([
            'message' => __("Користувач заблокований.", 'panterrea_v1'),
            'is_blocked' => true
            ]);
            }
            }

            add_action('wp_ajax_delete_chat', 'delete_chat');
            function delete_chat()
            {
            $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
            if (!$security || !wp_verify_nonce($security, 'chat_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            }

            if (!isset($_POST['chat_id'])) {
            wp_send_json_error(['error' => 'Недостатньо даних']);
            }

            global $wpdb;
            $user_id = get_current_user_id();
            $chat_id = intval($_POST['chat_id']);

            $chat = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}chat_sessions WHERE chat_id = %d AND (user1_id = %d OR user2_id
            = %d)",
            $chat_id, $user_id, $user_id)
            );

            if (!$chat) {
            wp_send_json_error(['message' => 'Чат не знайдено або у вас немає прав для його видалення.']);
            }

            $existing_delete = $wpdb->get_var(
            $wpdb->prepare(
            "SELECT deleted_at FROM {$wpdb->prefix}chat_deleted
            WHERE chat_id = %d AND user_id = %d",
            $chat_id, $user_id
            )
            );

            if ($existing_delete) {
            $updated = $wpdb->update(
            "{$wpdb->prefix}chat_deleted",
            ['deleted_at' => current_time('mysql')],
            ['chat_id' => $chat_id, 'user_id' => $user_id],
            ['%s'],
            ['%d', '%d']
            );

            if ($updated === false) {
            wp_send_json_error(['message' => 'Помилка при оновленні чату.']);
            }
            } else {
            $wpdb->insert(
            "{$wpdb->prefix}chat_deleted",
            ['chat_id' => $chat_id, 'user_id' => $user_id, 'deleted_at' => current_time('mysql')],
            ['%d', '%d', '%s']
            );

            if ($wpdb->last_error) {
            wp_send_json_error(['message' => 'Помилка БД']);
            }
            }

            wp_send_json_success([
            'message' => __("Чат видалено.", 'panterrea_v1')
            ]);
            }

            /**
            * Notification
            */

            add_action('after_switch_theme', 'create_notification_tables');
            function create_notification_tables()
            {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            $table_notifications = $wpdb->prefix . 'notifications';
            $table_users = $wpdb->prefix . 'users';

            $sql_notifications = "CREATE TABLE IF NOT EXISTS $table_notifications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES $table_users(ID) ON DELETE CASCADE
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql_notifications);
            }

            function add_notification($user_id, $type, $message) {
            global $wpdb;

            $table_name = $wpdb->prefix . 'notifications';

            $result = $wpdb->insert(
            $table_name,
            [
            'user_id' => intval($user_id),
            'type' => sanitize_text_field($type),
            'message' => sanitize_textarea_field($message),
            'status' => 'unread',
            'created_at' => current_time('mysql', true)
            ],
            ['%d', '%s', '%s', '%s', '%s']
            );

            $excluded_types = ['user_registered', 'email_verified'];

            $receive_email_notifications = get_user_meta($user_id, 'email_notify_' . $type, true) === '1';

            if ($result && !in_array($type, $excluded_types, true) && $receive_email_notifications) {
            try {
            $user = get_user_by('id', $user_id);
            $email = $user->user_email;

            $email_subjects = [
            'password_reset' => __('Пароль відновлено', 'panterrea_v1'),
            'create_ad' => __('Оголошення опубліковано', 'panterrea_v1'),
            'delete_ad' => __('Оголошення видалено', 'panterrea_v1'),
            'boost_ad' => __('Оголошення рекламується', 'panterrea_v1'),
            'new_message' => __('Нове повідомлення у чатах', 'panterrea_v1'),
            'boost_expiring' => __('Реклама оголошення закінчується', 'panterrea_v1'),
            'boost_expired' => __('Реклама оголошення закінчилась', 'panterrea_v1'),
            'ad_pending' => __('Оголошення очікує модерації', 'panterrea_v1'),
            'ad_approved' => __('Оголошення схвалено', 'panterrea_v1'),
            'ad_rejected' => __('Оголошення відхилено', 'panterrea_v1'),
            ];

            $subject = 'Panterrea: ' . $email_subjects[$type] ?? __('Panterrea: Нова нотифікація', 'panterrea_v1');

            $message = render_template(EMAIL_TEMPLATE_NOTIFICATION, [
            'display_name' => get_user_meta($user_id, 'name', true),
            'display_message' => sanitize_textarea_field($message),
            'display_title' => $email_subjects[$type]
            ]);

            $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: <customer@panterrea.com>'
                ];

                if (!wp_mail($email, $subject, $message, $headers)) {
                throw new Exception('Unable to send a confirmation email.');
                }
                } catch (Exception $e) {
                error_log('Error sending a confirmation email: ' . $e->getMessage());
                }
                }

                return (bool) $result;
                }

                add_action('wp_ajax_mark_notifications_read', 'mark_notifications_read');
                function mark_notifications_read()
                {
                $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                if (!$security || !wp_verify_nonce($security, 'notification_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce.']);
                }

                if (!is_user_logged_in()) {
                wp_send_json_error(['message' => 'Користувач не знайдений.']);
                }

                global $wpdb;
                $user_id = get_current_user_id();
                $table_name = $wpdb->prefix . 'notifications';

                $updated = $wpdb->update(
                $table_name,
                ['status' => 'read'],
                ['user_id' => $user_id, 'status' => 'unread'],
                ['%s'],
                ['%d', '%s']
                );

                if ($updated === false) {
                wp_send_json_error(['message' => 'Помилка оновлення нотифікацій.']);
                }

                wp_send_json_success(['message' => __('Нотифікації успішно прочитані.', 'panterrea_v1')]);
                }

                add_action('wp_ajax_delete_all_notifications', 'delete_all_notifications');
                function delete_all_notifications()
                {
                $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                if (!$security || !wp_verify_nonce($security, 'notification_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce.']);
                }

                if (!is_user_logged_in()) {
                wp_send_json_error(['message' => 'Користувач не знайдений.']);
                }

                global $wpdb;
                $user_id = get_current_user_id();
                $table_name = $wpdb->prefix . 'notifications';

                $deleted = $wpdb->delete($table_name, ['user_id' => $user_id], ['%d']);

                if ($deleted === false) {
                wp_send_json_error(['message' => 'Помилка при видаленні нотифікацій.']);
                }

                wp_send_json_success(['message' => __('Всі нотифікації видалені.', 'panterrea_v1')]);
                }

                add_action('wp_ajax_toggle_email_notification_status', 'toggle_email_notification_status');
                function toggle_email_notification_status() {
                $security = sanitize_text_field($_POST['security']);
                $type = sanitize_text_field($_POST['type']);

                if (!$security || !wp_verify_nonce($security, 'notification_nonce')) {
                wp_send_json_error(['message' => 'Invalid nonce.']);
                }

                if (!is_user_logged_in()) {
                wp_send_json_error(['message' => 'Необхідно увійти в систему.']);
                }

                $valid_types = ['password_reset', 'create_ad', 'delete_ad', 'boost_ad', 'new_message', 'boost_expiring',
                'boost_expired'];

                if (!in_array($type, $valid_types, true)) {
                wp_send_json_error(['message' => 'Invalid type.']);
                }

                $user_id = get_current_user_id();
                $meta_key = 'email_notify_' . $type;

                $current_status = get_user_meta($user_id, $meta_key, true);
                $new_status = $current_status === '1' ? '0' : '1';

                update_user_meta($user_id, $meta_key, $new_status);

                wp_send_json_success(['is_active' => $new_status]);
                }
                add_action('wp_ajax_toggle_email_notification_type', 'toggle_email_notification_type');

                /**
                * Contact Form
                */

                add_action('wp_ajax_contact_form', 'contact_form');
                add_action('wp_ajax_nopriv_contact_form', 'contact_form');

                function contact_form()
                {
                $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                $formDataRaw = sanitize_text_field(filter_input(INPUT_POST, 'formData'));

                if (!$security || !wp_verify_nonce($security, 'contact_nonce')) {
                wp_send_json_error(['message' => 'Invalid request (nonce).']);
                }

                if (!$formDataRaw) {
                wp_send_json_error(['message' => 'No data are available.']);
                }

                $formData = json_decode(stripslashes($formDataRaw), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Error JSON: ' . json_last_error_msg());
                wp_send_json_error(['message' => 'Incorrect data.']);
                }

                $requiredFields = ['contactName', 'contactEmail', 'theme', 'contactMessage'];
                foreach ($requiredFields as $field) {
                if (empty($formData[$field])) {
                wp_send_json_error(['message' => "The {$field} field is required."]);
                }
                }

                $validator = new FormValidator();

                $validator->addRules('contactName', [FormValidator::rules()['isNotEmpty'],
                FormValidator::rules()['isAlpha']]);
                $validator->addRules('contactSurname', [FormValidator::rules()['isNotEmpty'],
                FormValidator::rules()['isAlpha']]);
                $validator->addRules('contactEmail', [FormValidator::rules()['isNotEmpty'],
                FormValidator::rules()['isEmail']]);
                /*$validator->addRules('contactPhone', [FormValidator::rules()['isNotEmpty'],
                FormValidator::rules()['isPhone']]);*/
                $validator->addRules('contactMessage', [FormValidator::rules()['isNotEmpty']]);
                $validator->addRules('theme', [FormValidator::rules()['isNotEmpty']]);

                $validationResult = $validator->validate($formData);

                if (!$validationResult['success']) {
                wp_send_json_error($validationResult);
                }

                $formData = sanitize_form_data($formData);

                $post_id = wp_insert_post([
                'post_type' => 'contact_form',
                'post_title' => sanitize_text_field($formData['contactName']) . ' ' .
                sanitize_text_field($formData['contactSurname']),
                'post_content' => sanitize_textarea_field($formData['contactMessage']),
                'post_status' => 'publish',
                ]);

                if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, 'email', sanitize_email($formData['contactEmail']));
                update_post_meta($post_id, 'phone', sanitize_text_field($formData['contactPhone']));
                update_post_meta($post_id, 'theme', sanitize_text_field($formData['theme']));
                }

                send_contact_email_to_admin($formData);

                wp_send_json_success(['message' => 'Email sent!']);
                }

                function send_contact_email_to_admin($formData)
                {
                try {
                $adminEmail = get_option('admin_email');

                if (!is_email($adminEmail)) {
                throw new Exception('Admin email not configured.');
                }

                $message = "
                <h3>Нове повідомлення з форми зворотного зв'язку</h3>
                <p><strong>Ім'я:</strong> {$formData['contactName']}</p>
                <p><strong>Прізвище:</strong> {$formData['contactSurname']}</p>
                <p><strong>Email:</strong> {$formData['contactEmail']}</p>
                <p><strong>Телефон:</strong> {$formData['contactPhone']}</p>
                <p><strong>Тема:</strong> {$formData['theme']}</p>
                <p><strong>Повідомлення:</strong><br>{$formData['message']}</p>
                ";

                $subject = 'Panterrea: Новий запит з сайту';
                $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: Panterrea <customer@panterrea.com>'
                    ];

                    if (!wp_mail($adminEmail, $subject, $message, $headers)) {
                    throw new Exception('Unable to send admin email.');
                    }
                    } catch (Exception $e) {
                    error_log('Error sending admin email: ' . $e->getMessage());
                    }
                    }

                    /**
                    * Forum Post
                    */

                    function handle_forum_post()
                    {
                    $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                    if (!$security || !wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    $currentUserId = get_current_user_id();
                    if (!$currentUserId) {
                    wp_send_json_error(['message' => 'Користувач не авторизований.']);
                    }

                    $postContent = isset($_POST['postContent']) ? wp_kses_post($_POST['postContent']) : '';
                    if (empty($postContent)) {
                    wp_send_json_error(['message' => 'Пост не може бути порожнім.']);
                    }

                    $userName = get_user_meta($currentUserId, 'name', true);
                    $postTitle = $userName . ' — ' . current_time('Y-m-d H:i');

                    $postId = wp_insert_post([
                    'post_title' => $postTitle,
                    'post_content' => $postContent,
                    'post_status' => 'publish',
                    'post_type' => 'post',
                    'post_author' => $currentUserId,
                    ]);

                    if (is_wp_error($postId)) {
                    wp_send_json_error(['message' => 'Не вдалося створити пост.']);
                    }

                    $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => 'eu-central-1',
                    'credentials' => [
                    'key' => S3_KEY,
                    'secret' => S3_SECRET,
                    ],
                    ]);

                    $bucket = S3_BUCKET;
                    $folderPath = "forum/$currentUserId/$postId/";
                    $fileData = [];

                    if (!empty($_FILES['files'])) {
                    foreach ($_FILES['files']['tmp_name'] as $index => $tmpFilePath) {
                    if (is_uploaded_file($tmpFilePath)) {
                    $originalPath = $tmpFilePath;
                    $tmpFilePath = optimize_image_with_imagick($originalPath);

                    $fileExt = pathinfo($_FILES['files']['name'][$index], PATHINFO_EXTENSION);
                    $fileName = time() . '-' . uniqid('', true) . '-' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                    $key = $folderPath . $fileName;

                    try {
                    $result = $s3->putObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'SourceFile' => $tmpFilePath,
                    'ContentType' => mime_content_type($tmpFilePath),
                    ]);

                    $s3Url = $result['ObjectURL'];
                    $cleanUrl = str_replace('s3.eu-central-1.amazonaws.com/', '', $s3Url);

                    $mimeType = mime_content_type($tmpFilePath);
                    $fileType = str_starts_with($mimeType, 'video/') ? 'video/mp4' : 'image';

                    $fileData[] = [
                    'url' => $cleanUrl,
                    'type' => $fileType,
                    ];

                    if ($tmpFilePath !== $originalPath && file_exists($tmpFilePath)) {
                    unlink($tmpFilePath);
                    }
                    } catch (Exception $e) {
                    wp_send_json_error(['message' => 'Помилка завантаження у S3: ' . $e->getMessage()]);
                    }
                    }
                    }
                    }

                    if (!empty($fileData)) {
                    update_field('field_682a380147e78', $fileData, $postId);
                    }

                    update_post_meta($postId, '_is_forum_post', 1);
                    update_post_meta($postId, '_forum_like_count', 0);

                    $raw_cat_ids = [];
                    if (!empty($_POST['post_category_ids']) && is_array($_POST['post_category_ids'])) {
                        $raw_cat_ids = array_map('intval', $_POST['post_category_ids']);
                    } elseif (!empty($_POST['post_category_id'])) {
                        $raw_cat_ids = [(int) $_POST['post_category_id']];
                    }
                    $valid_cat_ids = array_filter($raw_cat_ids, fn($id) => $id > 0 && term_exists($id, 'category'));
                    if (!empty($valid_cat_ids)) {
                        wp_set_post_terms($postId, array_values($valid_cat_ids), 'category');
                    }

                    wp_send_json_success(['message' => 'Пост успішно створено.', 'postId' => $postId]);
                    }

                    add_action('wp_ajax_forum_submit_post', 'handle_forum_post');

                    add_action('template_redirect', function () {
                    if (is_single() && get_post_type() === 'post') {
                    $postId = get_queried_object_id();

                    if (get_post_meta($postId, '_is_forum_post', true)) {
                    wp_redirect(home_url());
                    exit;
                    }
                    }
                    });

                    add_action('wp_ajax_load_forum_posts', 'load_forum_posts');
                    add_action('wp_ajax_nopriv_load_forum_posts', 'load_forum_posts');

                    function load_forum_posts() {
                    $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                    if (!$security || !wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    /*$currentUserId = get_current_user_id();
                    if (!$currentUserId) {
                    wp_send_json_error(['message' => 'Користувач не авторизований.']);
                    }*/

                    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;

                    $sort = 'all';
                    if (!empty($_POST['forum_sort'])) {
                    $sort = panterrea_forum_sanitize_sort(sanitize_key(wp_unslash($_POST['forum_sort'])));
                    } elseif (isset($_POST['only_my']) && $_POST['only_my'] == '1' && is_user_logged_in()) {
                    $sort = 'mine';
                    }

                    $args = [
                    'post_type' => 'post',
                    'posts_per_page' => 10,
                    'paged' => $paged,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => [
                    [
                    'key' => '_is_forum_post',
                    'value' => '1',
                    'compare' => '=',
                    ],
                    ],
                    ];

                    if ($sort === 'mine' && is_user_logged_in()) {
                    $args['author'] = get_current_user_id();
                    }

                    $forum_cat_ids = panterrea_forum_cat_ids_from_post_param();
                    $tax_q = panterrea_forum_tax_query_for_categories($forum_cat_ids);
                    if ($tax_q !== []) {
                    $args['tax_query'] = $tax_q;
                    }

                    if ($sort === 'popular') {
                    $GLOBALS['panterrea_forum_popular_order'] = true;
                    }

                    $query = new WP_Query($args);

                    unset($GLOBALS['panterrea_forum_popular_order']);

                    $has_more = $paged < $query->max_num_pages;
                    header('X-Forum-Has-More: ' . ($has_more ? '1' : '0'));

                    if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                    get_template_part('template-parts/forum-item');
                    endwhile;
                    wp_reset_postdata();
                    endif;

                    wp_die();
                    }

                    add_action('wp_ajax_delete_forum_post', 'delete_forum_post');

                    function delete_forum_post() {
                    $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                    if (!$security || !wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

                    if (!$post_id) {
                    wp_send_json_error(['message' => __('Невірний ID поста', 'panterrea_v1')]);
                    }

                    $post = get_post($post_id);
                    if (!$post || get_post_meta($post_id, '_is_forum_post', true) != 1) {
                    wp_send_json_error(['message' => __('Пост не знайдено', 'panterrea_v1')]);
                    }

                    if ((int) $post->post_author !== get_current_user_id()) {
                    wp_send_json_error(['message' => __('У вас немає прав для видалення цього поста', 'panterrea_v1')]);
                    }

                    wp_delete_post($post_id, true);

                    $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => 'eu-central-1',
                    'credentials' => [
                    'key' => S3_KEY,
                    'secret' => S3_SECRET,
                    ],
                    ]);

                    $folderPath = "forum/$post->post_author/$post_id/";

                    try {
                    $objects = $s3->listObjectsV2([
                    'Bucket' => S3_BUCKET,
                    'Prefix' => $folderPath,
                    ]);

                    if (!empty($objects['Contents'])) {
                    $deleteList = [];

                    foreach ($objects['Contents'] as $object) {
                    $deleteList[] = ['Key' => $object['Key']];
                    }

                    if (!empty($deleteList)) {
                    $s3->deleteObjects([
                    'Bucket' => S3_BUCKET,
                    'Delete' => ['Objects' => $deleteList],
                    ]);
                    }
                    }
                    } catch (Exception $e) {
                    error_log('S3 delete error for post ' . $post_id . ': ' . $e->getMessage());
                    }

                    wp_send_json_success(['message' => __('Публікацію видалено', 'panterrea_v1')]);
                    }

                    add_action('wp_ajax_forum_edit_post', 'forum_edit_post');

                    function forum_edit_post() {
                    $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                    if (!$security || !wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    $postId = intval($_POST['post_id'] ?? 0);
                    if (!$postId || get_post_type($postId) !== 'post') {
                    wp_send_json_error(['message' => 'Некоректний пост.']);
                    }

                    $currentUserId = get_current_user_id();
                    if (!$currentUserId) {
                    wp_send_json_error(['message' => 'Користувач не авторизований.']);
                    }

                    $postAuthorId = (int)get_post_field('post_author', $postId);
                    if ($postAuthorId !== $currentUserId) {
                    wp_send_json_error(['message' => 'Ви не є автором цього поста.']);
                    }

                    $postContent = isset($_POST['postContent']) ? wp_kses_post($_POST['postContent']) : '';
                    if (empty($postContent)) {
                    wp_send_json_error(['message' => 'Пост не може бути порожнім.']);
                    }

                    $updateResult = wp_update_post([
                    'ID' => $postId,
                    'post_content' => $postContent,
                    ]);

                    if (is_wp_error($updateResult)) {
                    wp_send_json_error(['message' => 'Не вдалося оновити пост.']);
                    }

                    $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => 'eu-central-1',
                    'credentials' => [
                    'key' => S3_KEY,
                    'secret' => S3_SECRET,
                    ],
                    ]);

                    $bucket = S3_BUCKET;
                    $folderPath = "forum/$currentUserId/$postId/";

                    $existingImages = isset($_POST['existing'])
                    ? (is_array($_POST['existing']) ? $_POST['existing'] : [$_POST['existing']])
                    : [];

                    try {
                    $objects = $s3->listObjects([
                    'Bucket' => $bucket,
                    'Prefix' => $folderPath,
                    ]);
                    } catch (Exception $e) {
                    wp_send_json_error(['message' => 'Помилка отримання файлів S3: ' . $e->getMessage()]);
                    }

                    $existingFiles = [];
                    if (!empty($objects['Contents'])) {
                    foreach ($objects['Contents'] as $object) {
                    $existingFiles[] = $object['Key'];
                    }
                    }

                    $filesToKeep = [];
                    $fileData = [];

                    foreach ($existingImages as $imageUrl) {
                    $parsedUrl = parse_url($imageUrl);
                    if (!isset($parsedUrl['path'])) continue;
                    $key = ltrim($parsedUrl['path'], '/');

                    if (in_array($key, $existingFiles)) {
                    $filesToKeep[] = $key;

                    $ext = pathinfo($key, PATHINFO_EXTENSION);
                    $fileType = in_array(strtolower($ext), ['mp4', 'mov', 'avi', 'webm']) ? 'video/mp4' : 'image';

                    $fileData[] = [
                    'url' => $imageUrl,
                    'type' => $fileType,
                    ];
                    }
                    }

                    if (!empty($_FILES['files']) && is_array($_FILES['files']['tmp_name'])) {
                    foreach ($_FILES['files']['tmp_name'] as $index => $tmpFilePath) {
                    if (!is_uploaded_file($tmpFilePath)) continue;

                    $originalPath = $tmpFilePath;
                    $tmpFilePath = optimize_image_with_imagick($originalPath);

                    $fileExt = pathinfo($_FILES['files']['name'][$index], PATHINFO_EXTENSION);
                    $fileName = time() . '-' . uniqid('', true) . '-' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                    $key = $folderPath . $fileName;

                    try {
                    $result = $s3->putObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'SourceFile' => $tmpFilePath,
                    'ContentType' => mime_content_type($tmpFilePath),
                    ]);

                    $s3Url = $result['ObjectURL'];
                    $cleanUrl = str_replace('s3.eu-central-1.amazonaws.com/', '', $s3Url);

                    $mimeType = mime_content_type($tmpFilePath);
                    $fileType = str_starts_with($mimeType, 'video/') ? 'video/mp4' : 'image';

                    $fileData[] = [
                    'url' => $cleanUrl,
                    'type' => $fileType,
                    ];

                    $filesToKeep[] = $key;

                    if ($tmpFilePath !== $originalPath && file_exists($tmpFilePath)) {
                    unlink($tmpFilePath);
                    }
                    } catch (Exception $e) {
                    wp_send_json_error(['message' => 'Помилка завантаження у S3: ' . $e->getMessage()]);
                    }
                    }
                    }

                    $filesToDelete = array_diff($existingFiles, $filesToKeep);
                    foreach ($filesToDelete as $fileKey) {
                    try {
                    $s3->deleteObject([
                    'Bucket' => $bucket,
                    'Key' => $fileKey,
                    ]);
                    } catch (Exception $e) {
                    error_log('Помилка видалення з S3: ' . $e->getMessage());
                    }
                    }

                    if (!empty($fileData)) {
                    update_field('field_682a380147e78', $fileData, $postId);
                    } else {
                    update_field('field_682a380147e78', [], $postId);
                    }

                    $raw_cat_ids_edit = [];
                    if (!empty($_POST['post_category_ids']) && is_array($_POST['post_category_ids'])) {
                        $raw_cat_ids_edit = array_map('intval', $_POST['post_category_ids']);
                    } elseif (!empty($_POST['post_category_id'])) {
                        $raw_cat_ids_edit = [(int) $_POST['post_category_id']];
                    }
                    $valid_cat_ids_edit = array_filter($raw_cat_ids_edit, fn($id) => $id > 0 && term_exists($id, 'category'));
                    if (!empty($valid_cat_ids_edit)) {
                        wp_set_post_terms($postId, array_values($valid_cat_ids_edit), 'category');
                    }

                    wp_send_json_success(['message' => 'Пост успішно оновлено.', 'postId' => $postId]);
                    }

                    add_action('wp_ajax_add_comment', 'handle_ajax_add_comment');

                    function handle_ajax_add_comment() {
                    $security = sanitize_text_field(filter_input(INPUT_POST, 'security'));
                    if (!$security || !wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    if (!is_user_logged_in()) {
                    wp_send_json_error(['message' => __('Потрібно увійти, щоб коментувати.', 'panterrea_v1')]);
                    }

                    if (!isset($_POST['post_id']) || !isset($_POST['comment'])) {
                    wp_send_json_error(['message' => __('Некоректні дані.', 'panterrea_v1')]);
                    }

                    $post_id = intval($_POST['post_id']);
                    $comment_text = sanitize_textarea_field($_POST['comment']);

                    if (empty($comment_text)) {
                    wp_send_json_error(['message' => __('Коментар не може бути порожнім.', 'panterrea_v1')]);
                    }

                    if (!get_post($post_id)) {
                    wp_send_json_error(['message' => __('Пост не знайдено.', 'panterrea_v1')]);
                    }

                    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;

                    $commentdata = [
                    'comment_post_ID' => $post_id,
                    'comment_content' => $comment_text,
                    'comment_type' => '',
                    'user_id' => get_current_user_id(),
                    'comment_parent' => $parent_id,
                    'comment_author' => get_user_meta(get_current_user_id(), 'name', true),
                    'comment_author_email' => wp_get_current_user()->user_email,
                    ];

                    $comment_id = wp_insert_comment($commentdata);

                    if ($comment_id) {
                    $comment = get_comment($comment_id);

                    ob_start();
                    get_template_part('template-parts/comment-item', null, [
                    'comment' => $comment,
                    'current_user_id' => get_current_user_id(),
                    'extra_class' => '',
                    ]);
                    $comment_html = ob_get_clean();

                    wp_send_json_success([
                    'message' => __('Коментар додано успішно.', 'panterrea_v1'),
                    'comment_html' => $comment_html,
                    'comment_id' => $comment_id,
                    'parent_id' => $parent_id,
                    ]);
                    } else {
                    wp_send_json_error(['message' => __('Помилка при збереженні коментаря.', 'panterrea_v1')]);
                    }
                    }

                    add_action('wp_ajax_delete_forum_comment', 'delete_forum_comment');

                    function delete_forum_comment() {
                    $security = sanitize_text_field($_POST['security'] ?? '');

                    if (!$security || !wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

                    if (!$comment_id) {
                    wp_send_json_error(['message' => __('Невірний ID коментаря', 'panterrea_v1')]);
                    }

                    $comment = get_comment($comment_id);

                    if (!$comment) {
                    wp_send_json_error(['message' => __('Коментар не знайдено', 'panterrea_v1')]);
                    }

                    if ((int) $comment->user_id !== get_current_user_id()) {
                    wp_send_json_error(['message' => __('У вас немає прав для видалення цього коментаря',
                    'panterrea_v1')]);
                    }

                    delete_comment_with_children($comment_id);

                    wp_send_json_success(['message' => __('Коментар видалено', 'panterrea_v1')]);
                    }

                    function delete_comment_with_children($comment_id) {
                    $child_comments = get_comments([
                    'parent' => $comment_id,
                    'status' => 'approve',
                    'orderby' => 'comment_date_gmt',
                    'order' => 'ASC',
                    'hierarchical' => 'threaded',
                    'number' => 0,
                    ]);

                    foreach ($child_comments as $child) {
                    delete_comment_with_children($child->comment_ID);
                    }

                    wp_delete_comment($comment_id, true);
                    }

                    add_action('wp_ajax_edit_comment', 'edit_comment_callback');
                    function edit_comment_callback() {
                    $security = sanitize_text_field($_POST['security']);
                    if (!wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
                    $content = sanitize_text_field($_POST['comment']);

                    if (!$comment_id || !$content) {
                    wp_send_json_error(['message' => 'Некоректні дані.']);
                    }

                    $comment = get_comment($comment_id);
                    if (!$comment || $comment->user_id != get_current_user_id()) {
                    wp_send_json_error(['message' => 'Ви не маєте прав редагувати цей коментар.']);
                    }

                    wp_update_comment([
                    'comment_ID' => $comment_id,
                    'comment_content' => $content,
                    ]);

                    wp_send_json_success(['message' => 'Коментар оновлено.']);
                    }

                    add_action('wp_ajax_search_forum_posts', 'search_forum_posts_callback');
                    add_action('wp_ajax_nopriv_search_forum_posts', 'search_forum_posts_callback');

                    function search_forum_posts_callback() {
                    $security = sanitize_text_field($_POST['security']);
                    if (!wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
                    $current_user_id = get_current_user_id();

                    $sort = 'all';
                    if (!empty($_POST['forum_sort'])) {
                    $sort = panterrea_forum_sanitize_sort(sanitize_key(wp_unslash($_POST['forum_sort'])));
                    } elseif (isset($_POST['only_my']) && $_POST['only_my'] == '1' && $current_user_id) {
                    $sort = 'mine';
                    }

                    // Normalize and stem the query for Ukrainian language support
                    $query_normalized = normalize_ukrainian_keyboard($query);
                    $query_stem = ukrainian_stem($query_normalized);

                    $args = [
                    'post_type' => 'post',
                    'posts_per_page' => -1,
                    's' => $query_normalized,
                    'meta_query' => [
                    [
                    'key' => '_is_forum_post',
                    'value' => 1,
                    ],
                    ],
                    ];

                    if ($sort === 'mine' && $current_user_id) {
                    $args['author'] = $current_user_id;
                    }

                    $forum_cat_ids = panterrea_forum_cat_ids_from_post_param();
                    $tax_q = panterrea_forum_tax_query_for_categories($forum_cat_ids);
                    if ($tax_q !== []) {
                    $args['tax_query'] = $tax_q;
                    }

                    $posts = new WP_Query($args);

                    ob_start();

                    if ($posts->have_posts()) {
                    $query_lower = mb_strtolower($query_normalized, 'UTF-8');
                    $stem_lower = mb_strtolower($query_stem, 'UTF-8');
                    $matching_ids = [];

                    while ($posts->have_posts()) {
                    $posts->the_post();

                    $post_author_id = get_the_author_meta('ID');
                    $post_author_name = mb_strtolower(get_user_meta($post_author_id, 'name', true) ?: '', 'UTF-8');
                    $post_content = mb_strtolower(get_the_content(), 'UTF-8');
                    $post_title = mb_strtolower(get_the_title(), 'UTF-8');

                    $matches = false;

                    if (mb_stripos($post_content, $query_lower) !== false ||
                        mb_stripos($post_title, $query_lower) !== false ||
                        mb_stripos($post_author_name, $query_lower) !== false) {
                    $matches = true;
                    }

                    if (!$matches && mb_strlen($stem_lower, 'UTF-8') >= 3) {
                    if (mb_stripos($post_content, $stem_lower) !== false ||
                        mb_stripos($post_title, $stem_lower) !== false) {
                    $matches = true;
                    }
                    }

                    if ($matches) {
                    $matching_ids[] = get_the_ID();
                    }
                    }

                    wp_reset_postdata();

                    if ($sort === 'popular' && $matching_ids !== []) {
                    usort($matching_ids, static function ($a, $b) {
                    $la = panterrea_forum_get_like_count($a);
                    $lb = panterrea_forum_get_like_count($b);
                    if ($la !== $lb) {
                    return $lb <=> $la;
                    }
                    $ca = (int) get_comments_number($a);
                    $cb = (int) get_comments_number($b);
                    if ($ca !== $cb) {
                    return $cb <=> $ca;
                    }

                    return strtotime((string) get_post_field('post_date', $b)) <=> strtotime((string) get_post_field('post_date', $a));
                    });
                    }

                    $found_any = false;
                    foreach ($matching_ids as $pid) {
                    $post_obj = get_post($pid);
                    if (!$post_obj) {
                    continue;
                    }
                    setup_postdata($post_obj);
                    get_template_part('template-parts/forum-item');
                    $found_any = true;
                    }
                    wp_reset_postdata();

                    if (!$found_any) {
                    echo '<div class="no-results h6">' . __('Публікації не знайдено.', 'panterrea_v1') . '</div>';
                    }
                    } else {
                    echo '<div class="no-results h6">' . __('Публікації не знайдено.', 'panterrea_v1') . '</div>';
                    }

                    $html = ob_get_clean();

                    wp_send_json_success(['html' => $html]);
                    }

                    add_action('wp_ajax_toggle_post_like', 'toggle_post_like_callback');

                    function toggle_post_like_callback() {
                    $security = sanitize_text_field($_POST['security']);
                    if (!wp_verify_nonce($security, 'forum_nonce')) {
                    wp_send_json_error(['message' => 'Invalid nonce.']);
                    }

                    if (!is_user_logged_in()) {
                    wp_send_json_error(['message' => 'Not logged in.']);
                    }

                    $post_id = intval($_POST['post_id']);
                    $user_id = get_current_user_id();

                    $likes = get_post_meta($post_id, '_forum_likes', true);
                    $likes = is_array($likes) ? $likes : [];

                    if (in_array($user_id, $likes)) {
                    $likes = array_diff($likes, [$user_id]);
                    $liked = false;
                    } else {
                    $likes[] = $user_id;
                    $liked = true;
                    }

                    update_post_meta($post_id, '_forum_likes', array_values($likes));
                    panterrea_forum_sync_like_count($post_id);

                    wp_send_json_success([
                    'liked' => $liked,
                    'count' => count($likes),
                    ]);
                    }

                    add_action('pre_comment_on_post', function () {
                    if (!is_user_logged_in()) {
                    wp_die(__('Коментування доступне лише для авторизованих користувачів.', 'panterrea_v1'));
                    }
                    });

                    add_filter('rest_allow_anonymous_comments', '__return_false');

                    /**
                    * Structured Data (JSON-LD): Organization, BreadcrumbList, and Product
                    */
                    function psd_get_site_logo_url()
                    {
                    $custom_logo_id = get_theme_mod('custom_logo');
                    if ($custom_logo_id) {
                    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
                    if ($logo && isset($logo[0])) {
                    return esc_url($logo[0]);
                    }
                    }

                    if (function_exists('get_site_icon_url')) {
                    $icon = get_site_icon_url();
                    if ($icon) {
                    return esc_url($icon);
                    }
                    }

                    return '';
                    }

                    function psd_output_organization_schema()
                    {
                    $data = array(
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => get_bloginfo('name'),
                    'url' => home_url('/'),
                    );

                    $logo = psd_get_site_logo_url();
                    if ($logo) {
                    $data['logo'] = array(
                    '@type' => 'ImageObject',
                    'url' => $logo,
                    );
                    }

                    echo "\n" . '<script type="application/ld+json">
                    ' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '
                    </script>' . "\n";
                    }

                    function psd_get_breadcrumb_items()
                    {
                    $items = array();
                    $items[] = array('name' => get_bloginfo('name'), 'item' => home_url('/'));

                    if (is_singular('page')) {
                    global $post;
                    $ancestors = array_reverse(get_post_ancestors($post));
                    foreach ($ancestors as $ancestor_id) {
                    $items[] = array('name' => get_the_title($ancestor_id), 'item' => get_permalink($ancestor_id));
                    }
                    $items[] = array('name' => get_the_title($post), 'item' => get_permalink($post));
                    } elseif (is_singular('post')) {
                    $cats = get_the_category();
                    if (!empty($cats)) {
                    $primary = $cats[0];
                    $ancestors = array_reverse(get_ancestors($primary->term_id, 'category'));
                    foreach ($ancestors as $term_id) {
                    $term = get_term($term_id, 'category');
                    if ($term && !is_wp_error($term)) {
                    $items[] = array('name' => $term->name, 'item' => get_term_link($term));
                    }
                    }
                    $items[] = array('name' => $primary->name, 'item' => get_term_link($primary));
                    }
                    $items[] = array('name' => get_the_title(), 'item' => get_permalink());
                    } elseif (function_exists('is_product') && is_product()) {
                    if (function_exists('wc_get_page_id')) {
                    $shop_page_id = wc_get_page_id('shop');
                    if ($shop_page_id && $shop_page_id > 0) {
                    $items[] = array('name' => get_the_title($shop_page_id), 'item' => get_permalink($shop_page_id));
                    }
                    }
                    $terms = wp_get_post_terms(get_the_ID(), 'product_cat');
                    if (!is_wp_error($terms) && !empty($terms)) {
                    $primary = $terms[0];
                    $ancestors = array_reverse(get_ancestors($primary->term_id, 'product_cat'));
                    foreach ($ancestors as $term_id) {
                    $term = get_term($term_id, 'product_cat');
                    if ($term && !is_wp_error($term)) {
                    $items[] = array('name' => $term->name, 'item' => get_term_link($term));
                    }
                    }
                    $items[] = array('name' => $primary->name, 'item' => get_term_link($primary));
                    }
                    $items[] = array('name' => get_the_title(), 'item' => get_permalink());
                    } elseif (is_tax('catalog_category')) {
                    $term = get_queried_object();
                    if ($term && !is_wp_error($term)) {
                    $catalog_url = defined('URL_CATALOG') ? URL_CATALOG : home_url('/catalog');
                    $items[] = array('name' => __('Каталог', 'panterrea_v1'), 'item' => $catalog_url);
                    $ancestors = array_reverse(get_ancestors($term->term_id, 'catalog_category'));
                    foreach ($ancestors as $term_id) {
                    $anc = get_term($term_id, 'catalog_category');
                    if ($anc && !is_wp_error($anc)) {
                    $link = function_exists('panterrea_get_catalog_category_link') ? panterrea_get_catalog_category_link($anc) : get_term_link($anc);
                    $items[] = array('name' => $anc->name, 'item' => $link);
                    }
                    }
                    $items[] = array('name' => $term->name, 'item' => get_term_link($term));
                    }
                    } else {
                    if (is_singular()) {
                    $post_type = get_post_type_object(get_post_type());
                    if ($post_type && !is_wp_error($post_type) && !empty($post_type->has_archive)) {
                    $items[] = array('name' => $post_type->labels->name, 'item' =>
                    get_post_type_archive_link($post_type->name));
                    }
                    $items[] = array('name' => get_the_title(), 'item' => get_permalink());
                    }
                    }

                    return $items;
                    }

                    function psd_output_breadcrumb_schema()
                    {
                    if (is_front_page()) {
                    return;
                    }
                    if (!is_singular() && !is_tax('catalog_category')) {
                    return;
                    }

                    $items = psd_get_breadcrumb_items();
                    if (count($items) < 2) { return; } $itemListElement=array(); $position=1; foreach ($items as $item)
                        { $itemListElement[]=array( '@type'=> 'ListItem',
                        'position'=> $position++,
                        'name' => wp_strip_all_tags($item['name']),
                        'item' => esc_url($item['item']),
                        );
                        }

                        $data = array(
                        '@context' => 'https://schema.org',
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => $itemListElement,
                        );

                        echo "\n" . '<script type="application/ld+json">
                        ' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '
                        </script>' . "\n";
                        }

                        function psd_output_product_schema()
                        {
                        if (!function_exists('is_product') || !is_product()) {
                        return;
                        }

                        if (!function_exists('wc_get_product')) {
                        return;
                        }

                        $product = wc_get_product(get_the_ID());
                        if (!$product) {
                        return;
                        }

                        $images = array();
                        $image_id = $product->get_image_id();
                        if ($image_id) {
                        $image_url = wp_get_attachment_image_url($image_id, 'full');
                        if ($image_url) {
                        $images[] = $image_url;
                        }
                        }
                        $gallery_ids = method_exists($product, 'get_gallery_image_ids') ?
                        $product->get_gallery_image_ids() : array();
                        if (!empty($gallery_ids)) {
                        foreach ($gallery_ids as $gid) {
                        $url = wp_get_attachment_image_url($gid, 'full');
                        if ($url) {
                        $images[] = $url;
                        }
                        }
                        }
                        $images = array_values(array_unique(array_filter($images)));

                        $availability_map = array(
                        'instock' => 'https://schema.org/InStock',
                        'outofstock' => 'https://schema.org/OutOfStock',
                        'onbackorder' => 'https://schema.org/PreOrder',
                        );
                        $stock_status = method_exists($product, 'get_stock_status') ? $product->get_stock_status() :
                        'instock';
                        $availability = isset($availability_map[$stock_status]) ? $availability_map[$stock_status] :
                        'https://schema.org/InStock';

                        $offer = array(
                        '@type' => 'Offer',
                        'price' => (string) $product->get_price(),
                        'priceCurrency' => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() :
                        get_option('woocommerce_currency', 'USD'),
                        'availability' => $availability,
                        'url' => get_permalink($product->get_id()),
                        );
                        if ($product->get_sku()) {
                        $offer['sku'] = $product->get_sku();
                        }
                        if ($product->is_on_sale() && method_exists($product, 'get_date_on_sale_to') &&
                        $product->get_date_on_sale_to()) {
                        $offer['priceValidUntil'] = $product->get_date_on_sale_to()->date('c');
                        }

                        $data = array(
                        '@context' => 'https://schema.org',
                        '@type' => 'Product',
                        'name' => $product->get_name(),
                        'description' => wp_strip_all_tags($product->get_short_description() ?:
                        $product->get_description()),
                        'image' => $images,
                        'sku' => $product->get_sku(),
                        'url' => get_permalink($product->get_id()),
                        'offers' => $offer,
                        );

                        $brand = '';
                        $brand_taxonomies = array('pa_brand', 'product_brand', 'brand');
                        foreach ($brand_taxonomies as $tax) {
                        $terms = wp_get_post_terms($product->get_id(), $tax);
                        if (!is_wp_error($terms) && !empty($terms)) {
                        $brand = $terms[0]->name;
                        break;
                        }
                        }
                        if ($brand) {
                        $data['brand'] = array(
                        '@type' => 'Brand',
                        'name' => $brand,
                        );
                        }

                        echo "\n" . '<script type="application/ld+json">
                        ' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '
                        </script>' . "\n";
                        }

                        function psd_structured_data_wp_head()
                        {
                        psd_output_organization_schema();
                        psd_output_breadcrumb_schema();
                        psd_output_product_schema();
                        }
                        add_action('wp_head', 'psd_structured_data_wp_head', 99);

                        /**
                        * Get catalog category link — SEO path /catalog/{slug}/
                        *
                        * @param int|object $term Term ID or term object
                        * @return string URL
                        */
                        function panterrea_get_catalog_category_link($term) {
                        $term_link = get_term_link($term, 'catalog_category');
                        if (is_wp_error($term_link)) {
                        return home_url('/catalog/');
                        }
                        return $term_link;
                        }

add_action('init', function () {
    $term = get_term(1, 'category');
    if ($term && ! is_wp_error($term) && $term->name !== 'Інше') {
        wp_update_term(1, 'category', [
            'name' => 'Інше',
            'slug' => 'inhe',
        ]);
    }
});