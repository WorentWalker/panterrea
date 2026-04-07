<?php
// Get title and subtitle from ACF options
$section_title = get_field('how_it_works_title', 'option');
$section_subtitle = get_field('how_it_works_subtitle', 'option');
?>

<section class="howWork">
    <div class="container">
        <div class="howWork__inner">
            <?php if (!empty($section_title)): ?>
            <h2 class="howWork__title"><?php echo esc_html($section_title); ?></h2>
            <?php endif; ?>

            <?php if (!empty($section_subtitle)): ?>
            <div class="howWork__subtitle"><?php echo esc_html($section_subtitle); ?></div>
            <?php endif; ?>

            <?php if (have_rows('how_it_works_block', 'option')): ?>
            <div class="howWork__cards">
                <?php 
                $index = 0;
                while (have_rows('how_it_works_block', 'option')): the_row(); 
                    $icon = get_sub_field('icon');
                    $title = get_sub_field('title');
                    $text = get_sub_field('text');
                    $number = get_sub_field('number'); // Optional number field
                    
                    // If number is not set, generate it from index
                    if (empty($number)) {
                        $number = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    }
                    
                    // Handle icon - can be image array or URL string
                    $icon_url = '';
                    $icon_alt = '';
                    if (is_array($icon) && !empty($icon['url'])) {
                        $icon_url = $icon['url'];
                        $icon_alt = $icon['alt'] ?? '';
                    } elseif (is_string($icon) && !empty($icon)) {
                        $icon_url = $icon;
                    }
                ?>
                <div class="howWork__card <?php echo $index === 0 ? 'howWork__card--active' : ''; ?>"
                    data-index="<?php echo esc_attr($index); ?>">
                    <div class="howWork__card__closed">
                        <?php if (!empty($icon_url)): ?>
                        <div class="howWork__card__icon">
                            <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($icon_alt); ?>">
                        </div>
                        <?php endif; ?>
                        <div class="howWork__card__content">
                            <?php if (!empty($number)): ?>
                            <div class="howWork__card__number"><?php echo esc_html($number); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($title)): ?>
                            <div class="howWork__card__title"><?php echo esc_html($title); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="howWork__card__open">
                        <div class="howWork__card__content">
                            <?php if (!empty($icon_url)): ?>
                            <div class="howWork__card__icon">
                                <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($icon_alt); ?>">
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($text)): ?>
                            <div class="howWork__card__description">
                                <?php echo wpautop(wp_kses_post($text)); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="howWork__card__content">
                            <?php if (!empty($number)): ?>
                            <div class="howWork__card__number"><?php echo esc_html($number); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($title)): ?>
                            <div class="howWork__card__title subtitle1"><?php echo esc_html($title); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php 
                    $index++;
                endwhile; 
                ?>
            </div>
            <?php else: ?>
            <?php // Fallback data if no rows found ?>
            <div class="howWork__cards">
                <?php 
                    $fallback_cards = [
                        [
                            'icon' => get_template_directory_uri() . '/src/svg/logo_green.svg',
                            'number' => '01',
                            'title' => 'Створіть обліковий запис',
                            'description' => 'Зареєструйтеся на нашій платформі за кілька хвилин. Це швидко, безпечно та безкоштовно. Після реєстрації ви отримаєте доступ до всіх функцій платформи.'
                        ],
                        [
                            'icon' => get_template_directory_uri() . '/src/svg/logo_green.svg',
                            'number' => '02',
                            'title' => 'Додайте своє оголошення',
                            'description' => 'Заповніть форму з деталями вашого товару або послуги. Додайте якісні фотографії та опис, щоб привернути увагу потенційних клієнтів.'
                        ],
                        [
                            'icon' => get_template_directory_uri() . '/src/svg/logo_green.svg',
                            'number' => '03',
                            'title' => 'Отримайте відгуки',
                            'description' => 'Ваше оголошення буде опубліковане та стане доступним для тисяч користувачів. Ви зможете отримувати повідомлення та відгуки від зацікавлених осіб.'
                        ],
                        [
                            'icon' => get_template_directory_uri() . '/src/svg/logo_green.svg',
                            'number' => '04',
                            'title' => 'Успішна угода',
                            'description' => 'Зв\'яжіться з покупцями, обговоріть деталі та укладайте угоди. Наша платформа допомагає вам знайти найкращі пропозиції та швидко реалізувати ваші товари або послуги.'
                        ],
                        [
                            'icon' => get_template_directory_uri() . '/src/svg/logo_green.svg',
                            'number' => '05',
                            'title' => 'Підтримка та допомога',
                            'description' => 'Наша команда підтримки завжди готова допомогти вам з будь-якими питаннями. Ми забезпечуємо безперервну підтримку та консультації для забезпечення найкращого досвіду використання платформи.'
                        ]
                    ];
                    foreach ($fallback_cards as $index => $card): 
                    ?>
                <div class="howWork__card <?php echo $index === 0 ? 'howWork__card--active' : ''; ?>"
                    data-index="<?php echo esc_attr($index); ?>">
                    <div class="howWork__card__closed">
                        <?php if (!empty($card['icon'])): ?>
                        <div class="howWork__card__icon">
                            <img src="<?php echo esc_url($card['icon']); ?>"
                                alt="Іконка <?php echo esc_attr($index + 1); ?>">
                        </div>
                        <?php endif; ?>
                        <div class="howWork__card__content">
                            <?php if (!empty($card['number'])): ?>
                            <div class="howWork__card__number"><?php echo esc_html($card['number']); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($card['title'])): ?>
                            <div class="howWork__card__title"><?php echo esc_html($card['title']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="howWork__card__open">
                        <div class="howWork__card__content">
                            <?php if (!empty($card['icon'])): ?>
                            <div class="howWork__card__icon">
                                <img src="<?php echo esc_url($card['icon']); ?>"
                                    alt="Іконка <?php echo esc_attr($index + 1); ?>">
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($card['description'])): ?>
                            <div class="howWork__card__description">
                                <?php echo wpautop(wp_kses_post($card['description'])); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="howWork__card__content">
                            <?php if (!empty($card['number'])): ?>
                            <div class="howWork__card__number"><?php echo esc_html($card['number']); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($card['title'])): ?>
                            <div class="howWork__card__title subtitle1"><?php echo esc_html($card['title']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>