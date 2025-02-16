<?php

namespace Tests\Json;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use ApprovalTests\Scrubber\JsonScrubber;

class JsonTest extends TestCase
{
    /**
     * @test
     */
    public function jObjectOrdering1(): void
    {
        $json = json_encode([
            '@xmlns' => 2,
            '#text' => 1
        ]);

        Approvals::verifyJson($json);
    }

    /**
     * @test
     */
    public function jObjectOrdering2(): void
    {
        $json = json_encode([
            '#text' => 1,
            '@xmlns' => 2
        ]);

        Approvals::verifyJson($json);
    }

    /**
     * @test
     */
    public function jTokenIgnore(): void
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

    /**
     * @test
     */
    public function jTokenScrub(): void
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

    /**
     * @test
     */
    public function verifyJsonGuid(): void
    {
        $json = '{"key": "c572ff75-e1a2-49bd-99b9-4550697946c3"}';
        Approvals::verifyJson($json);
    }

    /**
     * @test
     */
    public function verifyJsonEmpty(): void
    {
        Approvals::verifyJson('{}');
    }

    /**
     * @test
     */
    public function verifyJsonDateTime(): void
    {
        $date = date('Y-m-d\TH:i:s');
        $json = sprintf('{"key": "%s"}', $date);
        Approvals::verifyJson($json);
    }

    /**
     * @test
     */
    public function verifyJsonWithArray(): void
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

    /**
     * @test
     */
    public function verifyJsonWithArrayAtRoot(): void
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

    /**
     * @test
     */
    public function verifyJsonString(): void
    {
        $json = '{"key": {"msg": "No action taken"}}';
        Approvals::verifyJson($json);
    }

    /**
     * @test
     */
    public function ignoreJTokenByName(): void
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

    /**
     * @test
     */
    public function scrubJTokenByName(): void
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
}
