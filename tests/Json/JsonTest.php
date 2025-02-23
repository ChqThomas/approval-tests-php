<?php

namespace ChqThomas\ApprovalTests\Tests\Json;

use ChqThomas\ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use ChqThomas\ApprovalTests\Scrubber\JsonScrubber;
use ChqThomas\ApprovalTests\Scrubber\RegexScrubber;

class JsonTest extends TestCase
{
    public function testJObjectOrdering1(): void
    {
        $json = json_encode([
            '@xmlns' => 2,
            '#text' => 1
        ]);

        Approvals::verifyJson($json);
    }

    public function testJObjectOrdering2(): void
    {
        $json = json_encode([
            '#text' => 1,
            '@xmlns' => 2
        ]);

        Approvals::verifyJson($json);
    }

    public function testJTokenIgnore(): void
    {
        $json = <<<JSON
{
  "Include": 1,
  "Ignore": 2,
  "Memory Info": {
    "fragmentedBytes": 208,
    "heapSizeBytes": 2479536,
    "highMemoryLoadThresholdBytes": 30821986713,
    "memoryLoadBytes": 14041127280,
    "totalAvailableMemoryBytes": 34246651904
  }
}
JSON;

        Approvals::verifyJson($json, JsonScrubber::create()
            ->ignoreMember('Ignore', 'Memory Info'));
    }

    public function testJTokenScrub(): void
    {
        $json = <<<JSON
{
  "Include": 1,
  "Scrub": 2,
  "Memory Info": {
    "fragmentedBytes": 208,
    "heapSizeBytes": 2479536,
    "highMemoryLoadThresholdBytes": 30821986713,
    "memoryLoadBytes": 14041127280,
    "totalAvailableMemoryBytes": 34246651904
  }
}
JSON;

        Approvals::verifyJson($json, JsonScrubber::create()
            ->scrubMember('Scrub', 'Memory Info'));
    }

    public function testVerifyJsonGuid(): void
    {
        $json = '{"key": "c572ff75-e1a2-49bd-99b9-4550697946c3"}';
        Approvals::verifyJson($json);
    }

    public function testVerifyJsonEmpty(): void
    {
        Approvals::verifyJson('{}');
    }

    public function testVerifyJsonDateTime(): void
    {
        $date = date('Y-m-d\TH:i:s');
        $json = sprintf('{"key": "%s"}', $date);
        Approvals::verifyJson($json);
    }

    public function testVerifyJsonWithArray(): void
    {
        $json = <<<JSON
{
  "commitments": [
    {
      "id": "9585dadf-551a-43eb-960c-18b935993cc3",
      "title": "Commitment1"
    }
  ]
}
JSON;
        Approvals::verifyJson($json);
    }

    public function testVerifyJsonWithArrayAtRoot(): void
    {
        $json = <<<JSON
[
  {
    "id": "9585dadf-551a-43eb-960c-18b935993cc3",
    "title": "Commitment1"
  }
]
JSON;
        Approvals::verifyJson($json);
    }

    public function testVerifyJsonString(): void
    {
        $json = '{"key": {"msg": "No action taken"}}';
        Approvals::verifyJson($json);
    }

    public function testIgnoreJTokenByName(): void
    {
        $json = <<<JSON
{
  "short": {
    "key": {
      "code": 0,
      "msg": "No action taken"
    },
    "Ignore1": {
      "code": 2,
      "msg": "ignore this"
    }
  }
}
JSON;

        Approvals::verifyJson($json, JsonScrubber::create()
            ->ignoreMember('Ignore1'));
    }

    public function testScrubJTokenByName(): void
    {
        $json = <<<JSON
{
  "short": {
    "key": {
      "code": 0,
      "msg": "No action taken"
    },
    "Scrub": {
      "code": 2,
      "msg": "ignore this"
    }
  }
}
JSON;

        Approvals::verifyJson($json, JsonScrubber::create()
            ->scrubMember('Scrub'));
    }

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
}
