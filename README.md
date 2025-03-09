# PHP Approval Tests

A PHP library for approval testing. This approach allows you to verify complex results by comparing them with approved versions, making it ideal for testing outputs that are difficult to assert traditionally (e.g., HTML, JSON, XML, or binary files).

> [!WARNING]  
> This library is still in development. It is not recommended for production use. Many features are still missing, and the API may change.

## Table of Contents

- [Installation](#installation)
- [Basic Usage](#basic-usage)
    - [Simple Test](#simple-test)
    - [Structured Data Test](#structured-data-test)
- [Specialized Verifications](#specialized-verifications)
    - [HTML](#html)
    - [JSON](#json)
    - [XML](#xml)
    - [CSV](#csv)
    - [Binary Files](#binary-files)
- [Advanced Features](#advanced-features)
    - [Tests with Data Providers](#tests-with-data-providers)
    - [Verify All Combinations](#verify-all-combinations)
- [Configuration](#configuration)
    - [PHPUnit Bootstrap Configuration](#phpunit-bootstrap-configuration)
    - [Set a Custom Reporter](#set-a-custom-reporter)
    - [Use a Custom Object Formatter](#use-a-custom-object-formatter)
    - [Custom Namer](#custom-namer)
    - [Auto-Approve Snapshots](#auto-approve-snapshots)
- [Scrubbers](#scrubbers)
    - [JSON Scrubbing](#json-scrubbing)
        - [Ignore JSON Members](#ignore-json-members)
        - [Scrub JSON Members](#scrub-json-members)
    - [XML Scrubbing](#xml-scrubbing)
    - [Regex Scrubbing](#regex-scrubbing)
    - [Custom Scrubber](#custom-scrubber)
- [Maintenance](#maintenance)
    - [Cleanup Received Files](#cleanup-received-files)
    - [Detect Orphaned Files](#detect-orphaned-files)
- [Reporters](#reporters)
    - [CLI Reporter](#cli-reporter)
    - [Diff Reporter](#diff-reporter)
    - [Composite Reporter](#composite-reporter)
- [Symfony Integration](#symfony-integration)
- [Best Practices](#best-practices)
- [Contributing](#contributing)
- [License](#license)

## Installation

Install the library via Composer:

```php
composer require chqthomas/approval-tests
```

## Basic Usage

### Simple Test

Verify a simple string output:

```php
use ChqThomas\ApprovalTests\Approvals;

public function testSimpleString(): void 
{
    Approvals::verify("Hello World");
}
```

The first time this runs, it generates a `.received.txt` file. Approve it by renaming it to `.approved.txt` or use auto-approval (see below).

### Structured Data Test

Verify complex data structures like arrays or objects:

```php
public function testArray(): void 
{
    $data = [
        'name' => 'John Doe',
        'age' => 30,
        'roles' => ['admin', 'user']
    ];
    Approvals::verify($data);
}
```

## Specialized Verifications

The library supports specific formats with dedicated methods:

### HTML

Verify HTML content with automatic formatting:

```php
public function testHtml(): void 
{
    $html = '<div>Hello <span>World</span></div>';
    Approvals::verifyHtml($html);
}
```

### JSON

Verify JSON with pretty-printing and scrubbing:

```php
public function testJson(): void 
{
    $json = '{"name":"John","age":30}';
    Approvals::verifyJson($json); // Automatically formatted
}
```

### XML

Verify XML with formatting:

```php
public function testXml(): void 
{
    $xml = '<?xml version="1.0"?><root><user>John</user></root>';
    Approvals::verifyXml($xml);
}
```

### CSV

Verify CSV content:

```php
public function testCsv(): void 
{
    $csv = "name,age\nJohn,30\nJane,25";
    Approvals::verifyCsv($csv);
}
```

### Binary Files

Verify binary content (e.g., images):

```php
public function testBinaryFile(): void 
{
    Approvals::verifyBinaryFile('path/to/image.png', 'png');
}
```

## Advanced Features

### Tests with Data Providers

Use PHPUnit data providers for parameterized tests:

```php
/**
 * @dataProvider provideTestData
 */
public function testWithDataProvider(array $data, string $expected): void 
{
    $result = processData($data);
    Approvals::verify($result);
}

public static function provideTestData(): array
{
    return [
        'case1' => [['input' => 1], 'output1'],
        'case2' => [['input' => 2], 'output2'],
    ];
}
```

### Verify All Combinations

Test all combinations of inputs:

```php
public function testAllCombinations(): void 
{
    $operations = ['+', '-', '*', '/'];
    $numbers = [1, 2, 3];
    
    Approvals::verifyAllCombinations(
        function($op, $a, $b) {
            switch($op) {
                case '+': return $a + $b;
                case '-': return $a - $b;
                case '*': return $a * $b;
                case '/': return $b != 0 ? $a / $b : 'Division by zero';
            }
        },
        [$operations, $numbers, $numbers]
    );
}
```

## Configuration

Customize the library’s behavior via the `Configuration` class:

### PHPUnit Bootstrap Configuration

Create a `tests/bootstrap.php` file to configure the library globally for all your tests:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChqThomas\ApprovalTests\Configuration;
use ChqThomas\ApprovalTests\Reporter\DiffReporter;
use ChqThomas\ApprovalTests\Formatter\SymfonyObjectFormatter;

// Global configuration
Configuration::getInstance()
    ->setReporter(new DiffReporter())
    ->setObjectFormatter(new SymfonyObjectFormatter())
    ->setAutoApprove(false);

// Configure default scrubbers for specific formats
Configuration::getInstance()
    ->setDefaultScrubber('json', JsonScrubber::create()
        ->scrubMember('password', 'token')
        ->ignoreMember('sensitive_data'))
    ->setDefaultScrubber('xml', XmlScrubber::create()
        ->addScrubber(RegexScrubber::create([
            '/\d{4}-\d{2}-\d{2}/' => '[DATE]'
        ])));
```

Then reference it in your phpunit.xml:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php">
    <!-- ... -->
</phpunit>
```

### Set a Custom Reporter

Change how differences are reported:

```php
use ChqThomas\ApprovalTests\Configuration;
use ChqThomas\ApprovalTests\Reporter\DiffReporter;

Configuration::getInstance()->setReporter(new DiffReporter());
```

### Use a Custom Object Formatter

Switch between default and Symfony formatters:

```php
use ChqThomas\ApprovalTests\Formatter\SymfonyObjectFormatter;

Configuration::getInstance()->setObjectFormatter(new SymfonyObjectFormatter());
```

*Note*: Requires `symfony/serializer` to be installed for `SymfonyObjectFormatter`.

### Custom Namer

Set a custom namer for file naming:

```php
use ChqThomas\ApprovalTests\Namer\EnvironmentAwareNamer;

Configuration::getInstance()->setNamerClass(EnvironmentAwareNamer::class);
```

### Auto-Approve Snapshots

Automatically approve new or changed snapshots:

```php
Configuration::getInstance()->setAutoApprove(true);
```

Or use an environment variable:

```php
APPROVE_SNAPSHOTS=true vendor/bin/phpunit
```

## Scrubbers

Scrubbers normalize content before comparison, handling dynamic data like dates or IDs.

### JSON Scrubbing

Scrub sensitive or variable data:

```php
public function testJsonScrubbing(): void 
{
    $json = <<<JSON
{
    "user": "John",
    "password": "secret123",
    "timestamp": "2024-01-01T12:00:00",
    "id": "550e8400-e29b-41d4-a716-446655440000"
}
JSON;

    // Default scrubbers automatically handle:
    // - GUIDs (replaced with Guid_1, Guid_2, etc.)
    // - Dates (replaced with DateTimeOffset_1, etc.)
    Approvals::verifyJson($json);
}
```

#### Ignore JSON Members

Remove specific members:

```php
public function testJsonIgnoreMember(): void 
{
    $json = <<<JSON
{
    "user": "John",
    "sensitive": {
        "password": "secret123",
        "token": "abc123"
    }
}
JSON;

    Approvals::verifyJson($json, JsonScrubber::create()
        ->ignoreMember('sensitive')); // Member will be removed
}
```

#### Scrub JSON Members

Replace members with a placeholder:

```php
public function testJsonScrubMember(): void 
{
    $json = <<<JSON
{
    "user": "John",
    "password": "secret123",
    "api_key": "xyz789"
}
JSON;

    Approvals::verifyJson($json, JsonScrubber::create()
        ->scrubMember('password', 'api_key')); // Members will be replaced with "[scrubbed]"
}
```

### XML Scrubbing

Custom scrubbing for XML:

```php
public function testXmlScrubbing(): void 
{
    $xml = <<<XML
<?xml version="1.0"?>
<user>
    <name>John</name>
    <created>2024-01-01T12:00:00</created>
    <id>550e8400-e29b-41d4-a716-446655440000</id>
</user>
XML;

    // Custom scrubber for XML
    Approvals::verifyXml($xml, XmlScrubber::create()
        ->addScrubber(fn($content) => preg_replace('/John/', '[NAME]', $content)));
}
```

### Regex Scrubbing

Use regular expressions for generic scrubbing:

```php
public function testRegexScrubbing(): void 
{
    $json = <<<JSON
{
  "nodes": [
    {"id": "ABC123", "name": "Node1"},
    {"id": "DEF456", "name": "Node2"},
    {"id": "GHI789", "name": "Node3"}
  ]
}
JSON;

    Approvals::verifyJson($json, JsonScrubber::create()
        ->addScrubber(RegexScrubber::create(['/"id": "([A-Z]{3}\d{3})"/' => '"id": "MATCHED"'])));
}
```

### Custom Scrubber

Create a custom scrubber for any content:

```php
use ChqThomas\ApprovalTests\Scrubber\AbstractScrubber;

class MyScrubber extends AbstractScrubber
{
    public function scrub(string $content): string
    {
        // Apply base scrubbers first (GUIDs, dates)
        $content = $this->scrubGuids($content);
        $content = $this->scrubDates($content);
        
        // Add your custom rules
        $content = preg_replace('/secret-\d+/', '[SECRET]', $content);
        
        // Apply additional scrubbers
        return $this->applyAdditionalScrubbers($content);
    }
}

// Usage
public function testWithCustomScrubber(): void 
{
    $content = "ID: secret-123\nDate: 2024-01-01";
    
    Approvals::verifyWithExtension(
        $content,
        "txt",
        MyScrubber::create()
            ->addScrubber(fn($text) => str_replace('ID:', 'Reference:', $text))
    );
}
```

## Maintenance

### Cleanup Received Files

Remove redundant `.received` files:

```php
use ChqThomas\ApprovalTests\ApprovalMaintenance;

ApprovalMaintenance::cleanUpReceivedFiles(__DIR__ . '/tests/approvals');
```

### Detect Orphaned Files

Find `.approved` files without associated tests:

```php
$orphanedFiles = ApprovalMaintenance::findOrphanedApprovedFiles(__DIR__ . '/tests');
```

## Reporters

Customize how differences are reported:

### CLI Reporter

Default reporter for terminal output:

```php
use ChqThomas\ApprovalTests\Reporter\CliReporter;

Configuration::getInstance()->setReporter(new CliReporter());
```

### Diff Reporter

Show differences using a diff format:

```php
use ChqThomas\ApprovalTests\Reporter\DiffReporter;

Configuration::getInstance()->setReporter(new DiffReporter());
```

### Composite Reporter

Combine multiple reporters:

```php
use ChqThomas\ApprovalTests\Reporter\CompositeReporter;

$reporter = new CompositeReporter([
    new CliReporter(),
    new DiffReporter()
]);
Configuration::getInstance()->setReporter($reporter);
```

## Symfony Integration

Use with Symfony’s DomCrawler for web testing:

```php
use ChqThomas\ApprovalTests\Symfony\ApprovalCrawlerAssertionsTrait;

class MyWebTest extends WebTestCase
{
use ApprovalCrawlerAssertionsTrait;

    public function testPageContent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::verifySelectorHtml('#main-content');
    }
}
```

## Best Practices

1. Store `.approved` files in version control.
2. Use scrubbers for variable data (e.g., dates, IDs).
3. Regularly clean up `.received` files.
4. Check for orphaned `.approved` files.
5. Use descriptive test names for clear file naming.

## Contributing

Contributions are welcome! To contribute:
1. Fork the project.
2. Create a feature branch.
3. Submit a pull request.

## License

MIT License