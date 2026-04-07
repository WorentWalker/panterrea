<?php
function create_custom_post_type()
{
    register_post_type('catalog_post', [
        'labels' => [
            'name' => 'Catalog Posts',
            'singular_name' => 'Catalog Post',
        ],
        'public' => true,
        'publicly_queryable' => true,
        'hierarchical' => true,
        'has_archive' => 'catalog',
        'supports' => ['title', 'editor', 'thumbnail', 'author', 'date'],
        'rewrite' => [
            'slug' => 'catalog',
            'with_front' => false,
            'ep_mask' => EP_PERMALINK,
        ],
    ]);
}

add_action('init', 'create_custom_post_type');

function create_custom_taxonomy()
{
    register_taxonomy('catalog_category', 'catalog_post', [
        'hierarchical' => true,
        'labels' => [
            'name' => 'Catalog Categories',
            'singular_name' => 'Catalog Category',
            'search_items' => 'Search Categories',
            'all_items' => 'All Categories',
            'parent_item' => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item' => 'Edit Category',
            'update_item' => 'Update Category',
            'add_new_item' => 'Add New Category',
            'new_item_name' => 'New Category Name',
            'menu_name' => 'Catalog Categories',
        ],
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => [
            'slug' => 'catalog',
            'with_front' => false,
        ],
    ]);

    register_taxonomy('catalog_tag', 'catalog_post', [
        'hierarchical' => false,
        'labels' => [
            'name' => 'Catalog Tags',
            'singular_name' => 'Catalog Tag',
        ],
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => [
            'slug' => 'catalog_tag',
            'with_front' => false,
        ],
    ]);
}

add_action('init', 'create_custom_taxonomy');

// Register query var for catalog routing
add_filter('query_vars', function ($vars) {
    $vars[] = 'panterrea_catalog_slug';
    return $vars;
});

// Our rule matches FIRST (top) - intercepts /catalog/{slug}/ before taxonomy
add_action('init', 'panterrea_add_catalog_rewrite_rules', 5);
function panterrea_add_catalog_rewrite_rules() {
    add_rewrite_rule('^catalog/([^/]+)/?$', 'index.php?panterrea_catalog_slug=$matches[1]', 'top');
    add_rewrite_rule('^([^/]+)/catalog/([^/]+)/?$', 'index.php?panterrea_catalog_slug=$matches[2]', 'top');
}

// Resolve: category or post
add_filter('request', 'panterrea_catalog_request_resolver', 1, 1);
function panterrea_catalog_request_resolver($query_vars) {
    if (empty($query_vars['panterrea_catalog_slug'])) {
        return $query_vars;
    }
    $slug = $query_vars['panterrea_catalog_slug'];
    unset($query_vars['panterrea_catalog_slug']);
    $term = get_term_by('slug', $slug, 'catalog_category');
    if ($term && !is_wp_error($term)) {
        $query_vars['catalog_category'] = $slug;
        return $query_vars;
    }
    $post = get_page_by_path($slug, OBJECT, 'catalog_post');
    if ($post && $post->post_status === 'publish') {
        $query_vars['name'] = $slug;
        $query_vars['post_type'] = 'catalog_post';
        return $query_vars;
    }
    // Allow author to view their own pending/draft ads (on moderation)
    if (!is_user_logged_in()) {
        return $query_vars;
    }
    $pending_posts = get_posts([
        'name' => $slug,
        'post_type' => 'catalog_post',
        'post_status' => ['pending', 'draft'],
        'posts_per_page' => 1,
        'no_found_rows' => true,
        'suppress_filters' => true,
    ]);
    $post = !empty($pending_posts) ? $pending_posts[0] : null;
    if ($post && (int) $post->post_author === get_current_user_id()) {
        global $panterrea_catalog_pending_for_author;
        $panterrea_catalog_pending_for_author = true;
        $query_vars['p'] = $post->ID;
        $query_vars['post_type'] = 'catalog_post';
        return $query_vars;
    }
    return $query_vars;
}

// Force main query to include pending/draft when author views own ad on moderation
add_action('pre_get_posts', 'panterrea_catalog_pre_get_posts_allow_pending', 1);
function panterrea_catalog_pre_get_posts_allow_pending($query) {
    global $panterrea_catalog_pending_for_author;
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if (empty($panterrea_catalog_pending_for_author)) {
        return;
    }
    if ($query->get('post_type') === 'catalog_post' && $query->get('p')) {
        $query->set('post_status', ['publish', 'pending', 'draft']);
    }
}

// Flush rewrite rules once
add_action('admin_init', 'panterrea_maybe_flush_catalog_rewrites');
function panterrea_maybe_flush_catalog_rewrites() {
    if (!current_user_can('manage_options')) return;
    if (get_option('panterrea_catalog_rewrite_flushed') !== 'catalog-v3') {
        flush_rewrite_rules();
        update_option('panterrea_catalog_rewrite_flushed', 'catalog-v3');
    }
}

function catalog_post_permalink($permalink, $post)
{
    if ($post->post_type === 'catalog_post') {
        $custom_slug = get_post_field('post_name', $post->ID);
        $url = home_url('catalog/' . $custom_slug . '/');
        // Ensure trailing slash is always present
        return rtrim($url, '/') . '/';
    }
    return $permalink;
}

add_filter('post_type_link', 'catalog_post_permalink', 10, 2);

// Additional filter to ensure trailing slashes for catalog_post permalinks
// This runs after other filters to catch any URLs that might have lost their trailing slash
function ensure_catalog_trailing_slash($permalink, $post)
{
    // Handle both post object and post ID
    if (is_numeric($post)) {
        $post = get_post($post);
    }
    
    if ($post && isset($post->post_type) && $post->post_type === 'catalog_post') {
        // Check if URL contains /catalog/ and ensure it has trailing slash
        if (strpos($permalink, '/catalog/') !== false) {
            $permalink = rtrim($permalink, '/') . '/';
        }
    }
    return $permalink;
}

add_filter('post_type_link', 'ensure_catalog_trailing_slash', 20, 2);

function modify_catalog_post_slug_on_save($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (get_post_meta($post_id, '_skip_slug_update', true)) {
        return;
    }

    static $already_saved = [];
    if (in_array($post_id, $already_saved, true)) {
        return;
    }

    $post = get_post($post_id);
    if ($post && $post->post_type === 'catalog_post') {
        $custom_slug = generate_catalog_post_slug($post_id, $post->post_title);

        $already_saved[] = $post_id;

        wp_update_post([
            'ID' => $post_id,
            'post_name' => $custom_slug,
        ]);
    }
}

add_action('save_post', 'modify_catalog_post_slug_on_save');

function generate_catalog_post_slug($post_id, $post_title): string
{
    $post_slug = custom_transliterate($post_title);
    $auth_token = substr(hash('sha256', $post_id ), 0, 7);
    return $post_slug . '-' . $auth_token;
}

/* Publish */

function render_status_meta_box($post)
{
    $value = get_post_meta($post->ID, '_is_active', true);
    ?>
<label for="catalog_post_status">
    <input type="checkbox" name="catalog_post_status" id="catalog_post_status" value="1" <?php checked($value, '1'); ?>>
    Publish
</label>
<?php
}

function add_status_meta_box()
{
    add_meta_box(
        'catalog_post_status',
        'Catalog Post Status',
        'render_status_meta_box',
        'catalog_post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_status_meta_box');

function save_status_meta_box($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (get_post_type($post_id) !== 'catalog_post') {
        return;
    }

    if (isset($_POST['catalog_post_status'])) {
        $is_active = '1';
    } else {
        if (!isset($_POST['_inline_edit'])) {
            $is_active = '0';
        } else {
            return;
        }
    }

    update_post_meta($post_id, '_is_active', $is_active);
}
add_action('save_post', 'save_status_meta_box');

function add_status_column($columns)
{
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['moderation_status'] = 'Moderation';
        }
    }
    $new_columns['status'] = 'Status';
    return $new_columns;
}
add_filter('manage_catalog_post_posts_columns', 'add_status_column');

function render_status_column($column, $post_id)
{
    if ($column === 'moderation_status') {
        $post_status = get_post_status($post_id);
        $status_labels = [
            'pending' => '<span style="color: #d63638; font-weight: bold;">⏳ Pending</span>',
            'publish' => '<span style="color: #00a32a; font-weight: bold;">✓ Approved</span>',
            'trash' => '<span style="color: #999; font-weight: bold;">✕ Rejected</span>',
            'draft' => '<span style="color: #646970;">📝 Draft</span>'
        ];
        echo $status_labels[$post_status] ?? $post_status;
    }
    
    if ($column === 'status') {
        $is_active = get_post_meta($post_id, '_is_active', true);
        echo $is_active === '1' ? 'Publish' : 'Deactivated';
    }
}
add_action('manage_catalog_post_posts_custom_column', 'render_status_column', 10, 2);

// Make moderation status column sortable
function make_moderation_column_sortable($columns)
{
    $columns['moderation_status'] = 'moderation_status';
    return $columns;
}
add_filter('manage_edit-catalog_post_sortable_columns', 'make_moderation_column_sortable');

// Add quick filter links for moderation status
function add_moderation_status_filters()
{
    global $typenow;
    
    if ($typenow === 'catalog_post') {
        $pending_count = wp_count_posts('catalog_post')->pending;
        $publish_count = wp_count_posts('catalog_post')->publish;
        $trash_count = wp_count_posts('catalog_post')->trash;
        
        $current_status = isset($_GET['post_status']) ? $_GET['post_status'] : '';
        
        echo '<style>
            .subsubsub { margin-bottom: 15px; }
            .subsubsub .moderation-filter { font-weight: 600; }
        </style>';
    }
}
add_action('restrict_manage_posts', 'add_moderation_status_filters');

/* Boost */

function render_boost_meta_box($post)
{
    $value = get_post_meta($post->ID, '_is_boosted', true);
    ?>
<label for="catalog_post_boost">
    <input type="checkbox" name="catalog_post_boost" id="catalog_post_boost" value="1" <?php checked($value, '1'); ?>>
    Boost
</label>
<?php
}

function add_boost_meta_box()
{
    add_meta_box(
        'catalog_post_boost',
        'Catalog Post Boost',
        'render_boost_meta_box',
        'catalog_post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_boost_meta_box');

function save_boost_meta_box($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (get_post_type($post_id) !== 'catalog_post') {
        return;
    }

    if (isset($_POST['catalog_post_boost'])) {
        $is_boosted = '1';

        $existing_expiration = get_post_meta($post_id, '_boost_expiration', true);
        if (!$existing_expiration) {
            $boost_duration = 30 * DAY_IN_SECONDS;
            /*$boost_duration = 10 * MINUTE_IN_SECONDS;*/
            $boost_expiration = time() + $boost_duration;
            update_post_meta($post_id, '_boost_expiration', $boost_expiration);
        }

        remove_action('save_post', 'save_boost_meta_box');

        $current_time = current_time('mysql');
        $post_data = [
            'ID'            => $post_id,
            'post_date'     => $current_time,
            'post_date_gmt' => get_gmt_from_date($current_time),
        ];
        wp_update_post($post_data);

        add_action('save_post', 'save_boost_meta_box');
    } else {
        if (!isset($_POST['_inline_edit'])) {
            $is_boosted = '0';

            delete_post_meta($post_id, '_boost_expiration');
        } else {
            return;
        }
    }

    update_post_meta($post_id, '_is_boosted', $is_boosted);
}
add_action('save_post', 'save_boost_meta_box');

function add_boost_column($columns)
{
    $columns['boost'] = 'Boost';
    return $columns;
}
add_filter('manage_catalog_post_posts_columns', 'add_boost_column');

function render_boost_column($column, $post_id)
{
    if ($column === 'boost') {
        $is_boosted = get_post_meta($post_id, '_is_boosted', true);
        echo $is_boosted === '1' ? 'Boosted' : 'Not Boosted';
    }
}
add_action('manage_catalog_post_posts_custom_column', 'render_boost_column', 10, 2);

/* Contact Form */

add_action('init', 'register_contact_form_post_type');

function register_contact_form_post_type()
{
    register_post_type('contact_form', [
        'labels' => [
            'name' => 'Contact Forms',
            'singular_name' => 'Contact Form',
            'add_new' => 'Add Contact Form',
            'add_new_item' => 'Add New Contact Form',
            'edit_item' => 'Edit Contact Form',
            'new_item' => 'New Contact Form',
            'view_item' => 'View Contact Form',
            'search_items' => 'Search Contact Forms',
            'not_found' => 'No Contact Forms found',
            'menu_name' => 'Contact Forms',
        ],
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'menu_position' => 25,
        'supports' => ['title', 'editor', 'custom-fields'],
        'menu_icon' => 'dashicons-email-alt',
    ]);
}

add_action('add_meta_boxes', function () {
    add_meta_box('contact_form_meta', 'Contact Form Details', 'render_contact_form_meta', 'contact_form', 'normal', 'high');
});

function render_contact_form_meta($post)
{
    $email = get_post_meta($post->ID, 'email', true);
    $phone = get_post_meta($post->ID, 'phone', true);
    /*$theme = get_post_meta($post->ID, 'theme', true);*/

    $utm_source   = get_post_meta($post->ID, 'lead_utm_source', true);
    $utm_medium   = get_post_meta($post->ID, 'lead_utm_medium', true);
    $utm_campaign = get_post_meta($post->ID, 'lead_utm_campaign', true);
    $utm_content  = get_post_meta($post->ID, 'lead_utm_content', true);
    $utm_term     = get_post_meta($post->ID, 'lead_utm_term', true);

    ?>
    <div style="display: flex; gap: 40px;">
        <div style="flex: 1;">
            <p><strong>Email:</strong> <?php echo esc_html($email ?: '—'); ?></p>
            <p><strong>Phone:</strong> <code><?php echo esc_html($phone ?: '—'); ?></code></p>
            <!--<p><strong>Subject:</strong> <?php /* echo esc_html($theme ?: '—'); */ ?></p>-->
        </div>

        <div style="flex: 1; background: #f6f7f7; padding: 15px; border: 1px solid #dcdcde; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #2271b1;"><?php _e('Marketing data (UTM)', 'panterrea_v1'); ?></h4>
            <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>Source:</strong> <?php echo esc_html($utm_source ?: '—'); ?></p>
            <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>Medium:</strong> <?php echo esc_html($utm_medium ?: '—'); ?></p>
            <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>Campaign:</strong> <?php echo esc_html($utm_campaign ?: '—'); ?></p>
            <p style="margin: 0 0 5px 0; font-size: 12px;"><strong>Content:</strong> <?php echo esc_html($utm_content ?: '—'); ?></p>
            <p style="margin: 0 0 0 0; font-size: 12px;"><strong>Term:</strong> <?php echo esc_html($utm_term ?: '—'); ?></p>
        </div>
    </div>
    <?php
}

add_filter('manage_contact_form_posts_columns', function ($columns) {
    $columns['email'] = 'Email';
    $columns['phone'] = 'Phone';
    /*$columns['theme'] = 'Subject';*/
    $columns['utm_source'] = 'Source';
    return $columns;
});

add_action('manage_contact_form_posts_custom_column', function ($column, $post_id) {
    if ($column === 'email') {
        echo esc_html(get_post_meta($post_id, 'email', true));
    }
    if ($column === 'phone') {
        echo esc_html(get_post_meta($post_id, 'phone', true));
    }
    /*if ($column === 'theme') {
        echo esc_html(get_post_meta($post_id, 'theme', true));
    }*/
    if ($column === 'utm_source') {
        $source = get_post_meta($post_id, 'lead_utm_source', true);
        $medium = get_post_meta($post_id, 'lead_utm_medium', true);
        if ($source) {
            echo '<strong>' . esc_html($source) . '</strong>';
            echo $medium ? ' <small style="color:#646970;">(' . esc_html($medium) . ')</small>' : '';
        } else {
            echo '<span style="color:#999;">—</span>';
        }
    }
}, 10, 2);