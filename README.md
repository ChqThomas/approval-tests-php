# PHP Approval Tests

A PHP library for approval testing. This approach allows you to verify complex results by comparing them with approved versions.

## Installation

```bash
composer require approval-tests/approval-tests
```

## Basic Usage

### Simple Test
```php
use ApprovalTests\Approvals;

public function testSimpleString(): void 
{
    Approvals::verify("Hello World");
}
```

### Structured Data Test
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

### HTML
```php
public function testHtml(): void 
{
    $html = '<div>Hello <span>World</span></div>';
    Approvals::verifyHtml($html);
}
```

### JSON
```php
public function testJson(): void 
{
    $json = '{"name":"John","age":30}';
    Approvals::verifyJson($json); // Automatically formatted
}
```

### XML
```php
public function testXml(): void 
{
    $xml = '<?xml version="1.0"?><root><user>John</user></root>';
    Approvals::verifyXml($xml);
}
```

### CSV
```php
public function testCsv(): void 
{
    $csv = "name,age\nJohn,30\nJane,25";
    Approvals::verifyCsv($csv);
}
```

### Binary Files
```php
public function testBinaryFile(): void 
{
    Approvals::verifyBinaryFile('path/to/image.png', 'png');
}
```

## Advanced Features

### Tests with Data Providers
```php
/**
 * @dataProvider provideTestData
 */
public function testWithDataProvider(array $data, string $expected): void 
{
    $result = processData($data);
    Approvals::verify($result);
}
```

### Verify All Combinations
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

### Environment-Specific Tests
```php
public function testEnvironmentSpecific(): void 
{
    Approvals::verifyWithEnvironment(
        "Windows-specific content",
        "Windows_10_Pro"
    );
}
```

## Scrubbers

Scrubbers allow you to normalize content before comparison.

### JSON Scrubbing

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

### Generic Custom Scrubber

For any type of content, you can create a custom scrubber:

```php
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

### Auto-accepting Snapshots

To automatically accept new snapshots or changes:

```bash
APPROVE_SNAPSHOTS=true vendor/bin/phpunit
```

## Maintenance

### Cleanup Received Files
```php
use ApprovalTests\ApprovalMaintenance;

// Delete .received files that match .approved files
ApprovalMaintenance::cleanUpReceivedFiles(__DIR__ . '/tests/approvals');
```

### Detect Orphaned Files
```php
// Find .approved files without associated tests
$orphanedFiles = ApprovalMaintenance::findOrphanedApprovedFiles(__DIR__ . '/tests');
```

## Reporters

Reporters define how differences are reported.

### CLI Reporter
```php
use ApprovalTests\Reporter\CliReporter;

// Default configuration
Configuration::getInstance()->setReporter(new CliReporter());
```

### Diff Reporter
```php
use ApprovalTests\Reporter\DiffReporter;

// Show differences using diff
Configuration::getInstance()->setReporter(new DiffReporter());
```

### Composite Reporter
```php
use ApprovalTests\Reporter\CompositeReporter;

// Combine multiple reporters
$reporter = new CompositeReporter([
    new CliReporter(),
    new DiffReporter()
]);
Configuration::getInstance()->setReporter($reporter);
```

## Best Practices

1. Store approved files in version control
2. Use scrubbers for variable data (dates, IDs, etc.)
3. Regularly clean up received files
4. Check for orphaned approved files
5. Use descriptive test names

## Contributing

Contributions are welcome! Feel free to:
1. Fork the project
2. Create a feature branch
3. Submit a pull request

## License

MIT License

## Regex Scrubbing

`RegexScrubber` allows you to normalize content using regular expressions before comparison. This is particularly useful for replacing values that may change, such as identifiers or names.

### Example of Regex Scrubbing

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

### Example of Multiple Regex Scrubbing

```php
public function testMultipleRegexScrubbing(): void 
{
    $json = <<<JSON
{
  "users": [
    {"username": "user123", "fullName": "John Doe"},
    {"username": "user456", "fullName": "Jane Smith"}
  ]
}
JSON;

    Approvals::verifyJson($json, JsonScrubber::create()
        ->addScrubber(RegexScrubber::create([
            '/user\d{3}/' => 'userXXX',
            '/[A-Z][a-z]+ [A-Z][a-z]+/' => 'PERSON_NAME'
        ])));
}
``` 