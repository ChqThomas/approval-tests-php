<?php

namespace ChqThomas\ApprovalTests\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\TestCase;
use ChqThomas\ApprovalTests\Approvals;
use ChqThomas\ApprovalTests\Scrubber\CallbackScrubber;

class ApprovalsTest extends TestCase
{
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
            fn ($user) => "Name: {$user['name']}, Age: {$user['age']}"
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
            function ($text) {
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
            function ($op, $a, $b) {
                switch ($op) {
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
        Approvals::verify("Unicode test: 你好, こんにちは, Привет, مرحبا, שָׁלוֹם");
    }

    public function testVerifyWithSpecialCharacters(): void
    {
        Approvals::verify('Special chars: &<>"\'/' . "\n");
    }

    /**
     * @dataProvider provideTestData
     * @requires PHPUnit < 10
     */
    #[RequiresPHPUnit('< 10')]
    public function testWithDataProviderAnnotation(string $input): void
    {
        Approvals::verify($input);
    }

    /**
     * @requires PHPUnit >= 10
     */
    #[DataProvider('provideTestData')]
    #[RequiresPHPUnit('>= 10')]
    public function testWithDataProviderAttribute(string $input): void
    {
        Approvals::verify($input);
    }

    public static function provideTestData(): array
    {
        return [
            'simple text' => ['hello'],
            'with spaces' => ['hello world'],
            'with numbers' => ['test123'],
        ];
    }

    /**
     * @dataProvider provideJsonData
     * @requires PHPUnit < 10
     */
    #[RequiresPHPUnit('< 10')]
    public function testJsonWithDataProviderAnnotation(array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);

        Approvals::verifyJson($json);
    }

    /**
     * @requires PHPUnit >= 10
     */
    #[RequiresPHPUnit('>= 10')]
    #[DataProvider('provideJsonData')]
    public function testJsonWithDataProviderAttribute(array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);

        Approvals::verifyJson($json);
    }

    public static function provideJsonData(): array
    {
        return [
            'simple object' => [
                ['name' => 'John', 'age' => 30]
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
                ]
            ]
        ];
    }
}
