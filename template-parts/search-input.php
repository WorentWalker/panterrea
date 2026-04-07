<div class="searchBlock <?= $args['width'] ?? ''; ?>">
    <input id="searchInput" type="text" name="search"
        placeholder="<?php _e('Наприклад: добрива...', 'panterrea_v1'); ?>" class="input input__search body1"
        aria-label="Search">
    <div class="btn__search"></div>

    <div class="searchBlock__recommend hidden">
        <div class="searchBlock__recommend__title caption"><?php _e('Рекомендації', 'panterrea_v1'); ?></div>
        <div class="searchBlock__recommend__content">

        </div>
    </div>
</div>