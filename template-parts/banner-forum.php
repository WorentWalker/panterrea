<div class="banner js-forumBanner">
    <div class="banner__container">
        <div class="banner__icon">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/forum_banner_icon.svg'); ?>"
                 alt="<?= __('Переглянути форум', 'panterrea_v1') ?>">
        </div>
        <div class="banner__info">
            <div class="banner__title h5">Обговорюй. Ділись досвідом. Долучайся до спільноти.</div>
            <div class="banner__subtitle">Знайомтесь та знаходьте цікавих співрозмовників у нашому форумі</div>
        </div>
        <a href="<?= esc_url(URL_FORUM) ?>" class="btn btn__submit btn__submit__transparentGreen button-large">Переглянути форум</a>

        <div class="banner__close js-closeBanner">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/forum_banner_close.svg'); ?>"
                 alt="<?= __('Закрити', 'panterrea_v1') ?>">
        </div>
    </div>
</div>