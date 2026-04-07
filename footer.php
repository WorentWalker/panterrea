<?php
/*global $currentLang;*/
/*$footerInfo = get_field($currentLang === 'en' ? 'footer_info_en' : 'footer_info', 'option');*/
$footerInfo = get_field('footer_info', 'option');

global $actionTemplate;

/*$menuLocation = ($currentLang === 'en') ? 'footer_menu_columns_en' : 'footer_menu_columns';*/
$menuLocation = 'footer_menu_columns';

?>

<footer id="footer" class="footer <?= ($actionTemplate) ? 'footer__actionTemplate' : ''; ?>">
    <div class="container">
        <?php if (!$actionTemplate) { ?>

        <div class="footer__inner">
            <div class="footer__infoBlock">
                <a href="<?= home_url() ?>" class="footer__logo">
                    <?= wp_get_attachment_image($footerInfo['logo']['id'], 'full') ?? ''; ?>
                </a>
                <div class="footer__text body2"><?= $footerInfo['text'] ?? ''; ?></div>
                <div class="footer__social">
                    <?php
                        if ($footerInfo['socials']) {
                            foreach ($footerInfo['socials'] as $i => $item) { ?>

                    <a href="<?= $item['link']; ?>" target="_blank">
                        <?= wp_get_attachment_image($item['icon']['id'], 'full') ?? ''; ?>
                    </a>

                    <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <div class="footer__colums">
                <?php wp_nav_menu([
                        'theme_location' => $menuLocation,
                        'container' => ''
                    ]); ?>
            </div>

            <div class="footer__copyright body2">
                <?= isset($footerInfo['copyright'])
                        ? str_replace('{year}', date('Y'), $footerInfo['copyright'])
                        : '';
                    ?>
            </div>
        </div>

        <?php } ?>
    </div>
</footer>

<div id="messageWrapper" class="messageWrapper"></div>

<div id="translations" style="display: none">
    <?php
    $path = get_template_directory() . "/languages/json/translations.json";
    $translations = json_decode(file_get_contents($path), true);

    foreach ($translations as $key => $value) {
        // Ensure placeholder {url} in translations does not leak into DOM as invalid href
        // Replace href='{url}' or href="{url}" (allowing whitespace inside braces)
        $login_url = esc_url(wp_login_url());
        $value = preg_replace('/href=([\'\"])\{\s*url\s*\}\1/i', 'href="' . $login_url . '"', $value);
        echo '<div data-key="' . esc_attr($key) . '">' . wp_kses_post($value) . '</div>';
    }
    ?>
</div>

<?php wp_footer(); ?>

<!-- eSputnik widget script -->
<script>
    !function (t, e, c, n) {
        var s = e.createElement(c);
        s.async = 1, s.src = 'https://statics.esputnik.com/scripts/' + n + '.js';
        var r = e.scripts[0];
        r.parentNode.insertBefore(s, r);
        var f = function () {
            f.c(arguments);
        };
        f.q = [];
        f.c = function () {
            f.q.push(arguments);
        };
        t['eS'] = t['eS'] || f;
    }(window, document, 'script', '4F284BA6138342EB83CF2F0E067B4F47');
</script>
<script>eS('init');</script>

</body>

</html>