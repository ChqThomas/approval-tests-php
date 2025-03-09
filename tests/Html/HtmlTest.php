<?php

namespace ChqThomas\ApprovalTests\Tests\Html;

use ChqThomas\ApprovalTests\Approvals;
use ChqThomas\ApprovalTests\Scrubber\HtmlScrubber;
use ChqThomas\ApprovalTests\Scrubber\RegexScrubber;
use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{
    public function testBasicHtml(): void
    {
        $html = '<div>Hello World</div>';
        Approvals::verifyHtml($html);
    }

    public function testCompleteHtmlDocument(): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
</head>
<body>
    <div>Content</div>
</body>
</html>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testNestedElements(): void
    {
        $html = <<<HTML
<div>
    <p>Paragraph 1</p>
    <p>Paragraph 2</p>
    <div>
        <span>Nested content</span>
    </div>
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testSelfClosingTags(): void
    {
        $html = <<<HTML
<div>
    <img src="image.jpg" />
    <br/>
    <input type="text" />
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testHtmlAttributes(): void
    {
        $html = <<<HTML
<div class="container" id="main" data-test="value">
    <span style="color: red;">Colored text</span>
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testInlineElements(): void
    {
        $html = <<<HTML
<p>This is <strong>bold</strong> and this is <em>italic</em> with a <a href="#">link</a></p>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testMalformedHtml(): void
    {
        $html = '<div>Unclosed div';
        Approvals::verifyHtml($html);
    }

    public function testHtmlWithComments(): void
    {
        $html = <<<HTML
<div>
    <!-- This is a comment -->
    <p>Content</p>
    <!-- Another comment -->
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testSpecialCharacters(): void
    {
        $html = <<<HTML
<div>
    <p>Special characters: € ‚ „ … † ‡ ‰ ‹ › ‘ ’ “ ” • — ™ › ® © é à è ù ï ç ä ö ü ÿ Ä Ö Ü ß ° ± ² ³ ´ · µ ¶ · ¸ ¹ º » « ÷ » « × € ‚ „ … † ‡ ‰ ‹ › ‘ ’ “ ” • — ™ › ® © é à è ù ï ç ä ö ü ÿ Ä Ö Ü ß ° ± ² ³ ´ · µ ¶ · ¸ ¹ º » « ÷ » « ×</p>
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testScriptTags(): void
    {
        $html = <<<HTML
<div>
    <script>
        function test() {
            console.log('Hello');
        }
    </script>
    <p>Content after script</p>
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testStyleTags(): void
    {
        $html = <<<HTML
<div>
    <style>
        .test {
            color: red;
        }
    </style>
    <p class="test">Styled content</p>
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testHtmlWithRegexScrubbing(): void
    {
        $html = <<<HTML
<div id="user123">
    <p>John Doe</p>
    <span>ID: ABC-456-789</span>
</div>
HTML;

        Approvals::verifyHtml($html, HtmlScrubber::create()
            ->addScrubber(RegexScrubber::create([
                '/user\d{3}/' => 'userXXX',
                '/[A-Z]+-\d{3}-\d{3}/' => 'ID-XXX-XXX',
                '/John Doe/' => 'NAME'
            ])));
    }

    public function testEmptyElements(): void
    {
        $html = <<<HTML
<div>
    <p></p>
    <span></span>
    <div class="empty"></div>
</div>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testMultipleRootElements(): void
    {
        $html = <<<HTML
<div>First element</div>
<p>Second element</p>
<span>Third element</span>
HTML;
        Approvals::verifyHtml($html);
    }

    public function testDataAttributes(): void
    {
        $html = <<<HTML
<div 
    data-id="123" 
    data-user="john" 
    data-timestamp="2023-01-01T12:00:00">
    Content
</div>
HTML;
        Approvals::verifyHtml($html);
    }
}
