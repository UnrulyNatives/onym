<?php

namespace Blaspsoft\Onym\Strategies;

use Illuminate\Support\Str;

class RandomStrategy extends AbstractStrategy
{
    /**
     * Get the strategy name.
     */
    public function getName(): string
    {
        return 'random';
    }

    /**
     * Generate a random filename.
     */
    public function generate(?string $filename, ?string $extension, array $options = []): string
    {
        [$filename, $extension] = $this->prepare($filename, $extension);
        $options = $this->mergeOptions($options);
        
        $this->validateOptions($options);
        
        $length = $options['length'] ?? 16;
        $randomString = Str::random($length);
        
        $generatedName = $options['use_filename'] ?? false 
            ? "{$filename}_{$randomString}" 
            : $randomString;
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Validate strategy-specific options.
     */
    public function validateOptions(array $options): void
    {
        if (isset($options['length'])) {
            $this->validateLength($options['length'], 1, 255);
        }
    }
}