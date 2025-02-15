<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ApprovalTests\Approvals;
use ApprovalTests\Scrubber\CallbackScrubber;

class ApprovalsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $approvalsDir = __DIR__ . '/approvals';
        if (!is_dir($approvalsDir)) {
            mkdir($approvalsDir, 0777, true);
        }
    }

    public function testVerifyString(): void
    {
        Approvals::verify("Simple string test");
    }

    public function testVerifyArray(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
            'roles' => ['admin', 'user']
        ];
        Approvals::verify($data);
    }

    public function testVerifyHtmlWithComplexStructure(): void
    {
        $html = '
            <div class="container">
                <h1>Title</h1>
                <p>Some text with <b>bold</b> and <i>italic</i></p>
                <ul>
                    <li>Item 1</li>
                    <li>Item 2</li>
                </ul>
            </div>
        ';
        Approvals::verifyHtml($html);
    }

    public function testVerifyHtmlWithMalformedContent(): void
    {
        $html = '<div>Unclosed div <p>Unclosed paragraph <b>Bold';
        Approvals::verifyHtml($html);
    }

    public function testVerifyJsonWithPrettyPrint(): void
    {
        $json = '{"name":"John","age":30,"nested":{"key":"value"}}';
        Approvals::verifyJson($json);
    }

    public function testVerifyXmlWithAttributes(): void
    {
        $xml = '<?xml version="1.0"?>
            <root>
                <user id="1" type="admin">
                    <name>John</name>
                    <roles>
                        <role>admin</role>
                        <role>user</role>
                    </roles>
                </user>
            </root>';
        Approvals::verifyXml($xml);
    }

    public function testVerifyAllWithCustomFormatter(): void
    {
        $users = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];
        
        Approvals::verifyAll(
            $users,
            fn($user) => "Name: {$user['name']}, Age: {$user['age']}"
        );
    }

    public function testVerifyWithEnvironment(): void
    {
        Approvals::verifyWithEnvironment(
            "Content specific to Windows",
            "Windows_10_Pro"
        );
    }

    public function testVerifyWithExtensionAndScrubber(): void
    {
        $content = "Date: 2024-01-01\nID: 12345\nContent: Test";
        
        Approvals::verifyWithExtension(
            $content,
            "log",
            function($text) {
                // Remplacer la date et l'ID par des valeurs fixes
                $text = preg_replace('/Date: \d{4}-\d{2}-\d{2}/', 'Date: YYYY-MM-DD', $text);
                $text = preg_replace('/ID: \d+/', 'ID: XXXXX', $text);
                return $text;
            }
        );
    }

    public function testVerifyAllCombinations(): void
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

    public function testVerifyWithUnicode(): void
    {
        $text = "Unicode test: 你好, こんにちは, Привет, مرحبا, שָׁלוֹם";
        Approvals::verify($text);
    }

    public function testVerifyWithSpecialCharacters(): void
    {
        $text = 'Special chars: &<>"\'/' . "\n";
        Approvals::verify($text);
    }

    /**
     * @dataProvider provideTestData
     */
    public function testWithDataProvider(string $input, string $expected): void
    {
        $result = strtoupper($input);
        // On vérifie que le résultat correspond à l'attendu
        $this->assertEquals($expected, $result);
        // Puis on l'approuve
        Approvals::verify($result);
    }

    public function provideTestData(): array
    {
        return [
            'simple text' => ['hello', 'HELLO'],
            'with spaces' => ['hello world', 'HELLO WORLD'],
            'with numbers' => ['test123', 'TEST123'],
        ];
    }

    /**
     * @dataProvider provideJsonData
     */
    public function testJsonWithDataProvider(array $data, string $expectedJson): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        // On vérifie que le JSON est valide
        $this->assertJson($json);
        // On vérifie qu'il correspond à l'attendu après formatage
        $expectedFormatted = json_encode(json_decode($expectedJson), JSON_PRETTY_PRINT);
        $this->assertJsonStringEqualsJsonString($expectedFormatted, $json);
        // Puis on l'approuve
        Approvals::verifyJson($json);
    }

    public function provideJsonData(): array
    {
        return [
            'simple object' => [
                ['name' => 'John', 'age' => 30],
                json_encode(['name' => 'John', 'age' => 30], JSON_PRETTY_PRINT)
            ],
            'nested object' => [
                [
                    'user' => [
                        'name' => 'John',
                        'address' => [
                            'city' => 'Paris',
                            'country' => 'France'
                        ]
                    ]
                ],
                json_encode([
                    'user' => [
                        'name' => 'John',
                        'address' => [
                            'city' => 'Paris',
                            'country' => 'France'
                        ]
                    ]
                ], JSON_PRETTY_PRINT)
            ]
        ];
    }
} 