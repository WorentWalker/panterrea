### Налаштування Google OAuth:

1. Перейдіть на [Google Cloud Console](https://console.cloud.google.com/)
2. Створіть новий проект або виберіть існуючий
3. Увімкніть **Google+ API**
4. Перейдіть у **Credentials → Create Credentials → OAuth client ID**
5. Виберіть **Web application**
6. Додайте **Authorized redirect URIs**:
   - `https://ваш-сайт.com/wp-admin/admin-ajax.php?action=nsl-callback&provider=google`
7. Скопіюйте **Client ID** та **Client Secret**
8. В WordPress перейдіть у **Nextend Social Login → Settings → Google**
9. Вставте Client ID та Client Secret
10. Збережіть налаштування

### Налаштування Facebook OAuth:

1. Перейдіть на [Facebook Developers](https://developers.facebook.com/)
2. Створіть новий додаток (тип: **Consumer**)
3. Додайте **Facebook Login** продукт
4. В налаштуваннях Facebook Login додайте **Valid OAuth Redirect URIs**:
   - `https://ваш-сайт.com/wp-admin/admin-ajax.php?action=nsl-callback&provider=facebook`
5. Скопіюйте **App ID** та **App Secret**
6. В WordPress перейдіть у **Nextend Social Login → Settings → Facebook**
7. Вставте App ID та App Secret
8. Збережіть налаштування
