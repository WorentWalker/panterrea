document.addEventListener("DOMContentLoaded", function () {
    let socket;
    let currentChatData = {
        userId: null,
        recipientId: null,
        postId: null,
        userInitials: null,
        recipientFullName: null,
        recipientInitials: null
    };
    let blockUserId = null;
    let deleteChatId = null;
    let pingInterval;
    let reconnectTimeout = 1000;


    const userMessagesPage = document.getElementById("userMessages");
    if (userMessagesPage) {
        const userId = userMessagesPage.getAttribute("data-user-id");
        initSocketConnection(userId, "all");
    }

    function openChatHandler(event) {
        const button = event.currentTarget;

        if (button.classList.contains("active") || button.classList.contains("blocked")) {
            return;
        }

        const userId = button.getAttribute("data-user-id");
        const recipientId = button.getAttribute("data-recipient-id");
        const postId = button.getAttribute("data-post-id");

        const loggedIn = mainObject.loggedIn === 'true';
        const emailConfirmed = mainObject.emailConfirmed === 'true';

        if (!loggedIn) {
            const message = getTranslatedText('login_required', {
                url: mainObject.loginURL
            })
            MessageSystem.showMessage('warning', message);
            return;
        }

        if (!emailConfirmed) {
            MessageSystem.showMessage('warning', getTranslatedText('email_confirmation_required'));
            return;
        }

        const adLink = button.getAttribute("data-link-ad");
        const chatAdLink = document.getElementById("chatAdLink");

        if (chatAdLink) {
            if (adLink) {
                chatAdLink.href = adLink;
                chatAdLink.classList.remove('hidden');
            } else {
                chatAdLink.classList.add('hidden');
            }
        }

        document.querySelectorAll(".js-chatOpen").forEach(btn => btn.classList.remove("active"));
        button.classList.add("active");
        button.classList.remove("new");
        checkIfAllChatsRead();

        currentChatData = {
            ...currentChatData,
            userId,
            recipientId,
            postId
        };

        openChat(userId, recipientId, postId);
    }

    document.querySelectorAll(".js-chatOpen").forEach(button => {
        button.addEventListener("click", openChatHandler);
    });

    const sendBtn = document.getElementById("chatSend");
    const chatInput = document.getElementById("chatInput");

    if (sendBtn) {
        sendBtn.addEventListener("click", sendMessageHandler);
    }

    if (chatInput) {
        chatInput.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                sendMessageHandler();
            }
        });
    }

    function sendMessageHandler() {
        const message = chatInput.value.trim();
        if (message === "" || !currentChatData.userId || !currentChatData.recipientId || !currentChatData.postId) return;

        sendMessage(message, currentChatData.userId, currentChatData.recipientId, currentChatData.postId);
        chatInput.value = "";
    }

    const closeChatButton = document.getElementById("chatClose");
    if (closeChatButton) {
        closeChatButton.addEventListener("click", function () {
            document.querySelectorAll(".js-chatContainer").forEach(chatContainer => {
                chatContainer.classList.add("hidden");
            });

            document.querySelectorAll(".js-chatOpen").forEach(chatOpen => {
                chatOpen.classList.remove("active");
            });
        });
    }

    function openChat(userId, recipientId, postId) {
        const chatContainers = document.querySelectorAll(".js-chatContainer");
        const chatNameElement = document.getElementById("chatName");
        const chatIconElement = document.getElementById("chatIcon");

        fetch(mainObject.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'get_chat_history',
                security: chatObject.chat_nonce,
                user_id: userId,
                recipient_id: recipientId,
                post_id: postId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.messages) {

                    currentChatData = {
                        ...currentChatData,
                        userInitials: data.data.user_initials,
                        recipientFullName: data.data.recipient_name.full_name,
                        recipientInitials: data.data.recipient_name.initials
                    };

                    if (chatNameElement) {
                        chatNameElement.textContent = currentChatData.recipientFullName;
                    }
                    if (chatIconElement) {
                        chatIconElement.textContent = currentChatData.recipientInitials;
                    }

                    loadChatHistory(data.data.messages);

                    initSocketConnection(userId, postId);

                } else {
                    /*console.log('Помилка при завантаженні історії чату');*/
                }
            })
            .catch(error => {
                /*console.log('Помилка при запиті на сервер:', error);*/
            });

        chatContainers.forEach(chatContainer => {
            chatContainer.classList.remove("hidden");
        });

    }

    function sendMessage(message, userId, recipientId, postId, files = []) {
        if (!message.trim() && files.length === 0) return;

        const now = new Date();
        const messageData = {
            type: "message",
            userId: userId,
            recipientId: recipientId,
            postId: postId,
            message: message,
            timestamp: now.toISOString()
        };

        const formData = new FormData();
        formData.append('action', 'save_chat_message');
        formData.append('security', chatObject.chat_nonce);
        formData.append('user_id', userId);
        formData.append('recipient_id', recipientId);
        formData.append('message', message);
        formData.append('post_id', postId);

        files.forEach((file, index) => {
            formData.append(`images[]`, file);
        });

        fetch(mainObject.ajax_url, {method: 'POST', body: formData})
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let parsedMessage = data.data.message;
                    if (typeof parsedMessage === "string") {
                        try {
                            parsedMessage = JSON.parse(parsedMessage);
                            } catch (e) {}
                    }

                    if (Array.isArray(parsedMessage) && parsedMessage.every(url => typeof url === "string")) {
                        messageData.message = parsedMessage;
                    }

                    displayOwnMessage(messageData);
                    sendSocketMessage(messageData);
                } else {
                    if (data.data.error === 'blocked') {
                        const message = getTranslatedText('user_blocked');
                        /*const message = getTranslation('user_blocked');*/
                        displaySystemMessage(message);
                    } else {
                        const errorMessage = getTranslatedText('server_error');
                        MessageSystem.showMessage('error', errorMessage);
                        /*const errorMessage = getTranslation('server_error', 'Помилка сервера. Спробуйте пізніше.');
                        MessageSystem.showMessage('error', errorMessage);*/
                    }
                }
            })
            .catch(error => {
                MessageSystem.showMessage('error', getTranslatedText('server_error'));
            });

        document.getElementById("chatInput").value = "";
    }

    function loadChatHistory(messages) {
        const chatMessages = document.getElementById("chatMessages");
        chatMessages.innerHTML = '';

        let lastDate = null;
        const today = new Date();

        messages.forEach(message => {
            const messageDate = new Date(message.timestamp.includes('T') ? message.timestamp : message.timestamp + 'Z');
            const formattedDate = messageDate.toLocaleDateString([], {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).replace(/\//g, '.');

            const isToday = messageDate.toDateString() === today.toDateString();
            const displayDate = isToday ? getTranslatedText('today') : formattedDate;

            if (displayDate !== lastDate) {
                const dateSeparator = document.createElement("div");
                dateSeparator.classList.add("chat__date", "caption");
                dateSeparator.innerText = displayDate;
                chatMessages.appendChild(dateSeparator);

                lastDate = displayDate;
            }

            const newMessage = document.createElement("div");
            newMessage.classList.add("chat__message");

            if (message.sender_id === currentChatData.userId) {
                newMessage.classList.add("own-message");
            } else {
                newMessage.classList.add("other-message");
            }

            const localTime = messageDate.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});

            let messageContent = '';
            let decodedContent = decodeHTMLEntities(message.content);

            try {
                const parsedContent = JSON.parse(decodedContent);
                if (Array.isArray(parsedContent) && parsedContent.every(url => typeof url === "string")) {
                    messageContent = parsedContent.map(url =>
                        `<a href="${url}" target="_blank">
                        <img src="${url}" alt="Sent image" class="chat-image">
                    </a>`
                    ).join('');
                } else {
                    messageContent = `<div class="text body2">${decodedContent}</div>`;
                }
            } catch (e) {
                messageContent = `<div class="text body2">${decodedContent}</div>`;
            }

            newMessage.innerHTML = `
                <span class="name label-text">${message.sender}</span>
                <div class="content">
                    ${messageContent}
                    <span class="time caption">${localTime}</span>
                </div>
            `;

            chatMessages.appendChild(newMessage);
        });

        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function initSocketConnection(userId, postId) {
        if (!socket || socket.readyState === WebSocket.CLOSED) {
            /*socket = new WebSocket('ws://localhost:8080');*/
            let socketHost;

            if (window.location.hostname === 'dev.panterrea.com') {
                socketHost = 'wss://ws.dev.panterrea.com:443';
            } else {
                socketHost = 'wss://ws.panterrea.com:443';
            }

            socket = new WebSocket(socketHost);

            socket.onopen = () => {
                /*console.log('✅ Підключено до WebSocket сервера');*/
                reconnectTimeout = 1000;
                sendSocketMessage({type: 'login', userId, postId});

                pingInterval = setInterval(() => {
                    if (socket.readyState === WebSocket.OPEN) {
                        console.log('✅ PING');
                        socket.send(JSON.stringify({type: 'ping'}));
                    }
                }, 45000);
            };

            socket.onmessage = handleSocketMessage;

            socket.onclose = () => {
                /*console.log('❌ З\'єднання з WebSocket сервером закрито');*/
                clearInterval(pingInterval);
                setTimeout(() => initSocketConnection(userId, postId), reconnectTimeout);
                reconnectTimeout = Math.min(reconnectTimeout * 2, 30000);
            };

            socket.onerror = (error) => {
                /*console.error('❗ Помилка WebSocket:', error);*/
            };
        }
    }

    function sendSocketMessage(messageObj) {
        if (socket && socket.readyState === WebSocket.OPEN) {
            socket.send(JSON.stringify(messageObj));
        } else {
            /*console.warn('⚠️ WebSocket не підключений. Повідомлення не надіслано.');*/
        }
    }

    function handleSocketMessage(event) {
        const message = JSON.parse(event.data);

        if (message.type === "message") {
            if (currentChatData.postId === message.postId) {
                displayIncomingMessage(message);
                markMessagesAsRead(currentChatData.postId, message.from);
            } else {
                markChatAsNew(message.postId, message.from);
            }
        }
    }

    function displayIncomingMessage(message) {
        const chatMessages = document.getElementById("chatMessages");
        const now = new Date(message.timestamp.includes('T') ? message.timestamp : message.timestamp + 'Z');
        const messageDate = now.toLocaleDateString([]).replace(/\//g, '.');
        const todayDate = new Date().toLocaleDateString([]).replace(/\//g, '.');
        const messageTime = now.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});

        addDateSeparator(chatMessages, messageDate, todayDate);

        const newMessage = document.createElement("div");
        newMessage.classList.add("chat__message", "other-message");

        let messageContent = '';

        if (Array.isArray(message.message) && message.message.every(url => typeof url === "string")) {
            messageContent = message.message.map(url =>
                `<a href="${url}" target="_blank">
                <img src="${url}" alt="Sent image" class="chat-image">
            </a>`
            ).join('');
        } else {
            messageContent = `<div class="text body2">${message.message}</div>`;
        }

        newMessage.innerHTML = `
            <span class="name label-text">${currentChatData.recipientInitials}</span>
            <div class="content">
                ${messageContent}
                <span class="time caption">${messageTime}</span>
            </div>
        `;

        chatMessages.appendChild(newMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function displayOwnMessage(message) {
        const chatMessages = document.getElementById("chatMessages");

        const now = new Date(message.timestamp.includes('T') ? message.timestamp : message.timestamp + 'Z');
        const messageDate = now.toLocaleDateString([]).replace(/\//g, '.');
        const todayDate = messageDate;
        const messageTime = now.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});

        addDateSeparator(chatMessages, messageDate, todayDate);

        const newMessage = document.createElement("div");
        newMessage.classList.add("chat__message", "own-message");

        let messageContent = '';
        if (Array.isArray(message.message) && message.message.every(url => typeof url === "string")) {
            messageContent = message.message.map(url =>
                `<a href="${url}" target="_blank">
                    <img src="${url}" alt="Sent image" class="chat-image">
                </a>`
            ).join('');
        } else {
            messageContent = `<div class="text body2">${message.message}</div>`;
        }

        newMessage.innerHTML = `
        <span class="name label-text">${currentChatData?.userInitials || getTranslatedText('user')}</span>
        <div class="content">
            ${messageContent}
            <span class="time caption">${messageTime}</span>
        </div>
    `;

        /*newMessage.innerHTML = `
        <span class="name label-text">${currentChatData?.userInitials || getTranslation('user', 'Користувач')}</span>
        <div class="content">
            ${messageContent}
            <span class="time caption">${messageTime}</span>
        </div>
    `;*/

        chatMessages.appendChild(newMessage);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addDateSeparator(chatMessages, messageDate, todayDate) {
        const dateSeparators = chatMessages.querySelectorAll(".chat__date");
        const lastDate = dateSeparators.length > 0 ? dateSeparators[dateSeparators.length - 1].innerText : null;
        const lastDateNormalized = lastDate === getTranslatedText('today') ? todayDate : lastDate;
        /*const lastDateNormalized = lastDate === getTranslation('today', 'Сьогодні') ? todayDate : lastDate;*/

        if (!lastDate || lastDateNormalized !== messageDate) {
            const dateSeparator = document.createElement("div");
            dateSeparator.classList.add("chat__date", "caption");
            dateSeparator.innerText = messageDate === todayDate ? getTranslatedText('today') : messageDate;
            /*dateSeparator.innerText = messageDate === todayDate ? getTranslation('today', 'Сьогодні') : messageDate;*/
            chatMessages.appendChild(dateSeparator);
        }
    }

    function markChatAsNew(postId, senderId) {
        const chatItem = document.querySelector(`.js-chatOpen[data-post-id="${postId}"][data-recipient-id="${senderId}"]`);
        const messagesIcon = document.getElementById("headerMessages");

        if (chatItem && !chatItem.classList.contains("new")) {
            chatItem.classList.add("new");
        }

        if (messagesIcon && !messagesIcon.classList.contains("new")) {
            messagesIcon.classList.add("new");
        }
    }

    function markMessagesAsRead(postId, senderId) {
        fetch(mainObject.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'mark_messages_read',
                security: chatObject.chat_nonce,
                post_id: postId,
                sender_id: senderId
            })
        }).catch(/*error => console.error('Помилка при позначенні як прочитані:', error)*/);
    }

    document.addEventListener("click", function (event) {
        const allOptionsLists = document.querySelectorAll(".userMessages__chatAd__optionsList");
        const chatOptions = event.target.closest(".js-chatOptions");

        if (chatOptions) {
            const optionsList = chatOptions.querySelector(".userMessages__chatAd__optionsList");

            if (optionsList) {
                allOptionsLists.forEach(list => {
                    if (list !== optionsList) {
                        list.classList.add("hidden");
                    }
                });

                optionsList.classList.toggle("hidden");
            }
        } else {
            allOptionsLists.forEach(list => list.classList.add("hidden"));
        }
    });

    document.querySelectorAll(".js-openPopUp[data-popUp='blockChat']").forEach(button => {
        button.addEventListener("click", function () {
            blockUserId = this.closest(".js-chatOpen").dataset.recipientId;
            const isBlocked = this.closest(".js-chatOpen").classList.contains("blocked");

            document.querySelector("#blockChat .popUp__confirm__title").textContent = isBlocked
                ? getTranslatedText('unblock_user')
                : getTranslatedText('block_chat_confirmation');

            /*document.querySelector("#blockChat .popUp__confirm__title").textContent = isBlocked
                ? getTranslation('unblock_user', 'Розблокувати користувача?')
                : getTranslation('block_chat_confirmation', 'Ви дійсно хочете заблокувати чат?');*/

            document.querySelector("#blockChat .popUp__confirm__text").textContent = isBlocked
                ? getTranslatedText('user_can_message_again')
                : getTranslatedText('no_messages_from_user');

            /*document.querySelector("#blockChat .popUp__confirm__text").textContent = isBlocked
                ? getTranslation('user_can_message_again', 'Користувач зможе знову писати вам повідомлення.')
                : getTranslation('no_messages_from_user', 'Ви не будете отримувати повідомлень від цього користувача.');*/

            document.querySelector("#blockChatButton").textContent = isBlocked
                ? getTranslatedText('unblock_button_confirm')
                : getTranslatedText('block_button_confirm');

            /*document.querySelector("#blockChatButton").textContent = isBlocked
                ? getTranslation('unblock_button_confirm', 'Так, розблокувати')
                : getTranslation('block_button_confirm', 'Так, блокувати');*/
        });
    });

    const blockChatButton = document.getElementById("blockChatButton");
    if (blockChatButton) {
        document.getElementById("blockChatButton").addEventListener("click", function () {
            if (!blockUserId) return;

            const formData = new FormData();
            formData.append("action", "block_chat_user");
            formData.append("security", chatObject.chat_nonce);
            formData.append("blocked_id", blockUserId);

            fetch(mainObject.ajax_url, {method: "POST", body: formData})
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll(`.js-chatOpen[data-recipient-id="${blockUserId}"]`).forEach(chat => {
                            chat.classList.toggle("blocked", data.data.is_blocked);

                            const blockButton = chat.querySelector(".js-openPopUp[data-popUp='blockChat']");
                            if (blockButton) {

                                blockButton.textContent = data.data.is_blocked
                                    ? getTranslatedText('unblock_button')
                                    : getTranslatedText('block_button');

                                /*blockButton.textContent = data.data.is_blocked
                                    ? getTranslation('unblock_button', 'Розблокувати')
                                    : getTranslation('block_button', 'Заблокувати');*/
                            }

                            if (chat.classList.contains("active") && data.data.is_blocked) {
                                chat.classList.remove("active");

                                document.querySelectorAll(".js-chatContainer").forEach(chatContainer => {
                                    chatContainer.classList.add("hidden");
                                });

                                currentChatData = {};

                                const chatMessages = document.getElementById("chatMessages");
                                if (chatMessages) {
                                    chatMessages.innerHTML = "";
                                }
                            }
                        });

                        document.querySelector("#blockChat .js-closePopUp").click();
                        MessageSystem.showMessage("success", data.data.message);
                    } else {
                        MessageSystem.showMessage("error", getTranslatedText('block_error'));
                        /*MessageSystem.showMessage("error", getTranslation('block_error', 'Помилка блокування.'));*/
                    }
                })
                .catch(() => {
                    MessageSystem.showMessage('error', getTranslatedText('server_error'));
                    /*MessageSystem.showMessage("error", getTranslation('server_error', 'Помилка сервера. Спробуйте пізніше.'));*/
                });
        });
    }

    function displaySystemMessage(text) {
        const chatWindow = document.querySelector("#chatMessages");
        if (chatWindow) {
            const messageElement = document.createElement("div");
            messageElement.classList.add("chat__system", "caption");
            messageElement.textContent = text;
            chatWindow.appendChild(messageElement);

            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
    }

    document.querySelectorAll(".js-openPopUp[data-popUp='deleteChat']").forEach(button => {
        button.addEventListener("click", function () {
            deleteChatId = this.closest(".js-chatOpen").dataset.chatId;
        });
    });

    const deleteChatButton = document.getElementById("deleteChatButton");
    if (deleteChatButton) {
        document.getElementById("deleteChatButton").addEventListener("click", function () {
            if (!deleteChatId) return;

            const formData = new FormData();
            formData.append("action", "delete_chat");
            formData.append("security", chatObject.chat_nonce);
            formData.append("chat_id", deleteChatId);

            fetch(mainObject.ajax_url, {method: "POST", body: formData})
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`.js-chatOpen[data-chat-id="${deleteChatId}"]`).remove();

                        const remainingChats = document.querySelectorAll(".js-chatOpen");
                        if (remainingChats.length === 0) {
                            const emptyChatsBlock = document.getElementById("emptyChats");
                            if (emptyChatsBlock) {
                                emptyChatsBlock.classList.remove("hidden");
                            }

                            const chatListBlock = document.querySelector(".js-chatList");
                            if (chatListBlock) {
                                chatListBlock.classList.add("hidden");
                            }
                        }

                        document.querySelector("#deleteChat .js-closePopUp").click();

                        document.querySelectorAll(".js-chatContainer").forEach(chatContainer => {
                            chatContainer.classList.add("hidden");
                        });

                        currentChatData = {};

                        const chatMessages = document.getElementById("chatMessages");
                        if (chatMessages) {
                            chatMessages.innerHTML = "";
                        }

                        MessageSystem.showMessage("success", data.data.message);
                    } else {
                        MessageSystem.showMessage("error", getTranslatedText('delete_error'));
                        /*MessageSystem.showMessage("error", getTranslation('delete_error', 'Помилка видалення.'));*/
                    }
                })
                .catch(() => {
                    MessageSystem.showMessage('error', getTranslatedText('server_error'));
                    /*MessageSystem.showMessage("error", getTranslation('server_error', 'Помилка сервера. Спробуйте пізніше.'));*/
                });
        });
    }

    const addImg = document.getElementById("addChatImg");
    const imageInput = document.getElementById("imageChatInput")
    if (addImg && imageInput) {
        addImg.addEventListener("click", function () {
            document.getElementById("imageChatInput").click();
        });
    }

    if (imageInput) {
        imageInput.addEventListener("change", function (event) {
            const files = Array.from(event.target.files);
            if (files.length === 0) return;

            const allowedTypes = ["image/jpeg", "image/png"];
            const maxFileSize = 5 * 1024 * 1024;

            const validFiles = files.filter(file =>
                allowedTypes.includes(file.type) && file.size <= maxFileSize
            ).slice(0, 5);

            if (validFiles.length < files.length) {
                MessageSystem.showMessage('warning', getTranslatedText('file_upload_error'));
                /*MessageSystem.showMessage('warning', getTranslation('file_upload_error', 'Дозволені тільки файли JPEG та PNG (макс. 5, кожен не більше 5 МБ).'));*/
            }

            if (validFiles.length > 0) {
                sendMessage("", currentChatData.userId, currentChatData.recipientId, currentChatData.postId, validFiles);
            }

            event.target.value = "";
        });
    }

    function decodeHTMLEntities(text) {
        const parser = new DOMParser();
        return parser.parseFromString(text, "text/html").body.textContent;
    }

    function checkIfAllChatsRead() {
        const unreadChats = document.querySelectorAll(".js-chatOpen.new");
        const messagesIcon = document.querySelector("#headerMessages.new");

        if (unreadChats.length === 0 && messagesIcon) {
            messagesIcon.classList.remove("new");
        }
    }

    const chatMessages = document.getElementById("chatMessages");

    if (chatMessages) {
        chatMessages.addEventListener("click", function (e) {
            const img = e.target.closest("img.chat-image");
            if (img) {
                e.preventDefault();
                const link = img.closest("a");
                if (link && link.href) {
                    window.open(link.href, "_blank", "noopener,noreferrer");
                }
            }
        });
    }

})
