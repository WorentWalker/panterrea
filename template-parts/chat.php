<div id="chatWindow" class="chat">
    <div class="chat__container">
        <div class="chat__header">
            <div class="chat__header__icon label-text" id="chatIcon"></div>
            <div class="chat__header__name subtitle2" id="chatName"></div>
            <a id="chatAdLink" href="#" target="_blank" class="chat__header__link subtitle2">
                <?= __('Переглянути оголошення', 'panterrea_v1') ?>
            </a>
            <div class="chat__header__close" id="chatClose"></div>
        </div>

        <div id="chatMessages" class="chat__messages"></div>

        <div class="chat__footer">
            <label for="chatInput"></label>
            <input class="body2" type="text" placeholder="<?= __('Введіть повідомлення...', 'panterrea_v1') ?>" id="chatInput"/>
            <input type="file" id="imageChatInput" accept="image/*" style="display: none;" multiple>
            <div class="chat__footer__addImg" id="addChatImg"></div>
            <div class="chat__footer__send" id="chatSend"></div>
        </div>
    </div>
</div>