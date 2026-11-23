<?php

class Flash
{
    private const SESSION_KEY = 'sb_flash_toast';

    private static function pastikanSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public static function set(string $type, string $message): void
    {
        self::pastikanSession();
        $_SESSION[self::SESSION_KEY] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    public static function get(): ?array
    {
        self::pastikanSession();
        if (!empty($_SESSION[self::SESSION_KEY]) && is_array($_SESSION[self::SESSION_KEY])) {
            $flash = $_SESSION[self::SESSION_KEY];
            unset($_SESSION[self::SESSION_KEY]);
            return $flash;
        }
        return null;
    }
}
