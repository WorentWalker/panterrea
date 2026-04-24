<?php
/**
 * Currency conversion helpers for catalog prices.
 *
 * Fetches the daily USD/UAH exchange rate from the NBU (National Bank of Ukraine)
 * public API and maintains a `catalog_post_price_uah` post meta value that is
 * used purely for internal sorting — users always see the original price and
 * currency on the front-end.
 *
 * NBU API: https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=USD&json
 */

if (!defined('ABSPATH')) {
    exit;
}

// Option keys.
const PANTERREA_NBU_RATE_OPTION      = 'panterrea_usd_uah_rate';
const PANTERREA_NBU_RATE_DATE_OPTION = 'panterrea_usd_uah_rate_date';
// Last-resort fallback if the option is empty and the API is unreachable.
const PANTERREA_NBU_RATE_DEFAULT     = 41.0;

/**
 * Fetch the current USD->UAH rate from NBU and cache it in wp_options.
 * Returns the fetched rate on success, or the previously cached rate on failure.
 * If nothing is cached yet and the API fails, returns the hardcoded default.
 */
function panterrea_refresh_usd_uah_rate(): float
{
    $url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=USD&json';

    $response = wp_remote_get($url, [
        'timeout'   => 10,
        'sslverify' => true,
        'headers'   => ['Accept' => 'application/json'],
    ]);

    if (!is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (is_array($data) && !empty($data[0]['rate']) && is_numeric($data[0]['rate'])) {
            $rate = (float) $data[0]['rate'];
            if ($rate > 0) {
                $date = !empty($data[0]['exchangedate']) ? (string) $data[0]['exchangedate'] : date('d.m.Y');
                update_option(PANTERREA_NBU_RATE_OPTION, $rate, false);
                update_option(PANTERREA_NBU_RATE_DATE_OPTION, $date, false);
                return $rate;
            }
        }
    } elseif (is_wp_error($response)) {
        error_log('[panterrea] NBU rate fetch failed: ' . $response->get_error_message());
    }

    return panterrea_get_usd_uah_rate(false);
}

/**
 * Return the cached USD->UAH rate. When $refresh_if_missing is true and no
 * rate has ever been cached, attempts to fetch it once (guarded to avoid
 * recursion with panterrea_refresh_usd_uah_rate).
 */
function panterrea_get_usd_uah_rate(bool $refresh_if_missing = true): float
{
    static $in_refresh = false;

    $rate = (float) get_option(PANTERREA_NBU_RATE_OPTION, 0);
    if ($rate > 0) {
        return $rate;
    }

    if ($refresh_if_missing && !$in_refresh) {
        $in_refresh = true;
        $rate = panterrea_refresh_usd_uah_rate();
        $in_refresh = false;
        if ($rate > 0) {
            return $rate;
        }
    }

    return PANTERREA_NBU_RATE_DEFAULT;
}

/**
 * Convert a price + currency pair into UAH using the cached NBU rate.
 * Returns a float UAH value (may be 0 for empty / invalid inputs).
 */
function panterrea_convert_to_uah($price, $currency): float
{
    if (!is_numeric($price)) {
        return 0.0;
    }
    $price = (float) $price;
    if ($price <= 0) {
        return 0.0;
    }

    $currency_norm = is_string($currency) ? trim($currency) : '';
    $is_usd = in_array($currency_norm, ['$', 'USD', 'usd', 'дол', 'долар'], true);

    if ($is_usd) {
        $rate = panterrea_get_usd_uah_rate();
        return round($price * $rate, 2);
    }

    return round($price, 2);
}

/**
 * Format a price + currency for display to the user. Always preserves the
 * original currency (UAH or USD) — the UAH equivalent is internal sorting data
 * only. Returns an already-escaped string safe for echoing.
 *
 * Examples: "4 000 грн", "100 $", "Ціна договірна".
 */
function panterrea_format_price_display($price, $currency): string
{
    if (!is_numeric($price) || (float) $price <= 0) {
        return esc_html__('Ціна договірна', 'panterrea_v1');
    }

    $price = (float) $price;
    $formatted = $price == floor($price)
        ? number_format($price, 0, ',', ' ')
        : rtrim(rtrim(number_format($price, 2, ',', ' '), '0'), ',');

    $currency = is_string($currency) ? trim($currency) : '';
    if ($currency === '' || strtoupper($currency) === 'UAH') {
        $currency = 'грн';
    } elseif (in_array($currency, ['USD', 'usd', 'дол', 'долар'], true)) {
        $currency = '$';
    }

    $currency_label = __($currency, 'panterrea_v1');

    return esc_html($formatted . ' ' . $currency_label);
}

/**
 * Return a [price, currency] pair for a catalog post, falling back to post
 * meta when the ACF group field is missing the currency (older records).
 */
function panterrea_get_post_price_pair(int $post_id, $catalog_data = null): array
{
    $price    = null;
    $currency = null;

    if (is_array($catalog_data)) {
        $price    = $catalog_data['price']    ?? null;
        $currency = $catalog_data['currency'] ?? null;
    }

    if ($price === null || $price === '') {
        $price = get_post_meta($post_id, 'catalog_post_price', true);
    }
    if (!$currency) {
        $currency = get_post_meta($post_id, 'catalog_post_currency', true);
    }

    return [$price, $currency];
}

/**
 * Recalculate and store the UAH-equivalent price for a single catalog post.
 */
function panterrea_update_post_price_uah(int $post_id): void
{
    if (get_post_type($post_id) !== 'catalog_post') {
        return;
    }

    $price    = get_post_meta($post_id, 'catalog_post_price', true);
    $currency = get_post_meta($post_id, 'catalog_post_currency', true);

    $uah = panterrea_convert_to_uah($price, $currency);

    if ($uah > 0) {
        update_post_meta($post_id, 'catalog_post_price_uah', $uah);
    } else {
        delete_post_meta($post_id, 'catalog_post_price_uah');
    }
}

// Recalculate whenever the ad's ACF fields are saved (covers both create + edit flows).
add_action('acf/save_post', function ($post_id) {
    if (!is_numeric($post_id)) {
        return;
    }
    panterrea_update_post_price_uah((int) $post_id);
}, 20);

// Also recalc on generic post save as a safety net for programmatic updates.
add_action('save_post_catalog_post', function ($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    panterrea_update_post_price_uah((int) $post_id);
}, 20);

/**
 * Recalculate price_uah for all USD-priced catalog posts. UAH posts never
 * change, so they are skipped. Invoked by the daily cron and can be triggered
 * manually via the admin action below.
 */
function panterrea_recalculate_all_usd_prices(): int
{
    $query = new WP_Query([
        'post_type'      => 'catalog_post',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            [
                'key'     => 'catalog_post_currency',
                'value'   => ['$', 'USD', 'usd'],
                'compare' => 'IN',
            ],
        ],
    ]);

    $count = 0;
    foreach ($query->posts as $post_id) {
        panterrea_update_post_price_uah((int) $post_id);
        $count++;
    }
    wp_reset_postdata();
    return $count;
}

/**
 * One-time backfill: ensures every catalog post has `catalog_post_price_uah`
 * stored so price sorting & filtering works for historical ads. Idempotent —
 * guarded by an option that stores the backfill version.
 */
function panterrea_backfill_price_uah_if_needed(): void
{
    $backfill_version = '1';
    if (get_option('panterrea_price_uah_backfill') === $backfill_version) {
        return;
    }

    $query = new WP_Query([
        'post_type'      => 'catalog_post',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    foreach ($query->posts as $post_id) {
        panterrea_update_post_price_uah((int) $post_id);
    }
    wp_reset_postdata();

    update_option('panterrea_price_uah_backfill', $backfill_version, false);
}

add_action('admin_init', 'panterrea_backfill_price_uah_if_needed');

// Daily cron: refresh NBU rate, then refresh all USD-priced posts.
if (!wp_next_scheduled('panterrea_refresh_currency_rates')) {
    wp_schedule_event(time() + 60, 'daily', 'panterrea_refresh_currency_rates');
}

add_action('panterrea_refresh_currency_rates', function () {
    panterrea_refresh_usd_uah_rate();
    panterrea_recalculate_all_usd_prices();
});

// Admin-triggered one-off action: runs the same job on demand (useful after deploy).
add_action('admin_post_panterrea_refresh_currency', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', '', ['response' => 403]);
    }
    check_admin_referer('panterrea_refresh_currency');
    panterrea_refresh_usd_uah_rate();
    $count = panterrea_recalculate_all_usd_prices();
    wp_safe_redirect(add_query_arg([
        'page'             => 'panterrea-tools',
        'currency_updated' => (int) $count,
    ], admin_url('admin.php')));
    exit;
});
