<?php

namespace Blaspsoft\Onym;

use DateTime;
use Illuminate\Support\Str;
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
     * The strategy to use.
     */
    protected string $strategy;

    /**
     * The options to use.
     */
    protected array $options;

    /**
     * The default filename to use.
     */
    protected string $defaultFilename;

    /**
     * The default extension to use.
     */
    protected string $defaultExtension;

    /**
     * Cached hash algorithms for performance.
     */
    protected static ?array $hashAlgorithms = null;

    /**
     * Storage path for collision detection.
     */
    protected ?string $storagePath = null;

    /**
     * Maximum attempts for unique generation.
     */
    protected int $maxUniqueAttempts = 10;

    public function __construct()
    {
        $this->strategy = config('onym.strategy', 'random');
        $this->options = config('onym.options', []);
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
        $filename = $this->sanitizeFilename($filename ?? $this->defaultFilename);
        $extension = $this->sanitizeExtension($extension ?? $this->defaultExtension);
        $useStrategy = $strategy ?? $this->strategy;
        $useOptions = $options !== null 
            ? $this->mergeOptions($options, $useStrategy, $this->options) 
            : ($this->options[$useStrategy] ?? []);

        $this->validateInputs($filename, $extension, $useStrategy, $useOptions);

        return match ($useStrategy) {
            'random' => $this->random($filename, $extension, $useOptions),
            'uuid' => $this->uuid($filename, $extension, $useOptions),
            'timestamp' => $this->timestamp($filename, $extension, $useOptions),
            'date' => $this->date($filename, $extension, $useOptions),
            'numbered' => $this->numbered($filename, $extension, $useOptions),
            'slug' => $this->slug($filename, $extension, $useOptions),
            'hash' => $this->hash($filename, $extension, $useOptions),
            default => throw new InvalidArgumentException("Unknown strategy: {$useStrategy}"),
        };
    }

    /**
     * Generate a random string filename.
     */
    public function random(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        $filename = $filename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;
        $options = $this->mergeOptions($options, 'random', $this->options);
        
        $length = $options['length'] ?? 16;
        $this->validateLength($length, 1, 255);
        
        $randomString = Str::random($length);
        $generatedName = $options['use_filename'] ?? false ? "{$filename}_{$randomString}" : $randomString;
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Generate a UUID filename.
     */
    public function uuid(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        $filename = $filename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;
        $options = $this->mergeOptions($options, 'uuid', $this->options);
        
        $uuid = (string) Str::uuid();
        $generatedName = $options['use_filename'] ?? false ? "{$filename}_{$uuid}" : $uuid;
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Generate a timestamp filename.
     */
    public function timestamp(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        $filename = $filename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;
        $options = $this->mergeOptions($options, 'timestamp', $this->options);

        $format = $options['format'] ?? 'Y-m-d_H-i-s';
        $this->validateDateFormat($format);
        
        $date = $this->getDateTime();
        $timestamp = $date->format($format);
        $generatedName = $options['prepend_timestamp'] ?? false 
            ? "{$timestamp}_{$filename}"
            : "{$filename}_{$timestamp}";
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Generate a date filename.
     */
    public function date(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        $filename = $filename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;
        $options = $this->mergeOptions($options, 'date', $this->options);
        
        $format = $options['format'] ?? 'Y-m-d';
        $this->validateDateFormat($format);
        
        $date = $this->getDateTime();
        $dateString = $date->format($format);
        $generatedName = $options['prepend_date'] ?? false
            ? "{$dateString}_{$filename}"
            : "{$filename}_{$dateString}";
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Generate a numbered filename.
     */
    public function numbered(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        $filename = $filename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;
        $options = $this->mergeOptions($options, 'numbered', $this->options);
        
        $number = $options['number'] ?? 1;
        $separator = $options['separator'] ?? '_';
        $padLength = $options['pad_length'] ?? 0;
        
        $this->validateNumber($number);
        
        $numberString = $padLength > 0 ? str_pad($number, $padLength, '0', STR_PAD_LEFT) : $number;
        $generatedName = "{$filename}{$separator}{$numberString}";
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Generate a slug filename.
     */
    public function slug(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        $filename = $filename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;
        $options = $this->mergeOptions($options, 'slug', $this->options);
        
        $separator = $options['separator'] ?? '-';
        $generatedName = Str::slug($filename, $separator);
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Generate a hash filename.
     */
    public function hash(?string $filename = null, ?string $extension = null, ?array $options = []): string
    {
        $filename = $filename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;
        $options = $this->mergeOptions($options, 'hash', $this->options);
        
        $algorithm = $options['algorithm'] ?? 'md5';
        $length = $options['length'] ?? null;
        
        $this->validateHashAlgorithm($algorithm);
        
        $hashInput = $options['include_timestamp'] ?? false 
            ? $filename . microtime(true) 
            : $filename;
            
        $hash = hash($algorithm, $hashInput);
        
        if ($length !== null && $length > 0) {
            $this->validateLength($length, 1, strlen($hash));
            $hash = substr($hash, 0, $length);
        }
        
        $generatedName = $options['use_filename'] ?? false ? "{$filename}_{$hash}" : $hash;
        
        return $this->applyAffixes($generatedName, $extension, $options);
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
        
        do {
            $generatedName = $this->make($filename, $extension, $strategy, $options);
            $attempts++;
            
            if (!$this->storagePath || !$this->fileExists($generatedName)) {
                return $generatedName;
            }
            
            // Modify options to ensure different output on retry
            if ($strategy === 'numbered' || ($strategy === null && $this->strategy === 'numbered')) {
                $options['number'] = ($options['number'] ?? 1) + 1;
            } elseif ($strategy === 'random' || ($strategy === null && $this->strategy === 'random')) {
                $options['length'] = ($options['length'] ?? 16) + 2;
            }
            
        } while ($attempts < $this->maxUniqueAttempts);
        
        // Final attempt with UUID to ensure uniqueness
        return $this->uuid($filename, $extension, array_merge($options, ['suffix' => '_' . time()]));
    }

    /**
     * Apply prefix and suffix to filename.
     */
    protected function applyAffixes(string $filename, string $extension, array $options = []): string
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
     * Merge options with the default options.
     */
    protected function mergeOptions(array $options, string $strategy, array $defaultOptions): array
    {
        $strategyOptions = $defaultOptions[$strategy] ?? [];
        return array_merge($strategyOptions, $options);
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
     * Validate inputs.
     */
    protected function validateInputs(string $filename, string $extension, string $strategy, array $options): void
    {
        if (empty($filename)) {
            throw new InvalidArgumentException('Filename cannot be empty');
        }
        
        if (empty($extension)) {
            throw new InvalidArgumentException('Extension cannot be empty');
        }
        
        if (strlen($filename) > 255) {
            throw new InvalidArgumentException('Filename is too long (max 255 characters)');
        }
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
     * Validate number parameter.
     */
    protected function validateNumber(int $number): void
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Number must be non-negative');
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

    /**
     * Validate hash algorithm.
     */
    protected function validateHashAlgorithm(string $algorithm): void
    {
        if (self::$hashAlgorithms === null) {
            self::$hashAlgorithms = hash_algos();
        }
        
        if (!in_array($algorithm, self::$hashAlgorithms)) {
            throw new InvalidArgumentException("Invalid hash algorithm: {$algorithm}");
        }
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
     * Get DateTime instance (allows for mocking in tests).
     */
    protected function getDateTime(): DateTime
    {
        return new DateTime();
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
}