<?php

namespace Blaspsoft\Onym\Strategies;

use DateTime;
use InvalidArgumentException;

class DateStrategy extends AbstractStrategy
{
    /**
     * Get the strategy name.
     */
    public function getName(): string
    {
        return 'date';
    }

    /**
     * Generate a date filename.
     */
    public function generate(?string $filename, ?string $extension, array $options = []): string
    {
        [$filename, $extension] = $this->prepare($filename, $extension);
        $options = $this->mergeOptions($options);
        
        $this->validateOptions($options);
        
        $format = $options['format'] ?? 'Y-m-d';
        $date = new DateTime();
        $dateString = $date->format($format);
        
        $generatedName = $options['prepend_date'] ?? false
            ? "{$dateString}_{$filename}"
            : "{$filename}_{$dateString}";
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Validate strategy-specific options.
     */
    public function validateOptions(array $options): void
    {
        if (isset($options['format'])) {
            $this->validateDateFormat($options['format']);
        }
    }

    /**
     * Validate date format.
     */
    protected function validateDateFormat(string $format): void
    {
        // List of valid date format characters from PHP documentation
        $validChars = 'dDjlNSwzWFmMntLoYyaABgGhHisuveIOPTZcrU -_/:\\';
        
        // Check if format contains only valid characters
        if (preg_match('/[^' . preg_quote($validChars, '/') . ']/', $format)) {
            throw new InvalidArgumentException("Invalid date format: {$format}");
        }

        // Additional check: try to format with current date
        try {
            $date = new DateTime();
            $date->format($format);
        } catch (\Exception) {
            throw new InvalidArgumentException("Invalid date format: {$format}");
        }
    }
}