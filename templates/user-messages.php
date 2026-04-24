<?php
/**
 * Template Name: User Messages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(URL_LOGIN);
    exit;
}

$user_id = get_current_user_id();
$email_confirmed = get_user_meta($user_id, 'email_verified', true);

if (!$email_confirmed) {
    setMessageCookies('warning', __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1'));
    wp_redirect(home_url());
    exit;
}

global $wpdb;

$chats = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT c.chat_id, c.post_id, c.created_at, 
                CASE WHEN c.user1_id = %d THEN c.user2_id ELSE c.user1_id END AS other_user_id,
                p.post_title,
                COALESCE(unread.unread_count, 0) AS unread_count,
                COALESCE(last_msg.last_message_time, c.created_at) AS last_message_time,
                EXISTS (
                    SELECT 1 FROM {$wpdb->prefix}chat_blocklist b
                    WHERE b.blocker_id = %d 
                    AND b.blocked_id = CASE WHEN c.user1_id = %d THEN c.user2_id ELSE c.user1_id END
                ) AS is_blocked,
                d.deleted_at
         FROM {$wpdb->prefix}chat_sessions c
         LEFT JOIN {$wpdb->prefix}posts p ON c.post_id = p.ID
         LEFT JOIN (
             SELECT chat_id, COUNT(*) AS unread_count
             FROM {$wpdb->prefix}chat_messages 
             WHERE is_read = 0 AND sender_id != %d
             GROUP BY chat_id
         ) unread ON c.chat_id = unread.chat_id
         LEFT JOIN (
             SELECT chat_id, MAX(timestamp) AS last_message_time
             FROM {$wpdb->prefix}chat_messages
             GROUP BY chat_id
         ) last_msg ON c.chat_id = last_msg.chat_id
         LEFT JOIN {$wpdb->prefix}chat_deleted d 
         ON c.chat_id = d.chat_id AND d.user_id = %d
         WHERE (c.user1_id = %d OR c.user2_id = %d)
         AND (d.deleted_at IS NULL OR last_msg.last_message_time > d.deleted_at)
         ORDER BY unread_count DESC, last_message_time DESC",
        $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id
    )
);

get_header();

/*global $currentLang;*/
?>

    <main id="userMessages" class="userMessages firstSectionPadding" data-user-id="<?php echo esc_attr($user_id); ?>">
        <div class="container">
            <div class="userMessages__inner">

                <h2 class="userMessages__title h3"><?php _e('Повідомлення', 'panterrea_v1'); ?></h2>

                <div class="userMessages__wrapper">

                    <?php if (!empty($chats)) { ?>
                        <div class="userMessages__chatList js-chatList">
                            <?php foreach ($chats as $chat) {
                                $catalog_data = get_field('catalog_post', $chat->post_id);
                                [$price, $currency] = panterrea_get_post_price_pair((int) $chat->post_id, $catalog_data);
                                $featured_image_url = $catalog_data['featured_image'] ?? '';
                                $main_image = !empty($featured_image_url) ? $featured_image_url : get_the_post_thumbnail_url($chat->post_id, 'medium');
                                $post_link = get_permalink($chat->post_id);

                                $recipient_id = $chat->other_user_id;
                                $is_new = $chat->unread_count > 0 ? ' new' : '';

                                /*if ($chat->unread_count > 0 && $currentLang === 'en') {
                                    $is_new .= ' new-en';
                                }*/

                                if ($chat->unread_count > 0 && str_contains($_SERVER['REQUEST_URI'], '/en')) {
                                    $is_new .= ' new-en';
                                }

                                $is_blocked = $chat->is_blocked ? ' blocked' : '';
                                $is_deleted = empty(get_post($chat->post_id)) ? ' deleted' : '';
                                ?>
                                <div class="userMessages__chatAd js-chatOpen <?php echo esc_attr($is_new . $is_blocked . $is_deleted); ?>"
                                     data-user-id="<?php echo esc_attr($user_id); ?>"
                                     data-recipient-id="<?php echo esc_attr($recipient_id); ?>"
                                     data-post-id="<?php echo esc_attr($chat->post_id); ?>"
                                     data-chat-id="<?php echo esc_attr($chat->chat_id); ?>"
                                    <?php if (!$is_deleted) : ?> data-link-ad="<?php echo esc_attr($post_link); ?>" <?php endif; ?>>

                                    <?php if ($is_deleted) : ?>
                                        <div class="userMessages__chatAd__info">
                                            <div class="userMessages__chatAd__title subtitle1">
                                                <?php _e('Оголошення було видалено', 'panterrea_v1'); ?>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($chat->post_title); ?>">
                                        <div class="userMessages__chatAd__info">
                                            <div class="userMessages__chatAd__title subtitle1">
                                                <?php echo esc_html($chat->post_title); ?>
                                            </div>
                                            <div class="userMessages__chatAd__price body2">
                                                <?php echo panterrea_format_price_display($price, $currency); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="userMessages__chatAd__options js-chatOptions">
                                        <div class="userMessages__chatAd__optionsList hidden">
                                            <div class="userMessages__chatAd__option subtitle2 block js-openPopUp" data-popUp="blockChat">
                                                <?php echo $chat->is_blocked ? __('Розблокувати', 'panterrea_v1') : __('Заблокувати', 'panterrea_v1'); ?>
                                            </div>
                                            <?php if (!$is_deleted) : ?>
                                                <a href="<?php echo esc_attr($post_link); ?>" target="_blank" class="userMessages__chatAd__option subtitle2 link">
                                                    <?php echo __('Переглянути оголошення', 'panterrea_v1'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <div class="userMessages__chatAd__option subtitle2 delete js-openPopUp" data-popUp="deleteChat">
                                                <?php echo __('Видалити', 'panterrea_v1'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>

                        <div class="userMessages__chatAd__empty">
                            <?php get_template_part('template-parts/empty-folder', null, [
                                'info' => __('У вас немає повідомлень', 'panterrea_v1')
                            ]); ?>
                        </div>
                    <?php } ?>

                    <div id="emptyChats" class="userMessages__chatAd__empty hidden">
                        <?php get_template_part('template-parts/empty-folder', null, [
                            'info' => __('У вас немає повідомлень', 'panterrea_v1')
                        ]); ?>
                    </div>

                    <div class="userMessages__chatBlock js-chatContainer hidden">
                        <?php get_template_part('template-parts/chat'); ?>
                    </div>

                </div>

            </div>
        </div>

        <div id="blockChat" class="popUp js-popUp hidden">
            <div class="container">
                <div class="popUp__inner confirm">
                    <div class="popUp__confirm">

                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_chat_block.svg'); ?>"
                             alt="Empty">

                        <div class="popUp__confirm__title h3"><?php _e('Ви дійсно хочете заблокувати чат?', 'panterrea_v1'); ?></div>
                        <div class="popUp__confirm__text body2"><?php _e('Ви не будете отримувати повідомлень від цього користувача', 'panterrea_v1'); ?></div>

                        <div id="blockChatButton" class="btn btn__submit button-large"><?php _e('Так, блокувати', 'panterrea_v1'); ?></div>
                        <div class="btn btn__cancelConfirm subtitle2 js-closePopUp" data-popUp="blockChat"><?php _e('Скасувати', 'panterrea_v1'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="deleteChat" class="popUp js-popUp hidden">
            <div class="container">
                <div class="popUp__inner confirm">
                    <div class="popUp__confirm">

                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_delete_ad.svg'); ?>"
                             alt="Empty">

                        <div class="popUp__confirm__title h3"><?php _e('Ви дійсно хочете видалити повідомлення?', 'panterrea_v1'); ?></div>
                        <div class="popUp__confirm__text body2"><?php _e('Повідомлення буде видалено назавжди', 'panterrea_v1'); ?></div>

                        <div id="deleteChatButton" class="btn btn__submit button-large"><?php _e('Так, видалити', 'panterrea_v1'); ?></div>
                        <div class="btn btn__cancelConfirm subtitle2 js-closePopUp" data-popUp="deleteChat"><?php _e('Скасувати', 'panterrea_v1'); ?></div>
                    </div>
                </div>
            </div>
        </div>

    </main>

<?php
get_footer();
