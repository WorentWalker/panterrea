# 🔑 Налаштування API ключів

## ⚠️ ВАЖЛИВО!

Після встановлення теми потрібно додати справжні API ключі для роботи Stripe та AWS S3.

---

## 📋 Необхідні ключі

### 1. **Stripe** (для оплати Boost оголошень)

Потрібно 2 ключі:
- `STRIPE_PUBLISHABLE_KEY` - Публічний ключ (починається з `pk_`)
- `STRIPE_SECRET_KEY` - Секретний ключ (починається з `sk_`)

### 2. **AWS S3** (для завантаження зображень)

Потрібно 3 параметри:
- `S3_KEY` - AWS Access Key ID
- `S3_SECRET` - AWS Secret Access Key
- `S3_BUCKET` - Назва S3 бакета

---

## 🚀 Де взяти ключі

### Stripe:

1. Зайдіть на https://dashboard.stripe.com/
2. Увійдіть в свій аккаунт
3. Перейдіть в **Developers → API keys**
4. Скопіюйте:
   - **Publishable key** (для `STRIPE_PUBLISHABLE_KEY`)
   - **Secret key** (для `STRIPE_SECRET_KEY`)

**Режими:**
- **Test keys** (для розробки): `pk_test_...` і `sk_test_...`
- **Live keys** (для продакшену): `pk_live_...` і `sk_live_...`

### AWS S3:

1. Зайдіть на https://console.aws.amazon.com/
2. Перейдіть в **IAM → Users**
3. Створіть нового користувача або виберіть існуючого
4. Надайте права **AmazonS3FullAccess**
5. Створіть **Access Key**
6. Скопіюйте:
   - **Access key ID** (для `S3_KEY`)
   - **Secret access key** (для `S3_SECRET`)
7. Перейдіть в **S3 → Buckets**
8. Створіть новий bucket або виберіть існуючий
9. Скопіюйте назву bucket (для `S3_BUCKET`)

---

## ⚙️ Як додати ключі

### Варіант 1: У файл `wp-config.php` (РЕКОМЕНДОВАНО) ✅

Це найбезпечніший спосіб, оскільки `wp-config.php` не завантажується в Git.

**Де:** `/wp-config.php`

**Додайте ці рядки перед `/* That's all, stop editing! */`:**

```php
/**
 * API Keys for PanTerrea
 */

// Stripe
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_ваш_ключ_тут');
define('STRIPE_SECRET_KEY', 'sk_test_ваш_ключ_тут');

// AWS S3
define('S3_KEY', 'ваш_aws_access_key');
define('S3_SECRET', 'ваш_aws_secret_key');
define('S3_BUCKET', 'назва_вашого_bucket');
```

### Варіант 2: У файл `functions.php` (Менш безпечний)

**Де:** `/wp-content/themes/panterrea_v1/functions.php`

**Знайдіть рядки ~27-48 і замініть значення:**

```php
// Stripe API Keys
if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    define('STRIPE_PUBLISHABLE_KEY', 'pk_test_ваш_ключ_тут'); // ← СЮДИ
}
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', 'sk_test_ваш_ключ_тут'); // ← СЮДИ
}

// AWS S3 Keys
if (!defined('S3_KEY')) {
    define('S3_KEY', 'ваш_aws_access_key'); // ← СЮДИ
}
if (!defined('S3_SECRET')) {
    define('S3_SECRET', 'ваш_aws_secret_key'); // ← СЮДИ
}
if (!defined('S3_BUCKET')) {
    define('S3_BUCKET', 'назва_вашого_bucket'); // ← СЮДИ
}
```

---

## 🧪 Перевірка

### Перевірка Stripe:

1. Перейдіть на сторінку створення оголошення
2. Створіть оголошення
3. Виберіть "Швидко продаю" (Boost)
4. Якщо з'явилась форма оплати Stripe - все працює! ✅

### Перевірка S3:

1. Створіть оголошення з фото
2. Завантажте зображення
3. Якщо зображення завантажилось - все працює! ✅
4. Перевірте в AWS S3 Console - файли мають з'явитись в bucket

---

## 🔒 Безпека

### ⚠️ НЕ РОБІТЬ:

- ❌ Не додавайте ключі в Git
- ❌ Не публікуйте ключі в публічних репозиторіях
- ❌ Не діліться секретними ключами
- ❌ Не використовуйте Live ключі на тестових сайтах

### ✅ РОБІТЬ:

- ✅ Додайте `wp-config.php` в `.gitignore`
- ✅ Використовуйте Test ключі для розробки
- ✅ Використовуйте Live ключі тільки на продакшені
- ✅ Регулярно оновлюйте ключі
- ✅ Обмежте права IAM користувача в AWS

---

## 📝 Environment Variables (для продакшену)

На продакшен серверах використовуйте змінні оточення:

```php
// Stripe
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY'));
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY'));

// AWS S3
define('S3_KEY', getenv('AWS_ACCESS_KEY_ID'));
define('S3_SECRET', getenv('AWS_SECRET_ACCESS_KEY'));
define('S3_BUCKET', getenv('AWS_S3_BUCKET'));
```

Налаштуйте змінні на сервері через панель хостингу або `.env` файл.

---

## 🐛 Проблеми та рішення

### Помилка: "Undefined constant STRIPE_PUBLISHABLE_KEY"

**Причина:** Ключі не додані або додані неправильно

**Рішення:**
1. Перевірте чи додали ви константи
2. Перевірте назви констант (має бути точно як в прикладі)
3. Перевірте чи немає зайвих пробілів
4. Очистіть кеш WordPress

### Помилка: "Invalid API Key provided"

**Причина:** Неправильний ключ Stripe

**Рішення:**
1. Перевірте чи скопіювали правильний ключ
2. Перевірте чи не закінчився термін дії ключа
3. Перевірте режим (test/live)
4. Створіть новий ключ в Stripe Dashboard

### Помилка: "Access Denied" при завантаженні в S3

**Причина:** Недостатньо прав IAM користувача

**Рішення:**
1. Перевірте права IAM користувача
2. Додайте політику `AmazonS3FullAccess`
3. Перевірте CORS налаштування bucket
4. Перевірте чи не заблоковано публічний доступ

### Зображення не завантажуються

**Причина:** Неправильна назва bucket або регіон

**Рішення:**
1. Перевірте назву bucket (має бути точна)
2. Перевірте регіон в коді (зараз `eu-central-1`)
3. Змініть регіон якщо ваш bucket в іншому:

```php
// У functions.php знайдіть:
'region' => 'eu-central-1', // ← змініть на свій регіон
```

---

## 📊 Тестування

### Test Cards для Stripe:

Використовуйте ці тестові карти в режимі Test:

| Карта | Номер | Результат |
|-------|-------|-----------|
| Успішна | `4242 4242 4242 4242` | Оплата пройде |
| Відхилена | `4000 0000 0000 0002` | Оплата відхилена |
| 3D Secure | `4000 0027 6000 3184` | Потребує підтвердження |

**Для всіх карт:**
- CVC: будь-які 3 цифри
- Дата: будь-яка майбутня дата
- ZIP: будь-який

---

## 🌍 Регіони AWS

Якщо ваш S3 bucket в іншому регіоні, змініть `eu-central-1` на:

| Регіон | Код |
|--------|-----|
| US East (N. Virginia) | `us-east-1` |
| US West (Oregon) | `us-west-2` |
| Europe (Frankfurt) | `eu-central-1` |
| Europe (Ireland) | `eu-west-1` |
| Asia Pacific (Tokyo) | `ap-northeast-1` |
| Asia Pacific (Singapore) | `ap-southeast-1` |

Повний список: https://docs.aws.amazon.com/general/latest/gr/s3.html

---

## 📞 Підтримка

**Stripe:**
- Документація: https://stripe.com/docs
- Support: https://support.stripe.com/

**AWS S3:**
- Документація: https://docs.aws.amazon.com/s3/
- Support: https://aws.amazon.com/support/

**PanTerrea:**
- Читайте документацію в папці теми
- Перевіряйте логи WordPress (`wp-content/debug.log`)

---

## ✅ Checklist

Перед запуском на продакшені:

- [ ] Додані справжні ключі (не тестові значення)
- [ ] Stripe використовує Live keys (`pk_live_`, `sk_live_`)
- [ ] AWS IAM користувач має мінімально необхідні права
- [ ] S3 bucket налаштований правильно (CORS, політики)
- [ ] Ключі додані в `wp-config.php`, а не в `functions.php`
- [ ] `wp-config.php` в `.gitignore`
- [ ] Протестовано створення оголошення з фото
- [ ] Протестовано Boost оплату
- [ ] Логи не показують помилок API

---

**Дата створення:** 2025-02-06  
**Версія:** 1.0

**Успішного налаштування! 🚀**
