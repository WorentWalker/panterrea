<?php

/**
 * Template Name: Register
 */

get_header();
?>

<main class="actionTemplate register firstSectionPadding">
    <div class="actionTemplate__container">

        <div class="actionTemplate__innerForm">
            <h1 class="actionTemplate__innerForm__title h4">
                <?php _e('Спробуйте абсолютно безкоштовно', 'panterrea_v1'); ?></h1>
            <div class="actionTemplate__innerForm__info body2"><?php _e('Вже є акаунт?', 'panterrea_v1'); ?><a
                    href="<?= URL_LOGIN ?>"> <?php _e('Увійти', 'panterrea_v1'); ?></a></div>
            <div class="form__socialLogin">
                <a class="btn btn__social btn__social--google"
                    href="https://panterrea.com/wp-login.php?loginSocial=google" data-plugin="nsl" data-action="connect"
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
                <!-- 
                <a class="btn btn__social btn__social--facebook"
                    href="https://panterrea.com/wp-login.php?loginSocial=facebook" data-plugin="nsl"
                    data-action="connect" data-redirect="current" data-provider="facebook" data-popupwidth="600"
                    data-popupheight="679">

                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_1114_54)">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M24 12.0707C24 5.40424 18.6274 0 12 0C5.37258 0 0 5.40424 0 12.0707C0 18.0956 4.38823 23.0893 10.125 23.9948V15.5599H7.07812V12.0707H10.125V9.41139C10.125 6.38617 11.9165 4.71513 14.6576 4.71513C15.9705 4.71513 17.3438 4.95088 17.3438 4.95088V7.92141H15.8306C14.3399 7.92141 13.875 8.85187 13.875 9.80645V12.0707H17.2031L16.6711 15.5599H13.875V23.9948C19.6118 23.0893 24 18.0956 24 12.0707Z"
                                fill="#1877F2" />
                        </g>
                        <defs>
                            <clipPath id="clip0_1114_54">
                                <rect width="24" height="24" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>

                    <span><?php _e('Continue with Facebook', 'panterrea_v1'); ?></span>
                </a> -->
            </div>
            <div class="form__divider">
                <span class="form__divider__text"><?php _e('або', 'panterrea_v1'); ?></span>
            </div>
            <form id="formRegister" class="form form__actionTemplate">

                <!--<div class="form__row">
                        <div class="input__form">
                            <input class="body2" type="text" name="name" id="name"
                                   placeholder="<?php _e('Ім’я', 'panterrea_v1'); ?>"/>
                            <label class="body2" for="name"><?php _e('Ім’я', 'panterrea_v1'); ?></label>
                            <span class="error caption"></span>
                        </div>
                        <div class="input__form">
                            <input class="body2" type="text" name="surname" id="surname"
                                   placeholder="<?php _e('Прізвище', 'panterrea_v1'); ?>"/>
                            <label class="body2" for="surname"><?php _e('Прізвище', 'panterrea_v1'); ?></label>
                            <span class="error caption"></span>
                        </div>
                    </div>-->

                <div class="input__form">
                    <input class="body2" type="text" name="name" id="name"
                        placeholder="<?php _e('Ім’я', 'panterrea_v1'); ?>" />
                    <label class="body2" for="name"><?php _e('Ім’я', 'panterrea_v1'); ?></label>
                    <span class="error caption"></span>
                    <span class="helper caption">
                        <span><?php _e('Введіть прізвище та ім’я або назву підприємства.', 'panterrea_v1'); ?></span>
                    </span>
                </div>

                <div class="input__form">
                    <input class="body2" type="text" name="city" id="city"
                        placeholder="<?php _e('Місто проживання (Або місце реєстрації підприємства)', 'panterrea_v1'); ?>" />
                    <label class="body2" for="city">
                        <?php _e('Місто проживання', 'panterrea_v1'); ?>
                        <span
                            class="mob__hidden">(<?php _e('Або місце реєстрації підприємства', 'panterrea_v1'); ?>)</span>
                    </label>
                    <span class="error caption"></span>
                </div>

                <div class="input__form">
                    <input class="body2" type="text" name="email" id="email"
                        placeholder="<?php _e('Електронна адреса', 'panterrea_v1'); ?>" />
                    <label class="body2" for="email"><?php _e('Електронна адреса', 'panterrea_v1'); ?></label>
                    <span class="error caption"></span>
                </div>

                <div class="input__form">
                    <input class="body2" type="text" name="profession" id="profession"
                        placeholder="<?php _e('Рід діяльності', 'panterrea_v1'); ?>" />
                    <label class="body2" for="profession"><?php _e('Рід діяльності', 'panterrea_v1'); ?></label>
                    <span class="error caption"></span>
                </div>

                <div class="form__rowPhone">
                    <div class="input__form js-fakeSelectFlagWrapper">
                        <label class="subtitle2" for="mask"></label>
                        <input class="body2 js-fakeSelectFlagInput" type="text" name="mask" id="mask" value="UA"
                            readonly />

                        <div class="input__fakeSelectFlag js-fakeSelectFlagImg"><img
                                src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/flags/ua.png'); ?>"
                                alt="UA"></div>

                        <ul class="input__fakeSelect js-fakeSelectFlagOptions">
                            <li class="input__fakeSelect__flag" data-value="ua" data-flag="UA"><img
                                    src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/flags/ua.png'); ?>"
                                    alt="UA"></li>
                            <li class="input__fakeSelect__flag" data-value="gb" data-flag="GB"><img
                                    src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/flags/gb.png'); ?>"
                                    alt="GB"></li>
                            <li class="input__fakeSelect__flag" data-value="de" data-flag="DE"><img
                                    src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/flags/de.png'); ?>"
                                    alt="DE"></li>
                        </ul>

                        <div class="input__arrowBottom"></div>
                        <span class="error caption"></span>
                    </div>

                    <div class="input__form">
                        <input class="body2" type="text" name="phone" id="phone"
                            placeholder="<?php _e('Контактний номер', 'panterrea_v1'); ?>" />
                        <label class="body2" for="phone"><?php _e('Контактний номер', 'panterrea_v1'); ?></label>
                        <span class="error caption"></span>
                    </div>
                </div>

                <div class="input__form">
                    <input class="body2" type="password" name="password" id="password"
                        placeholder="<?php _e('Створити пароль', 'panterrea_v1'); ?>" />
                    <label class="body2" for="password"><?php _e('Створити пароль', 'panterrea_v1'); ?></label>
                    <div class="input__togglePass js-togglePass"></div>
                    <span class="error caption"></span>
                    <span class="helper caption">
                        <span><?php _e('Пароль має містити 8 знаків, велика буква, мала буква і символ.', 'panterrea_v1'); ?></span>
                    </span>
                </div>

                <!-- Cloudflare Turnstile -->
                <div class="cf-turnstile" 
                     data-sitekey="<?php echo esc_attr(TURNSTILE_SITE_KEY); ?>" 
                     data-theme="light"
                     data-size="flexible"
                     data-callback="onTurnstileSuccess"
                     data-expired-callback="onTurnstileExpired"
                     data-error-callback="onTurnstileError"
                     style="margin-bottom: 20px;">
                </div>
                <input type="hidden" id="turnstile-token" name="turnstile_token" value="">

                <button class="btn btn__submit button-large" type="submit">
                    <?php _e('Створити акаунт', 'panterrea_v1'); ?>
                </button>

                <div class="form__condition caption">
                    <?php _e('Реєструючись, я погоджуюсь з', 'panterrea_v1'); ?>
                    <a href="#"><?php _e('Умовами користування', 'panterrea_v1'); ?></a>
                    <?php _e('та', 'panterrea_v1'); ?>
                    <a href="#"><?php _e('Політикою конфіденційності', 'panterrea_v1'); ?></a>.
                </div>
            </form>
        </div>

        <?php get_template_part('template-parts/check-email', null, [
                'title' => __('Перевірте свою електронну пошту!', 'panterrea_v1'),
                'info' => __('Будь ласка, відкрийте лист і натисніть на посилання, щоб завершити процес реєстрації. Зверніть увагу, що лист може надійти з затримкою до 15 хвилин. Якщо ви не отримали його, перевірте папку "Спам" або спробуйте ще раз.', 'panterrea_v1'),
                'textBackLink' => __('Повернутися до входу', 'panterrea_v1'),
                'urlBackLink' => URL_LOGIN
            ]); ?>

    </div>
</main>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
    // Cloudflare Turnstile callbacks
    window.onTurnstileSuccess = function(token) {
        document.getElementById('turnstile-token').value = token;
    };

    window.onTurnstileExpired = function() {
        document.getElementById('turnstile-token').value = '';
    };

    window.onTurnstileError = function() {
        document.getElementById('turnstile-token').value = '';
    };
</script>

<?php
get_footer();