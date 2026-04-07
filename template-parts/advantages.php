<section class="advantages">
    <div class="container">
        <div class="advantages__inner">
            <h2 class="advantages__title"><?php the_field( 'solutions_title' ); ?></h2>

            <div class="advantages__subtitle"><?php the_field( 'solutions_subtitle' ); ?></div>

            <?php if (have_rows('solution_block')): ?>
            <div class="advantages__grid">
                <?php 
                $all_rows = get_field('solution_block');
                $total_rows = $all_rows ? count($all_rows) : 0;
                $current_index = 0;
                
                while (have_rows('solution_block')): the_row();
                    $current_index++;
                    $is_last = ($current_index === $total_rows);
                ?>
                <div class="advantages__card <?php echo $is_last ? 'advantages__card--overlay' : ''; ?>">
                    <?php if (get_sub_field('image')): ?>
                    <div class="advantages__card__image">
                        <?php 
                        $image = get_sub_field('image');
                        if (is_array($image)) {
                            echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt'] ?? '') . '">';
                        } else {
                            echo '<img src="' . esc_url($image) . '" alt="">';
                        }
                        ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($is_last): ?>
                    <div class="advantages__card__overlay">
                        <div class="advantages__card__overlay__text"><?php the_sub_field('text'); ?></div>
                    </div>
                    <?php else: ?>
                    <div class="advantages__card__content">
                        <?php if (get_sub_field('title')): ?>
                        <h3 class="advantages__card__title"><?php the_sub_field('title'); ?></h3>
                        <?php endif; ?>

                        <?php if (get_sub_field('text')): ?>
                        <div class="advantages__card__subtitle"><?php the_sub_field('text'); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <?php // No rows found ?>
            <?php endif; ?>
        </div>
    </div>
</section>