<?php

/**
 * Template Name: Forgot Password
 */

get_header();
?>

    <main class="actionTemplate forgotPassword firstSectionPadding">
        <div class="actionTemplate__container">

            <div class="actionTemplate__innerPopUp">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_lock.svg'); ?>"
                     alt="Забули пароль?">
                <h1 class="actionTemplate__innerPopUp__title h3"><?php _e('Забули пароль?', 'panterrea_v1'); ?></h1>
                <div class="actionTemplate__innerPopUp__info body2"><?php _e('Будь ласка, введіть адресу електронної пошти, пов’язану з вашим акаунтом, і ми надішлемо вам посилання для скидання пароля.', 'panterrea_v1'); ?></div>
                <form id="formForgot" class="form form__actionTemplate">

                    <div class="input__form">
                        <input class="body2" type="text" name="email" id="email" placeholder="<?php _e('Електронна адреса', 'panterrea_v1'); ?>"/>
                        <label class="body2" for="email"><?php _e('Електронна адреса', 'panterrea_v1'); ?></label>
                        <span class="error caption"></span>
                    </div>

                    <button class="btn btn__submit button-large" type="submit"><?php _e('Надіслати запит', 'panterrea_v1'); ?></button>
                </form>

                <a href="<?= URL_LOGIN ?>" class="actionTemplate__backLink subtitle2"><span><?php _e('Повернутися до входу', 'panterrea_v1'); ?></span></a>
            </div>

            <?php get_template_part('template-parts/check-email', null, [
                'title' => __('Перевірте свою електронну пошту!', 'panterrea_v1'),
                'info' => __('Будь ласка, відкрийте лист і натисніть на посилання, щоб завершити процес відновлення паролю. Зверніть увагу, що лист може надійти з затримкою до 15 хвилин. Якщо ви не отримали його, перевірте папку "Спам" або спробуйте ще раз.', 'panterrea_v1'),
                'textBackLink' => __('Назад', 'panterrea_v1'),
                'urlBackLink' => URL_LOGIN
            ]); ?>

        </div>
    </main>

<?php
get_footer();
