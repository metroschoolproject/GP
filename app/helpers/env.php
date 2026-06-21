<?php
/**
 * Minimal .env loader (no external dependency).
 * Parses KEY=VALUE lines into the process environment. Existing real
 * environment variables always win over .env file values.
 */
function loadEnv(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Strip matching surrounding quotes.
        $len = strlen($value);
        if ($len >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[$len - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

/**
 * Read an env var, returning $default when unset or empty.
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}
