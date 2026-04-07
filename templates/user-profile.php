<?php
/**
 * Template Name: User Profile
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(URL_LOGIN);
    exit;
}

get_header();

$current_user = wp_get_current_user();
?>

    <main class="profilePage firstSectionPadding">
        <div class="container">
            <div class="profilePage__inner">
                <h1 class="profilePage__title h4"><?php _e('Деталі профілю', 'panterrea_v1'); ?></h1>

                <?php
                $user_id = get_current_user_id();

                if ($user_id) {
                    $user_info = get_userdata($user_id);
                    ?>

                    <div class="profilePage__tabs js-tabs">
                        <div role="tablist" class="profilePage__tabs__titles">
                            <div role="tab" data-tabpanel-id="tabpanel-1" class="profilePage__tabs__title active">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M10 4H14C17.771 4 19.657 4 20.828 5.172C22 6.343 22 8.229 22 12C22 15.771 22 17.657 20.828 18.828C19.657 20 17.771 20 14 20H10C6.229 20 4.343 20 3.172 18.828C2 17.657 2 15.771 2 12C2 8.229 2 6.343 3.172 5.172C4.343 4 6.229 4 10 4ZM13.25 9C13.25 8.80109 13.329 8.61032 13.4697 8.46967C13.6103 8.32902 13.8011 8.25 14 8.25H19C19.1989 8.25 19.3897 8.32902 19.5303 8.46967C19.671 8.61032 19.75 8.80109 19.75 9C19.75 9.19891 19.671 9.38968 19.5303 9.53033C19.3897 9.67098 19.1989 9.75 19 9.75H14C13.8011 9.75 13.6103 9.67098 13.4697 9.53033C13.329 9.38968 13.25 9.19891 13.25 9ZM14.25 12C14.25 11.8011 14.329 11.6103 14.4697 11.4697C14.6103 11.329 14.8011 11.25 15 11.25H19C19.1989 11.25 19.3897 11.329 19.5303 11.4697C19.671 11.6103 19.75 11.8011 19.75 12C19.75 12.1989 19.671 12.3897 19.5303 12.5303C19.3897 12.671 19.1989 12.75 19 12.75H15C14.8011 12.75 14.6103 12.671 14.4697 12.5303C14.329 12.3897 14.25 12.1989 14.25 12ZM15.25 15C15.25 14.8011 15.329 14.6103 15.4697 14.4697C15.6103 14.329 15.8011 14.25 16 14.25H19C19.1989 14.25 19.3897 14.329 19.5303 14.4697C19.671 14.6103 19.75 14.8011 19.75 15C19.75 15.1989 19.671 15.3897 19.5303 15.5303C19.3897 15.671 19.1989 15.75 19 15.75H16C15.8011 15.75 15.6103 15.671 15.4697 15.5303C15.329 15.3897 15.25 15.1989 15.25 15ZM11 9C11 9.53043 10.7893 10.0391 10.4142 10.4142C10.0391 10.7893 9.53043 11 9 11C8.46957 11 7.96086 10.7893 7.58579 10.4142C7.21071 10.0391 7 9.53043 7 9C7 8.46957 7.21071 7.96086 7.58579 7.58579C7.96086 7.21071 8.46957 7 9 7C9.53043 7 10.0391 7.21071 10.4142 7.58579C10.7893 7.96086 11 8.46957 11 9ZM9 17C13 17 13 16.105 13 15C13 13.895 11.21 13 9 13C6.79 13 5 13.895 5 15C5 16.105 5 17 9 17Z"/>
                                </svg>
                                <span class="subtitle2"><?php _e('Основна інформація', 'panterrea_v1'); ?></span>
                            </div>
                            <div role="tab" data-tabpanel-id="tabpanel-2" class="profilePage__tabs__title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M12.6501 10.0001C12.1676 8.62984 11.2042 7.48128 9.93891 6.76769C8.67358 6.0541 7.19226 5.82398 5.77008 6.12005C3.48008 6.58005 1.62008 8.41005 1.14008 10.7001C0.94656 11.5772 0.952231 12.4865 1.15667 13.3611C1.36112 14.2357 1.75912 15.0534 2.32139 15.7538C2.88366 16.4542 3.59587 17.0196 4.40558 17.4084C5.21529 17.7971 6.10188 17.9993 7.00008 18.0001C8.24002 18.0001 9.44942 17.6154 10.4614 16.8989C11.4734 16.1824 12.2381 15.1696 12.6501 14.0001H17.0001V16.0001C17.0001 17.1001 17.9001 18.0001 19.0001 18.0001C20.1001 18.0001 21.0001 17.1001 21.0001 16.0001V14.0001C22.1001 14.0001 23.0001 13.1001 23.0001 12.0001C23.0001 10.9001 22.1001 10.0001 21.0001 10.0001H12.6501ZM7.00008 14.0001C5.90008 14.0001 5.00008 13.1001 5.00008 12.0001C5.00008 10.9001 5.90008 10.0001 7.00008 10.0001C8.10008 10.0001 9.00008 10.9001 9.00008 12.0001C9.00008 13.1001 8.10008 14.0001 7.00008 14.0001Z"/>
                                </svg>
                                <span class="subtitle2"><?php _e('Зміна паролю', 'panterrea_v1'); ?></span>
                            </div>
                            <div role="tab" data-tabpanel-id="tabpanel-3" class="profilePage__tabs__title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M8.35206 20.242C8.78721 20.7922 9.34171 21.2364 9.97368 21.541C10.6056 21.8455 11.2985 22.0025 12.0001 22C12.7016 22.0025 13.3945 21.8455 14.0264 21.541C14.6584 21.2364 15.2129 20.7922 15.6481 20.242C13.2271 20.5702 10.773 20.5702 8.35206 20.242Z"
                                          fill="#637381"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M18.7501 9.704V9C18.7501 5.134 15.7271 2 12.0001 2C8.27306 2 5.25006 5.134 5.25006 9V9.704C5.25006 10.549 5.01006 11.375 4.55806 12.078L3.45006 13.801C2.43906 15.375 3.21106 17.514 4.97006 18.011C9.56631 19.313 14.4338 19.313 19.0301 18.011C20.7891 17.514 21.5611 15.375 20.5501 13.801L19.4421 12.078C18.9885 11.3693 18.749 10.5454 18.7501 9.704ZM12.0001 5.25C12.199 5.25 12.3897 5.32902 12.5304 5.46967C12.671 5.61032 12.7501 5.80109 12.7501 6V10C12.7501 10.1989 12.671 10.3897 12.5304 10.5303C12.3897 10.671 12.199 10.75 12.0001 10.75C11.8011 10.75 11.6104 10.671 11.4697 10.5303C11.3291 10.3897 11.2501 10.1989 11.2501 10V6C11.2501 5.80109 11.3291 5.61032 11.4697 5.46967C11.6104 5.32902 11.8011 5.25 12.0001 5.25Z"/>
                                </svg>
                                <span class="subtitle2"><?php _e('Налаштування сповіщень', 'panterrea_v1'); ?></span>
                            </div>
                        </div>


                        <div role="tabpanel" id="tabpanel-1" class="profilePage__tabs__content active">

                            <?php $user_name = get_user_meta($user_id, 'name', true);  ?>
                            <?php $user_city = get_user_meta($user_id, 'city', true);  ?>
                            <?php $user_phone = get_user_meta($user_id, 'phone', true);  ?>
                            <?php $email_verified = get_user_meta($user_id, 'email_verified', true) === '1'; ?>
                            <?php $user_profession = get_user_meta($user_id, 'profession', true);  ?>

                            <div id="showDataProfile" class="profilePage__tabs__show">
                                <div class="profilePage__tabs__row">
                                    <div class="profilePage__tabs__item">
                                        <span class="input-label"><?php _e('Ім’я', 'panterrea_v1'); ?></span>
                                        <span class="body2"><?= esc_html($user_name) ?? ''; ?></span>
                                    </div>

                                    <div class="profilePage__tabs__item">
                                        <span class="input-label"><?php _e('Місто проживання', 'panterrea_v1'); ?></span>
                                        <span class="body2"><?= esc_html($user_city) ?? ''; ?></span>
                                    </div>

                                    <div class="profilePage__tabs__item">
                                        <span class="input-label"><?php _e('Рід діяльності', 'panterrea_v1'); ?></span>
                                        <span class="body2"><?= esc_html($user_profession) ?? ''; ?></span>
                                    </div>
                                </div>

                                <div class="profilePage__tabs__row">
                                    <div class="profilePage__tabs__item">
                                        <span class="input-label"><?php _e('Електронна адреса', 'panterrea_v1'); echo !$email_verified ? ' (' . __('не верифіковано', 'panterrea_v1') . ')' : ''; ?></span>
                                        <span class="body2"><?= esc_html($user_info->user_email) ?? ''; ?></span>
                                    </div>
                                    <div class="profilePage__tabs__item">
                                        <span class="input-label"><?php _e('Контактний номер', 'panterrea_v1'); ?></span>
                                        <span class="body2"><?= esc_html($user_phone) ?? ''; ?></span>
                                    </div>
                                </div>

                                <div class="profilePage__tabs__rowBtn">
                                    <div id="editProfile" class="btn btn__submit button-large"><?php _e('Редагувати', 'panterrea_v1'); ?></div>
                                    <?php echo !$email_verified ? '<div id="emailVerified" class="btn btn__submit button-large">' . __('Верифікувати пошту', 'panterrea_v1') . '</div>' : ''; ?>
                                </div>

                            </div>

                            <form id="formEditProfile" class="form profilePage__tabs__edit hidden">

                                <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>" />

                                <div class="profilePage__tabs__row">
                                    <div class="input__form">
                                        <input class="body2" type="text" name="name" id="name" value="<?= esc_html($user_name) ?? ''; ?>" placeholder="<?php _e('Ім’я', 'panterrea_v1'); ?>"/>
                                        <label class="body2" for="name"><?php _e('Ім’я', 'panterrea_v1'); ?></label>
                                        <span class="error caption"></span>
                                    </div>

                                    <div class="input__form">
                                        <input class="body2" type="text" name="city" id="city" value="<?= esc_html($user_city) ?? ''; ?>"
                                               placeholder="<?php _e('Місто проживання', 'panterrea_v1'); ?>"/>
                                        <label class="body2" for="city"><?php _e('Місто проживання', 'panterrea_v1'); ?></label>
                                        <span class="error caption"></span>
                                    </div>

                                    <div class="input__form">
                                        <input class="body2" type="text" name="profession" id="profession" value="<?= esc_html($user_profession) ?? ''; ?>"
                                               placeholder="<?php _e('Рід діяльності', 'panterrea_v1'); ?>"/>
                                        <label class="body2" for="profession"><?php _e('Рід діяльності', 'panterrea_v1'); ?></label>
                                        <span class="error caption"></span>
                                    </div>
                                </div>

                                <div class="profilePage__tabs__row">
                                    <div class="input__form noEdit">
                                        <input class="body2" type="text" name="email" id="email" value="<?= esc_html($user_info->user_email) ?? ''; ?>"
                                               placeholder="<?php _e('Електронна адреса', 'panterrea_v1'); ?>"/>
                                        <label class="body2" for="email"><?php _e('Електронна адреса', 'panterrea_v1'); ?></label>
                                        <span class="error caption"></span>
                                    </div>

                                    <?php
                                    $country_code = 'UA';
                                    $flag_src = get_template_directory_uri() . '/src/svg/flags/ua.png';

                                    if (str_starts_with($user_phone, '+44')) {
                                        $country_code = 'GB';
                                        $flag_src = get_template_directory_uri() . '/src/svg/flags/GB.png';
                                    } elseif (str_starts_with($user_phone, '+49')) {
                                        $country_code = 'DE';
                                        $flag_src = get_template_directory_uri() . '/src/svg/flags/de.png';
                                    }
                                    ?>

                                    <div class="form__rowPhone">
                                        <div class="input__form js-fakeSelectFlagWrapper">
                                            <label class="subtitle2" for="mask"></label>
                                            <input class="body2 js-fakeSelectFlagInput" type="text" name="mask" id="mask" value="<?= esc_attr($country_code); ?>" readonly/>

                                            <div class="input__fakeSelectFlag js-fakeSelectFlagImg">
                                                <img src="<?= esc_url($flag_src); ?>" alt="<?= esc_attr($country_code); ?>">
                                            </div>

                                            <ul class="input__fakeSelect js-fakeSelectFlagOptions">
                                                <li class="input__fakeSelect__flag" data-value="ua" data-flag="UA"><img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/flags/ua.png'); ?>" alt="UA"></li>
                                                <li class="input__fakeSelect__flag" data-value="gb" data-flag="GB"><img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/flags/gb.png'); ?>" alt="GB"></li>
                                                <li class="input__fakeSelect__flag" data-value="de" data-flag="DE"><img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/flags/de.png'); ?>" alt="DE"></li>
                                            </ul>

                                            <div class="input__arrowBottom"></div>
                                            <span class="error caption"></span>
                                        </div>

                                        <div class="input__form">
                                            <input class="body2" type="text" name="phone" id="phone" value="<?= esc_html($user_phone) ?? ''; ?>"
                                                   placeholder="<?php _e('Контактний номер', 'panterrea_v1'); ?>"/>
                                            <label class="body2" for="phone"><?php _e('Контактний номер', 'panterrea_v1'); ?></label>
                                            <span class="error caption"></span>
                                        </div>
                                    </div>

                                </div>

                                <div class="profilePage__tabs__rowBtn">
                                    <div id="editCancelProfile" class="btn btn__submit btn__submit__transparent button-large"><?php _e('Скасувати', 'panterrea_v1'); ?></div>
                                    <button class="btn btn__submit button-large" type="submit"><?php _e('Зберегти', 'panterrea_v1'); ?></button>
                                </div>
                            </form>

                        </div>

                        <div role="tabpanel" id="tabpanel-2" class="profilePage__tabs__content ">
                            <form id="formEditPass" class="form profilePage__tabs__editPass">

                                <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>" />

                                <div class="input__form">
                                    <input class="body2" type="password" name="oldPassword" id="oldPassword" placeholder="<?php _e('Існуючий пароль', 'panterrea_v1'); ?>"/>
                                    <label class="body2" for="oldPassword"><?php _e('Існуючий пароль', 'panterrea_v1'); ?></label>
                                    <div class="input__togglePass js-togglePass"></div>
                                    <span class="error caption"></span>
                                </div>

                                <div class="input__form">
                                    <input class="body2" type="password" name="password" id="password" placeholder="<?php _e('Новий пароль', 'panterrea_v1'); ?>"/>
                                    <label class="body2" for="password"><?php _e('Новий пароль', 'panterrea_v1'); ?></label>
                                    <div class="input__togglePass js-togglePass"></div>
                                    <span class="error caption"></span>
                                </div>

                                <div class="input__form">
                                    <input class="body2" type="password" name="confirmPassword" id="confirmPassword"
                                           placeholder="<?php _e('Підтвердьте новий пароль', 'panterrea_v1'); ?>"/>
                                    <label class="body2" for="confirmPassword"><?php _e('Підтвердьте новий пароль', 'panterrea_v1'); ?></label>
                                    <div class="input__togglePass js-togglePass"></div>
                                    <span class="error caption"></span>
                                </div>

                                <button class="btn btn__submit button-large" type="submit"><?php _e('Оновити пароль', 'panterrea_v1'); ?></button>
                            </form>
                        </div>

                        <div role="tabpanel" id="tabpanel-3" class="profilePage__tabs__content ">
                            <div class="profilePage__tabs__notification">
                                <div class="profilePage__tabs__notification__info">
                                    <div class="h6"><?php _e('Сповіщення платформи', 'panterrea_v1'); ?></div>
                                    <div class="body2"><?php _e('Тут ви можете налаштувати, які сповіщення хочете отримувати', 'panterrea_v1'); ?></div>
                                </div>
                                <div class="profilePage__tabs__notification__settings">

                                    <!--<div class="profilePage__tabs__notification__setting">
                                        <div class="table-head">
                                            Сповіщення в додатку <span class="body2">(ввімкніть/вимикніть push-сповіщення для мобільних пристроїв.)</span>
                                        </div>
                                        <div class="switcher js-toggleSwitch"></div>
                                    </div>-->

                                    <?php
                                    $notification_types = [
                                        'password_reset'  => __('Пароль відновлено', 'panterrea_v1'),
                                        'create_ad'       => __('Оголошення опубліковано', 'panterrea_v1'),
                                        'delete_ad'       => __('Оголошення видалено', 'panterrea_v1'),
                                        'boost_ad'        => __('Оголошення рекламується', 'panterrea_v1'),
                                        'new_message'     => __('Нове повідомлення у чатах', 'panterrea_v1'),
                                        'boost_expiring'  => __('Реклама оголошення закінчується', 'panterrea_v1'),
                                        'boost_expired'   => __('Реклама оголошення закінчилась', 'panterrea_v1'),
                                    ];
                                    ?>

                                    <?php foreach ($notification_types as $type => $label) :
                                        $status = get_user_meta($user_id, "email_notify_$type", true);
                                        $is_active_class = $status === '1' ? 'active' : '';
                                        ?>
                                        <div class="profilePage__tabs__notification__setting">
                                            <div class="table-head">
                                                <span class="body2"><?php echo sprintf(__('Email-сповіщення типу "%s"', 'panterrea_v1'), esc_html($label)); ?></span>
                                            </div>
                                            <div
                                                    id="emailNotification-<?php echo esc_attr($type); ?>"
                                                    class="switcher js-toggleSwitch <?php echo esc_attr($is_active_class); ?>"
                                                    data-type="<?php echo esc_attr($type); ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                </div>
                            </div>
                        </div>

                    </div>

                <?php } ?>

            </div>
        </div>
    </main>

<?php
get_footer();
