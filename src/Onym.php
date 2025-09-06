<?php

namespace Blaspsoft\Onym;

use InvalidArgumentException;

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
 */
class Onym
{
    /**
     * The strategy registry.
     */
    protected StrategyRegistry $registry;

    /**
     * Storage path for collision detection.
     */
    protected ?string $storagePath = null;

    /**
     * Maximum attempts for unique generation.
     */
    protected int $maxUniqueAttempts = 10;

    /**
     * Default filename to use.
     */
    protected string $defaultFilename;

    /**
     * Default extension to use.
     */
    protected string $defaultExtension;

    public function __construct(StrategyRegistry $registry)
    {
        $this->registry = $registry;
        $this->defaultFilename = config('onym.default_filename', 'file');
        $this->defaultExtension = config('onym.default_extension', 'txt');
        $this->storagePath = config('onym.storage_path');
        $this->maxUniqueAttempts = config('onym.max_unique_attempts', 10);
    }

    /**
     * Generate a new filename based on the strategy and options.
     */
    public function make(
        ?string $filename = null,
        ?string $extension = null,
        ?string $strategy = null,
        ?array $options = null
    ): string {
        $strategy = $this->registry->get($strategy);
        return $strategy->generate($filename, $extension, $options ?? []);
    }

    /**
     * Generate a random filename.
     */
    public function random(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        return $this->make($filename, $extension, 'random', $options);
    }

    /**
     * Generate a UUID filename.
     */
    public function uuid(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        return $this->make($filename, $extension, 'uuid', $options);
    }

    /**
     * Generate a timestamp filename.
     */
    public function timestamp(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        return $this->make($filename, $extension, 'timestamp', $options);
    }

    /**
     * Generate a date filename.
     */
    public function date(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        return $this->make($filename, $extension, 'date', $options);
    }

    /**
     * Generate a numbered filename.
     */
    public function numbered(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        return $this->make($filename, $extension, 'numbered', $options);
    }

    /**
     * Generate a slug filename.
     */
    public function slug(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        return $this->make($filename, $extension, 'slug', $options);
    }

    /**
     * Generate a hash filename.
     */
    public function hash(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        return $this->make($filename, $extension, 'hash', $options);
    }

    /**
     * Generate a unique filename, checking for collisions.
     */
    public function unique(
        ?string $filename = null,
        ?string $extension = null,
        ?string $strategy = null,
        ?array $options = null
    ): string {
        $options = $options ?? [];
        $attempts = 0;
        $strategyName = $strategy ?? $this->registry->getDefault();
        $strategyInstance = $this->registry->get($strategyName);
        
        do {
            $generatedName = $strategyInstance->generate($filename, $extension, $options);
            $attempts++;
            
            if (!$this->storagePath || !$this->fileExists($generatedName)) {
                return $generatedName;
            }
            
            // Modify options to ensure different output on retry based on strategy type
            $options = $this->modifyOptionsForRetry($strategyInstance->getName(), $options, $attempts);
            
        } while ($attempts < $this->maxUniqueAttempts);
        
        // Final attempt with UUID to ensure uniqueness
        $uuidStrategy = $this->registry->get('uuid');
        return $uuidStrategy->generate($filename, $extension, array_merge($options, ['suffix' => '_' . time()]));
    }

    /**
     * Modify options for retry attempt to ensure different output.
     */
    protected function modifyOptionsForRetry(string $strategyName, array $options, int $attempt): array
    {
        switch ($strategyName) {
            case 'numbered':
                $options['number'] = ($options['number'] ?? 1) + 1;
                break;
            case 'random':
                $options['length'] = ($options['length'] ?? 16) + 2;
                break;
            case 'timestamp':
            case 'date':
                // Add microseconds or increment suffix
                $options['suffix'] = ($options['suffix'] ?? '') . '_' . $attempt;
                break;
            case 'hash':
                // Force timestamp inclusion for uniqueness
                $options['include_timestamp'] = true;
                break;
            default:
                // For other strategies, append attempt number
                $options['suffix'] = ($options['suffix'] ?? '') . '_' . $attempt;
                break;
        }
        
        return $options;
    }

    /**
     * Register a custom strategy.
     */
    public function extend(string $name, callable $generator): self
    {
        $this->registry->extend($name, $generator);
        return $this;
    }

    /**
     * Check if a strategy exists.
     */
    public function hasStrategy(string $name): bool
    {
        return $this->registry->has($name);
    }

    /**
     * Get all registered strategy names.
     */
    public function getStrategies(): array
    {
        return $this->registry->names();
    }

    /**
     * Get the strategy registry.
     */
    public function getRegistry(): StrategyRegistry
    {
        return $this->registry;
    }

    /**
     * Check if file exists (for collision detection).
     */
    protected function fileExists(string $filename): bool
    {
        if (!$this->storagePath) {
            return false;
        }
        
        $fullPath = rtrim($this->storagePath, '/') . '/' . $filename;
        return file_exists($fullPath);
    }

    /**
     * Set storage path for collision detection.
     */
    public function setStoragePath(?string $path): self
    {
        $this->storagePath = $path;
        return $this;
    }

    /**
     * Set maximum unique attempts.
     */
    public function setMaxUniqueAttempts(int $attempts): self
    {
        $this->maxUniqueAttempts = max(1, $attempts);
        return $this;
    }

    /**
     * Handle dynamic method calls for custom strategies.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __call(string $method, array $arguments)
    {
        if ($this->registry->has($method)) {
            $filename = $arguments[0] ?? null;
            $extension = $arguments[1] ?? null;
            $options = $arguments[2] ?? [];
            
            return $this->make($filename, $extension, $method, $options);
        }
        
        throw new InvalidArgumentException("Method or strategy '{$method}' does not exist");
    }
}