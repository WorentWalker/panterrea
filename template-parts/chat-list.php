<?php

$new = $args['new'] ?? false;
$chats = $args['chats'] ?? null;
$user_id = $args['user_id'] ?? null;
$empty_message = $args['empty_message'] ?? null;

if (!empty($chats)) { ?>
    <div class="userMessages__chatList">
        <?php foreach ($chats as $chat) {
            $price = get_field('catalog_post_price', $chat->post_id);
            $currency = get_field('catalog_post_currency', $chat->post_id);
            $main_image = get_the_post_thumbnail_url($chat->post_id, 'medium');
            $post_link = get_permalink($chat->post_id);

            $recipient_id = $chat->other_user_id;
            ?>
            <div class="userMessages__chatAd js-chatOpen <?php echo $new ? 'new' : ''; ?>"
                 data-user-id="<?php echo esc_attr($user_id); ?>"
                 data-recipient-id="<?php echo esc_attr($recipient_id); ?>"
                 data-post-id="<?php echo esc_attr($chat->post_id); ?>">
                <img src="<?php echo esc_url($main_image); ?>"
                     alt="<?php echo esc_attr($chat->post_title); ?>">
                <div class="userMessages__chatAd__info">
                    <div class="userMessages__chatAd__title subtitle1">
                        <?php echo esc_html($chat->post_title); ?>
                    </div>
                    <div class="userMessages__chatAd__price body2">
                        <?php echo esc_html($price . ' ' . $currency); ?>
                    </div>
                </div>
                <div class="userMessages__chatAd__options">
                    <div class="userMessages__chatAd__optionsList">
                        <div class="userMessages__chatAd__option block"></div>
                        <div class="userMessages__chatAd__option link"></div>
                        <div class="userMessages__chatAd__option delete"></div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>
    <div class="userMessages__chatAd__empty">
    <?php get_template_part('template-parts/empty-folder', null, [
        'info' => $empty_message ?? 'Немає повідомлень'
    ]); ?>
    </div>
<?php } ?>

