# Upgrade Guide

## From v2.x to v3.0 - Registry Pattern Refactor

### Overview

Version 3.0 introduces a major architectural change using the Registry pattern with separate strategy classes. This provides better separation of concerns, improved maintainability, and the ability to easily add custom strategies.

### Key Changes

#### 1. Architecture Changes

**Old (v2.x)**: All strategies were implemented as methods in a single `Onym` class.

**New (v3.0)**: Each strategy is now a separate class implementing the `Strategy` interface, managed by a `StrategyRegistry`.

```
src/
├── Contracts/
│   └── Strategy.php           # Strategy interface
├── Strategies/
│   ├── AbstractStrategy.php   # Base strategy class
│   ├── RandomStrategy.php     # Individual strategy implementations
│   ├── UuidStrategy.php
│   ├── TimestampStrategy.php
│   ├── DateStrategy.php
│   ├── NumberedStrategy.php
│   ├── SlugStrategy.php
│   └── HashStrategy.php
├── StrategyRegistry.php       # Registry for managing strategies
└── Onym.php                   # Main facade class
```

#### 2. API Compatibility

The public API remains **100% backward compatible**. All existing code will continue to work without changes:

```php
// These all work the same as before
Onym::random('document', 'pdf');
Onym::uuid('backup', 'sql');
Onym::timestamp('log', 'txt');
Onym::make('file', 'txt', 'slug');
```

### New Features in v3.0

#### 1. Custom Strategy Registration

You can now easily add custom strategies at runtime:

```php
// Register a custom strategy
Onym::extend('reverse', function($filename, $extension, $options) {
    return strrev($filename);
});

// Use it like any built-in strategy
$filename = Onym::reverse('document', 'pdf');
// Result: "tnemucod.pdf"

// Or call it dynamically
$filename = Onym::make('document', 'pdf', 'reverse');
```

#### 2. Strategy Management

```php
// Check if a strategy exists
if (Onym::hasStrategy('custom')) {
    // Use the strategy
}

// Get all registered strategies
$strategies = Onym::getStrategies();
// Returns: ['random', 'uuid', 'timestamp', 'date', 'numbered', 'slug', 'hash', 'custom']

// Access the registry directly for advanced use cases
$registry = Onym::getRegistry();
$registry->remove('unwanted_strategy');
$registry->setDefault('uuid');
```

#### 3. Creating Custom Strategy Classes

For more complex strategies, you can create a full strategy class:

```php
namespace App\Strategies;

use Blaspsoft\Onym\Strategies\AbstractStrategy;

class SequentialStrategy extends AbstractStrategy
{
    private static int $counter = 1;
    
    public function getName(): string
    {
        return 'sequential';
    }
    
    public function generate(?string $filename, ?string $extension, array $options = []): string
    {
        [$filename, $extension] = $this->prepare($filename, $extension);
        $options = $this->mergeOptions($options);
        
        $number = self::$counter++;
        $generatedName = "{$filename}_{$number}";
        
        return $this->applyAffixes($generatedName, $extension, $options);
    }
}
```

Register it in your service provider:

```php
use App\Strategies\SequentialStrategy;

public function boot()
{
    $registry = app(StrategyRegistry::class);
    $registry->register(new SequentialStrategy());
}
```

### Migration Guide

#### For Basic Users

**No action required!** Your existing code will continue to work without any changes.

#### For Advanced Users

If you've extended or modified the Onym class directly, you'll need to update your customizations:

**Old way (v2.x)** - Extending the Onym class:
```php
class CustomOnym extends Onym
{
    public function customMethod($filename, $extension, $options)
    {
        // Custom implementation
    }
}
```

**New way (v3.0)** - Creating a strategy:
```php
// Option 1: Quick custom strategy
Onym::extend('custom', function($filename, $extension, $options) {
    // Custom implementation
});

// Option 2: Full strategy class (see above)
```

#### For Package Maintainers

If you're maintaining a package that depends on Onym:

1. Update your composer.json to require v3.0:
```json
{
    "require": {
        "blaspsoft/onym": "^3.0"
    }
}
```

2. If you're accessing internal methods, update to use the registry:
```php
// Old
$onym = new Onym();
$onym->someInternalMethod();

// New
$registry = app(StrategyRegistry::class);
$strategy = $registry->get('strategyName');
```

### Benefits of the New Architecture

1. **Better Separation of Concerns**: Each strategy is isolated in its own class
2. **Easier Testing**: Strategies can be tested independently
3. **Improved Extensibility**: Add new strategies without modifying core code
4. **Runtime Flexibility**: Register/unregister strategies dynamically
5. **Cleaner Codebase**: Reduced complexity in the main Onym class
6. **Type Safety**: Full PHP 8+ type declarations throughout

### Performance Considerations

The new architecture has minimal performance impact:
- Strategy objects are created once and reused
- The registry uses efficient array lookups
- Cached hash algorithms for the hash strategy
- No additional overhead for standard operations

### Getting Help

If you encounter any issues during the upgrade:

1. Check the [examples](examples/) directory for usage patterns
2. Review the [test suite](tests/) for implementation details
3. Open an issue on [GitHub](https://github.com/blaspsoft/onym/issues)
4. Refer to the [API documentation](docs/)

### Deprecations

No methods have been deprecated in v3.0. All v2.x methods continue to work.

### Future Considerations

In future versions (v4.0+), we may:
- Add async strategy support
- Implement strategy pipelines
- Add built-in cloud storage integration
- Provide strategy performance metrics

These changes will maintain backward compatibility where possible.