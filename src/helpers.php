<?php

require_once __DIR__.'/services/Translator.php';

if (!function_exists('__')) {
    function __(string $key): string
    {
        return Translator::translate($key);
    }
}

if (!function_exists('current_locale')) {
    function current_locale(): string
    {
        return Translator::getLocale();
    }
}

if (!function_exists('localize_category_name')) {
    function localize_category_name(string $name): string
    {
        $normalized = strtolower(trim($name));
        $map = [
            'bills' => 'categories.default.bills',
            'food' => 'categories.default.food',
            'fun' => 'categories.default.fun',
            'health' => 'categories.default.health',
            'other' => 'categories.default.other',
            'retail' => 'categories.default.retail',
            'transport' => 'categories.default.transport',
            'travel' => 'categories.default.travel',
        ];

        $key = $map[$normalized] ?? null;

        if ($key === null) {
            return $name;
        }

        return __($key);
    }
}

Translator::setLocale(Translator::detectLocale());
