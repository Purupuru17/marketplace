<?php

namespace IdCore\CoreStarter\Support;

class Render
{
    public static function badge(string $variant, string $label): string
    {
        return view('idcore::components.badge', [
            'variant' => $variant,
            'slot' => $label,
        ])->render();
    }
}