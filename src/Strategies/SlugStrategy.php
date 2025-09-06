<?php

namespace Blaspsoft\Onym\Strategies;

use Illuminate\Support\Str;

class SlugStrategy extends AbstractStrategy
{
    /**
     * Get the strategy name.
     */
    public function getName(): string
    {
        return 'slug';
    }

    /**
     * Generate a slug filename.
     */
    public function generate(?string $filename, ?string $extension, array $options = []): string
    {
        [$filename, $extension] = $this->prepare($filename, $extension);
        $options = $this->mergeOptions($options);
        
        $separator = $options['separator'] ?? '-';
        $generatedName = Str::slug($filename, $separator);
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }
}