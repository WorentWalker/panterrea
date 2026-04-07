<?php get_header(); ?>

    <main class="firstSectionPadding noPage">
        <div class="container">
            <?php
            $current_url = $_SERVER['REQUEST_URI'];
            if (str_contains($current_url, '/catalog/')) :
                ?>
                <div class="noPage__title h3"><?php _e('Оголошення не знайдено або його було деактивовано', 'panterrea_v1'); ?></div>
            <?php else : ?>
                <div class="noPage__title h3"><?php _e('Цієї сторінки не існує', 'panterrea_v1'); ?></div>
            <?php endif; ?>
            <a href="<?= home_url() ?>" class="btn btn__submit button-large"><?php _e('Повернутися на головну', 'panterrea_v1'); ?></a>
        </div>
    </main>

<?php get_footer(); ?>