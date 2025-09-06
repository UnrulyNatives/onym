<p align="center">
    <img src="./.github/assets/icon.png" alt="Onym Icon" width="150" height="150"/>
    <p align="center">
        <a href="https://github.com/Blaspsoft/blasp/actions/workflows/main.yml"><img alt="GitHub Workflow Status (main)" src="https://github.com/Blaspsoft/onym/actions/workflows/main.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/blaspsoft/onym"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="Latest Version" src="https://img.shields.io/packagist/v/blaspsoft/onym"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="License" src="https://img.shields.io/packagist/l/blaspsoft/onym"></a>
    </p>
</p>

# Onym v2 - Advanced Filename Generator

A powerful Laravel package for generating secure, unique, and structured filenames using various strategies with extensive validation and collision detection.

## 🚀 Features

- ✅ **Advanced Filename Generation** – Generate filenames using multiple strategies with comprehensive options
- 🎲 **Multiple Strategies** – Supports `random`, `uuid`, `timestamp`, `date`, `numbered`, `slug`, and `hash`
- 🔒 **Security-First** – Built-in protection against path traversal attacks and malicious input
- 🎯 **Laravel-Friendly** – Seamless integration with Laravel's ecosystem
- 📂 **Collision Detection** – Automatic uniqueness checking with configurable storage paths
- ⚙️ **Highly Configurable** – Extensive configuration options for each strategy
- 🔧 **Method Consistency** – Standardized method signatures across all strategies
- 🧪 **Thoroughly Tested** – Comprehensive test suite with 29 test cases
- 🛡️ **Input Validation** – Robust validation for all parameters and edge cases
- ⚡ **Performance Optimized** – Efficient caching and optimized algorithms

## 📋 What's New in v2

### 🔧 Breaking Changes
- **Standardized method signatures** - All strategy methods now use consistent parameter order
- **Updated `make()` method** - Parameter order changed to `filename, extension, strategy, options`
- **Enhanced validation** - Stricter input validation with proper error handling

### ✨ New Features
- **`unique()` method** - Generate unique filenames with collision detection
- **Enhanced security** - Path traversal prevention and input sanitization
- **Advanced options** - New options like `use_filename`, `prepend_timestamp`, `pad_length`
- **Storage path configuration** - Configure paths for collision detection
- **Return type declarations** - Full PHP 8+ type safety
- **Performance improvements** - Cached hash algorithms and optimized DateTime handling

## Installation

```bash
composer require blaspsoft/onym
```

Publish the config file:

```bash
php artisan vendor:publish --tag="onym-config"
```

## Basic Usage

### Quick Start

```php
use Blaspsoft\Onym\Facades\Onym;

// Generate a random filename
$filename = Onym::random('document', 'pdf');
// Result: "a1b2c3d4e5f6g7h8.pdf"

// Generate with timestamp
$filename = Onym::timestamp('report', 'xlsx');
// Result: "report_2024-03-15_14-30-00.xlsx"

// Generate unique filename (collision detection)
$filename = Onym::unique('document', 'pdf', 'uuid');
// Result: "550e8400-e29b-41d4-a716-446655440000.pdf"
```

### Universal `make()` Method

```php
// Using the make() method with different strategies
$filename = Onym::make('document', 'pdf', 'random', ['length' => 12]);
$filename = Onym::make('report', 'xlsx', 'timestamp', ['format' => 'Y-m-d_H-i-s']);
$filename = Onym::make('My Document', 'txt', 'slug', ['separator' => '_']);
```

## Available Strategies

### Random Strategy

Generates cryptographically secure random strings.

**Options:**
- `length` (int): Length of random string (1-255, default: 16)
- `use_filename` (bool): Include original filename (default: false)
- `prefix` (string): String to prepend
- `suffix` (string): String to append

```php
// Basic random filename
Onym::random('document', 'pdf');
// Result: "a1b2c3d4e5f6g7h8.pdf"

// With original filename included
Onym::random('document', 'pdf', ['use_filename' => true, 'length' => 8]);
// Result: "document_a1b2c3d4.pdf"

// With prefix and suffix
Onym::random('temp', 'txt', [
    'length' => 10,
    'prefix' => 'tmp_',
    'suffix' => '_draft'
]);
// Result: "tmp_a1b2c3d4e5_draft.txt"
```

### UUID Strategy

Generates RFC 4122 compliant UUID v4 identifiers.

**Options:**
- `use_filename` (bool): Include original filename (default: false)
- `prefix` (string): String to prepend
- `suffix` (string): String to append

```php
// Pure UUID filename
Onym::uuid('document', 'pdf');
// Result: "550e8400-e29b-41d4-a716-446655440000.pdf"

// With original filename
Onym::uuid('backup', 'sql', ['use_filename' => true]);
// Result: "backup_550e8400-e29b-41d4-a716-446655440000.sql"
```

### Timestamp Strategy

Adds timestamps to filenames with customizable formats.

**Options:**
- `format` (string): PHP DateTime format (default: 'Y-m-d_H-i-s')
- `prepend_timestamp` (bool): Put timestamp before filename (default: false)
- `prefix` (string): String to prepend
- `suffix` (string): String to append

```php
// Standard timestamp
Onym::timestamp('log', 'txt');
// Result: "log_2024-03-15_14-30-00.txt"

// Prepended timestamp
Onym::timestamp('backup', 'sql', ['prepend_timestamp' => true]);
// Result: "2024-03-15_14-30-00_backup.sql"

// Custom format
Onym::timestamp('report', 'pdf', ['format' => 'YmdHis']);
// Result: "report_20240315143000.pdf"
```

### Date Strategy

Similar to timestamp but focused on date-only formats.

**Options:**
- `format` (string): PHP DateTime format (default: 'Y-m-d')
- `prepend_date` (bool): Put date before filename (default: false)
- `prefix` (string): String to prepend
- `suffix` (string): String to append

```php
// Standard date
Onym::date('report', 'xlsx');
// Result: "report_2024-03-15.xlsx"

// Prepended date with custom format
Onym::date('daily', 'log', [
    'format' => 'Ymd',
    'prepend_date' => true
]);
// Result: "20240315_daily.log"
```

### Numbered Strategy

Adds sequential numbers to filenames with optional padding.

**Options:**
- `number` (int): Starting number (default: 1)
- `separator` (string): Separator character (default: '_')
- `pad_length` (int): Zero-pad to length (0 = no padding, default: 0)
- `prefix` (string): String to prepend
- `suffix` (string): String to append

```php
// Basic numbered
Onym::numbered('document', 'pdf', ['number' => 5]);
// Result: "document_5.pdf"

// With zero padding
Onym::numbered('file', 'txt', ['number' => 7, 'pad_length' => 3]);
// Result: "file_007.txt"

// Custom separator
Onym::numbered('image', 'jpg', ['number' => 42, 'separator' => '-']);
// Result: "image-42.jpg"
```

### Slug Strategy

Converts filenames to URL-friendly slugs.

**Options:**
- `separator` (string): Separator character (default: '-')
- `prefix` (string): String to prepend
- `suffix` (string): String to append

```php
// Basic slug
Onym::slug('My Document Name', 'pdf');
// Result: "my-document-name.pdf"

// With underscore separator
Onym::slug('Product Catalog 2024', 'xlsx', ['separator' => '_']);
// Result: "product_catalog_2024.xlsx"
```

### Hash Strategy

Generates hashes from filenames with multiple algorithm support.

**Options:**
- `algorithm` (string): Hash algorithm (default: 'md5')
  - Supported: md5, sha1, sha256, sha512, and all PHP `hash_algos()`
- `length` (int): Truncate hash to length (null = full hash)
- `use_filename` (bool): Include original filename (default: false)
- `include_timestamp` (bool): Add timestamp for uniqueness (default: false)
- `prefix` (string): String to prepend
- `suffix` (string): String to append

```php
// Basic MD5 hash
Onym::hash('document', 'pdf');
// Result: "86985e105f79b95d6bc918fb45ec7727.pdf"

// SHA256 with length limit
Onym::hash('secure', 'txt', ['algorithm' => 'sha256', 'length' => 16]);
// Result: "2c26b46b68ffc68f.txt"

// With filename and timestamp for uniqueness
Onym::hash('data', 'json', [
    'use_filename' => true,
    'include_timestamp' => true
]);
// Result: "data_1a2b3c4d5e6f7g8h.json"
```

## Collision Detection & Uniqueness

### The `unique()` Method

Generate filenames guaranteed to be unique within a specified directory.

```php
// Configure storage path for collision detection
Onym::setStoragePath('/path/to/uploads');

// Generate unique filename
$filename = Onym::unique('document', 'pdf', 'timestamp');
// If "document_2024-03-15_14-30-00.pdf" exists, 
// it will generate a different one

// With custom retry strategy
$filename = Onym::unique('file', 'txt', 'numbered', ['number' => 1]);
// Will increment number until unique: file_1.txt, file_2.txt, etc.
```

### Configuration

```php
// In config/onym.php
return [
    'storage_path' => '/path/to/check/for/collisions',
    'max_unique_attempts' => 10, // Max attempts before fallback to UUID
    // ... other config
];
```

## Security Features

### Path Traversal Prevention

```php
// Malicious input is automatically sanitized
$filename = Onym::make('../../../etc/passwd', 'txt');
// Result: "etcpasswd_a1b2c3d4.txt" (sanitized)

$filename = Onym::make('..\\..\\windows\\system32', 'exe');
// Result: "windowssystem32_a1b2c3d4.exe" (sanitized)
```

### Input Validation

```php
// Length validation
try {
    Onym::random('test', 'txt', ['length' => 0]);
} catch (InvalidArgumentException $e) {
    // "Length must be between 1 and 255"
}

// Hash algorithm validation
try {
    Onym::hash('test', 'txt', ['algorithm' => 'invalid']);
} catch (InvalidArgumentException $e) {
    // "Invalid hash algorithm: invalid"
}
```

## Advanced Configuration

### Complete Configuration Example

```php
// config/onym.php
return [
    'default_filename' => 'file',
    'default_extension' => 'txt',
    'strategy' => 'random',
    'storage_path' => storage_path('app/uploads'),
    'max_unique_attempts' => 15,
    
    'options' => [
        'random' => [
            'length' => 20,
            'use_filename' => false,
            'prefix' => 'rnd_',
            'suffix' => '',
        ],
        
        'uuid' => [
            'use_filename' => true,
            'prefix' => '',
            'suffix' => '_uuid',
        ],
        
        'timestamp' => [
            'format' => 'Y-m-d_H-i-s-u', // Include microseconds
            'prepend_timestamp' => false,
            'prefix' => 'ts_',
            'suffix' => '',
        ],
        
        'date' => [
            'format' => 'Y/m/d',
            'prepend_date' => false,
            'prefix' => '',
            'suffix' => '_daily',
        ],
        
        'numbered' => [
            'number' => 1000,
            'separator' => '-',
            'pad_length' => 5,
            'prefix' => 'doc_',
            'suffix' => '',
        ],
        
        'slug' => [
            'separator' => '_',
            'prefix' => 'slug_',
            'suffix' => '_clean',
        ],
        
        'hash' => [
            'algorithm' => 'sha256',
            'length' => 32,
            'use_filename' => true,
            'include_timestamp' => true,
            'prefix' => 'hash_',
            'suffix' => '_secure',
        ],
    ],
];
```

### Runtime Configuration

```php
// Set storage path for collision detection
Onym::setStoragePath('/custom/path');

// Set maximum unique attempts
Onym::setMaxUniqueAttempts(20);

// Chain configuration
$filename = Onym::setStoragePath('/uploads')
                 ->setMaxUniqueAttempts(5)
                 ->unique('document', 'pdf');
```

## Error Handling

```php
try {
    $filename = Onym::hash('test', 'txt', ['algorithm' => 'nonexistent']);
} catch (InvalidArgumentException $e) {
    echo "Hash algorithm error: " . $e->getMessage();
}

try {
    $filename = Onym::timestamp('test', 'txt', ['format' => '@#$%']);
} catch (InvalidArgumentException $e) {
    echo "Date format error: " . $e->getMessage();
}

try {
    $filename = Onym::random('test', 'txt', ['length' => -1]);
} catch (InvalidArgumentException $e) {
    echo "Length validation error: " . $e->getMessage();
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Changelog

### v2.0.0 - Major Refactor
- **Breaking**: Standardized all method signatures
- **Breaking**: Updated `make()` parameter order
- **New**: Added `unique()` method with collision detection  
- **New**: Enhanced security with path traversal prevention
- **New**: Comprehensive input validation
- **New**: Advanced configuration options
- **New**: Performance optimizations
- **Improved**: 100% test coverage with 29 test cases
- **Improved**: Full PHP 8+ type declarations

### v1.x - Legacy
- Basic filename generation strategies
- Simple configuration options
- Limited validation

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Add tests for your changes
4. Ensure all tests pass (`composer test`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)  
7. Open a Pull Request

## License

Onym is open-sourced software licensed under the [MIT license](LICENSE.md).

---

**Note**: This is version 2.0 with breaking changes from v1.x. Please review the upgrade guide in the documentation before upgrading existing installations.