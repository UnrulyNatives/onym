<?php

namespace Blaspsoft\Onym\Strategies;

use InvalidArgumentException;

class NumberedStrategy extends AbstractStrategy
{
    /**
     * Get the strategy name.
     */
    public function getName(): string
    {
        return 'numbered';
    }

    /**
     * Generate a numbered filename.
     */
    public function generate(?string $filename, ?string $extension, array $options = []): string
    {
        [$filename, $extension] = $this->prepare($filename, $extension);
        $options = $this->mergeOptions($options);
        
        $this->validateOptions($options);
        
        $number = $options['number'] ?? 1;
        $separator = $options['separator'] ?? '_';
        $padLength = $options['pad_length'] ?? 0;
        
        $numberString = $padLength > 0 
            ? str_pad((string)$number, $padLength, '0', STR_PAD_LEFT) 
            : (string)$number;
            
        $generatedName = "{$filename}{$separator}{$numberString}";
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Validate strategy-specific options.
     */
    public function validateOptions(array $options): void
    {
        if (isset($options['number'])) {
            $this->validateNumber($options['number']);
        }
        
        if (isset($options['pad_length'])) {
            $this->validatePadLength($options['pad_length']);
        }
    }

    /**
     * Validate number parameter.
     */
    protected function validateNumber(int $number): void
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Number must be non-negative');
        }
    }

    /**
     * Validate pad length parameter.
     */
    protected function validatePadLength(int $padLength): void
    {
        if ($padLength < 0 || $padLength > 20) {
            throw new InvalidArgumentException('Pad length must be between 0 and 20');
        }
    }
}