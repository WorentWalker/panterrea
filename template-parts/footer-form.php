<?php
/**
 * Footer Form Section
 * Displays Contact Form 7 form with image
 */
?>

<section class="footer-form">
    <div class="container">
        <div class="footer-form__wrapper">
            <div class="footer-form__content">
                <div class="footer-form__panel">
                    <?php if (!empty($data['title'])) : ?>
                    <h2 class="footer-form__title h3"><?php echo esc_html($data['title']); ?></h2>
                    <?php else : ?>
                    <h2 class="footer-form__title h3"><?php the_field( 'contact_title' ); ?></h2>
                    <?php endif; ?>

                    <?php if (!empty($data['subtitle'])) : ?>
                    <div class="footer-form__subtitle body1"><?php echo esc_html($data['subtitle']); ?></div>
                    <?php else : ?>
                    <div class="footer-form__subtitle body1"><?php the_field( 'contact_subtitle' ); ?></div>
                    <?php endif; ?>

                    <div class="footer-form__form">
                        <form id="formContact" class="form form__contact" enctype="multipart/form-data">

                            <!-- Ім’я/Компанія — 100% -->
                            <div class="form__row">
                                <div class="input__form" style="width: 100%;">
                                    <input class="body2" type="text" name="contactCompany" id="contactCompany"
                                        placeholder="<?= __('Ім’я/Компанія', 'panterrea_v1') ?>" />
                                    <label class="body2"
                                        for="contactCompany"><?= __('Ім’я/Компанія', 'panterrea_v1') ?></label>
                                    <span class="error caption"></span>
                                </div>
                            </div>

                            <!-- Телефон 50% + Email 50% -->
                            <div class="form__row">

                                <!-- Блок телефона со страновым фейк-флагом -->
                                <div class="form__rowPhone" style="width: 50%;">
                                    <div class="input__form">
                                        <input class="body2" type="text" name="contactPhone" id="contactPhone"
                                            placeholder="<?php _e('Номер телефону', 'panterrea_v1'); ?>" />
                                        <label class="body2"
                                            for="contactPhone"><?php _e('Номер телефону', 'panterrea_v1'); ?></label>
                                        <span class="error caption"></span>
                                    </div>
                                </div>

                                <!-- Email 50% -->
                                <div class="input__form" style="width: 50%;">
                                    <input class="body2" type="text" name="contactEmail" id="contactEmail"
                                        placeholder="<?= __('Електронна адреса', 'panterrea_v1') ?>" />
                                    <label class="body2"
                                        for="contactEmail"><?= __('Електронна адреса', 'panterrea_v1') ?></label>
                                    <span class="error caption"></span>
                                </div>
                            </div>

                            <!-- Кнопка -->
                            <div class="form__rowBtn">
                                <button class="btn btn__submit button-large"
                                    type="submit"><?= __('Надіслати повідомлення', 'panterrea_v1') ?></button>
                            </div>

                        </form>
                    </div>

                </div>
            </div>

            <div class="footer-form__image">
                <?php if ( get_field( 'contact_image' ) ) : ?>
                <img src="<?php the_field( 'contact_image' ); ?>" />
                <?php endif ?>
            </div>
        </div>
    </div>
</section>