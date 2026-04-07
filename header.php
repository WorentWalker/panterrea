<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    (function(){
        if (sessionStorage.getItem('headerTopSeen')) {
            document.documentElement.classList.add('header-top-seen');
        } else {
            setTimeout(function(){ sessionStorage.setItem('headerTopSeen', '1'); }, 700);
        }
    })();
    </script>
    <?php wp_head(); ?>

</head>

<?php global $whiteBg ?>
<?php global $actionTemplate ?>
<?php /*global $currentLang*/ ?>

<?php

if (is_user_logged_in()) {
    global $wpdb;
    $user_id = get_current_user_id();

    $has_unread = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}chat_messages m
     JOIN {$wpdb->prefix}chat_sessions s ON m.chat_id = s.chat_id
     WHERE (s.user1_id = %d AND m.sender_id != s.user1_id OR 
            s.user2_id = %d AND m.sender_id != s.user2_id)
     AND m.is_read = 0",
        $user_id, $user_id
    ));

    $new_class = ($has_unread > 0) ? ' new' : '';

    $table_name = $wpdb->prefix . 'notifications';
    $notifications = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC LIMIT 10",
            $user_id
        )
    );

    $has_unread_notifications = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}notifications 
         WHERE user_id = %d AND status = 'unread'",
            $user_id
        )
    );

    $new_notification_class = ($has_unread_notifications > 0) ? ' new' : '';
}
?>

<body <?php body_class(array($whiteBg ? 'whiteBg' : '')); ?>>

    <header id="header" class="header <?= ($actionTemplate) ? 'header__actionTemplate' : ''; ?>">
        <style>
        .header__top {
            animation: slideDownHeaderTop 0.6s cubic-bezier(0.4, 0, 0.2, 1) both;
            will-change: transform, opacity;
        }
        html.header-top-seen .header__top {
            animation: none;
            transform: none;
            opacity: 1;
        }

        @keyframes slideDownHeaderTop {
            0% {
                transform: translateY(-100%);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
        </style>
        <div class="header__top">
            <div class="container">
                <div class="header__info">
                    <?php
                        $header_phone = get_field('header_phone', 'option');
                        $header_email = get_field('header_email', 'option');
                    ?>
                    <a href="tel:<?php echo esc_attr($header_phone); ?>" class="header__info__item">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M14.6417 11.9753C14.6417 12.2393 14.583 12.5107 14.4584 12.7747C14.3337 13.0387 14.1724 13.288 13.9598 13.5227C13.6005 13.9187 13.2046 14.2047 12.7574 14.388C12.3175 14.5713 11.8409 14.6667 11.3277 14.6667C10.5798 14.6667 9.78066 14.4907 8.9375 14.1313C8.09434 13.772 7.25118 13.288 6.41535 12.6793C5.57219 12.0633 4.77302 11.3813 4.01051 10.626C3.25533 9.86333 2.57347 9.064 1.96493 8.228C1.36372 7.392 0.87982 6.556 0.527892 5.72733C0.175964 4.89133 0 4.092 0 3.32933C0 2.83067 0.0879819 2.354 0.263946 1.914C0.43991 1.46667 0.71852 1.056 1.10711 0.689333C1.57634 0.227333 2.08957 0 2.63213 0C2.83742 0 3.04271 0.0440001 3.22601 0.132C3.41663 0.22 3.58527 0.352 3.71724 0.542667L5.41822 2.94067C5.5502 3.124 5.64551 3.29267 5.7115 3.454C5.77748 3.608 5.81414 3.762 5.81414 3.90133C5.81414 4.07733 5.76282 4.25333 5.66017 4.422C5.56486 4.59067 5.42556 4.76667 5.24959 4.94267L4.69237 5.522C4.61172 5.60267 4.57506 5.698 4.57506 5.81533C4.57506 5.874 4.5824 5.92533 4.59706 5.984C4.61905 6.04267 4.64105 6.08667 4.65571 6.13067C4.78769 6.37267 5.01497 6.688 5.33757 7.06933C5.66751 7.45067 6.01943 7.83933 6.40069 8.228C6.79661 8.61667 7.17786 8.976 7.56645 9.306C7.94771 9.62867 8.26297 9.84867 8.51226 9.98067C8.54892 9.99533 8.59291 10.0173 8.64423 10.0393C8.70288 10.0613 8.76154 10.0687 8.82753 10.0687C8.95217 10.0687 9.04748 10.0247 9.12813 9.944L9.68535 9.394C9.86865 9.21067 10.0446 9.07133 10.2132 8.98333C10.3819 8.88067 10.5505 8.82933 10.7338 8.82933C10.8731 8.82933 11.0197 8.85867 11.181 8.92467C11.3423 8.99067 11.511 9.086 11.6943 9.21067L14.1211 10.934C14.3117 11.066 14.4437 11.22 14.5244 11.4033C14.5977 11.5867 14.6417 11.77 14.6417 11.9753Z"
                                fill="white" />
                        </svg>
                        <span>
                            <?php if (!empty($header_phone)) : ?>
                            <?php echo esc_html($header_phone); ?>
                            <?php else : ?>
                            +380 (99) 123 45 67
                            <?php endif; ?>
                        </span>
                    </a>
                    <a href="mailto:<?php echo esc_attr($header_email); ?>" class="header__info__item">
                        <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M13.125 0H4.375C1.75 0 0 1.25 0 4.16667V10C0 12.9167 1.75 14.1667 4.375 14.1667H13.125C15.75 14.1667 17.5 12.9167 17.5 10V4.16667C17.5 1.25 15.75 0 13.125 0ZM13.5362 5.075L10.7975 7.15833C10.22 7.6 9.485 7.81667 8.75 7.81667C8.015 7.81667 7.27125 7.6 6.7025 7.15833L3.96375 5.075C3.68375 4.85833 3.64 4.45833 3.85875 4.19167C4.08625 3.925 4.4975 3.875 4.7775 4.09167L7.51625 6.175C8.18125 6.68333 9.31 6.68333 9.975 6.175L12.7137 4.09167C12.9937 3.875 13.4137 3.91667 13.6325 4.19167C13.86 4.45833 13.8162 4.85833 13.5362 5.075Z"
                                fill="white" />
                        </svg>
                        <span>
                            <?php if (!empty($header_email)) : ?>
                            <?php echo esc_html($header_email); ?>
                            <?php else : ?>
                            info@panterrea.com
                            <?php endif; ?>
                        </span>
                    </a>
                </div>
                <div class="header__langBar">
                    <button id="langBtn" type="button">
                        Loading...
                    </button>
                </div>
            </div>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const btn = document.getElementById("langBtn");
                if (!btn) return;

                const path = window.location.pathname; // напр. "/", "/en/", "/en/contact/"

                let currentLang;

                // Определяем язык по URL:
                if (path === "/en" || path.startsWith("/en/")) {
                    currentLang = "en";
                } else {
                    // всё остальное считаем украинским (язык по умолчанию без префикса)
                    currentLang = "uk";
                }

                function updateButtonText() {
                    btn.textContent = currentLang === "en" ? "English" : "Ukrainian";
                }

                updateButtonText();

                btn.addEventListener("click", function(e) {
                    e.preventDefault(); // на всякий случай, если в форме

                    const search = window.location.search || "";
                    const hash = window.location.hash || "";
                    let newUrl;

                    if (currentLang === "en") {
                        // Было /en/... → переключаем на украинский без префикса
                        const cleanPath = path.replace(/^\/en/, "") || "/";
                        newUrl = cleanPath + search + hash;
                        currentLang = "uk";
                    } else {
                        // Было украинский (/) → добавляем /en перед путём
                        const cleanPath = path === "/" ? "" : path; // чтобы не было /en//
                        newUrl = "/en" + cleanPath + search + hash;
                        currentLang = "en";
                    }

                    // Обновляем текст кнопки сразу (на случай, если будет SPA-навигация)
                    updateButtonText();

                    // Переход на новый URL
                    window.location.href = newUrl;
                });
            });
            </script>
        </div>
        <div class="container">
            <div class="header__inner">
                <div class="header__left">
                    <a href="<?= home_url() ?>" class="header__logoSection">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/logo_green.svg'); ?>"
                            alt="Logo">
                        <div class="header__logoSection__title h5">PanTerrea</div>
                    </a>
                    <?php
                    wp_nav_menu(array(
                        'menu' => 'Header Menu', // Используем имя меню напрямую
                        'theme_location' => 'header_menu', // Fallback на location
                        'container' => 'nav',
                        'container_class' => 'header__menu',
                        'menu_class' => 'header__menu-list',
                        'fallback_cb' => false,
                    ));
                    ?>
                </div>
                <div class="header__right">
                    <div class="header__accessBar">

                        <?php if (!$actionTemplate) { ?>



                        <?php if (!is_user_logged_in()) { ?>
                        <div class="header__langBar header__mob__noLoginShow">
                            <?php echo do_shortcode('[gtranslate]'); ?>
                            <!--<div class="header__langBar__item body2 <?php /*echo ($currentLang === 'uk') ? 'active' : ''; */ ?> js-langSelect" data-lang="uk">Укр</div>
                            <div class="header__langBar__sep"></div>
                            <div class="header__langBar__item body2 <?php /*echo ($currentLang === 'en') ? 'active' : ''; */ ?> js-langSelect" data-lang="en">Eng</div>-->
                        </div>
                        <?php } ?>

                        <?php if (is_user_logged_in()) { ?>

                        <a href="<?= esc_url(URL_FAVORITES) ?>" class="header__accessIcon header__mob__hidden">
                            <img src="/wp-content/uploads/2025/12/icons.svg" alt="Favorites">
                        </a>

                        <a id="headerMessages" href="<?= esc_url(URL_MESSAGES) ?>"
                            class="header__accessIcon <?= esc_attr($new_class) ?>">
                            <img src="/wp-content/uploads/2025/12/message.svg" alt="Messages">
                        </a>

                        <div id="toggleNotificationPanel"
                            class="header__accessIcon <?= esc_attr($new_notification_class) ?>">
                            <img src="/wp-content/themes/panterrea_v1/src/svg/start icon1.svg" alt="Notification">
                        </div>

                        <?php } ?>
                        <?php } ?>
                        <div class="header__actionButtons">

                            <?php if (!$actionTemplate) { ?>
                            <?php if (is_user_logged_in()) { ?>
                            <div class="header__profileMenu header__dropdown">
                                <div
                                    class="btn btn__transparent button-large header__dropdown__title header__mob__hidden">
                                    <?php _e('Мій профіль', 'panterrea_v1'); ?>
                                    <svg width="11" height="7" viewBox="0 0 11 7" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M4.42664 6.00115C4.82004 6.40999 5.47439 6.40999 5.86779 6.00115L10.013 1.69338C10.6243 1.05807 10.174 0 9.29238 0H1.00205C0.120393 0 -0.329844 1.05807 0.281477 1.69338L4.42664 6.00115Z"
                                            fill="#147575" />
                                    </svg>

                                </div>

                                <div class="header__mob__profile">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none">
                                        <path
                                            d="M12 10C14.2091 10 16 8.20914 16 6C16 3.79086 14.2091 2 12 2C9.79086 2 8 3.79086 8 6C8 8.20914 9.79086 10 12 10Z"
                                            fill="#147575" />
                                        <path
                                            d="M12 21C15.866 21 19 19.2091 19 17C19 14.7909 15.866 13 12 13C8.13401 13 5 14.7909 5 17C5 19.2091 8.13401 21 12 21Z"
                                            fill="#147575" />
                                    </svg>
                                </div>


                                <div class="header__dropdown__list">

                                    <div class="header__langBar header__mob__lang">
                                        <?php echo do_shortcode('[gtranslate]'); ?>
                                        <!--<div class="header__langBar__item body2 <?php /*echo ($currentLang === 'uk') ? 'active' : ''; */ ?> js-langSelect" data-lang="uk">Укр</div>
                                            <div class="header__langBar__sep"></div>
                                            <div class="header__langBar__item body2 <?php /*echo ($currentLang === 'en') ? 'active' : ''; */ ?> js-langSelect" data-lang="en">Eng</div>-->
                                    </div>

                                    <a href="<?= esc_url(URL_CATALOG) ?>"
                                        class="header__dropdown__item subtitle2"><?php _e('Каталог', 'panterrea_v1'); ?></a>
                                    <a href="<?= esc_url(URL_USERAD) ?>"
                                        class="header__dropdown__item subtitle2"><?php _e('Мої оголошення', 'panterrea_v1'); ?></a>
                                    <a href="<?= esc_url(URL_FAVORITES) ?>"
                                        class="header__dropdown__item subtitle2 header__mob__show"><?php _e('Обрані оголошення', 'panterrea_v1'); ?></a>
                                    <a href="<?= esc_url(URL_MESSAGES) ?>"
                                        class="header__dropdown__item subtitle2"><?php _e('Повідомлення', 'panterrea_v1'); ?></a>
                                    <a href="<?= esc_url(URL_PROFILE) ?>"
                                        class="header__dropdown__item subtitle2"><?php _e('Деталі профілю', 'panterrea_v1'); ?></a>

                                    <!--<a href="<?php /*= esc_url(URL_FORUM) */ ?>" class="header__dropdown__item subtitle2"><?php /*_e('Форум', 'panterrea_v1'); */ ?></a>-->

                                    <a href="<?php echo wp_logout_url(home_url()); ?>"
                                        class="header__dropdown__item logout subtitle2"><?php _e('Вийти', 'panterrea_v1'); ?></a>
                                </div>
                            </div>
                            <?php } ?>

                            <!-- <a href="<?= esc_url(URL_FORUM) ?>" class="btn btn__green button-large mobIcon">
                                <span class="header__mob__hidden"><?php _e('Форум', 'panterrea_v1'); ?></span>
                                <span class="header__mob__show">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path
                                            d="M18 9H20C20.5304 9 21.0391 9.21071 21.4142 9.58579C21.7893 9.96086 22 10.4696 22 11V22L18 18H12C11.4696 18 10.9609 17.7893 10.5858 17.4142C10.2107 17.0391 10 16.5304 10 16V15M14 9C14 9.53043 13.7893 10.0391 13.4142 10.4142C13.0391 10.7893 12.5304 11 12 11H6L2 15V4C2 2.9 2.9 2 4 2H12C12.5304 2 13.0391 2.21071 13.4142 2.58579C13.7893 2.96086 14 3.46957 14 4V9Z"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </a> -->

                            <?php if (!is_user_logged_in()) { ?>
                            <a href="<?= esc_url(URL_LOGIN) ?>" class="btn btn__transparent button-large mobIcon">
                                <span class="header__mob__hidden"><?php _e('Вхід', 'panterrea_v1'); ?></span>
                                <span class="header__mob__show">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none">
                                        <path
                                            d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15M10 17L15 12M15 12L10 7M15 12H3"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </a>
                            <?php } ?>

                            <a href="<?= esc_url(URL_CREATEAD) ?>"
                                class="btn btn__green button-large mobIcon <?php echo !is_user_logged_in() ? 'is-guest' : ''; ?>">
                                <span
                                    class="header__mob__hidden"><?php _e('Додати оголошення', 'panterrea_v1'); ?></span>
                                <span class="header__mob__show">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none">
                                        <path d="M5 12H19M12 5V19" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </a>
                            <?php } else { ?>
                            <a href="<?= home_url() ?>" class="btn btn__transparent button-large">
                                <?php _e('Назад до оголошень', 'panterrea_v1'); ?>
                            </a>
                            <?php } ?>
                            <!-- Бургер-меню для мобильной версии -->
                            <button class="header__burger header__mob__show" id="headerBurger" aria-label="Меню">
                                <span class="header__burger__line"></span>
                                <span class="header__burger__line"></span>
                                <span class="header__burger__line"></span>
                            </button>


                        </div>
                    </div>

                </div>
            </div>
        </div>
    </header>

    <!-- Мобильное меню -->
    <div class="header__mobileMenu" id="headerMobileMenu">
        <div class="header__mobileMenu__overlay" id="headerMobileMenuOverlay"></div>
        <div class="header__mobileMenu__content">
            <div class="header__mobileMenu__header">
                <a href="<?= home_url() ?>" class="header__mobileMenu__logo">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/logo_green.svg'); ?>"
                        alt="Logo">
                    <div class="header__logoSection__title h5">PanTerrea</div>
                </a>
                <button class="header__mobileMenu__close" id="headerMobileMenuClose" aria-label="Закрыть меню">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <nav class="header__mobileMenu__nav">
                <?php
                wp_nav_menu(array(
                    'menu' => 'Header Menu',
                    'theme_location' => 'header_menu',
                    'container' => false,
                    'menu_class' => 'header__mobileMenu__list',
                    'fallback_cb' => false,
                ));
                ?>
            </nav>
        </div>
    </div>

    <?php if (is_user_logged_in()) : ?>

    <div class="notificationPanel">
        <div class="notificationPanel__header">
            <div id="notificationTitle" class="notificationPanel__header__title h6">
                <?php _e('Сповіщення', 'panterrea_v1'); ?>
                (<?php echo !empty($notifications) ? count($notifications) : 0; ?>)
            </div>
            <div id="closeNotificationPanel" class="notificationPanel__header__close"></div>
        </div>

        <div id="notificationContent" class="notificationPanel__content">
            <?php if (!empty($notifications)) : ?>
            <?php foreach ($notifications as $notification) : ?>
            <div class="notificationPanel__item">
                <div class="notificationPanel__item__date caption"
                    data-server-date="<?php echo esc_attr($notification->created_at); ?>">
                    <?php echo date("d.m.Y H:i", strtotime($notification->created_at)); ?>
                </div>
                <div
                    class="notificationPanel__item__text subtitle2 <?php echo ($notification->status === 'read') ? 'read' : ''; ?>">
                    <?php echo esc_html($notification->message); ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="notificationPanel__footer">
            <div id="deleteAllNotification" class="btn btn__deleteAd button-large notificationPanel__delete">
                <?php _e('Видалити всі', 'panterrea_v1'); ?></div>
        </div>
    </div>

    <?php endif; ?>