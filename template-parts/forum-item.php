<?php
$post_id   = get_the_ID();
$author_id = get_post_field('post_author', $post_id);
$author_name = get_user_meta($author_id, 'name', true) ?: get_the_author();
$author_avatar = get_avatar_url($author_id, ['size' => 40]);

$media = get_field('files_url');

$post_categories = get_the_category($post_id);

$top_level_comments = get_comments([
    'post_id' => $post_id,
    'status'  => 'approve',
    'order'   => 'ASC',
    'parent'  => 0,
]);

$top_count = count($top_level_comments);
?>

<div class="forum__itemPost" data-post-id="<?php echo esc_attr($post_id); ?>">

    <!-- ── Header: avatar + name/date + options ── -->
    <div class="forum__itemPost__header">
        <div class="forum__itemPost__avatar">
            <img src="<?php echo esc_url($author_avatar); ?>"
                 alt="<?php echo esc_attr($author_name); ?>"
                 width="40" height="40">
        </div>
        <div class="forum__itemPost__info">
            <div class="forum__itemPost__author subtitle1"><?php echo esc_html($author_name); ?></div>
            <div class="forum__itemPost__date caption"><?php echo get_the_date('d.m.Y'); ?></div>
        </div>
        <?php if (is_user_logged_in() && get_current_user_id() === (int) $author_id) : ?>
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

    <!-- ── Content ── -->
    <div class="forum__itemPost__content body2">
        <?php the_content(); ?>
    </div>

    <!-- ── Media ── -->
    <?php if ($media && is_array($media)) : ?>
    <div class="forum__itemPost__media" data-media='<?php echo esc_attr(wp_json_encode($media)); ?>'>
        <?php
        $total = count($media);
        foreach ($media as $i => $item) :
            if ($i > 1) break;
            $url  = $item['url']  ?? '';
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
                <div class="forum__itemPost__mediaOverlay h3">+<?php echo $remaining; ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Categories ── -->
    <?php if (!empty($post_categories)) :
        $cat_colors = ['green', 'blue', 'orange', 'purple', 'teal', 'rose'];
    ?>
    <div class="forum__itemPost__cats">
        <?php foreach ($post_categories as $cat) :
            $color = $cat_colors[$cat->term_id % count($cat_colors)];
        ?>
        <span class="forum__itemPost__cat forum__itemPost__cat--<?php echo esc_attr($color); ?> caption">
            <?php echo esc_html($cat->name); ?>
        </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Actions: likes + share ── -->
    <div class="forum__itemPost__additional">
        <?php
        $likes = get_post_meta($post_id, '_forum_likes', true);
        $likes = is_array($likes) ? $likes : [];
        $count = count($likes);
        $liked_class = in_array(get_current_user_id(), $likes) ? 'active' : '';
        ?>
        <div class="forum__itemPost__actions">
            <!-- Like -->
            <button type="button"
                    class="forum__itemPost__actionBtn js-likes <?php echo esc_attr($liked_class); ?>"
                    data-post-id="<?php echo esc_attr($post_id); ?>"
                    aria-label="<?php esc_attr_e('Вподобати', 'panterrea_v1'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="forum__itemPost__actionLabel js-likes-count body2"><?php echo $count > 0 ? $count : ''; ?></span>
            </button>

            <!-- Comments count -->
            <button type="button"
                    class="forum__itemPost__actionBtn js-scrollToComments"
                    data-post-id="<?php echo esc_attr($post_id); ?>"
                    aria-label="<?php esc_attr_e('Коментарі', 'panterrea_v1'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="forum__itemPost__actionLabel body2"><?php echo $top_count > 0 ? $top_count : ''; ?></span>
            </button>

            <!-- Share -->
            <button type="button"
                    class="forum__itemPost__actionBtn js-shareForum"
                    data-post-id="<?php echo esc_attr($post_id); ?>"
                    aria-label="<?php esc_attr_e('Поділитись', 'panterrea_v1'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
                    <circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    <circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="forum__itemPost__actionLabel body2"><?php esc_html_e('Поділитись', 'panterrea_v1'); ?></span>
            </button>
        </div>
    </div>

    <!-- ── Comments ── -->
    <div class="forum__itemPost__commentsBlock">

        <?php $is_user_logged = is_user_logged_in(); ?>
        <form class="form form__forum js-forumCommentForm"
              id="commentForm-<?php echo $post_id; ?>"
              data-post-id="<?php echo $post_id; ?>"
              data-logged-in="<?php echo $is_user_logged ? '1' : '0'; ?>"
              enctype="multipart/form-data">
            <div class="input__form">
                <input class="body2" type="text" name="comment" id="comment-<?php echo $post_id; ?>"
                       placeholder="<?php _e('Коментар', 'panterrea_v1'); ?>"/>
                <label class="body2" for="comment-<?php echo $post_id; ?>"><?php _e('Коментар', 'panterrea_v1'); ?></label>
                <span class="error caption"></span>
            </div>
            <div class="forum__commentForm__toolbar">
                <button type="button" class="btn btn__commentCancel js-forumCommentCancel button-medium">
                    <?php _e('Скасувати', 'panterrea_v1'); ?>
                </button>
                <button class="btn btn__commentSubmit js-forumCommentSubmit button-medium" type="submit">
                    <?php if (!$is_user_logged) : ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="forum__commentLock">
                        <path d="M18 11H6C4.895 11 4 11.895 4 13V20C4 21.105 4.895 22 6 22H18C19.105 22 20 21.105 20 20V13C20 11.895 19.105 11 18 11Z" fill="currentColor"/>
                        <path d="M8 11V7C8 4.791 9.791 3 12 3C14.209 3 16 4.791 16 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <?php endif; ?>
                    <?php _e('Відповісти', 'panterrea_v1'); ?>
                </button>
            </div>
        </form>

        <div class="forum__itemPost__comments">
            <?php
            if ($top_level_comments) :
                foreach ($top_level_comments as $idx => $comment) :
                    $extra_class = ($idx >= 2) ? ' hidden-comment' : '';
                    get_template_part('template-parts/comment-item', null, [
                        'comment'         => $comment,
                        'current_user_id' => get_current_user_id(),
                        'extra_class'     => $extra_class,
                    ]);
                endforeach;
            endif;
            ?>
        </div>

        <?php if ($top_count > 2) : ?>
        <button type="button" class="forum__itemPost__showAllComments js-toggleComments">
            <?php echo esc_html__('Показати більше', 'panterrea_v1'); ?>
        </button>
        <?php endif; ?>

    </div>

</div>
