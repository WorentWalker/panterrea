<?php
$author_id = get_post_field('post_author', get_the_ID());
$author_name = get_user_meta($author_id, 'name', true) ?: get_the_author();
$media = get_field('files_url');

$comments = get_comments([
    'post_id' => get_the_ID(),
    'status' => 'approve',
    'order' => 'ASC',
    'parent' => 0,
]);

$rendered_count = 0;
?>

<div class="forum__itemPost" data-post-id="<?php echo get_the_ID(); ?>">
    <div class="forum__itemPost__header">
        <div class="forum__itemPost__info">
            <div class="forum__itemPost__author subtitle1"><?php echo esc_html($author_name); ?></div>
            <div class="forum__itemPost__date caption"><?php echo get_the_date('d.m.Y'); ?></div>
        </div>
        <?php if (is_user_logged_in() && get_current_user_id() === (int)$author_id) : ?>
            <div class="forum__itemPost__options js-forumOptions">
                <div class="forum__itemPost__optionsList hidden">
                    <div class="forum__itemPost__option body2 edit js-forumEditTrigger">
                        <?php echo __('Редагувати', 'panterrea_v1'); ?>
                    </div>
                    <div class="forum__itemPost__option body2 delete js-openPopUp" data-popUp="deleteForumItem">
                        <?php echo __('Видалити', 'panterrea_v1'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="forum__itemPost__content body2">
        <?php the_content(); ?>
    </div>

    <?php if ($media && is_array($media)) : ?>
        <div class="forum__itemPost__media" data-media='<?php echo json_encode($media); ?>'>
            <?php
            $total = count($media);
            foreach ($media as $i => $item) :
                if ($i > 1) break;

                $url = $item['url'] ?? '';
                $type = $item['type'] ?? '';
                $is_last_visible = ($i === 1 && $total > 2);
                $remaining = $total - 2;
                ?>
                <div class="forum__itemPost__mediaItem js-forumSlider">
                    <?php if (str_starts_with($type, 'image')) : ?>
                        <img src="<?php echo esc_url($url); ?>" alt="">
                    <?php elseif (str_starts_with($type, 'video')) : ?>
                        <video controls>
                            <source src="<?php echo esc_url($url); ?>" type="<?php echo esc_attr($type); ?>">
                            <?php echo __('Ваш браузер не підтримує відео.', 'panterrea_v1'); ?>
                        </video>
                    <?php endif; ?>

                    <?php if ($is_last_visible) : ?>
                        <div class="forum__itemPost__mediaOverlay h3">
                            +<?php echo $remaining; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="forum__itemPost__additional">
        <?php
        $likes = get_post_meta(get_the_ID(), '_forum_likes', true);
        $likes = is_array($likes) ? $likes : [];
        $count = count($likes);
        $class = in_array(get_current_user_id(), $likes) ? 'active' : '';
        ?>

        <div class="forum__itemPost__likes">
            <div class="btn btn__favorites js-likes <?php echo esc_attr($class); ?>"
                 data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                <span class="js-likes-count body2"><?php echo $count > 0 ? $count : ''; ?></span>
            </div>
        </div>
        <div class="forum__itemPost__share">
            <div class="btn btn__shareForum js-shareForum"
                 data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
            </div>
        </div>
    </div>

    <div class="forum__itemPost__commentsBlock">

        <form class="form form__forum js-forumCommentForm <?php echo !is_user_logged_in() ? 'is-guest' : ''; ?>"
              id="commentForm-<?php echo get_the_ID(); ?>"
              data-post-id="<?php echo get_the_ID(); ?>"
              enctype="multipart/form-data">

            <div class="input__form">
                <input class="body2" type="text" name="comment" id="comment-<?php echo get_the_ID(); ?>"
                       placeholder="<?php _e('Коментувати...', 'panterrea_v1'); ?>"/>
                <label class="body2"
                       for="comment-<?php echo get_the_ID(); ?>"><?php _e('Коментувати...', 'panterrea_v1'); ?></label>
                <span class="error caption"></span>
            </div>

            <div class="form__rowBtn">
                <button class="btn btn__submit button-medium"
                        type="submit"><?= __('Коментувати', 'panterrea_v1') ?></button>
            </div>

        </form>

        <div class="forum__itemPost__comments">
            <?php if ($comments): foreach ($comments as $comment):
                $rendered_count++;
                $extra_class = $rendered_count > 3 ? ' hidden-comment' : '';
                get_template_part('template-parts/comment-item', null, [
                    'comment' => $comment,
                    'current_user_id' => get_current_user_id(),
                    'extra_class' => $extra_class,
                    'counter' => &$rendered_count,
                ]);
            endforeach; endif; ?>
        </div>

        <?php if ($rendered_count > 3): ?>
            <div class="btn btn__transparent forum__itemPost__showAllComments js-toggleComments subtitle2">
                <?= __('Показати всі коментарі', 'panterrea_v1'); ?>
            </div>
        <?php endif; ?>

    </div>

</div>