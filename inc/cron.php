<?php
if (!wp_next_scheduled('deactivate_expired_boosts')) {
    wp_schedule_event(time(), 'hourly', 'deactivate_expired_boosts');
}

add_action('deactivate_expired_boosts', function () {
    global $wpdb;

    $day_in_seconds = 24 * 60 * 60;
    $current_time = time();

    $expiring_query = new WP_Query([
        'post_type' => 'catalog_post',
        'meta_query' => [
            [
                'key' => '_is_active',
                'value' => '1',
                'compare' => '='
            ],
            [
                'key'     => '_is_boosted',
                'value'   => '1',
                'compare' => '='
            ],
            [
                'key' => '_boost_expiration',
                'value' => [$current_time, $current_time + $day_in_seconds],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ],
        ],
    ]);

    if ($expiring_query->have_posts()) {
        foreach ($expiring_query->posts as $post) {
            $post_id = $post->ID;
            $owner_id = get_post_field('post_author', $post_id);
            $ad_name = get_the_title($post_id);
            $max_length = 40;

            if (mb_strlen($ad_name) > $max_length) {
                $ad_name = mb_substr($ad_name, 0, $max_length) . '...';
            }

            $table_name = $wpdb->prefix . 'notifications';
            $existing_notification = $wpdb->get_var($wpdb->prepare("
                SELECT id FROM $table_name 
                WHERE user_id = %d 
                AND type = 'boost_expiring' 
                AND status = 'unread' 
                AND message = %s
                LIMIT 1
            ", $owner_id, sprintf(__('Реклама оголошення %s завершиться через день.', 'panterrea_v1'), $ad_name)));

            if (!$existing_notification) {
                add_notification($owner_id, 'boost_expiring', sprintf(__('Реклама оголошення %s завершиться через день.', 'panterrea_v1'), $ad_name));
            }
        }
    }

    $query = new WP_Query([
        'post_type' => 'catalog_post',
        'meta_query' => [
            [
                'key' => '_is_active',
                'value' => '1',
                'compare' => '='
            ],
            [
                'key' => '_is_boosted',
                'value' => '1',
                'compare' => '=',
            ],
            [
                'key' => '_boost_expiration',
                'value' => time(),
                'compare' => '<',
                'type' => 'NUMERIC',
            ],
        ],
    ]);

    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $post_id = $post->ID;
            $owner_id = get_post_field('post_author', $post_id);
            $ad_name = get_the_title($post_id);
            $max_length = 40;
            if (mb_strlen($ad_name) > $max_length) {
                $ad_name = mb_substr($ad_name, 0, $max_length) . '...';
            }

            update_post_meta($post_id, '_is_boosted', false);
            delete_post_meta($post_id, '_boost_expiration');
            delete_post_meta($post_id, '_boost_remaining_time');

            add_notification($owner_id, 'boost_expired', sprintf(__('Реклама оголошення %s завершилась.', 'panterrea_v1'), $ad_name));
        }
    }
});