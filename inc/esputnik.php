<?php

/**
 * eSputnik: реєстрація на сайті.
 *
 * - Підписка контакта: https://docs.esputnik.com/reference/subscribecontact-1
 * - Подія (Generate event v3): https://docs.esputnik.com/reference/registerevent_2
 * - Загальне по API: https://docs.esputnik.com/reference/getting-started-with-your-api
 */

/**
 * Розбити одне поле «ім’я / назва» на ім’я та прізвище (перше слово — ім’я, решта — прізвище).
 *
 * @return array{0: string, 1: string}
 */
function panterrea_esputnik_split_name(string $name): array
{
    $name = preg_replace('/\s+/u', ' ', trim($name));
    if ($name === '') {
        return ['', ''];
    }
    $parts = preg_split('/\s+/u', $name, 2);

    return [
        isset($parts[0]) ? $parts[0] : '',
        isset($parts[1]) ? $parts[1] : '',
    ];
}

/**
 * @return array<string, string>
 */
function panterrea_esputnik_basic_auth_headers(): array
{
    $auth_user = defined('ESPUTNIK_API_AUTH_USER') ? (string) ESPUTNIK_API_AUTH_USER : 'api';

    return [
        'Content-Type' => 'application/json; charset=UTF-8',
        'Authorization' => 'Basic ' . base64_encode($auth_user . ':' . ESPUTNIK_API_KEY),
    ];
}

/**
 * @param int   $user_id  ID користувача WordPress.
 * @param array $formData Санітизовані поля форми (name, city, email, phone, password тощо).
 */
function panterrea_esputnik_subscribe_on_registration(int $user_id, array $formData): void
{
    if (!defined('ESPUTNIK_API_KEY') || ESPUTNIK_API_KEY === '') {
        return;
    }

    $email = isset($formData['email']) ? sanitize_email($formData['email']) : '';
    if ($email === '' || !is_email($email)) {
        return;
    }

    $base = defined('ESPUTNIK_API_BASE') ? ESPUTNIK_API_BASE : 'https://esputnik.com';
    $base = rtrim((string) $base, '/');
    $url = $base . '/api/v1/contact/subscribe';

    $channels = [
        [
            'type' => 'email',
            'value' => $email,
        ],
    ];

    $phone_digits = isset($formData['phone']) ? preg_replace('/\D+/', '', (string) $formData['phone']) : '';
    if ($phone_digits !== '') {
        $channels[] = [
            'type' => 'sms',
            'value' => $phone_digits,
        ];
    }

    [$first, $last] = panterrea_esputnik_split_name(isset($formData['name']) ? (string) $formData['name'] : '');

    $contact = [
        'firstName' => $first,
        'lastName' => $last,
        'channels' => $channels,
        'externalCustomerId' => (string) $user_id,
    ];

    if (!empty($formData['city'])) {
        $contact['address'] = [
            'town' => sanitize_text_field($formData['city']),
        ];
    }

    $groups = [];
    if (defined('ESPUTNIK_SUBSCRIBE_GROUPS') && ESPUTNIK_SUBSCRIBE_GROUPS !== '') {
        foreach (explode(',', (string) ESPUTNIK_SUBSCRIBE_GROUPS) as $g) {
            $g = trim($g);
            if ($g !== '') {
                $groups[] = $g;
            }
        }
    }

    $payload = [
        'contact' => $contact,
        'formType' => 'PanTerrea registration',
    ];
    if ($groups !== []) {
        $payload['groups'] = $groups;
    }

    $payload = apply_filters('panterrea_esputnik_registration_payload', $payload, $user_id, $formData);

    $response = wp_remote_post(
        $url,
        [
            'timeout' => 15,
            'headers' => panterrea_esputnik_basic_auth_headers(),
            'body' => wp_json_encode($payload),
        ]
    );

    if (is_wp_error($response)) {
        error_log('eSputnik subscribe (registration): ' . $response->get_error_message());

        return;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        error_log(
            'eSputnik subscribe (registration) HTTP ' . $code . ': ' . wp_remote_retrieve_body($response)
        );
    }
}

/**
 * Подія «Реєстрація нового користувача» (Generate event v3). Усі значення params — рядки.
 *
 * Пароль передається лише якщо дозволено фільтром (за замовчуванням true за ТЗ; вимкніть з міркувань безпеки).
 */
function panterrea_esputnik_fire_registration_event(int $user_id, array $formData): void
{
    if (!defined('ESPUTNIK_API_KEY') || ESPUTNIK_API_KEY === '') {
        return;
    }

    $email = isset($formData['email']) ? sanitize_email($formData['email']) : '';
    if ($email === '' || !is_email($email)) {
        return;
    }

    $token = (string) get_user_meta($user_id, 'email_confirmation_token', true);
    if ($token === '') {
        error_log('eSputnik registration event: missing email_confirmation_token for user ' . $user_id);

        return;
    }

    $confirmation_url = add_query_arg(
        [
            'action' => 'confirm_email',
            'token' => $token,
            'user_id' => $user_id,
        ],
        site_url()
    );

    [$firstName, $lastName] = panterrea_esputnik_split_name(isset($formData['name']) ? (string) $formData['name'] : '');
    $phone_digits = isset($formData['phone']) ? preg_replace('/\D+/', '', (string) $formData['phone']) : '';
    $phone_display = isset($formData['phone']) ? sanitize_text_field((string) $formData['phone']) : '';
    $city = isset($formData['city']) ? sanitize_text_field((string) $formData['city']) : '';

    $event_key = defined('ESPUTNIK_REGISTRATION_EVENT_TYPE_KEY')
        ? (string) ESPUTNIK_REGISTRATION_EVENT_TYPE_KEY
        : 'Реєстрація нового користувача';

    $params = [
        // ідентифікатор контакту для eSputnik
        'emailAddress' => $email,
        'externalCustomerId' => (string) $user_id,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'email' => $email,
        'phone' => $phone_display !== '' ? $phone_display : $phone_digits,
        'city' => $city,
        'confirmationUrl' => $confirmation_url,
    ];

    if (apply_filters('panterrea_esputnik_registration_send_password', true, $user_id, $formData)) {
        $params['password'] = isset($formData['password']) ? (string) $formData['password'] : '';
    } else {
        $params['password'] = '';
    }

    $params = apply_filters('panterrea_esputnik_registration_event_params', $params, $user_id, $formData);

    foreach ($params as $k => $v) {
        $params[$k] = is_scalar($v) ? (string) $v : wp_json_encode($v);
    }

    $body = [
        'eventTypeKey' => $event_key,
        'params' => $params,
    ];

    $body = apply_filters('panterrea_esputnik_registration_event_body', $body, $user_id, $formData);

    $base = defined('ESPUTNIK_API_BASE') ? ESPUTNIK_API_BASE : 'https://esputnik.com';
    $base = rtrim((string) $base, '/');
    $url = $base . '/api/v3/event';

    $response = wp_remote_post(
        $url,
        [
            'timeout' => 15,
            'headers' => panterrea_esputnik_basic_auth_headers(),
            'body' => wp_json_encode($body),
        ]
    );

    if (is_wp_error($response)) {
        error_log('eSputnik event (registration): ' . $response->get_error_message());

        return;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        error_log(
            'eSputnik event (registration) HTTP ' . $code . ': ' . wp_remote_retrieve_body($response)
        );
    }
}

/**
 * Підписка контакта + генерація події реєстрації (після збереження токена верифікації пошти).
 */
function panterrea_esputnik_sync_after_registration(int $user_id, array $formData): void
{
    panterrea_esputnik_subscribe_on_registration($user_id, $formData);
    panterrea_esputnik_fire_registration_event($user_id, $formData);
}
