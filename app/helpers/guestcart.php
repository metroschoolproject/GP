<?php
/**
 * Guest Cart Persistence — Cookie-based helper
 *
 * Stores cart items for non-logged-in users in a cookie
 * so they survive browser restarts.
 *
 * Cookie name: gp_guest_cart
 * Lifetime: 7 days
 */

define('GUEST_CART_COOKIE', 'gp_guest_cart');
define('GUEST_CART_DAYS', 7);

/**
 * Save a cart item to the guest cart cookie.
 * @param array $itemData The cart item data array
 */
function saveGuestCartItem(array $itemData): void
{
    $items = getGuestCartItems();
    $items[] = $itemData;
    setGuestCartCookie($items);
}

/**
 * Get all items from the guest cart cookie.
 * @return array Array of cart item data
 */
function getGuestCartItems(): array
{
    if (empty($_COOKIE[GUEST_CART_COOKIE])) {
        return [];
    }

    $decoded = json_decode($_COOKIE[GUEST_CART_COOKIE], true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Clear the guest cart cookie.
 */
function clearGuestCart(): void
{
    $path = parse_url(URLROOT, PHP_URL_PATH) ?: '/';
    setcookie(GUEST_CART_COOKIE, '', [
        'expires'  => time() - 3600,
        'path'     => $path,
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[GUEST_CART_COOKIE]);
}

/**
 * Set the guest cart cookie with the given items.
 * @param array $items Array of cart item data
 */
function setGuestCartCookie(array $items): void
{
    $expires = time() + (GUEST_CART_DAYS * 86400);
    $path = parse_url(URLROOT, PHP_URL_PATH) ?: '/';

    setcookie(GUEST_CART_COOKIE, json_encode($items), [
        'expires'  => $expires,
        'path'     => $path,
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);

    // Also update $_COOKIE so current request sees it
    $_COOKIE[GUEST_CART_COOKIE] = json_encode($items);
}

/**
 * Check if there's a guest cart with items.
 * @return bool
 */
function hasGuestCart(): bool
{
    $items = getGuestCartItems();
    return !empty($items);
}
