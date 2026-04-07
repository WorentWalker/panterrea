<?php
add_filter('manage_edit-catalog_category_columns', function ($columns) {
    $columns['category_order'] = __('Order', 'text-domain');
    return $columns;
});

add_filter('manage_catalog_category_custom_column', function ($content, $column_name, $term_id) {
    if ($column_name === 'category_order') {
        $order = get_field('category_order', 'catalog_category_' . $term_id);
        $content = $order ? esc_html($order) : '0';
    }
    return $content;
}, 10, 3);

/**
 * Displaying PanTerrea registration data at the top of the profile page
 */
add_action('personal_options', 'panterrea_display_custom_user_fields_top');

function panterrea_display_custom_user_fields_top($user) {
    $name       = get_user_meta($user->ID, 'name', true);
    $city       = get_user_meta($user->ID, 'city', true);
    $phone      = get_user_meta($user->ID, 'phone', true);
    $profession = get_user_meta($user->ID, 'profession', true);

    $utm_source   = get_user_meta($user->ID, 'reg_utm_source', true);
    $utm_medium   = get_user_meta($user->ID, 'reg_utm_medium', true);
    $utm_campaign = get_user_meta($user->ID, 'reg_utm_campaign', true);
    $utm_content  = get_user_meta($user->ID, 'reg_utm_content', true);
    $utm_term     = get_user_meta($user->ID, 'reg_utm_term', true);

    ?>
    <table class="form-table" role="presentation" style="margin-top: 0;">
        <tr>
            <th style="width: 200px; padding: 10px 0;"><strong><?php _e('Name', 'panterrea_v1'); ?></strong></th>
            <td style="padding: 10px 0;"><strong><?php echo esc_html($name ?: '—'); ?></strong></td>
        </tr>
        <tr>
            <th style="width: 200px; padding: 10px 0;"><?php _e('City', 'panterrea_v1'); ?></th>
            <td style="padding: 10px 0;"><?php echo esc_html($city ?: '—'); ?></td>
        </tr>
        <tr>
            <th style="width: 200px; padding: 10px 0;"><?php _e('Phone', 'panterrea_v1'); ?></th>
            <td style="padding: 10px 0;"><code><?php echo esc_html($phone ?: '—'); ?></code></td>
        </tr>
        <tr>
            <th style="width: 200px; padding: 10px 0;"><?php _e('Profession', 'panterrea_v1'); ?></th>
            <td style="padding: 10px 0;"><?php echo esc_html($profession ?: '—'); ?></td>
        </tr>

        <tr>
            <th colspan="2" style="padding: 20px 0 10px 0;">
                <div style="border-bottom: 1px solid #ccd0d4; line-height: 0.1em; margin: 10px 0 20px; text-align: left;">
                    <span style="background:#f0f0f1; padding:0 10px; color: #2271b1; font-weight: 600;">
                        <?php _e('Marketing Attribution (UTM)', 'panterrea_v1'); ?>
                    </span>
                </div>
            </th>
        </tr>

        <?php 
        $utm_labels = [
            'Source'   => $utm_source,
            'Medium'   => $utm_medium,
            'Campaign' => $utm_campaign,
            'Content'  => $utm_content,
            'Term'     => $utm_term,
        ];

        foreach ($utm_labels as $label => $value) : 
        ?>
        <tr>
            <th style="width: 200px; padding: 5px 0; font-size: 12px; color: #646970; font-weight: normal;">
                <?php echo esc_html($label); ?>
            </th>
            <td style="padding: 5px 0;">
                <code style="font-size: 11px; background: #fff; border: 1px solid #dcdcde; padding: 2px 6px;">
                    <?php echo esc_html($value ?: 'not set'); ?>
                </code>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <style>
        tr + .show-admin-bar { border-top: 1px solid #ccd0d4; margin-top: 20px; }
    </style>
    <?php
}