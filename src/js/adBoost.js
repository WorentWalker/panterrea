document.addEventListener('DOMContentLoaded', function () {
    const formBoost = document.getElementById('formBoost');

    if(!formBoost) return;

    const stepPayment = document.getElementById("stepPayment");
    const stepSuccess = document.getElementById("stepSuccess");

    const authorName = formBoost.dataset.authorName || getTranslatedText('default_author');
    /*const authorName = formBoost.dataset.authorName || getTranslation('default_author', 'Користувач Сайту');*/

    const stripe = Stripe(adBoostObject.stripe_publishable_key);
    const elements = stripe.elements();

    const card = elements.create('card', {
        style: {
            base: {
                fontSize: '14px',
                color: '#212B36',
            },
        },
        hidePostalCode: true,
    });
    card.mount('#card-element');

    formBoost.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!navigator.onLine) {
            const message = getTranslatedText('no_internet');
            /*const message = getTranslation('no_internet', 'Немає підключення до інтернету. Перевірте мережу і спробуйте ще раз.');*/
            MessageSystem.showMessage('error', message);
            return;
        }

        const postId = formBoost.dataset.postId;

        if (!postId || postId === "0") return;

        toggleLoadingCursor(true);

        try {

            const response = await fetch(mainObject.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'create_payment_intent',
                    security: adBoostObject.boost_nonce,
                    post_id: postId,
                }),
            });

            if (!response.ok) {
                const errorMessage = getTranslatedText('payment_data_error');
                /*const errorMessage = getTranslation('payment_data_error', 'Не вдалося отримати платіжні дані.');*/
                throw new Error(errorMessage);
            }

            const { success, data } = await response.json();

            if (!success) {
                toggleLoadingCursor(false);
                const errorMessage = getTranslatedText('payment_failed') + data.message;
                /*const errorMessage = getTranslation('payment_failed', 'Платіж не відбувся: ') + data.message;*/
                MessageSystem.showMessage('error', errorMessage);
                return;
            }

            const result = await stripe.confirmCardPayment(data.clientSecret, {
                payment_method: {
                    card: card,
                    billing_details: { name: authorName },
                },
            });

            if (result.error) {
                toggleLoadingCursor(false);
                const errorMessage = getTranslatedText('payment_failed') + result.error.message;
                /*const errorMessage = getTranslation('payment_failed', 'Платіж не відбувся: ') + result.error.message;*/
                MessageSystem.showMessage('error', errorMessage);
            } else if (result.paymentIntent.status === 'succeeded') {
                const postResponse = await fetch(mainObject.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'successful_payment',
                        security: adBoostObject.boost_nonce,
                        post_id: postId,
                    }),
                });

                if (!postResponse.ok) {
                    const errorMessage = getTranslatedText('status_update_error');
                    /*const errorMessage = getTranslation('status_update_error', 'Помилка під час оновлення статусу оголошення.');*/
                    throw new Error(errorMessage);
                }

                const postResult = await postResponse.json();

                if (postResult.success) {
                    toggleLoadingCursor(false);
                    stepPayment.classList.add("hidden");
                    stepSuccess.classList.remove("hidden");
                    window.scrollTo(0, 0);
                } else {
                    toggleLoadingCursor(false);
                    const errorMessage = postResult.data.message ?? getTranslatedText('error_generic');
                    MessageSystem.showMessage('error', errorMessage);
                }
            }

        } catch (error) {
            MessageSystem.showMessage('error', getTranslatedText('internet_connection_error'));
            /*MessageSystem.showMessage('error', getTranslation('internet_connection_error', 'Помилка: Провірьте підключення до інтернету.'));*/
        } finally {
            toggleLoadingCursor(false);
        }
    });
});
