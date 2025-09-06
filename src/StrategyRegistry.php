<?php

namespace Blaspsoft\Onym;

use Blaspsoft\Onym\Contracts\Strategy;
use InvalidArgumentException;

class StrategyRegistry
{
    /**
     * Registered strategies.
     *
     * @var array<string, Strategy>
     */
    protected array $strategies = [];

    /**
     * Default strategy name.
     */
    protected string $defaultStrategy;

    public function __construct()
    {
        $this->defaultStrategy = config('onym.strategy', 'random');
    }

    /**
     * Register a strategy.
     *
     * @param Strategy $strategy
     * @param string|null $name Optional name override
     * @return self
     */
    public function register(Strategy $strategy, ?string $name = null): self
    {
        $name = $name ?? $strategy->getName();
        $this->strategies[$name] = $strategy;
        
        return $this;
    }

    /**
     * Register multiple strategies at once.
     *
     * @param array<Strategy> $strategies
     * @return self
     */
    public function registerMany(array $strategies): self
    {
        foreach ($strategies as $strategy) {
            $this->register($strategy);
        }
        
        return $this;
    }

    /**
     * Get a strategy by name.
     *
     * @param string|null $name
     * @return Strategy
     * @throws InvalidArgumentException
     */
    public function get(?string $name = null): Strategy
    {
        $name = $name ?? $this->defaultStrategy;
        
        if (!isset($this->strategies[$name])) {
            throw new InvalidArgumentException("Unknown strategy: {$name}");
        }
        
        return $this->strategies[$name];
    }

    /**
     * Check if a strategy is registered.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->strategies[$name]);
    }

    /**
     * Get all registered strategies.
     *
     * @return array<string, Strategy>
     */
    public function all(): array
    {
        return $this->strategies;
    }

    /**
     * Get all registered strategy names.
     *
     * @return array<string>
     */
    public function names(): array
    {
        return array_keys($this->strategies);
    }

    /**
     * Set the default strategy.
     *
     * @param string $name
     * @return self
     * @throws InvalidArgumentException
     */
    public function setDefault(string $name): self
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("Cannot set default to unknown strategy: {$name}");
        }
        
        $this->defaultStrategy = $name;
        
        return $this;
    }

    /**
     * Get the default strategy name.
     *
     * @return string
     */
    public function getDefault(): string
    {
        return $this->defaultStrategy;
    }

    /**
     * Remove a strategy from the registry.
     *
     * @param string $name
     * @return self
     */
    public function remove(string $name): self
    {
        unset($this->strategies[$name]);
        
        return $this;
    }

    /**
     * Clear all registered strategies.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->strategies = [];
        
        return $this;
    }

    /**
     * Create and register a custom strategy on the fly.
     *
     * @param string $name
     * @param callable $generator
     * @return self
     */
    public function extend(string $name, callable $generator): self
    {
        $strategy = new class($name, $generator) extends \Blaspsoft\Onym\Strategies\AbstractStrategy {
            private string $customName;
            private $customGenerator;
            
            public function __construct(string $name, callable $generator)
            {
                $this->customName = $name;
                $this->customGenerator = $generator;
                parent::__construct();
            }
            
            public function getName(): string
            {
                return $this->customName;
            }
            
            public function generate(?string $filename, ?string $extension, array $options = []): string
            {
                [$filename, $extension] = $this->prepare($filename, $extension);
                $options = $this->mergeOptions($options);
                
                $result = call_user_func($this->customGenerator, $filename, $extension, $options);
                
                return $this->applyAffixes($result, $extension, $options);
            }
        };
        
        return $this->register($strategy, $name);
    }
}