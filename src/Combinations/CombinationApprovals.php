<?php

namespace ChqThomas\ApprovalTests\Combinations;

use ChqThomas\ApprovalTests\Approvals;

class CombinationApprovals
{
    public static function verifyAllCombinations(
        callable $func,
        array $parameters,
        ?callable $formatter = null
    ): void {
        $results = [];
        $combinations = self::generateCombinations($parameters);

        foreach ($combinations as $combination) {
            $result = $func(...$combination);
            $formattedParams = implode(', ', array_map(fn ($p) => var_export($p, true), $combination));
            $formattedResult = $formatter ? $formatter($result) : var_export($result, true);
            $results[] = "({$formattedParams}) => {$formattedResult}";
        }

        Approvals::verify(implode("\n", $results));
    }

    private static function generateCombinations(array $arrays): array
    {
        if (empty($arrays)) {
            return [[]];
        }

        $result = [];
        $firstArray = array_shift($arrays);
        $remainingCombinations = self::generateCombinations($arrays);

        foreach ($firstArray as $value) {
            foreach ($remainingCombinations as $combination) {
                array_unshift($combination, $value);
                $result[] = $combination;
            }
        }

        return $result;
    }
}
