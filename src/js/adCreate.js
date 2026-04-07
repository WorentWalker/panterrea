document.addEventListener("DOMContentLoaded", () => {
    const categoriesContainer = document.querySelector('.popUp__categories');
    const subcategoriesContainer = document.querySelector('.popUp__subCategories');
    const categoryInput = document.querySelector('#adCategory');
    const cancelButton = document.querySelector('#cancelSelectCategory');
    const selectButton = document.querySelector('#applySelectCategory');
    const popup = document.querySelector('#selectCategory');

    if (!categoriesContainer || !subcategoriesContainer || !categoryInput || !cancelButton || !selectButton) return;

    let selectedCategory = null;
    let selectedSubcategory = null;

    const selectedCategoryElement = document.querySelector('.popUp__category.active');
    const selectedSubcategoryElement = document.querySelector('.popUp__subcategory.active');
    const selectedSubcategoryContainer = subcategoriesContainer.innerHTML;
    let selectedCategoryInput = false;

    if (categoryInput && categoryInput.value.trim().length > 0) {
        selectedCategoryInput = categoryInput.value.trim();
    }

    if (selectedCategoryElement) {
        const categoryId = selectedCategoryElement.dataset.id;
        selectedCategory = {
            name: selectedCategoryElement.querySelector('.popUp__category__title').textContent.trim(),
            id: categoryId
        };
    }

    if (selectedSubcategoryElement) {
        selectedSubcategory = {
            name: selectedSubcategoryElement.textContent.trim(),
            id: selectedSubcategoryElement.dataset.id
        };
    }

    categoriesContainer.addEventListener('click', (event) => {
        const categoryElement = event.target.closest('.popUp__category');
        if (!categoryElement) return;

        if (categoryElement.classList.contains('active')) {
            return;
        }

        const categoryId = categoryElement.dataset.id;
        if (!categoryId) return;

        document.querySelectorAll('.popUp__category').forEach(el => el.classList.remove('active'));
        categoryElement.classList.add('active');
        selectedCategory = {
            name: categoryElement.querySelector('.popUp__category__title').textContent.trim(),
            id: categoryId
        };

        subcategoriesContainer.innerHTML = '';
        toggleLoadingCursor(true);

        fetch(mainObject.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'get_subcategories',
                security: adCreateObject.getSubcategories_nonce,
                category_id: categoryId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.data.subcategories.length) {
                        subcategoriesContainer.innerHTML = data.data.subcategories
                            .map(subcat => {
                                const translatedName = getTranslatedSubcategoryName(subcat.id) || subcat.name;
                                return `<div class="popUp__subcategory h6" data-id="${subcat.id}">${translatedName}</div>`;
                            })
                            .join('');
                        selectedSubcategory = null;
                    } else {
                        selectedSubcategory = null;
                    }
                }
            })
            .catch(() => {})
            .finally(() => {
                toggleLoadingCursor(false);
            });
    });

    subcategoriesContainer.addEventListener('click', (event) => {
        const subcategoryElement = event.target.closest('.popUp__subcategory');
        if (!subcategoryElement) return;

        subcategoriesContainer.querySelectorAll('.popUp__subcategory').forEach(el => el.classList.remove('active'));
        subcategoryElement.classList.add('active');

        selectedSubcategory = {
            name: subcategoryElement.textContent.trim(),
            id: subcategoryElement.dataset.id
        };
    });

    selectButton.addEventListener('click', () => {
        if (!selectedCategory) {
            MessageSystem.showMessage('warning', getTranslatedText('select_category_warning'));
            /*MessageSystem.showMessage('warning', getTranslation('select_category_warning', 'Будь ласка, виберіть категорію.'));*/
            return;
        }

        if (subcategoriesContainer.children.length > 0 && !selectedSubcategory) {
            MessageSystem.showMessage('warning', getTranslatedText('select_subcategory_warning'));
            /*MessageSystem.showMessage('warning', getTranslation('select_subcategory_warning', 'Будь ласка, виберіть підкатегорію.'));*/
            return;
        }

        const categoryName = selectedCategory.name;
        const subcategoryName = selectedSubcategory ? selectedSubcategory.name : null;

        categoryInput.value = subcategoryName
            ? `${categoryName} / ${subcategoryName}`
            : categoryName;
        categoryInput.dispatchEvent(new Event('input'));

        if (popup) {
            popup.classList.add("hidden");
            document.body.classList.remove("noScroll");
        }
    });

    cancelButton.addEventListener('click', () => {
        document.querySelectorAll('.popUp__category').forEach(el => el.classList.remove('active'));

        if (selectedCategoryElement) {
            selectedCategory = {
                name: selectedCategoryElement.querySelector('.popUp__category__title').textContent.trim(),
                id: selectedCategoryElement.dataset.id
            };
            selectedCategoryElement.classList.add('active');
        } else {
            selectedCategory = null;
        }

        if (selectedSubcategoryElement) {
            selectedSubcategory = {
                name: selectedSubcategoryElement.textContent.trim(),
                id: selectedSubcategoryElement.dataset.id
            };
        } else {
            selectedSubcategory = null;
        }

        subcategoriesContainer.innerHTML = selectedSubcategoryContainer;

        if (selectedCategoryInput){
            categoryInput.value = selectedCategoryInput;
        } else {
            categoryInput.value = '';
        }
        categoryInput.dispatchEvent(new Event('input'));

        if (popup) {
            popup.classList.add("hidden");
            document.body.classList.remove("noScroll");
        }
    });

    const toggleShowInputs = () => {
        const elementsToToggleCategory = document.querySelectorAll('[data-show-inputs="category"]');
        const elementsToToggleMachinery = document.querySelectorAll('[data-show-inputs="machinery"]');
        const adTypeInput = document.getElementById('adType');

        const hasCategory = categoryInput.value.trim() !== '';
        const isMachineryCategory = [
            'Техніка',
            'Обладнання',
            'Земля',
            'Machinery',
            'Lands',
            'Equipment'
            /*getTranslation('tech_category', 'Техніка'),
            getTranslation('equipment_category', 'Обладнання'),
            getTranslation('land_category', 'Земля')*/
        ].some(category => categoryInput.value.startsWith(category));

        if (!isMachineryCategory && adTypeInput && adTypeInput.value === 'Оренда') {
            adTypeInput.value = 'Продаж';
        }

        if (!isMachineryCategory && adTypeInput && adTypeInput.value === 'Rent') {
            adTypeInput.value = 'Sale';
        }

        elementsToToggleCategory.forEach(el => {
            el.style.display = hasCategory ? 'flex' : 'none';
        });

        elementsToToggleMachinery.forEach(el => {
            el.style.display = isMachineryCategory ? 'flex' : 'none';
        });
    };

    toggleShowInputs();

    categoryInput.addEventListener('input', toggleShowInputs);

    const adTypeItems = document.querySelectorAll(".js-adType");

    adTypeItems.forEach((item) => {
        item.addEventListener("click", function () {
            adTypeItems.forEach((el) => el.classList.remove("active"));
            this.classList.add("active");
        });
    });

    const reloadButton = document.querySelector(".js-reload");

    if (reloadButton) {
        reloadButton.addEventListener("click", function () {
            if (document.referrer && document.referrer !== window.location.href) {
                history.back();
            } else {
                window.location.href = '/';
            }
        });
    }

    function updateCategoryInputFromTranslation() {
        const translatedCategory = document.querySelector('#categoryFullName');
        const categoryInput = document.querySelector('#adCategory');

        if (translatedCategory && categoryInput) {
            categoryInput.value = translatedCategory.textContent.trim();
        }
    }

    updateCategoryInputFromTranslation();

});

function getTranslatedSubcategoryName(id) {
    const translated = document.querySelector(`#translatedSubcategories [data-id="${id}"]`);
    return translated ? translated.textContent.trim() : '';
}