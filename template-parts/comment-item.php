<?php
$comment = $args['comment'] ?? null;
$current_user_id = $args['current_user_id'] ?? get_current_user_id();
$extra_class = $args['extra_class'] ?? '';

if (!$comment) {
    return;
}

$replies = get_comments([
    'parent' => $comment->comment_ID,
    'post_id' => $comment->comment_post_ID,
    'status' => 'approve',
    'orderby' => 'comment_date_gmt',
    'order' => 'ASC',
]);

$is_comment_author = $comment->user_id && (int) $comment->user_id === (int) $current_user_id;
?>

<div class="forum__itemPost__comment <?= esc_attr($extra_class); ?>" data-comment-id="<?= esc_attr($comment->comment_ID); ?>">
    <div class="forum__itemPost__comment__header">
        <div class="forum__itemPost__comment__info">
            <div class="forum__itemPost__comment__author subtitle2">
                <?= esc_html(get_comment_author($comment)); ?>
            </div>
            <div class="forum__itemPost__comment__date caption">
                <?= esc_html(get_comment_date('d.m.Y', $comment)); ?>
            </div>
        </div>
        <div class="forum__itemPost__comment__options">

            <?php if (is_user_logged_in() && !$is_comment_author) : ?>
                <div class="forum__itemPost__comment__option reply button-small js-replyComment">
                    <span><?= __('Відповісти', 'panterrea_v1'); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($is_comment_author) : ?>
                <div class="forum__itemPost__comment__option edit button-small js-editComment">
                    <span><?= __('Редагувати', 'panterrea_v1'); ?></span>
                </div>
                <div class="forum__itemPost__comment__option delete button-small js-openPopUp" data-popUp="deleteForumComment">
                    <span class="js-openPopUp" data-popUp="deleteForumComment"><?= __('Видалити', 'panterrea_v1'); ?></span>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="forum__itemPost__comment__textBlock">
        <div class="forum__itemPost__comment__body body2 is-forumCommentClampable">
            <?= nl2br(esc_html($comment->comment_content)); ?>
        </div>
        <button type="button" class="forum__itemPost__comment__textToggle body2 js-forumCommentToggle" hidden
            data-show="<?= esc_attr__('Показати всі', 'panterrea_v1'); ?>"
            data-hide="<?= esc_attr__('Сховати', 'panterrea_v1'); ?>">
            <?= esc_html__('Показати всі', 'panterrea_v1'); ?>
        </button>
    </div>

    <form class="form form__forumReply hidden" enctype="multipart/form-data" data-editing="false">
        <div class="input__form">
            <input class="body2" type="text" name="commentReply" id="commentReply-<?= esc_attr($comment->comment_ID); ?>"
                   placeholder="<?php _e('Коментувати...', 'panterrea_v1'); ?>"/>
            <label for="commentReply-<?= esc_attr($comment->comment_ID); ?>" class="body2"><?php _e('Коментувати...', 'panterrea_v1'); ?></label>
            <span class="error caption"></span>
        </div>

        <div class="form__editBtn">
            <div class="btn btn__transparent button-medium js-commentCancel">
                <?= __('Скасувати', 'panterrea_v1'); ?>
            </div>
            <div class="btn btn__submit button-medium js-commentReply">
                <?= __('Відповісти', 'panterrea_v1'); ?>
            </div>
        </div>
    </form>

    <?php if ($replies) : ?>
        <div class="forum__itemPost__comment__replies">
            <?php foreach ($replies as $reply) :
                get_template_part('template-parts/comment-item', null, [
                    'comment' => $reply,
                    'current_user_id' => $current_user_id,
                    'extra_class' => '',
                ]);
            endforeach; ?>
        </div>
    <?php endif; ?>
</div>
