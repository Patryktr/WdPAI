<?php

#[Attribute(Attribute::TARGET_METHOD)]
class AllowedMethods
{
    public array $methods;

    public function __construct(string ...$methods)
    {
        $this->methods = array_map('strtoupper', $methods);
    }
}
