document.addEventListener("DOMContentLoaded", function () {
    const phoneInputs = ["phone", "contactPhone"];
    const defaultMask = '+{38}(000)000-00-00';
    const masksByCountry = {
        UA: '+{38}(000)000-00-00',
        GB: '+{44} 0000 000000',
        DE: '+{49} 0000 0000000'
    };

    phoneInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            let mask = null;
            let isMaskInitialized = false;

            // Инициализация маски при focus для contactPhone
            if (id === 'contactPhone') {
                input.addEventListener('focus', function() {
                    if (!isMaskInitialized && typeof IMask !== 'undefined') {
                        mask = IMask(input, {
                            mask: defaultMask,
                            lazy: false,
                            placeholderChar: '_'
                        });
                        isMaskInitialized = true;
                    }
                });
            } else {
                // Для других полей (phone) маска инициализируется сразу
                if (typeof IMask !== 'undefined') {
                    mask = IMask(input, {
                        mask: defaultMask,
                        lazy: false,
                        placeholderChar: '_'
                    });

                    const flagSelect = input.closest('.form__rowPhone')?.querySelector('.js-fakeSelectFlagInput') || document.querySelector('.js-fakeSelectFlagInput');

                    if (flagSelect) {
                        flagSelect.addEventListener('input', () => {
                            const countryCode = flagSelect.value.toUpperCase();
                            const newMask = masksByCountry[countryCode] || defaultMask;

                            if (mask) {
                                mask.updateOptions({ mask: newMask });
                                mask.value = '';
                            }
                        });
                    }
                }
            }
        }
    });
});