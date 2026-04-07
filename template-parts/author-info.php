<?php
$author_id = $args['author_id'] ?? false;
?>

<div class="authorBlock">
    <?php
    $author_info = get_catalog_post_author_info($author_id);
    if ($author_info) { ?>

    <div class="authorBlock__info">
        <h3 class="authorBlock__title h5"><?= esc_html($author_info['name']) ?? ''; ?></h3>
        <p class="authorBlock__city body2"><?= esc_html($author_info['city']) ?? ''; ?></p>
    </div>

    <div class="btn btn__showPhone button-large js-showPhone" data-user="<?= esc_html($author_id); ?>">
        <?= __('Показати мобільний номер', 'panterrea_v1') ?>
    </div>

    <?php } ?>
</div>