<?php
/**
 * Reusable login form (actionTemplate__innerForm)
 *
 * @param array $args {
 *   @type string $title   Form title. Default: login page title.
 *   @type string $info    Info text with register link. Default: login page info.
 *   @type string $context 'login'|'article' - changes title/info for article overlay.
 * }
 */
$args = wp_parse_args($args ?? [], [
    'context'  => 'login',
    'redirect' => '',
]);
$is_article = ($args['context'] === 'article');
?>
<div class="actionTemplate__innerForm">
    <h1 class="actionTemplate__innerForm__title h4">
        <?php echo $is_article ? esc_html__('Цей текст має продовження', 'panterrea_v1') : esc_html__('Увійти до PanTerrea', 'panterrea_v1'); ?>
    </h1>
    <div class="actionTemplate__innerForm__info body2">
        <?php if ($is_article) : ?>
            <?php echo esc_html__('Наступна частина статті доступна для авторизованих читачів PanTerrea.', 'panterrea_v1'); ?>
            <?php _e('Новий користувач?', 'panterrea_v1'); ?>
        <?php else : ?>
            <?php _e('Новий користувач?', 'panterrea_v1'); ?>
        <?php endif; ?>
        <a href="<?= esc_url(URL_REGISTER); ?>">
            <?php _e('Створити акаунт', 'panterrea_v1'); ?>
        </a>
    </div>
    <div class="form__socialLogin">
        <a class="btn btn__social btn__social--google"
            href="<?php echo esc_url(add_query_arg('loginSocial', 'google', home_url('/wp-login.php'))); ?>" data-plugin="nsl" data-action="connect"
            data-redirect="current" data-provider="google" data-popupwidth="600" data-popupheight="600">
            <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M19.6084 11.5943C19.6084 10.988 19.554 10.4051 19.4529 9.84546H11.4004V13.1528H16.0018C15.8036 14.2215 15.2013 15.127 14.2957 15.7333V17.8786H17.0589C18.6757 16.3901 19.6084 14.1982 19.6084 11.5943Z"
                    fill="#4285F4" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M11.4004 19.95C13.7089 19.95 15.6443 19.1844 17.0589 17.8786L14.2957 15.7333C13.5301 16.2463 12.5507 16.5494 11.4004 16.5494C9.17347 16.5494 7.28858 15.0454 6.61624 13.0245H3.75977V15.2397C5.16663 18.034 8.05808 19.95 11.4004 19.95Z"
                    fill="#34A853" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.6155 13.0245C6.4445 12.5115 6.34734 11.9635 6.34734 11.4C6.34734 10.8365 6.4445 10.2885 6.6155 9.7755V7.56027H3.75902C3.17995 8.71452 2.84961 10.0203 2.84961 11.4C2.84961 12.7797 3.17995 14.0855 3.75902 15.2397L6.6155 13.0245Z"
                    fill="#FBBC05" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M11.4004 6.25057C12.6557 6.25057 13.7827 6.68196 14.6688 7.52919L17.1211 5.07689C15.6404 3.69723 13.705 2.85001 11.4004 2.85001C8.05808 2.85001 5.16663 4.76598 3.75977 7.56028L6.61624 9.77551C7.28858 7.7546 9.17347 6.25057 11.4004 6.25057Z"
                    fill="#EA4335" />
            </svg>
            <span><?php _e('Continue with Google', 'panterrea_v1'); ?></span>
        </a>
    </div>

    <div class="form__divider">
        <span class="form__divider__text"><?php _e('або', 'panterrea_v1'); ?></span>
    </div>

    <form id="formLogin" class="form form__actionTemplate">
        <?php if (!empty($args['redirect'])) : ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($args['redirect']); ?>" />
        <?php elseif ($is_article && is_singular('post')) : ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>" />
        <?php endif; ?>
        <div class="input__form">
            <input class="body2" type="text" name="email" id="email"
                placeholder="<?php _e('Електронна адреса', 'panterrea_v1'); ?>" />
            <label class="body2" for="email"><?php _e('Електронна адреса', 'panterrea_v1'); ?></label>
            <span class="error caption"></span>
        </div>

        <div class="input__form">
            <input class="body2" type="password" name="password" id="password"
                placeholder="<?php _e('Пароль до PanTerrea', 'panterrea_v1'); ?>" />
            <label class="body2" for="password"><?php _e('Пароль до PanTerrea', 'panterrea_v1'); ?></label>
            <div class="input__togglePass js-togglePass"></div>
            <span class="error caption"></span>
        </div>

        <a href="<?= esc_url(URL_FORGOTPASS); ?>"
            class="form__forgotPass body2"><?php _e('Забули пароль?', 'panterrea_v1'); ?></a>

        <button class="btn btn__submit button-large" type="submit">
            <?php _e('Увійти', 'panterrea_v1'); ?>
        </button>
    </form>
</div>
