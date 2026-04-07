<?php
/**
 * Template Name: Boost Ad
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
    setMessageCookies('warning', 'Підтвердіть свою електронну пошту для доступу.');
    wp_redirect(home_url());
    exit;
}

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

$author_id = (int)get_post_field('post_author', $post_id);
$is_author = ($author_id === get_current_user_id());
$is_active = get_post_meta($post_id, '_is_active', true);

if (!$is_active || !$is_author || $post_id === 0) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    include get_template_directory() . '/404.php';
    exit;
}

$author_name = trim(get_user_meta($author_id, 'name', true));

get_header();
?>

<main class="boostPage firstSectionPadding">
    <div class="container">

        <div id="stepPayment" class="boostPage__inner">

            <div class="boostPage__title h3"><?= __('Давайте завершимо!', 'panterrea_v1') ?></div>
            <div class="boostPage__subtitle body1"><?= __('Це найкращий вибір для Вас', 'panterrea_v1') ?></div>

            <form id="formBoost" class="form__boost" data-post-id="<?php echo esc_attr($post_id); ?>" data-author-name="<?php echo esc_attr($author_name); ?>">

                <div class="form__boost__left">
                    <div class="form__boost__left__title h6"><?= __('Платіжні дані', 'panterrea_v1') ?></div>
                    <div id="card-element" class="form__boost__left__card">
                        <!-- Stripe Elements -->
                    </div>
                </div>

                <div class="form__boost__right">
                    <div class="title h4"><?= __('Швидкий <span class="tag label-text">Найпопулярніший</span>', 'panterrea_v1') ?></div>
                    <div class="price h2"><?= __('90,00 грн', 'panterrea_v1') ?></div>
                    <div class="sep"></div>
                    <div class="list">
                        <div class="list__title overline"><?= __('Переваги', 'panterrea_v1') ?></div>
                        <div class="list__item body2 checked"><span><?= __('Підняття в топ протягом 30ти днів, що в рази пришвидшить продаж', 'panterrea_v1') ?></span></div>
                    </div>
                    <div class="totalPrice subtitle1"><?= __('Загальна сума оплати', 'panterrea_v1') ?><span class="subtitle1"><?= __('90,00 грн', 'panterrea_v1') ?></span></div>
                    <button id="submit" class="btn btn__submit button-large"><?= __('Оплатити рекламу', 'panterrea_v1') ?></button>
                </div>

            </form>
        </div>

        <div id="stepSuccess" class="boostPage__innerSuccess hidden">
            <div class="boostPage__title h3"><?= __('Ваше оголошення успішно рекламується!', 'panterrea_v1') ?></div>
            <div class="boostPage__subtitle body1"><?= __('Ви можете переглянути всі ваші оголошення перейшовши по пункту меню “Мої оголошення”', 'panterrea_v1') ?></div>

            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/ad_create.avif'); ?>" alt="Ad Create">

            <a href="<?= home_url() ?>" class="btn btn__submit button-large"><?= __('Повернутись на Головну сторінку', 'panterrea_v1') ?></a>
            <a href="<?= esc_url(URL_USERAD) ?>" class="btn btn__submit btn__submit__transparentGreen button-large"><?= __('Переглянути Мої оголошення', 'panterrea_v1') ?></a>
        </div>

    </div>
</main>
<?php
get_footer();
