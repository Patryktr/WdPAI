<?php

class Translator
{
    private const DEFAULT_LOCALE = 'pl';
    private const SUPPORTED_LOCALES = ['pl', 'en'];

    private static array $cache = [];
    private static ?string $locale = null;

    public static function getLocale(): string
    {
        if (self::$locale === null) {
            self::$locale = self::detectLocale();
        }

        return self::$locale;
    }

    public static function setLocale(string $locale): void
    {
        $normalized = self::normalizeLocale($locale);

        if (!self::isSupported($normalized)) {
            $normalized = self::DEFAULT_LOCALE;
        }

        self::$locale = $normalized;

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['locale'] = $normalized;
        }
    }

    public static function translate(string $key): string
    {
        $locale = self::getLocale();
        $translations = self::loadTranslations($locale);

        if (!array_key_exists($key, $translations)) {
            return $key;
        }

        return (string) $translations[$key];
    }

    public static function isSupported(string $locale): bool
    {
        return in_array(self::normalizeLocale($locale), self::SUPPORTED_LOCALES, true);
    }

    public static function detectLocale(): string
    {
        $sessionLocale = $_SESSION['locale'] ?? null;
        if (is_string($sessionLocale) && self::isSupported($sessionLocale)) {
            return self::normalizeLocale($sessionLocale);
        }

        $cookieLocale = $_COOKIE['locale'] ?? null;
        if (is_string($cookieLocale) && self::isSupported($cookieLocale)) {
            return self::normalizeLocale($cookieLocale);
        }

        $acceptLanguage = (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        if ($acceptLanguage !== '') {
            foreach (explode(',', $acceptLanguage) as $languagePart) {
                $candidate = self::normalizeLocale(trim(explode(';', $languagePart)[0]));

                if (self::isSupported($candidate)) {
                    return $candidate;
                }
            }
        }

        return self::DEFAULT_LOCALE;
    }

    private static function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === '') {
            return '';
        }

        $parts = preg_split('/[-_]/', $normalized);

        return is_array($parts) ? (string) ($parts[0] ?? '') : $normalized;
    }

    private static function loadTranslations(string $locale): array
    {
        $effectiveLocale = self::isSupported($locale) ? self::normalizeLocale($locale) : self::DEFAULT_LOCALE;

        if (isset(self::$cache[$effectiveLocale])) {
            return self::$cache[$effectiveLocale];
        }

        $basePath = realpath(__DIR__.'/../../resources/lang');

        if ($basePath === false) {
            self::$cache[$effectiveLocale] = [];
            return self::$cache[$effectiveLocale];
        }

        $filePath = $basePath.DIRECTORY_SEPARATOR.$effectiveLocale.'.php';
        $translations = self::safeRequireTranslations($filePath);

        if (!is_array($translations) && $effectiveLocale !== self::DEFAULT_LOCALE) {
            $fallbackPath = $basePath.DIRECTORY_SEPARATOR.self::DEFAULT_LOCALE.'.php';
            $translations = self::safeRequireTranslations($fallbackPath);
            $effectiveLocale = self::DEFAULT_LOCALE;
        }

        if (!is_array($translations)) {
            $translations = [];
        }

        self::$cache[$effectiveLocale] = $translations;

        return $translations;
    }

    private static function safeRequireTranslations(string $filePath): array|null
    {
        if (!is_file($filePath)) {
            return null;
        }

        $translations = require $filePath;

        return is_array($translations) ? $translations : null;
    }
}
