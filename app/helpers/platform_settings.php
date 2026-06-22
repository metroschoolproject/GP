<?php
/**
 * Read a setting from the platform_settings table.
 * Results are cached per-request via a static array.
 */
function get_platform_setting(string $key, string $default = ''): string
{
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $db = new Database();
        $db->dbquery('SELECT setting_value FROM platform_settings WHERE setting_key = :key LIMIT 1');
        $db->dbbind(':key', $key, PDO::PARAM_STR);
        $row = $db->getsingledata();
        $cache[$key] = $row ? (string) $row['setting_value'] : $default;
    } catch (\Throwable $e) {
        $cache[$key] = $default;
    }

    return $cache[$key];
}

/**
 * Return the platform fee percentage (e.g. 5.0).
 * Falls back to the PLATFORM_FEE_PERCENT constant if the DB table is missing.
 */
function get_platform_fee_percent(): float
{
    $fallback = defined('PLATFORM_FEE_PERCENT') ? (string) PLATFORM_FEE_PERCENT : '5';
    return (float) get_platform_setting('platform_fee_percent', $fallback);
}
