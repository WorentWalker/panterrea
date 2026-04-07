<?php

/**
 * Template Name: Reset Password
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

    <main class="actionTemplate resetPassword firstSectionPadding">
        <div class="actionTemplate__container">

            <?php
            $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

            if (!$token || !$user_id) {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            MessageSystem.showMessage('warning', '" . esc_js(__('Невірне або відсутнє посилання для скидання паролю', 'panterrea_v1')) . "');
                        });
                      </script>";
            } else {
                $stored_token = get_user_meta($user_id, 'password_reset_token', true);
                $expiration_time = get_user_meta($user_id, 'password_reset_token_expiration', true);
                if (!$stored_token || $stored_token !== $token) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                MessageSystem.showMessage('warning', '" . esc_js(__('Невірний токен для скидання паролю', 'panterrea_v1')) . "');
                            });
                          </script>";
                } elseif (!$expiration_time || time() > $expiration_time) {
                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                MessageSystem.showMessage('warning', '" . esc_js(__('Час сплинув', 'panterrea_v1')) . "');
                            });
                          </script>";
                } else { ?>
                    <div class="actionTemplate__innerPopUp">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_lock.svg'); ?>"
                             alt="Забули пароль?">
                        <h1 class="actionTemplate__innerPopUp__title h3"><?php _e('Введіть новий пароль', 'panterrea_v1'); ?></h1>
                        <div class="actionTemplate__innerPopUp__info body2">
                            <?php _e('Ваш пароль має містити щонайменше одну велику літеру, одну малу літеру, один спеціальний символ та бути довжиною щонайменше 8 символів.', 'panterrea_v1'); ?>
                        </div>
                        <form id="formReset" class="form form__actionTemplate">

                            <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>"/>
                            <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>"/>

                            <div class="input__form">
                                <input class="body2" type="password" name="password" id="password"
                                       placeholder="<?php _e('Новий пароль', 'panterrea_v1'); ?>"/>
                                <label class="body2" for="password"><?php _e('Новий пароль', 'panterrea_v1'); ?></label>
                                <div class="input__togglePass js-togglePass"></div>
                                <span class="error caption"></span>
                            </div>

                            <div class="input__form">
                                <input class="body2" type="password" name="confirmPassword" id="confirmPassword"
                                       placeholder="<?php _e('Підтвердіть пароль', 'panterrea_v1'); ?>"/>
                                <label class="body2" for="confirmPassword"><?php _e('Підтвердіть пароль', 'panterrea_v1'); ?></label>
                                <div class="input__togglePass js-togglePass"></div>
                                <span class="error caption"></span>
                                <span class="helper caption"><?php _e('Пароль має містити 8 знаків, велика буква, мала буква і символ.', 'panterrea_v1'); ?></span>
                            </div>

                            <button class="btn btn__submit button-large" type="submit"><?php _e('Оновити пароль', 'panterrea_v1'); ?></button>
                        </form>
                    </div>
                <?php }
            } ?>


        </div>
    </main>

<?php
get_footer();
