class MessageSystem {
    static showMessage(type, message, duration = 5000) {
        const wrapper = document.getElementById('messageWrapper');
        if (!wrapper) return;

        const messageElement = document.createElement('div');
        messageElement.className = `message ${type}`;
        messageElement.innerHTML = `
            <span class="subtitle2">${message}</span>
            <div class="close-btn"></div>
        `;

        wrapper.appendChild(messageElement);

        const closeButton = messageElement.querySelector('.close-btn');
        closeButton.addEventListener('click', () => {
            this.closeMessage(messageElement);
        });

        setTimeout(() => {
            this.closeMessage(messageElement);
        }, duration);
    }

    static closeMessage(messageElement) {
        messageElement.style.animation = 'slideOut 0.3s forwards';

        setTimeout(() => {
            messageElement.remove();
        }, 300);
    }
}
