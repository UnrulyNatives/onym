<?php

namespace Blaspsoft\Onym\Strategies;

use InvalidArgumentException;

class HashStrategy extends AbstractStrategy
{
    /**
     * Cached hash algorithms for performance.
     */
    protected static ?array $hashAlgorithms = null;

    /**
     * Get the strategy name.
     */
    public function getName(): string
    {
        return 'hash';
    }

    /**
     * Generate a hash filename.
     */
    public function generate(?string $filename, ?string $extension, array $options = []): string
    {
        [$filename, $extension] = $this->prepare($filename, $extension);
        $options = $this->mergeOptions($options);
        
        $this->validateOptions($options);
        
        $algorithm = $options['algorithm'] ?? 'md5';
        $length = $options['length'] ?? null;
        
        $hashInput = $options['include_timestamp'] ?? false 
            ? $filename . microtime(true) 
            : $filename;
            
        $hash = hash($algorithm, $hashInput);
        
        if ($length !== null && $length > 0) {
            $this->validateLength($length, 1, strlen($hash));
            $hash = substr($hash, 0, $length);
        }
        
        $generatedName = $options['use_filename'] ?? false 
            ? "{$filename}_{$hash}" 
            : $hash;
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }

    /**
     * Validate strategy-specific options.
     */
    public function validateOptions(array $options): void
    {
        if (isset($options['algorithm'])) {
            $this->validateHashAlgorithm($options['algorithm']);
        }
        
        if (isset($options['length'])) {
            // Maximum length will be validated after hash is generated
            if ($options['length'] < 1) {
                throw new InvalidArgumentException('Hash length must be at least 1');
            }
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
}