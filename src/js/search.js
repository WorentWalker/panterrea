document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('searchInput');
    const recommendBlock = document.querySelector('.searchBlock__recommend');
    const recommendContent = document.querySelector('.searchBlock__recommend__content');
    const searchButton = document.querySelector('.btn__search');

    if (!searchInput || !recommendBlock || !searchButton) return;

    searchInput.addEventListener('input', function () {
        let searchQuery = this.value.trim();

        if (searchQuery.length > 2) {
            fetchSearchResults(searchQuery);
        } else {
            recommendBlock.classList.add('hidden');
        }
    });

    searchInput.addEventListener('focus', function () {
        if (recommendContent.children.length > 0) {
            recommendBlock.classList.remove('hidden');
        }
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const searchQuery = searchInput.value.trim();
            if (searchQuery.length > 0) {
                window.location.href = `/search/?search_string=${encodeURIComponent(searchQuery)}`;
            }
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchInput.contains(event.target) && !recommendBlock.contains(event.target)) {
            recommendBlock.classList.add('hidden');
        }
    });

    searchButton.addEventListener('click', function () {
        const searchQuery = searchInput.value.trim();
        if (searchQuery.length > 0) {
            window.location.href = `/search/?search_string=${encodeURIComponent(searchQuery)}`;
        }
    });

    function highlightSearchQuery(text, query) {
        if (!query || !text) return text;
        
        // Экранируем HTML в тексте
        const div = document.createElement('div');
        div.textContent = text;
        const escapedText = div.innerHTML;
        
        // Экранируем специальные символы для регулярного выражения
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        
        // Создаем регулярное выражение для поиска (регистронезависимо)
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        
        // Заменяем совпадения на выделенный текст
        return escapedText.replace(regex, '<strong>$1</strong>');
    }

    function fetchSearchResults(query) {
        const data = new URLSearchParams({
            action: 'search_suggestions',
            query: query,
            security: searchObject.search_nonce,
        });

        fetch(mainObject.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: data.toString(),
        })
            .then(response => response.json())
            .then(data => {
                recommendContent.innerHTML = '';

                if (data.success) {
                    if (data.data.length === 0) {
                        recommendBlock.classList.add('hidden');
                    } else {
                        data.data.forEach(item => {
                            let resultItem = document.createElement('a');
                            resultItem.href = item.url;
                            resultItem.classList.add('searchBlock__recommend__item');

                            resultItem.innerHTML = `
                                <div class="searchBlock__recommend__itemTitle body1">${highlightSearchQuery(item.title, query)}</div>
                                <div class="searchBlock__recommend__itemCategory body2">${highlightSearchQuery(item.category, query)}</div>
                            `;

                            recommendContent.appendChild(resultItem);
                        });
                        recommendBlock.classList.remove('hidden');
                    }
                }
            })
            .catch(error => console.error('Error fetching search results:', error));
    }
});
