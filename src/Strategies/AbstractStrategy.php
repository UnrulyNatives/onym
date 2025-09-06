<?php

namespace Blaspsoft\Onym\Strategies;

use Blaspsoft\Onym\Contracts\Strategy;
use InvalidArgumentException;

abstract class AbstractStrategy implements Strategy
{
    /**
     * Default filename when none is provided.
     */
    protected string $defaultFilename;

    /**
     * Default extension when none is provided.
     */
    protected string $defaultExtension;

    /**
     * Strategy-specific default options.
     */
    protected array $defaultOptions = [];

    public function __construct()
    {
        $this->defaultFilename = config('onym.default_filename', 'file');
        $this->defaultExtension = config('onym.default_extension', 'txt');
        $this->loadDefaultOptions();
    }

    /**
     * Load default options from config.
     */
    protected function loadDefaultOptions(): void
    {
        $strategyName = $this->getName();
        $this->defaultOptions = config("onym.options.{$strategyName}", []);
    }

    /**
     * Get default options for the strategy.
     */
    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    /**
     * Merge user options with default options.
     */
    protected function mergeOptions(array $options): array
    {
        return array_merge($this->defaultOptions, $options);
    }

    /**
     * Apply prefix and suffix to filename.
     */
    protected function applyAffixes(string $filename, string $extension, array $options): string
    {
        if (!empty($options['prefix'])) {
            $filename = $options['prefix'] . $filename;
        }
        
        if (!empty($options['suffix'])) {
            $filename = $filename . $options['suffix'];
        }
        
        return $filename . '.' . $extension;
    }

    /**
     * Sanitize filename to prevent path traversal.
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove any path separators and parent directory references
        $filename = str_replace(['/', '\\', '..'], '', $filename);
        
        // Remove any non-printable characters
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);
        
        // Trim whitespace
        $filename = trim($filename);
        
        if (empty($filename)) {
            return $this->defaultFilename;
        }
        
        return $filename;
    }

    /**
     * Sanitize file extension.
     */
    protected function sanitizeExtension(string $extension): string
    {
        // Remove dots and path separators
        $extension = str_replace(['.', '/', '\\'], '', $extension);
        
        // Convert to lowercase
        $extension = strtolower($extension);
        
        // Limit length
        if (strlen($extension) > 10) {
            $extension = substr($extension, 0, 10);
        }
        
        if (empty($extension)) {
            return $this->defaultExtension;
        }
        
        return $extension;
    }

    /**
     * Prepare filename and extension.
     */
    protected function prepare(?string $filename, ?string $extension): array
    {
        $filename = $this->sanitizeFilename($filename ?? $this->defaultFilename);
        $extension = $this->sanitizeExtension($extension ?? $this->defaultExtension);
        
        return [$filename, $extension];
    }

    /**
     * Validate length parameter.
     */
    protected function validateLength(int $length, int $min = 1, int $max = 255): void
    {
        if ($length < $min || $length > $max) {
            throw new InvalidArgumentException("Length must be between {$min} and {$max}");
        }
    }

    /**
     * Default validation implementation.
     */
    public function validateOptions(array $options): void
    {
        // Override in child classes for specific validation
    }
}