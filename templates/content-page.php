<?php

/**
 * Template Name: Content Page
 */

/*global $currentLang;*/

get_header();

/*$content = get_field($currentLang === 'en' ? 'content_en' : 'content');*/
$content = get_field('content');
if ($content) :
    ?>
    <main class="contentPage firstSectionPadding">
        <div class="container">
            <div class="contentPage__inner">
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
    </main>
<?php endif;

get_footer();
