<?php
/**
 * Moderation Panel for Catalog Posts
 * Панель модерації оголошень
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Moderation Menu Page
 */
add_action('admin_menu', 'add_moderation_menu_page');

function add_moderation_menu_page()
{
    add_menu_page(
        __('Модерація оголошень', 'panterrea_v1'),
        __('Модерація', 'panterrea_v1'),
        'edit_posts',
        'catalog-moderation',
        'render_moderation_page',
        'dashicons-yes-alt',
        26
    );
}

/**
 * Render Moderation Page
 */
function render_moderation_page()
{
    // Check user permissions
    if (!current_user_can('edit_posts')) {
        wp_die(__('У вас немає прав для доступу до цієї сторінки.'));
    }

    // Get filter status
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'pending';

    // Count posts by status
    $pending_count = wp_count_posts('catalog_post')->pending;
    $publish_count = wp_count_posts('catalog_post')->publish;
    $trash_count = wp_count_posts('catalog_post')->trash;

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Модерація оголошень', 'panterrea_v1'); ?></h1>

        <hr class="wp-header-end">

        <!-- Filter Tabs -->
        <ul class="subsubsub">
            <li>
                <a href="?page=catalog-moderation&status=pending"
                   class="<?php echo $status_filter === 'pending' ? 'current' : ''; ?>">
                    <?php _e('Очікує модерації', 'panterrea_v1'); ?>
                    <span class="count">(<?php echo $pending_count; ?>)</span>
                </a> |
            </li>
            <li>
                <a href="?page=catalog-moderation&status=publish"
                   class="<?php echo $status_filter === 'publish' ? 'current' : ''; ?>">
                    <?php _e('Опубліковано', 'panterrea_v1'); ?>
                    <span class="count">(<?php echo $publish_count; ?>)</span>
                </a> |
            </li>
            <li>
                <a href="?page=catalog-moderation&status=trash"
                   class="<?php echo $status_filter === 'trash' ? 'current' : ''; ?>">
                    <?php _e('Відхилено', 'panterrea_v1'); ?>
                    <span class="count">(<?php echo $trash_count; ?>)</span>
                </a>
            </li>
        </ul>

        <br class="clear">

        <?php
        // Query posts based on filter
        $args = [
            'post_type' => 'catalog_post',
            'post_status' => $status_filter,
            'posts_per_page' => 20,
            'orderby' => 'date',
            'order' => 'DESC'
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            ?>
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                <tr>
                    <th style="width: 50px;"><?php _e('ID', 'panterrea_v1'); ?></th>
                    <th><?php _e('Назва', 'panterrea_v1'); ?></th>
                    <th><?php _e('Автор', 'panterrea_v1'); ?></th>
                    <th><?php _e('Категорія', 'panterrea_v1'); ?></th>
                    <th><?php _e('Ціна', 'panterrea_v1'); ?></th>
                    <th><?php _e('Дата', 'panterrea_v1'); ?></th>
                    <th><?php _e('Статус', 'panterrea_v1'); ?></th>
                    <th style="width: 280px;"><?php _e('Дії', 'panterrea_v1'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    $author_id = get_post_field('post_author', $post_id);
                    $author_name = get_user_meta($author_id, 'name', true) ?: get_the_author_meta('display_name', $author_id);
                    
                    $catalog_data = get_field('catalog_post', $post_id);
                    $price = isset($catalog_data['price']) ? $catalog_data['price'] : '—';
                    $currency = isset($catalog_data['currency']) ? $catalog_data['currency'] : '';
                    
                    $categories = get_the_terms($post_id, 'catalog_category');
                    $category_names = $categories ? implode(', ', wp_list_pluck($categories, 'name')) : '—';
                    
                    $post_status = get_post_status($post_id);
                    $status_label = [
                        'pending' => '<span style="color: #d63638;">Очікує модерації</span>',
                        'publish' => '<span style="color: #00a32a;">Опубліковано</span>',
                        'trash' => '<span style="color: #999;">Відхилено</span>'
                    ];
                    ?>
                    <tr data-post-id="<?php echo $post_id; ?>">
                        <td><?php echo $post_id; ?></td>
                        <td>
                            <strong>
                                <a href="<?php echo get_permalink($post_id); ?>" target="_blank">
                                    <?php echo esc_html(get_the_title()); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('user-edit.php?user_id=' . $author_id); ?>">
                                <?php echo esc_html($author_name); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($category_names); ?></td>
                        <td><?php echo $price !== '—' ? number_format($price, 2) . ' ' . $currency : '—'; ?></td>
                        <td><?php echo get_the_date('d.m.Y H:i'); ?></td>
                        <td class="status-cell"><?php echo $status_label[$post_status] ?? $post_status; ?></td>
                        <td class="actions-cell">
                            <?php if ($post_status === 'pending'): ?>
                                <button class="button button-primary moderate-btn"
                                        data-action="approve"
                                        data-post-id="<?php echo $post_id; ?>">
                                    <?php _e('✓ Схвалити', 'panterrea_v1'); ?>
                                </button>
                                <button class="button moderate-btn"
                                        data-action="reject"
                                        data-post-id="<?php echo $post_id; ?>">
                                    <?php _e('✕ Відхилити', 'panterrea_v1'); ?>
                                </button>
                            <?php elseif ($post_status === 'publish'): ?>
                                <button class="button moderate-btn"
                                        data-action="unpublish"
                                        data-post-id="<?php echo $post_id; ?>">
                                    <?php _e('Зняти з публікації', 'panterrea_v1'); ?>
                                </button>
                            <?php elseif ($post_status === 'trash'): ?>
                                <button class="button button-primary moderate-btn"
                                        data-action="restore"
                                        data-post-id="<?php echo $post_id; ?>">
                                    <?php _e('Відновити', 'panterrea_v1'); ?>
                                </button>
                            <?php endif; ?>
                            
                            <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>"
                               class="button">
                                <?php _e('Редагувати', 'panterrea_v1'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php
        } else {
            ?>
            <div class="notice notice-info inline" style="margin-top: 20px;">
                <p><?php _e('Оголошень не знайдено.', 'panterrea_v1'); ?></p>
            </div>
            <?php
        }
        wp_reset_postdata();
        ?>

        <!-- Loading Overlay -->
        <div id="moderation-loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999; align-items: center; justify-content: center;">
            <div class="spinner is-active" style="float: none; width: 50px; height: 50px; margin: 0;"></div>
        </div>
    </div>

    <style>
        .moderate-btn {
            margin-right: 5px;
        }
        .moderate-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #moderation-loading {
            display: none !important;
        }
        #moderation-loading.active {
            display: flex !important;
        }
    </style>

    <script>
        jQuery(document).ready(function ($) {
            $('.moderate-btn').on('click', function (e) {
                e.preventDefault();

                const $btn = $(this);
                const postId = $btn.data('post-id');
                const action = $btn.data('action');
                const $row = $btn.closest('tr');
                const $loading = $('#moderation-loading');

                if (!confirm('<?php _e("Ви впевнені?", "panterrea_v1"); ?>')) {
                    return;
                }

                // Disable all buttons in row
                $row.find('button').prop('disabled', true);
                $loading.addClass('active');

                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: {
                        action: 'moderate_catalog_post',
                        security: '<?php echo wp_create_nonce("moderate_post_nonce"); ?>',
                        post_id: postId,
                        moderate_action: action
                    },
                    success: function (response) {
                        console.log('Response:', response);
                        if (response.success) {
                            // Show success message
                            const notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                            $('.wrap h1').after(notice);

                            // Reload page after 1 second
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        } else {
                            alert(response.data.message || '<?php _e("Помилка", "panterrea_v1"); ?>');
                            $row.find('button').prop('disabled', false);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error:', xhr, status, error);
                        alert('<?php _e("Помилка з\'єднання", "panterrea_v1"); ?>: ' + error);
                        $row.find('button').prop('disabled', false);
                    },
                    complete: function () {
                        $loading.removeClass('active');
                    }
                });
            });
        });
    </script>
    <?php
}
