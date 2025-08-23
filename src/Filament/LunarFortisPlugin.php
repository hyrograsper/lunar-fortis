<?php

namespace Hyrograsper\LunarFortis\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

class LunarFortisPlugin implements Plugin
{
    public function getId(): string
    {
        return 'lunar-fortis';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(in: __DIR__.'/Resources', for: 'Hyrograsper\\LunarFortis\\Filament\\Resources');
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
