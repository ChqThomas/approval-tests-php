# PHP Approval Tests

Une librairie PHP pour les tests d'approbation (approval testing). Cette approche permet de vérifier des résultats complexes en les comparant avec des versions approuvées.

## Installation

```bash
composer require approval-tests/approval-tests
```

## Utilisation de base

### Test simple
```php
use ApprovalTests\Approvals;

public function testSimpleString(): void 
{
    Approvals::verify("Hello World");
}
```

### Test avec données structurées
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

## Types de vérifications spécialisées

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
    Approvals::verifyJson($json); // Formaté automatiquement
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

### Fichiers binaires
```php
public function testBinaryFile(): void 
{
    Approvals::verifyBinaryFile('path/to/image.png', 'png');
}
```

## Fonctionnalités avancées

### Tests avec data providers
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

### Vérification de toutes les combinaisons
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

### Tests spécifiques à l'environnement
```php
public function testEnvironmentSpecific(): void 
{
    Approvals::verifyWithEnvironment(
        "Contenu spécifique à Windows",
        "Windows_10_Pro"
    );
}
```

## Scrubbers (Nettoyeurs)

Les scrubbers permettent de normaliser le contenu avant la comparaison.

### DateScrubber
```php
public function testWithDates(): void 
{
    $content = "Date: 2024-01-01\nID: 12345";
    Approvals::verifyWithExtension(
        $content,
        "log",
        new DateScrubber()
    );
}
```

### Scrubber personnalisé
```php
public function testWithCustomScrubber(): void 
{
    Approvals::verifyWithExtension(
        $content,
        "txt",
        function($text) {
            return preg_replace('/ID: \d+/', 'ID: XXXXX', $text);
        }
    );
}
```

## Maintenance

### Nettoyage des fichiers received
```php
use ApprovalTests\ApprovalMaintenance;

// Supprime les fichiers .received qui correspondent aux .approved
ApprovalMaintenance::cleanUpReceivedFiles(__DIR__ . '/tests/approvals');
```

### Détection des fichiers orphelins
```php
// Trouve les fichiers .approved sans test associé
$orphanedFiles = ApprovalMaintenance::findOrphanedApprovedFiles(__DIR__ . '/tests');
```

## Reporters

Les reporters définissent comment les différences sont rapportées.

### Reporter CLI
```php
use ApprovalTests\Reporter\CliReporter;

// Configuration par défaut
Configuration::getInstance()->setReporter(new CliReporter());
```

### Reporter de différences
```php
use ApprovalTests\Reporter\DiffReporter;

// Affiche les différences en utilisant diff
Configuration::getInstance()->setReporter(new DiffReporter());
```

### Reporter composite
```php
use ApprovalTests\Reporter\CompositeReporter;

// Combine plusieurs reporters
$reporter = new CompositeReporter([
    new CliReporter(),
    new DiffReporter()
]);
Configuration::getInstance()->setReporter($reporter);
```

## Bonnes pratiques

1. Stockez les fichiers approved dans le contrôle de version
2. Utilisez des scrubbers pour les données variables (dates, IDs, etc.)
3. Nettoyez régulièrement les fichiers received
4. Vérifiez les fichiers approved orphelins
5. Utilisez des noms de tests descriptifs

## Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Forker le projet
2. Créer une branche pour votre fonctionnalité
3. Soumettre une pull request

## Licence

MIT License 