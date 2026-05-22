<?php

function checkRequestAllowed(object|string $controller, string $methodName): bool
{
    if (!method_exists($controller, $methodName)) {
        return true;
    }

    $reflectionMethod = new ReflectionMethod($controller, $methodName);
    $attributes = $reflectionMethod->getAttributes(AllowedMethods::class);

    if (empty($attributes)) {
        return true;
    }

    /** @var AllowedMethods $allowedMethods */
    $allowedMethods = $attributes[0]->newInstance();
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    return in_array($requestMethod, $allowedMethods->methods, true);
}

function getAllowedRequestMethods(object|string $controller, string $methodName): array
{
    if (!method_exists($controller, $methodName)) {
        return [];
    }

    $reflectionMethod = new ReflectionMethod($controller, $methodName);
    $attributes = $reflectionMethod->getAttributes(AllowedMethods::class);

    if (empty($attributes)) {
        return [];
    }

    /** @var AllowedMethods $allowedMethods */
    $allowedMethods = $attributes[0]->newInstance();

    return $allowedMethods->methods;
}
