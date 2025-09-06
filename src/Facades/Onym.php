<?php

namespace Blaspsoft\Onym\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string make(?string $filename = null, ?string $extension = null, ?string $strategy = null, ?array $options = null)
 * @method static string random(?string $filename = null, ?string $extension = null, ?array $options = [])
 * @method static string uuid(?string $filename = null, ?string $extension = null, ?array $options = [])
 * @method static string timestamp(?string $filename = null, ?string $extension = null, ?array $options = [])
 * @method static string date(?string $filename = null, ?string $extension = null, ?array $options = [])
 * @method static string numbered(?string $filename = null, ?string $extension = null, ?array $options = [])
 * @method static string slug(?string $filename = null, ?string $extension = null, ?array $options = [])
 * @method static string hash(?string $filename = null, ?string $extension = null, ?array $options = [])
 * @method static string unique(?string $filename = null, ?string $extension = null, ?string $strategy = null, ?array $options = null)
 * @method static self setStoragePath(?string $path)
 * @method static self setMaxUniqueAttempts(int $attempts)
 */
class Onym extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'onym';
    }
}