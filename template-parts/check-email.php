<div id="checkEmail" class="actionTemplate__innerPopUp hidden">
    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_email_inbox.svg'); ?>"
         alt="<?= __('Забули пароль?', 'panterrea_v1') ?>">
    <h1 class="actionTemplate__innerPopUp__title h3"><?= esc_html($args['title'] ?? '') ?></h1>
    <div class="actionTemplate__innerPopUp__info body2"><?= esc_html($args['info'] ?? '') ?></div>

    <div class="actionTemplate__resend body2"><?= __('Немає листа?', 'panterrea_v1') ?> <span id="resendEmail"><?= __('Надіслати повторно', 'panterrea_v1') ?></span></div>

    <a href="<?= esc_url($args['urlBackLink'] ?? '') ?>" class="actionTemplate__backLink subtitle2"><span><?= esc_html($args['textBackLink'] ?? '') ?></span></a>
</div>