function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

function setCookie(name, value, durationInSeconds) {
    const expiryTime = new Date();
    expiryTime.setTime(expiryTime.getTime() + (durationInSeconds * 1000));

    const expires = `expires=${expiryTime.toUTCString()}`;
    document.cookie = `${name}=${encodeURIComponent(value)}; ${expires}; path=/`;
}

function setMessageCookies(messageType, messageText, durationInSeconds = 60) {
    setCookie('message_type', messageType, durationInSeconds);
    setCookie('message_text', messageText, durationInSeconds);
}