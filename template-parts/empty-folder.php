<?php
$title = $args['title'] ?? false;
$info = $args['info'] ?? false;
?>

<div class="emptyFolder">
    <div class="emptyFolder__inner">

        <?php if ($title) : ?>
            <h2 class="emptyFolder__title h3"><?= esc_html($title); ?></h2>
        <?php endif; ?>

        <div class="emptyFolder__content">

            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/empty_folder.avif'); ?>" alt="Empty">

            <?php if ($info) : ?>
                <div class="emptyFolder__info h6"><?= esc_html($info); ?></div>
            <?php endif; ?>

        </div>

    </div>
</div>