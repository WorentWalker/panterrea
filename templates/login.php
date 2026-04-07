<?php

/**
 * Template Name: Login
 */

get_header();
?>

<main class="actionTemplate login firstSectionPadding">
    <div class="actionTemplate__container">
        <?php get_template_part('template-parts/login-form', null, ['context' => 'login']); ?>
    </div>
</main>

<?php
get_footer();