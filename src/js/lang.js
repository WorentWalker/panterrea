/*
document.querySelectorAll('.js-langSelect').forEach(item => {
    item.addEventListener('click', function() {
        const lang = this.getAttribute('data-lang');
        setCookie('lang', lang, 60 * 60 * 24 * 365 * 10);
        updateActiveLang(lang);
        location.reload();
    });
});

function updateActiveLang(lang) {
    document.querySelectorAll('.js-langSelect').forEach(item => {
        item.classList.remove('active');
    });
    const activeItem = document.querySelector(`[data-lang="${lang}"]`);
    if (activeItem) activeItem.classList.add('active');
}

function getTranslation(key, replacements = {}) {
    const translation = window.translations && window.translations[key];
    if (!translation) return key;

    return translation.replace(/{(.*?)}/g, (match, p1) => {
        return replacements[p1] || match;
    });
}
*/

document.querySelectorAll(".js-langSwitch").forEach((el) => {
  el.addEventListener("click", () => {
    const lang = el.getAttribute("data-lang");
    const currentUrl = window.location.href;

    window.location.href = currentUrl.replace(/\/(uk|en)\//, `/${lang}/`);
  });
});

function getTranslatedText(key, replacements = {}) {
  const el = document.querySelector(`#translations [data-key="${key}"]`);
  let text = el ? el.innerHTML.trim() : key;

  return text.replace(/{(.*?)}/g, (_, k) => {
    if (
      Object.prototype.hasOwnProperty.call(replacements, k) &&
      replacements[k] != null
    ) {
      return replacements[k];
    }
    // Fallback: remove unknown placeholders to avoid leaking {url} as invalid relative href
    return "";
  });
}
