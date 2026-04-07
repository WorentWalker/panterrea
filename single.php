<?php
/**
 * Single Post Template
 */

get_header(); ?>

<main class="singlePost">
    <div class="container">
        <div class="forum__topBlock">
            <div class="forum__topBlock__left">
                <h2 class="blog__title">
                    Блог
                </h2>
                <div class="blog__subtitle">
                    <? the_title(); ?>
                </div>
                <?php blog_breadcrumbs(); ?>
            </div>
            <div class="forum__topBlock__right">
                <input id="searchInputForum" type="text" name="search"
                    placeholder="<?php _e('Пошук', 'panterrea_v1'); ?>" class="input input__searchForum body2"
                    aria-label="Search">
                <div class="btn__searchForum"></div>
            </div>
        </div>

        <?php
        $post_id = get_the_ID();
        $author_id = get_post_field('post_author', $post_id);
        $author_name = get_user_meta($author_id, 'name', true) ?: get_the_author();
        $author_avatar = get_avatar_url($author_id, ['size' => 80]);
        $featured_image = get_the_post_thumbnail_url($post_id, 'full');
        $post_url = get_permalink($post_id);
        // Ensure absolute URL
        if (strpos($post_url, 'http') !== 0) {
            $post_url = home_url($post_url);
        }
        $post_title = get_the_title($post_id);
        ?>

        <?php if ($featured_image) : ?>
        <div class="singlePost__hero">
            <div class="singlePost__hero__bg" style="background-image: url('<?php echo esc_url($featured_image); ?>');">
                <div class="singlePost__hero__overlay"></div>
            </div>
            <div class="singlePost__hero__content">
                <h1 class="singlePost__hero__title"><?php echo esc_html($post_title); ?></h1>
                <div class="singlePost__hero__author">
                    <?php if ($author_avatar) : ?>
                    <img src="<?php echo esc_url($author_avatar); ?>" alt="<?php echo esc_attr($author_name); ?>"
                        class="singlePost__hero__author__avatar">
                    <?php endif; ?>
                    <div class="singlePost__hero__author__info">
                        <div class="singlePost__hero__author__name"><?php echo esc_html($author_name); ?></div>
                        <div class="singlePost__hero__author__date"><?php echo get_the_date('d F Y'); ?></div>
                    </div>
                </div>
                <div class="singlePost__hero__share">
                    <!-- Мобильная кнопка с системным шарингом -->
                    <button type="button"
                        class="singlePost__hero__share__item singlePost__hero__share__mobile js-share-mobile"
                        data-url="<?php echo esc_attr($post_url); ?>" data-title="<?php echo esc_attr($post_title); ?>"
                        aria-label="Поделиться">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15 13.4C14.3667 13.4 13.8 13.65 13.3667 14.0417L7.425 10.5833C7.45833 10.3917 7.5 10.2 7.5 10C7.5 9.8 7.45833 9.60833 7.425 9.41667L13.3417 5.99167C13.7917 6.40833 14.3833 6.66667 15 6.66667C16.3833 6.66667 17.5 5.55 17.5 4.16667C17.5 2.78333 16.3833 1.66667 15 1.66667C13.6167 1.66667 12.5 2.78333 12.5 4.16667C12.5 4.36667 12.5417 4.55833 12.575 4.75L6.65833 8.175C6.20833 7.75833 5.61667 7.5 5 7.5C3.61667 7.5 2.5 8.61667 2.5 10C2.5 11.3833 3.61667 12.5 5 12.5C5.61667 12.5 6.20833 12.2417 6.65833 11.825L12.575 15.25C12.5417 15.4417 12.5 15.6333 12.5 15.8333C12.5 17.2167 13.6167 18.3333 15 18.3333C16.3833 18.3333 17.5 17.2167 17.5 15.8333C17.5 14.45 16.3833 13.3333 15 13.3333L15 13.4Z"
                                fill="white" />
                        </svg>
                    </button>

                    <!-- Десктопные кнопки -->
                    <div class="singlePost__hero__share__desktop">
                        <button type="button" class="singlePost__hero__share__item js-share-facebook"
                            data-url="<?php echo esc_attr($post_url); ?>"
                            data-title="<?php echo esc_attr($post_title); ?>" aria-label="Share on Facebook">
                            <svg width="10" height="15" viewBox="0 0 10 15" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.16667 0.421678C9.16667 0.191559 8.98012 0.00501128 8.75 0.00501128H6.66667C4.48276 -0.103777 2.62106 1.57175 2.5 3.75501V6.00501H0.416667C0.186548 6.00501 0 6.19156 0 6.42168V8.58834C0 8.81846 0.186548 9.00501 0.416667 9.00501H2.5V14.5883C2.5 14.8185 2.68655 15.005 2.91667 15.005H5.41667C5.64679 15.005 5.83333 14.8185 5.83333 14.5883V9.00501H8.01667C8.20729 9.00775 8.37545 8.88077 8.425 8.69668L9.025 6.53001C9.0586 6.40524 9.03241 6.27194 8.9541 6.16915C8.87578 6.06637 8.75422 6.00574 8.625 6.00501H5.83333V3.75501C5.87629 3.32759 6.2371 3.00286 6.66667 3.00501H8.75C8.98012 3.00501 9.16667 2.81846 9.16667 2.58834V0.421678Z"
                                    fill="white" />
                            </svg>
                        </button>
                        <button type="button" class="singlePost__hero__share__item js-share-instagram"
                            data-url="<?php echo esc_attr($post_url); ?>"
                            data-title="<?php echo esc_attr($post_title); ?>" aria-label="Share on Instagram">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.4816 8.33333C6.4816 9.3559 7.31042 10.1851 8.33333 10.1851C9.35625 10.1851 10.1851 9.35625 10.1851 8.33333C10.1851 7.31076 9.35625 6.4816 8.33333 6.4816C7.31076 6.4816 6.4816 7.31042 6.4816 8.33333Z"
                                    fill="white" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M8.33333 3.77882C9.81667 3.77882 9.99236 3.78438 10.5781 3.81111C11.2104 3.83993 11.7972 3.96667 12.2486 4.41806C12.7 4.86944 12.8267 5.45625 12.8556 6.08854C12.8823 6.67431 12.8878 6.85 12.8878 8.33333C12.8878 9.81667 12.8823 9.99236 12.8556 10.5781C12.8267 11.2104 12.7 11.7972 12.2486 12.2486C11.7972 12.7 11.2104 12.8267 10.5781 12.8556C9.99236 12.8823 9.81667 12.8878 8.33333 12.8878C6.85 12.8878 6.67431 12.8823 6.08854 12.8556C5.45625 12.8267 4.86944 12.7 4.41806 12.2486C3.96667 11.7972 3.83993 11.2104 3.81111 10.5781C3.78438 9.99236 3.77882 9.81667 3.77882 8.33333C3.77882 6.85 3.78438 6.67431 3.81111 6.08854C3.83993 5.45625 3.96667 4.86944 4.41806 4.41806C4.86944 3.96667 5.45625 3.83993 6.08854 3.81111C6.67431 3.78438 6.85 3.77882 8.33333 3.77882ZM5.48056 8.33333C5.48056 6.75799 6.75764 5.48056 8.33333 5.48056C9.90903 5.48056 11.1861 6.75799 11.1861 8.33333C11.1861 9.90868 9.90868 11.1861 8.33333 11.1861C6.75799 11.1861 5.48056 9.90868 5.48056 8.33333ZM11.299 6.03437C11.6671 6.03437 11.9656 5.7359 11.9656 5.36771C11.9656 4.99952 11.6671 4.70104 11.299 4.70104C10.9308 4.70104 10.6323 4.99952 10.6323 5.36771C10.6323 5.7359 10.9308 6.03437 11.299 6.03437Z"
                                    fill="white" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M4.89757 0.05C5.78646 0.00972222 6.07014 0 8.33333 0C10.5965 0 10.8802 0.00972222 11.7691 0.05C13.124 0.111806 14.3132 0.44375 15.2681 1.39861C16.2233 2.35382 16.5549 3.5434 16.6167 4.89757C16.6569 5.78646 16.6667 6.07014 16.6667 8.33333C16.6667 10.5965 16.6569 10.8802 16.6167 11.7691C16.5549 13.124 16.2229 14.3132 15.2681 15.2681C14.3128 16.2233 13.1229 16.5549 11.7691 16.6167C10.8802 16.6569 10.5965 16.6667 8.33333 16.6667C6.07014 16.6667 5.78646 16.6569 4.89757 16.6167C3.54271 16.5549 2.35347 16.2229 1.39861 15.2681C0.443403 14.3128 0.111806 13.1233 0.05 11.7691C0.00972222 10.8802 0 10.5965 0 8.33333C0 6.07014 0.00972222 5.78646 0.05 4.89757C0.111806 3.54271 0.44375 2.35347 1.39861 1.39861C2.35382 0.443403 3.5434 0.111806 4.89757 0.05ZM8.33333 2.77778C6.82465 2.77778 6.63542 2.78403 6.04271 2.81111C5.13993 2.85243 4.34688 3.07361 3.71007 3.71007C3.07361 4.34653 2.85243 5.13958 2.81111 6.04271C2.78403 6.63542 2.77778 6.82465 2.77778 8.33333C2.77778 9.84201 2.78403 10.0313 2.81111 10.624C2.85243 11.5267 3.07361 12.3198 3.71007 12.9566C4.34653 13.5931 5.13958 13.8142 6.04271 13.8556C6.63542 13.8826 6.82465 13.8889 8.33333 13.8889C9.84201 13.8889 10.0313 13.8826 10.624 13.8556C11.5267 13.8142 12.3198 13.5931 12.9566 12.9566C13.5931 12.3201 13.8142 11.5271 13.8556 10.624C13.8826 10.0313 13.8889 9.84201 13.8889 8.33333C13.8889 6.82465 13.8826 6.63542 13.8556 6.04271C13.8142 5.13993 13.5931 4.34688 12.9566 3.71007C12.3201 3.07361 11.5271 2.85243 10.624 2.81111C10.0313 2.78403 9.84201 2.77778 8.33333 2.77778Z"
                                    fill="white" />
                            </svg>
                        </button>
                        <button type="button" class="singlePost__hero__share__item js-share-linkedin"
                            data-url="<?php echo esc_attr($post_url); ?>"
                            data-title="<?php echo esc_attr($post_title); ?>" aria-label="Share on LinkedIn">
                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10.125 4.5C8.83505 4.49557 7.59629 5.00433 6.68181 5.91412C5.76733 6.82392 5.25221 8.06004 5.25 9.35V14.25C5.25 14.4489 5.32902 14.6397 5.46967 14.7803C5.61032 14.921 5.80109 15 6 15H7.75C8.16421 15 8.5 14.6642 8.5 14.25V9.35C8.49967 8.89057 8.69483 8.45269 9.03672 8.14578C9.37861 7.83888 9.83493 7.69193 10.2917 7.74167C11.13 7.84726 11.7566 8.56344 11.75 9.40833V14.25C11.75 14.6642 12.0858 15 12.5 15H14.25C14.6642 15 15 14.6642 15 14.25V9.35C14.9978 8.06004 14.4827 6.82392 13.5682 5.91412C12.6537 5.00433 11.415 4.49557 10.125 4.5Z"
                                    fill="white" />
                                <path
                                    d="M0 6C0 5.58579 0.335786 5.25 0.75 5.25H3C3.41421 5.25 3.75 5.58579 3.75 6V14.25C3.75 14.6642 3.41421 15 3 15H0.75C0.335787 15 0 14.6642 0 14.25V6Z"
                                    fill="white" />
                                <path
                                    d="M3.75 1.875C3.75 2.91053 2.91053 3.75 1.875 3.75C0.839466 3.75 0 2.91053 0 1.875C0 0.839466 0.839466 0 1.875 0C2.91053 0 3.75 0.839466 3.75 1.875Z"
                                    fill="white" />
                            </svg>
                        </button>
                        <button type="button" class="singlePost__hero__share__item js-share-twitter"
                            data-url="<?php echo esc_attr($post_url); ?>"
                            data-title="<?php echo esc_attr($post_title); ?>" aria-label="Share on Twitter">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6.73326 16.6667C9.22277 16.75 11.6402 15.823 13.4357 14.0966C15.2312 12.3701 16.2522 9.99092 16.2666 7.50006C16.8313 6.80124 17.2506 5.99658 17.4999 5.13339C17.5379 4.99408 17.4907 4.84543 17.3791 4.7537C17.2676 4.66196 17.1126 4.64422 16.9833 4.70839C16.3775 5 15.6532 4.87258 15.1833 4.39173C14.5922 3.7445 13.7643 3.36419 12.8882 3.3375C12.0121 3.31081 11.1626 3.64002 10.5333 4.25006C9.66573 5.09022 9.30204 6.32362 9.57493 7.50006C6.78326 7.66673 4.8666 6.34173 3.33326 4.52506C3.23914 4.41847 3.09089 4.37762 2.95545 4.42096C2.82001 4.4643 2.72302 4.58362 2.70826 4.72506C2.11003 8.04353 3.63899 11.384 6.5416 13.1001C5.5913 14.1899 4.25677 14.8706 2.8166 15.0001C2.65841 15.0263 2.53441 15.1502 2.50805 15.3084C2.48169 15.4665 2.55881 15.6239 2.69993 15.7001C3.95286 16.3262 5.33262 16.6569 6.73326 16.6667Z"
                                    fill="white" />
                            </svg>
                        </button>
                        <button type="button" class="singlePost__hero__share__item js-copy-link"
                            data-url="<?php echo esc_attr($post_url); ?>" aria-label="Copy link">
                            <svg width="17" height="20" viewBox="0 0 17 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M10.303 3.33301C10.303 1.49301 11.803 9.60172e-06 13.651 9.60172e-06C14.0897 -0.00104223 14.5244 0.0843342 14.9302 0.251264C15.3359 0.418194 15.7048 0.663408 16.0158 0.972906C16.3268 1.2824 16.5738 1.65012 16.7426 2.05507C16.9115 2.46002 16.999 2.89426 17 3.33301C17 5.17401 15.5 6.66701 13.651 6.66701C13.2076 6.66756 12.7685 6.57996 12.3592 6.40932C11.95 6.23868 11.5787 5.98839 11.267 5.67301L6.632 8.82901C6.76108 9.47201 6.69778 10.1388 6.45 10.746L11.532 14.086C12.1306 13.5978 12.8796 13.3318 13.652 13.333C14.0907 13.3321 14.5254 13.4176 14.9311 13.5846C15.3368 13.7517 15.7056 13.997 16.0165 14.3066C16.3274 14.6162 16.5743 14.984 16.743 15.389C16.9118 15.794 16.9991 16.2283 17 16.667C17 18.507 15.5 20 13.651 20C12.7651 20.0019 11.9147 19.6518 11.2869 19.0268C10.659 18.4017 10.3051 17.5529 10.303 16.667C10.3022 16.1996 10.4007 15.7374 10.592 15.311L5.55 12C4.93941 12.5309 4.15712 12.8226 3.348 12.821C2.90922 12.8221 2.47453 12.7366 2.06877 12.5696C1.66301 12.4026 1.29413 12.1573 0.983212 11.8477C0.672295 11.5381 0.425431 11.1702 0.256728 10.7652C0.0880245 10.3601 0.000786975 9.92579 0 9.48701C0.000918172 9.04831 0.0882526 8.61409 0.257015 8.20915C0.425777 7.80421 0.67266 7.43648 0.983564 7.12697C1.29447 6.81746 1.6633 6.57223 2.069 6.40528C2.47469 6.23834 2.9093 6.15296 3.348 6.15401C4.412 6.15401 5.358 6.64701 5.971 7.41501L10.464 4.35601C10.3571 4.02554 10.3028 3.68034 10.303 3.33301Z"
                                    fill="white" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="singlePost__content">
            <div class="singlePost__sidebar">
                <nav class="singlePost__sidebar__nav" id="singlePostNav">
                    <h3>Зміст</h3>
                    <?php
                // Получаем контент поста
                $content = get_the_content();
                    // Извлекаем все H2 заголовки
                    preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches);
                if (!empty($matches[0])) {
                    echo '<ul class="singlePost__sidebar__list">';
                        foreach ($matches[1] as $index => $heading) {
                        // Создаем ID для заголовка
                            $heading_id = 'heading-' . ($index + 1);
                            // Очищаем текст от HTML тегов для отображения
                            $heading_text = strip_tags($heading);
                            echo '<li class="singlePost__sidebar__item">';
                            echo '<a href="#' . esc_attr($heading_id) . '" class="singlePost__sidebar__link">' . esc_html($heading_text) . '</a>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </nav>
                <div class="singlePost__sidebar__share">
                    <button type="button" class="singlePost__sidebar__share__item js-share-facebook"
                        data-url="<?php echo esc_attr($post_url); ?>" data-title="<?php echo esc_attr($post_title); ?>"
                        aria-label="Share on Facebook">
                        <svg width="10" height="15" viewBox="0 0 10 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.16667 0.421678C9.16667 0.191559 8.98012 0.00501128 8.75 0.00501128H6.66667C4.48276 -0.103777 2.62106 1.57175 2.5 3.75501V6.00501H0.416667C0.186548 6.00501 0 6.19156 0 6.42168V8.58834C0 8.81846 0.186548 9.00501 0.416667 9.00501H2.5V14.5883C2.5 14.8185 2.68655 15.005 2.91667 15.005H5.41667C5.64679 15.005 5.83333 14.8185 5.83333 14.5883V9.00501H8.01667C8.20729 9.00775 8.37545 8.88077 8.425 8.69668L9.025 6.53001C9.0586 6.40524 9.03241 6.27194 8.9541 6.16915C8.87578 6.06637 8.75422 6.00574 8.625 6.00501H5.83333V3.75501C5.87629 3.32759 6.2371 3.00286 6.66667 3.00501H8.75C8.98012 3.00501 9.16667 2.81846 9.16667 2.58834V0.421678Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                    <button type="button" class="singlePost__sidebar__share__item js-share-instagram"
                        data-url="<?php echo esc_attr($post_url); ?>" data-title="<?php echo esc_attr($post_title); ?>"
                        aria-label="Share on Instagram">
                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.4816 8.33333C6.4816 9.3559 7.31042 10.1851 8.33333 10.1851C9.35625 10.1851 10.1851 9.35625 10.1851 8.33333C10.1851 7.31076 9.35625 6.4816 8.33333 6.4816C7.31076 6.4816 6.4816 7.31042 6.4816 8.33333Z"
                                fill="currentColor" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M8.33333 3.77882C9.81667 3.77882 9.99236 3.78438 10.5781 3.81111C11.2104 3.83993 11.7972 3.96667 12.2486 4.41806C12.7 4.86944 12.8267 5.45625 12.8556 6.08854C12.8823 6.67431 12.8878 6.85 12.8878 8.33333C12.8878 9.81667 12.8823 9.99236 12.8556 10.5781C12.8267 11.2104 12.7 11.7972 12.2486 12.2486C11.7972 12.7 11.2104 12.8267 10.5781 12.8556C9.99236 12.8823 9.81667 12.8878 8.33333 12.8878C6.85 12.8878 6.67431 12.8823 6.08854 12.8556C5.45625 12.8267 4.86944 12.7 4.41806 12.2486C3.96667 11.7972 3.83993 11.2104 3.81111 10.5781C3.78438 9.99236 3.77882 9.81667 3.77882 8.33333C3.77882 6.85 3.78438 6.67431 3.81111 6.08854C3.83993 5.45625 3.96667 4.86944 4.41806 4.41806C4.86944 3.96667 5.45625 3.83993 6.08854 3.81111C6.67431 3.78438 6.85 3.77882 8.33333 3.77882ZM5.48056 8.33333C5.48056 6.75799 6.75764 5.48056 8.33333 5.48056C9.90903 5.48056 11.1861 6.75799 11.1861 8.33333C11.1861 9.90868 9.90868 11.1861 8.33333 11.1861C6.75799 11.1861 5.48056 9.90868 5.48056 8.33333ZM11.299 6.03437C11.6671 6.03437 11.9656 5.7359 11.9656 5.36771C11.9656 4.99952 11.6671 4.70104 11.299 4.70104C10.9308 4.70104 10.6323 4.99952 10.6323 5.36771C10.6323 5.7359 10.9308 6.03437 11.299 6.03437Z"
                                fill="currentColor" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M4.89757 0.05C5.78646 0.00972222 6.07014 0 8.33333 0C10.5965 0 10.8802 0.00972222 11.7691 0.05C13.124 0.111806 14.3132 0.44375 15.2681 1.39861C16.2233 2.35382 16.5549 3.5434 16.6167 4.89757C16.6569 5.78646 16.6667 6.07014 16.6667 8.33333C16.6667 10.5965 16.6569 10.8802 16.6167 11.7691C16.5549 13.124 16.2229 14.3132 15.2681 15.2681C14.3128 16.2233 13.1229 16.5549 11.7691 16.6167C10.8802 16.6569 10.5965 16.6667 8.33333 16.6667C6.07014 16.6667 5.78646 16.6569 4.89757 16.6167C3.54271 16.5549 2.35347 16.2229 1.39861 15.2681C0.443403 14.3128 0.111806 13.1233 0.05 11.7691C0.00972222 10.8802 0 10.5965 0 8.33333C0 6.07014 0.00972222 5.78646 0.05 4.89757C0.111806 3.54271 0.44375 2.35347 1.39861 1.39861C2.35382 0.443403 3.5434 0.111806 4.89757 0.05ZM8.33333 2.77778C6.82465 2.77778 6.63542 2.78403 6.04271 2.81111C5.13993 2.85243 4.34688 3.07361 3.71007 3.71007C3.07361 4.34653 2.85243 5.13958 2.81111 6.04271C2.78403 6.63542 2.77778 6.82465 2.77778 8.33333C2.77778 9.84201 2.78403 10.0313 2.81111 10.624C2.85243 11.5267 3.07361 12.3198 3.71007 12.9566C4.34653 13.5931 5.13958 13.8142 6.04271 13.8556C6.63542 13.8826 6.82465 13.8889 8.33333 13.8889C9.84201 13.8889 10.0313 13.8826 10.624 13.8556C11.5267 13.8142 12.3198 13.5931 12.9566 12.9566C13.5931 12.3201 13.8142 11.5271 13.8556 10.624C13.8826 10.0313 13.8889 9.84201 13.8889 8.33333C13.8889 6.82465 13.8826 6.63542 13.8556 6.04271C13.8142 5.13993 13.5931 4.34688 12.9566 3.71007C12.3201 3.07361 11.5271 2.85243 10.624 2.81111C10.0313 2.78403 9.84201 2.77778 8.33333 2.77778Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                    <button type="button" class="singlePost__sidebar__share__item js-share-linkedin"
                        data-url="<?php echo esc_attr($post_url); ?>" data-title="<?php echo esc_attr($post_title); ?>"
                        aria-label="Share on LinkedIn">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10.125 4.5C8.83505 4.49557 7.59629 5.00433 6.68181 5.91412C5.76733 6.82392 5.25221 8.06004 5.25 9.35V14.25C5.25 14.4489 5.32902 14.6397 5.46967 14.7803C5.61032 14.921 5.80109 15 6 15H7.75C8.16421 15 8.5 14.6642 8.5 14.25V9.35C8.49967 8.89057 8.69483 8.45269 9.03672 8.14578C9.37861 7.83888 9.83493 7.69193 10.2917 7.74167C11.13 7.84726 11.7566 8.56344 11.75 9.40833V14.25C11.75 14.6642 12.0858 15 12.5 15H14.25C14.6642 15 15 14.6642 15 14.25V9.35C14.9978 8.06004 14.4827 6.82392 13.5682 5.91412C12.6537 5.00433 11.415 4.49557 10.125 4.5Z"
                                fill="currentColor" />
                            <path
                                d="M0 6C0 5.58579 0.335786 5.25 0.75 5.25H3C3.41421 5.25 3.75 5.58579 3.75 6V14.25C3.75 14.6642 3.41421 15 3 15H0.75C0.335787 15 0 14.6642 0 14.25V6Z"
                                fill="currentColor" />
                            <path
                                d="M3.75 1.875C3.75 2.91053 2.91053 3.75 1.875 3.75C0.839466 3.75 0 2.91053 0 1.875C0 0.839466 0.839466 0 1.875 0C2.91053 0 3.75 0.839466 3.75 1.875Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                    <button type="button" class="singlePost__sidebar__share__item js-share-twitter"
                        data-url="<?php echo esc_attr($post_url); ?>" data-title="<?php echo esc_attr($post_title); ?>"
                        aria-label="Share on Twitter">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6.73326 16.6667C9.22277 16.75 11.6402 15.823 13.4357 14.0966C15.2312 12.3701 16.2522 9.99092 16.2666 7.50006C16.8313 6.80124 17.2506 5.99658 17.4999 5.13339C17.5379 4.99408 17.4907 4.84543 17.3791 4.7537C17.2676 4.66196 17.1126 4.64422 16.9833 4.70839C16.3775 5 15.6532 4.87258 15.1833 4.39173C14.5922 3.7445 13.7643 3.36419 12.8882 3.3375C12.0121 3.31081 11.1626 3.64002 10.5333 4.25006C9.66573 5.09022 9.30204 6.32362 9.57493 7.50006C6.78326 7.66673 4.8666 6.34173 3.33326 4.52506C3.23914 4.41847 3.09089 4.37762 2.95545 4.42096C2.82001 4.4643 2.72302 4.58362 2.70826 4.72506C2.11003 8.04353 3.63899 11.384 6.5416 13.1001C5.5913 14.1899 4.25677 14.8706 2.8166 15.0001C2.65841 15.0263 2.53441 15.1502 2.50805 15.3084C2.48169 15.4665 2.55881 15.6239 2.69993 15.7001C3.95286 16.3262 5.33262 16.6569 6.73326 16.6667Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                    <button type="button" class="singlePost__sidebar__share__item js-copy-link"
                        data-url="<?php echo esc_attr($post_url); ?>" aria-label="Copy link">
                        <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M10.303 3.33301C10.303 1.49301 11.803 9.60172e-06 13.651 9.60172e-06C14.0897 -0.00104223 14.5244 0.0843342 14.9302 0.251264C15.3359 0.418194 15.7048 0.663408 16.0158 0.972906C16.3268 1.2824 16.5738 1.65012 16.7426 2.05507C16.9115 2.46002 16.999 2.89426 17 3.33301C17 5.17401 15.5 6.66701 13.651 6.66701C13.2076 6.66756 12.7685 6.57996 12.3592 6.40932C11.95 6.23868 11.5787 5.98839 11.267 5.67301L6.632 8.82901C6.76108 9.47201 6.69778 10.1388 6.45 10.746L11.532 14.086C12.1306 13.5978 12.8796 13.3318 13.652 13.333C14.0907 13.3321 14.5254 13.4176 14.9311 13.5846C15.3368 13.7517 15.7056 13.997 16.0165 14.3066C16.3274 14.6162 16.5743 14.984 16.743 15.389C16.9118 15.794 16.9991 16.2283 17 16.667C17 18.507 15.5 20 13.651 20C12.7651 20.0019 11.9147 19.6518 11.2869 19.0268C10.659 18.4017 10.3051 17.5529 10.303 16.667C10.3022 16.1996 10.4007 15.7374 10.592 15.311L5.55 12C4.93941 12.5309 4.15712 12.8226 3.348 12.821C2.90922 12.8221 2.47453 12.7366 2.06877 12.5696C1.66301 12.4026 1.29413 12.1573 0.983212 11.8477C0.672295 11.5381 0.425431 11.1702 0.256728 10.7652C0.0880245 10.3601 0.000786975 9.92579 0 9.48701C0.000918172 9.04831 0.0882526 8.61409 0.257015 8.20915C0.425777 7.80421 0.67266 7.43648 0.983564 7.12697C1.29447 6.81746 1.6633 6.57223 2.069 6.40528C2.47469 6.23834 2.9093 6.15296 3.348 6.15401C4.412 6.15401 5.358 6.64701 5.971 7.41501L10.464 4.35601C10.3571 4.02554 10.3028 3.68034 10.303 3.33301Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="singlePost__block">
                <?php
                // Выводим контент с добавлением ID к H2
                $content = get_the_content();
                // Добавляем ID к каждому H2
                $content = preg_replace_callback('/<h2([^>]*)>(.*?)<\/h2>/is', function($matches) {
                    static $index = 0;
                    $index++;
                    $heading_id = 'heading-' . $index;
                    // Проверяем, есть ли уже ID в атрибутах
                    if (strpos($matches[1], 'id=') === false) {
                        return '<h2' . $matches[1] . ' id="' . $heading_id . '">' . $matches[2] . '</h2>';
                    } else {
                        return $matches[0];
                    }
                }, $content);
                
                // Применяем фильтры WordPress
                $content = apply_filters('the_content', $content);

                $show_login_overlay = !is_user_logged_in();

                if ($show_login_overlay) {
                    // Розрізаємо після ~25%, але завжди на початку нового абзацу
                    $content_len = strlen(strip_tags($content));
                    $split_at = (int) ($content_len * 0.25);
                    if ($split_at < 100) {
                        $split_at = $content_len; // Якщо стаття дуже коротка — показуємо все
                    }
                    // Шукаємо перший кінець блоку (</p>, </h2>, тощо) після split_at
                    $search_from = $split_at;
                    $rest = substr($content, $search_from);
                    if (preg_match('/<\/(p|h2|h3|h4|div)>/i', $rest, $m, PREG_OFFSET_CAPTURE)) {
                        $split_pos = $search_from + $m[0][1] + strlen($m[0][0]);
                    } else {
                        $split_pos = $split_at;
                    }
                    $content_visible = substr($content, 0, $split_pos);
                    $content_hidden = substr($content, $split_pos);
                    ?>
                <div class="singlePost__contentVisible">
                    <?php echo $content_visible; ?>
                </div>
                <div class="singlePost__loginOverlay">
                    <div class="singlePost__loginOverlay__blurred">
                        <?php echo $content_hidden; ?>
                    </div>

                </div>
                <div class="singlePost__loginOverlay__form">
                    <div class="singlePost__loginOverlay__formInner">
                        <?php get_template_part('template-parts/login-form', null, ['context' => 'article']); ?>
                    </div>
                    <p class="singlePost__loginOverlay__cta body2">
                        <?php echo esc_html__('Приєднуйтесь до 5,000+ професіоналів, які вже досліджують майбутнє разом з PanTerrea', 'panterrea_v1'); ?>
                    </p>
                </div>
                <?php
                } else {
                    echo $content;
                }
                ?>
            </div>
        </div>
        <?php
        // Получаем связанные посты по категориям (по slug)
        $current_post_id = get_the_ID();
        $categories = get_the_category($current_post_id);
        $category_slugs = array();
        
        // Получаем slug категорий текущего поста
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $default_category_id = get_option('default_category');
                // Исключаем категорию по умолчанию
                if ($category->term_id != $default_category_id) {
                    $category_slugs[] = $category->slug;
                }
            }
        }
        
        // Базовые параметры запроса
        $related_args = array(
            'post__not_in' => array($current_post_id),
            'post_type' => 'post',
            'posts_per_page' => 6,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Если есть категории, ищем посты по slug категорий
        if (!empty($category_slugs)) {
            $related_args['tax_query'] = array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'slug',
                    'terms'    => $category_slugs,
                ),
            );
        }
        
        $related_query = new WP_Query($related_args);
        
        // Собираем ID найденных постов по категориям
        $found_post_ids = array($current_post_id);
        $all_post_ids = array();
        
        if ($related_query->have_posts()) {
            while ($related_query->have_posts()) {
                $related_query->the_post();
                $post_id = get_the_ID();
                $found_post_ids[] = $post_id;
                $all_post_ids[] = $post_id;
            }
            wp_reset_postdata();
        }
        
        // Если найдено меньше 2 постов, дополняем рандомными
        $found_count = count($all_post_ids);
        $needed_count = 6 - $found_count;
        
        if ($found_count < 2 && $needed_count > 0) {
            $random_args = array(
                'post__not_in' => $found_post_ids,
                'post_type' => 'post',
                'posts_per_page' => $needed_count,
                'orderby' => 'rand',
            );
            
            // Исключаем категорию по умолчанию из рандомных постов
            $default_category_id = get_option('default_category');
            if ($default_category_id) {
                $random_args['category__not_in'] = array($default_category_id);
            }
            
            $random_query = new WP_Query($random_args);
            
            // Добавляем рандомные посты к списку
            if ($random_query->have_posts()) {
                while ($random_query->have_posts()) {
                    $random_query->the_post();
                    $all_post_ids[] = get_the_ID();
                }
                wp_reset_postdata();
            }
        }
        
        // Создаем финальный запрос с объединенными ID
        if (!empty($all_post_ids)) {
            $final_args = array(
                'post__in' => $all_post_ids,
                'post_type' => 'post',
                'posts_per_page' => 6,
                'orderby' => 'post__in',
            );
            
            $related_query = new WP_Query($final_args);
        }
        
        if ($related_query->have_posts()) :
        ?>
        <div class="singlePost__related">
            <div class="singlePost__related__header">
                <h2 class="singlePost__related__title">Схожі статті</h2>
                <div class="singlePost__related__pagination">
                    <div class="singlePost__related__pagination__prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                            <path
                                d="M11.5247 15.8333C11.2726 15.8342 11.0336 15.7208 10.8747 15.525L6.84973 10.525C6.59697 10.2175 6.59697 9.77415 6.84973 9.46665L11.0164 4.46665C11.3109 4.11227 11.837 4.06377 12.1914 4.35832C12.5458 4.65287 12.5943 5.17893 12.2997 5.53332L8.57473 9.99998L12.1747 14.4667C12.3828 14.7164 12.4266 15.0644 12.287 15.358C12.1474 15.6515 11.8498 15.8371 11.5247 15.8333Z" />
                        </svg>
                    </div>
                    <div class="singlePost__related__pagination__next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                            <path
                                d="M8.47722 4.16668C8.72938 4.16582 8.96837 4.27919 9.12722 4.47501L13.1522 9.47501C13.405 9.78252 13.405 10.2258 13.1522 10.5333L8.98556 15.5333C8.69101 15.8877 8.16494 15.9362 7.81056 15.6417C7.45617 15.3471 7.40767 14.8211 7.70222 14.4667L11.4272 10L7.82722 5.53335C7.61915 5.28358 7.57531 4.93561 7.71494 4.64204C7.85456 4.34846 8.15216 4.16288 8.47722 4.16668Z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="swiper singlePost__related__slider">
                <div class="swiper-wrapper">
                    <?php while ($related_query->have_posts()) : $related_query->the_post();
                        $post_categories = get_the_category();
                        $valid_categories = array_filter($post_categories, function($cat) {
                            $default_category_id = get_option('default_category');
                            return $cat->term_id != $default_category_id;
                        });
                        $primary_category = !empty($valid_categories) ? reset($valid_categories) : null;
                    ?>
                    <div class="swiper-slide">
                        <a href="<?php the_permalink(); ?>" class="singlePost__related__item blogItem catalogItem">
                            <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium', ['alt' => get_the_title(), 'loading' => 'lazy']); ?>
                            <?php else : ?>
                            <img class="blogItem__image"
                                src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/logo_green.svg'); ?>"
                                alt="<?php the_title(); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>

                            <div class="blogItem__desc catalogItem__desc">
                                <?php if ($primary_category) : ?>
                                <div class="blogItem__category body2">
                                    <?php echo esc_html($primary_category->name); ?>
                                </div>
                                <?php endif; ?>

                                <div class="blogItem__title catalogItem__title subtitle1">
                                    <h3><?php the_title(); ?></h3>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php
        endif;
        wp_reset_postdata();
        ?>
</main>

<script>
(function() {
    'use strict';

    // Определение мобильного устройства
    function isMobileDevice() {
        return window.innerWidth <= 659 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
            navigator.userAgent);
    }

    function initShareButtons() {
        const shareContainer = document.querySelector('.singlePost__hero__share');
        if (!shareContainer) return;

        const mobileButton = shareContainer.querySelector('.js-share-mobile');
        const desktopButtons = shareContainer.querySelector('.singlePost__hero__share__desktop');

        // Показываем/скрываем кнопки в зависимости от устройства
        function toggleShareButtons() {
            const isMobile = isMobileDevice();
            if (mobileButton) {
                mobileButton.style.display = isMobile ? 'flex' : 'none';
            }
            if (desktopButtons) {
                desktopButtons.style.display = isMobile ? 'none' : 'flex';
            }
        }

        // Инициализация при загрузке
        toggleShareButtons();

        // Обновление при изменении размера окна
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(toggleShareButtons, 250);
        });

        // Обработка кликов на мобильную кнопку
        if (mobileButton) {
            mobileButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const url = mobileButton.getAttribute('data-url') || window.location.href;
                const title = mobileButton.getAttribute('data-title') || document.title;

                if (!url) {
                    console.error('URL не знайдено');
                    return;
                }

                // Используем системный Web Share API
                shareViaWebAPI(url, title);
            });
        }

        // Обработка кликов на десктопные кнопки
        if (desktopButtons) {
            desktopButtons.addEventListener('click', function(e) {
                const button = e.target.closest('button');
                if (!button) return;

                e.preventDefault();
                e.stopPropagation();

                const url = button.getAttribute('data-url') || window.location.href;
                const title = button.getAttribute('data-title') || document.title;

                if (!url) {
                    console.error('URL не знайдено');
                    return;
                }

                // Facebook share
                if (button.classList.contains('js-share-facebook')) {
                    shareToFacebook(url, title, button);
                    return;
                }

                // LinkedIn share
                if (button.classList.contains('js-share-linkedin')) {
                    shareToLinkedIn(url, title, button);
                    return;
                }

                // Twitter share
                if (button.classList.contains('js-share-twitter')) {
                    shareToTwitter(url, title, button);
                    return;
                }

                // Instagram share
                if (button.classList.contains('js-share-instagram')) {
                    shareToInstagram(url, title, button);
                    return;
                }

                // Copy link
                if (button.classList.contains('js-copy-link')) {
                    copyToClipboard(url, button);
                    return;
                }
            });
        }

        // Обработка кликов на кнопки в сайдбаре (используем делегирование событий)
        const sidebarShareContainer = document.querySelector('.singlePost__sidebar__share');
        if (sidebarShareContainer) {
            sidebarShareContainer.addEventListener('click', function(e) {
                const button = e.target.closest('button');
                if (!button) return;

                e.preventDefault();
                e.stopPropagation();

                const url = button.getAttribute('data-url') || window.location.href;
                const title = button.getAttribute('data-title') || document.title;

                if (!url) {
                    console.error('URL не знайдено');
                    return;
                }

                // Facebook share
                if (button.classList.contains('js-share-facebook')) {
                    shareToFacebook(url, title, button);
                    return;
                }

                // LinkedIn share
                if (button.classList.contains('js-share-linkedin')) {
                    shareToLinkedIn(url, title, button);
                    return;
                }

                // Twitter share
                if (button.classList.contains('js-share-twitter')) {
                    shareToTwitter(url, title, button);
                    return;
                }

                // Instagram share
                if (button.classList.contains('js-share-instagram')) {
                    shareToInstagram(url, title, button);
                    return;
                }

                // Copy link
                if (button.classList.contains('js-copy-link')) {
                    copyToClipboard(url, button);
                    return;
                }
            });
        }
    }

    // Системный Web Share API для мобильных
    function shareViaWebAPI(url, title) {
        if (navigator.share) {
            navigator.share({
                title: title,
                text: 'Подивись цей пост 👇',
                url: url
            }).catch(function(err) {
                if (err.name !== 'AbortError') {
                    console.log('Помилка шарингу', err);
                    // Фолбек на копирование ссылки
                    copyToClipboardFallback(url);
                }
            });
        } else {
            // Фолбек если Web Share API не поддерживается
            copyToClipboardFallback(url);
        }
    }

    function shareToFacebook(url, title, buttonElement) {
        const shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
        const shareWindow = openShareWindow(shareUrl, 'facebook-share');

        if (!shareWindow) {
            console.error('Не вдалося відкрити вікно Facebook');
        }
    }

    function shareToLinkedIn(url, title, buttonElement) {
        const shareUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(url);
        const shareWindow = openShareWindow(shareUrl, 'linkedin-share');

        if (!shareWindow) {
            console.error('Не вдалося відкрити вікно LinkedIn');
        }
    }

    function shareToTwitter(url, title, buttonElement) {
        const shareUrl = 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' +
            encodeURIComponent(title);
        const shareWindow = openShareWindow(shareUrl, 'twitter-share');

        if (!shareWindow) {
            console.error('Не вдалося відкрити вікно Twitter');
        }
    }

    function shareToInstagram(url, title, buttonElement) {
        // Instagram не поддерживает прямые ссылки для шаринга через веб
        // Копируем ссылку в буфер обмена и открываем Instagram
        copyToClipboard(url, buttonElement);

        // Определяем мобильное устройство
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (isMobile) {
            // На мобильных пробуем открыть приложение Instagram через deep link
            try {
                // Создаем временную ссылку для открытия приложения
                const link = document.createElement('a');
                link.href = 'instagram://';
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();

                // Если приложение не открылось через 500ms, открываем веб-версию
                setTimeout(function() {
                    window.open('https://www.instagram.com/', '_blank');
                    if (document.body.contains(link)) {
                        document.body.removeChild(link);
                    }
                }, 500);
            } catch (e) {
                // Если ошибка, просто открываем веб-версию
                window.open('https://www.instagram.com/', '_blank');
            }
        } else {
            // На десктопе открываем Instagram.com в новой вкладке
            window.open('https://www.instagram.com/', '_blank');
        }
    }

    function openShareWindow(url, name) {
        const width = 626;
        const height = 436;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;

        const windowFeatures = 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top +
            ',menubar=no,toolbar=no,resizable=yes,scrollbars=yes';

        const shareWindow = window.open(url, name, windowFeatures);

        if (!shareWindow || shareWindow.closed || typeof shareWindow.closed === 'undefined') {
            return null;
        }

        return shareWindow;
    }

    function copyToClipboard(text, buttonElement) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopyFeedback(buttonElement);
            }).catch(function(err) {
                console.error('Помилка копіювання: ', err);
                fallbackCopyToClipboard(text, buttonElement);
            });
        } else {
            fallbackCopyToClipboard(text, buttonElement);
        }
    }

    function copyToClipboardFallback(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Посилання скопійовано!');
            }).catch(function(err) {
                console.error('Помилка копіювання: ', err);
            });
        }
    }

    function fallbackCopyToClipboard(text, buttonElement) {
        const input = document.createElement('input');
        input.value = text;
        input.style.position = 'fixed';
        input.style.top = '0';
        input.style.left = '0';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        input.setSelectionRange(0, 99999);

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopyFeedback(buttonElement);
            } else {
                console.error('Копіювання не вдалося');
            }
        } catch (err) {
            console.error('Помилка копіювання: ', err);
        }

        document.body.removeChild(input);
    }

    function showCopyFeedback(buttonElement) {
        if (buttonElement) {
            const originalHTML = buttonElement.innerHTML;
            buttonElement.innerHTML =
                '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="rgba(0,0,0,0.5)"/><path d="M9 16.2L5.8 13L7.2 11.6L9 13.4L16.8 5.6L18.2 7L9 16.2Z" fill="white"/></svg>';
            setTimeout(function() {
                buttonElement.innerHTML = originalHTML;
            }, 2000);
        }
    }

    // Ініціалізація коли DOM готовий
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShareButtons);
    } else {
        initShareButtons();
    }
})();

// Скрипт для подсветки активного пункта в сайдбаре при скролле
(function() {
    'use strict';

    function initSidebarHighlight() {
        const sidebarLinks = document.querySelectorAll('.singlePost__sidebar__link');
        const headings = document.querySelectorAll('.singlePost__block h2[id]');

        if (sidebarLinks.length === 0 || headings.length === 0) {
            return;
        }

        function updateActiveLink() {
            let current = '';
            const scrollPosition = window.scrollY + 150; // Отступ для учета sticky позиции

            // Находим текущий заголовок
            headings.forEach((heading) => {
                const headingTop = heading.getBoundingClientRect().top + window.scrollY;
                if (scrollPosition >= headingTop - 100) {
                    current = heading.getAttribute('id');
                }
            });

            // Обновляем активные классы
            sidebarLinks.forEach((link) => {
                link.classList.remove('active');
                const href = link.getAttribute('href');
                if (href && href === '#' + current) {
                    link.classList.add('active');
                }
            });
        }

        // Обновляем при скролле
        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    updateActiveLink();
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Обновляем при загрузке страницы
        updateActiveLink();
    }

    // Инициализация когда DOM готов
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarHighlight);
    } else {
        initSidebarHighlight();
    }
})();

// Инициализация Swiper для related posts
(function() {
    'use strict';

    function initRelatedPostsSwiper() {
        const relatedSwiper = document.querySelector('.singlePost__related__slider');
        if (!relatedSwiper) {
            return;
        }

        // Проверяем наличие Swiper библиотеки
        if (typeof Swiper === 'undefined') {
            console.warn('Swiper library is not loaded');
            return;
        }

        // Проверяем количество слайдов
        const slides = relatedSwiper.querySelectorAll('.swiper-slide');
        if (slides.length === 0) {
            return;
        }

        // Инициализируем Swiper
        const swiperInstance = new Swiper('.singlePost__related__slider', {
            slidesPerView: 2,
            spaceBetween: 24,
            loop: false,
            watchOverflow: true,
            allowTouchMove: true,
            navigation: {
                nextEl: '.singlePost__related__pagination__next',
                prevEl: '.singlePost__related__pagination__prev',
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 16,
                },
                560: {
                    slidesPerView: 1.5,
                    spaceBetween: 16,
                },
                659: {
                    slidesPerView: 2,
                    spaceBetween: 24,
                },
            },
            on: {
                init: function() {
                    // Принудительно обновляем размеры после инициализации
                    this.update();
                }
            }
        });

        // Скрываем кнопки навигации если слайдов меньше или равно количеству видимых
        function updateNavigationVisibility() {
            const prevBtn = document.querySelector('.singlePost__related__pagination__prev');
            const nextBtn = document.querySelector('.singlePost__related__pagination__next');

            if (prevBtn && nextBtn && swiperInstance) {
                const slidesPerView = swiperInstance.params.slidesPerView;
                const totalSlides = slides.length;

                if (totalSlides <= slidesPerView) {
                    prevBtn.style.display = 'none';
                    nextBtn.style.display = 'none';
                } else {
                    prevBtn.style.display = 'flex';
                    nextBtn.style.display = 'flex';
                }
            }
        }

        // Обновляем видимость при изменении размера окна
        swiperInstance.on('resize', function() {
            updateNavigationVisibility();
        });

        // Обновляем при загрузке
        updateNavigationVisibility();
    }

    // Инициализация когда DOM готов и Swiper загружен
    function tryInitSwiper() {
        if (typeof Swiper !== 'undefined') {
            initRelatedPostsSwiper();
        } else {
            // Если Swiper еще не загружен, ждем немного и пробуем снова
            setTimeout(tryInitSwiper, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryInitSwiper);
    } else {
        tryInitSwiper();
    }
})();
</script>

<?php get_footer(); ?>