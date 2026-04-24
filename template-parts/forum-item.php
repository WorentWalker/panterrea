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
        <div class="forum__itemPost__mediaItem js-forumSlider" data-index="<?php echo $i; ?>">
            <?php if (str_starts_with($type, 'image')) : ?>
                <img src="<?php echo esc_url($url); ?>" alt="" loading="lazy">
            <?php elseif (str_starts_with($type, 'video')) : ?>
                <video preload="metadata">
                    <source src="<?php echo esc_url($url); ?>#t=0.1" type="<?php echo esc_attr($type); ?>">
                </video>
                <div class="forum__itemPost__mediaPlay" aria-hidden="true"></div>
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
                    <path d="M12 2.99023C16.9707 2.99023 21 7.01952 21 11.9902C21 16.9609 16.9707 20.9902 12 20.9902C10.7385 20.9902 9.54001 20.7307 8.45312 20.2637L7.99414 20.0518C7.42656 19.7623 6.77345 19.6882 6.15527 19.8438L6.14746 19.8457L6.14062 19.8477L3.91406 20.4434C3.86356 20.4566 3.81021 20.4569 3.75977 20.4434C3.70906 20.4297 3.66312 20.4023 3.62598 20.3652C3.58878 20.328 3.56147 20.2813 3.54785 20.2305C3.53434 20.1798 3.53429 20.1259 3.54785 20.0752L4.14355 17.8496L4.14746 17.834C4.30206 17.2166 4.2266 16.5646 3.9375 15.998H3.93848C3.31928 14.7537 2.99786 13.3821 3 11.9922V11.9902C3 7.01952 7.02929 2.99024 12 2.99023Z" stroke="currentColor" stroke-width="2"/>
                    <path d="M7.59961 11.2402C7.8401 11.2402 8.06251 11.3301 8.21875 11.4766C8.37329 11.6215 8.4502 11.8076 8.4502 11.9902C8.4502 12.1729 8.37329 12.359 8.21875 12.5039C8.06251 12.6504 7.8401 12.7402 7.59961 12.7402C7.35927 12.7401 7.1376 12.6503 6.98145 12.5039C6.82684 12.359 6.75 12.173 6.75 11.9902C6.75 11.8075 6.82684 11.6215 6.98145 11.4766C7.1376 11.3302 7.35927 11.2403 7.59961 11.2402ZM12 11.2402C12.2404 11.2402 12.4619 11.3302 12.6182 11.4766C12.7728 11.6215 12.8496 11.8075 12.8496 11.9902C12.8496 12.173 12.7728 12.359 12.6182 12.5039C12.4619 12.6503 12.2404 12.7402 12 12.7402C11.7596 12.7402 11.5381 12.6503 11.3818 12.5039C11.2272 12.359 11.1504 12.173 11.1504 11.9902C11.1504 11.8075 11.2272 11.6215 11.3818 11.4766C11.5381 11.3302 11.7596 11.2402 12 11.2402ZM16.4004 11.2402C16.6407 11.2403 16.8624 11.3302 17.0186 11.4766C17.1732 11.6215 17.25 11.8075 17.25 11.9902C17.25 12.173 17.1732 12.359 17.0186 12.5039C16.8624 12.6503 16.6407 12.7401 16.4004 12.7402C16.1599 12.7402 15.9375 12.6504 15.7812 12.5039C15.6267 12.359 15.5498 12.1729 15.5498 11.9902C15.5498 11.8076 15.6267 11.6215 15.7812 11.4766C15.9375 11.3301 16.1599 11.2402 16.4004 11.2402Z" fill="currentColor" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <span class="forum__itemPost__actionLabel body2 js-forumTopCommentsCount"><?php echo $top_count > 0 ? $top_count : ''; ?></span>
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
            <div class="input__form input__form--textarea">
                <textarea class="body2 js-forumCommentField js-autoGrow" name="comment"
                          id="comment-<?php echo $post_id; ?>"
                          rows="3"
                          data-min-rows="3"
                          data-max-rows="8"
                          placeholder="<?php _e('Коментар', 'panterrea_v1'); ?>"></textarea>
                <label class="body2" for="comment-<?php echo $post_id; ?>"><?php _e('Коментар', 'panterrea_v1'); ?></label>
                <span class="error caption"></span>
            </div>
            <div class="forum__commentForm__toolbar">
                <?php if ($is_user_logged) : ?>
                <button type="button" class="btn btn__commentCancel js-forumCommentCancel button-medium" disabled>
                    <?php _e('Скасувати', 'panterrea_v1'); ?>
                </button>
                <?php endif; ?>
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
