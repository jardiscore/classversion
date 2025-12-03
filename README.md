# Jardis ClassVersion
![Build Status](https://github.com/jardiscore/classversion/actions/workflows/ci.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-777BB4.svg)](https://www.php.net/)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)
[![PSR-4](https://img.shields.io/badge/autoload-PSR--4-blue.svg)](https://www.php-fig.org/psr/psr-4/)
[![PSR-12](https://img.shields.io/badge/code%20style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)
[![Coverage](https://img.shields.io/badge/coverage->95%25-brightgreen)](https://github.com/jardiscore/factory)

### ClassVersion enables loading classes with the same name from specific subdirectories associated with respective version labels.

## Features

- **Dynamic Class Loading**: Load different versions of a class from corresponding subdirectories
- **Proxy Support**: Register and use proxy objects for classes instead of loading from subdirectories
- **Flexible Configuration**: Map version labels to subdirectory structures
- **Type-Safe**: Full PHP 8.2+ type safety with generics support
- **PSR-4 Compatible**: Follows PSR-4 autoloading standards

## Installation

### Via Composer

```bash
composer require jardiscore/classversion
```

### Via GitHub

```bash
git clone https://github.com/jardiscore/classversion.git
cd classversion
composer install
```

## How It Works

The ClassVersion system provides two primary loading strategies:

1. **Subdirectory Loading** (`LoadClassFromSubDirectory`): Dynamically loads classes from version-specific subdirectories
2. **Proxy Loading** (`LoadClassFromProxy`): Uses pre-registered proxy objects for specific class versions

When you request a class with a version label, ClassVersion first checks for registered proxies. If no proxy exists, it attempts to load the class from the corresponding subdirectory.

## Basic Usage

### 1. Configure Version Mapping

```php
use JardisCore\ClassVersion\ClassVersion;
use JardisCore\ClassVersion\Data\ClassVersionConfig;
use JardisCore\ClassVersion\Reader\LoadClassFromSubDirectory;

// Map subdirectories to version labels
$versionConfig = new ClassVersionConfig([
    'v1' => ['version1', 'v1.0', 'stable'],
    'v2' => ['version2', 'v2.0', 'beta'],
    'v3' => ['version3', 'v3.0', 'experimental']
]);

$classFinder = new LoadClassFromSubDirectory();
$classVersion = new ClassVersion($versionConfig, $classFinder);
```

### 2. Load Classes by Version

```php
// Load default version (no subdirectory)
$defaultClass = $classVersion(YourClass::class);

// Load from v1 subdirectory (using any mapped label)
$class1 = $classVersion(YourClass::class, 'version1');
$class1Alt = $classVersion(YourClass::class, 'v1.0');  // Same result

// Load from v2 subdirectory
$class2 = $classVersion(YourClass::class, 'version2');
```

### 3. Directory Structure Example

```
src/
├── YourClass.php              # Default version
├── v1/
│   └── YourClass.php          # Version 1 implementation
├── v2/
│   └── YourClass.php          # Version 2 implementation
└── v3/
    └── YourClass.php          # Version 3 implementation
```

## Advanced Usage

### Using Proxy Objects

Proxies allow you to register pre-configured object instances for specific class versions:

```php
use JardisCore\ClassVersion\Reader\LoadClassFromProxy;

$proxyLoader = new LoadClassFromProxy($versionConfig);

// Register a proxy for a specific class and version
$customInstance = new YourClass('custom configuration');
$proxyLoader->addProxy(YourClass::class, $customInstance, 'version1');

// Use with ClassVersion
$classVersion = new ClassVersion($versionConfig, $classFinder, $proxyLoader);

// This will return the proxy instance instead of loading from subdirectory
$proxiedClass = $classVersion(YourClass::class, 'version1');

// Remove a proxy when no longer needed
$proxyLoader->removeProxy(YourClass::class, 'version1');
```

### Complete Example with Proxies

```php
use JardisCore\ClassVersion\ClassVersion;
use JardisCore\ClassVersion\Data\ClassVersionConfig;
use JardisCore\ClassVersion\Reader\LoadClassFromSubDirectory;
use JardisCore\ClassVersion\Reader\LoadClassFromProxy;

// Configuration
$versionConfig = new ClassVersionConfig([
    'legacy' => ['v1', 'old'],
    'current' => ['v2', 'stable'],
    'future' => ['v3', 'next']
]);

// Setup loaders
$classFinder = new LoadClassFromSubDirectory();
$proxyFinder = new LoadClassFromProxy($versionConfig);

// Register some proxies
$legacyService = new YourService('legacy-config');
$proxyFinder->addProxy(YourService::class, $legacyService, 'v1');

// Create ClassVersion instance
$classVersion = new ClassVersion($versionConfig, $classFinder, $proxyFinder);

// Usage
$default = $classVersion(YourService::class);           // Load from default location
$legacy = $classVersion(YourService::class, 'v1');      // Returns proxy
$current = $classVersion(YourService::class, 'stable'); // Load from 'current' subdirectory
$future = $classVersion(YourService::class, 'next');    // Load from 'future' subdirectory
```

## Use Cases

- **API Versioning**: Maintain multiple API versions side-by-side
- **Feature Flags**: Test experimental implementations alongside stable versions
- **Legacy Support**: Keep old implementations while rolling out new versions
- **A/B Testing**: Switch between different implementations dynamically
- **Domain-Driven Design**: Version domain models and services independently

## Configuration Details

### ClassVersionConfig

The configuration maps subdirectory names to version labels:

```php
$config = new ClassVersionConfig([
    'subdirectory_name' => ['label1', 'label2', 'label3'],
    'another_directory' => ['labelA', 'labelB']
]);
```

- **Keys**: Subdirectory names (where versioned classes are located)
- **Values**: Arrays of version labels that map to that subdirectory
- All version labels are trimmed and deduplicated automatically
- Invalid configurations throw `InvalidArgumentException`

## Development

### Requirements

- PHP >= 8.2
- Composer

### Setup

```bash
# Clone repository
git clone https://github.com/jardiscore/classversion.git
cd classversion

# Install dependencies
make install

# Run tests
make phpunit

# Run code style checks
make phpcs

# Run static analysis
make phpstan
```

### Available Make Commands

```bash
make               # Show all available commands
make install       # Install dependencies
make phpunit       # Run PHPUnit tests
make phpcs         # Run PHP CodeSniffer
make phpstan       # Run PHPStan analysis
make phpunit-coverage      # Run tests with coverage text output
make phpunit-coverage-html # Generate HTML coverage report
make phpunit-reports       # Generate clover and coverage-xml reports
make shell         # Open shell in Docker container
```

## Repository Structure

```
.
├── src/                        # Source code
│   ├── ClassVersion.php       # Main class version loader
│   ├── data/
│   │   └── ClassVersionConfig.php
│   └── reader/
│       ├── LoadClassFromSubDirectory.php
│       └── LoadClassFromProxy.php
├── tests/                      # Unit tests
├── support/                    # Docker and development tools
├── Makefile                    # Development commands
├── composer.json              # Dependencies and metadata
├── phpunit.xml                # PHPUnit configuration
├── phpstan.neon              # PHPStan configuration
└── phpcs.xml                  # Code style configuration
```

## Support

- **Issues**: [GitHub Issues](https://github.com/jardiscore/classversion/issues)
- **Email**: jardisCore@headgent.dev

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

Developed and maintained by Jardis Core Development Team.
