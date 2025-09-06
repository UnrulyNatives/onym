<?php

namespace Blaspsoft\Onym\Strategies;

use Illuminate\Support\Str;

class UuidStrategy extends AbstractStrategy
{
    /**
     * Get the strategy name.
     */
    public function getName(): string
    {
        return 'uuid';
    }

    /**
     * Generate a UUID filename.
     */
    public function generate(?string $filename, ?string $extension, array $options = []): string
    {
        [$filename, $extension] = $this->prepare($filename, $extension);
        $options = $this->mergeOptions($options);
        
        $uuid = (string) Str::uuid();
        
        $generatedName = $options['use_filename'] ?? false 
            ? "{$filename}_{$uuid}" 
            : $uuid;
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }
}