<?php

namespace App\Services\Expression;

class ExpressionEvaluator
{
    public static function evaluate(string $expression, array $context): bool
    {
        $expression = self::replaceVariables($expression, $context);

        return self::evaluateExpression($expression);
    }

    private static function replaceVariables(string $expression, array $context): string
    {
        return preg_replace_callback('/{{\s*\$json\.([^\s}]+)\s*}}/', function ($matches) use ($context) {
            $value = data_get($context, $matches[1]);

            if (is_string($value)) {
                return "'".addslashes($value)."'";
            } elseif (is_bool($value)) {
                return $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                return 'null';
            }

            return $value;
        }, $expression);
    }

    private static function evaluateExpression(string $expression): bool
    {
        $expression = trim($expression);

        if (preg_match('/^(.+?)\s*(==|!=|>|<|>=|<=)\s*(.+)$/', $expression, $matches)) {
            $left = self::parseValue(trim($matches[1]));
            $operator = $matches[2];
            $right = self::parseValue(trim($matches[3]));

            return self::compare($left, $operator, $right);
        }

        if (preg_match('/^(.+?)\s+(and|AND|&&)\s+(.+)$/', $expression, $matches)) {
            return self::evaluateExpression($matches[1]) && self::evaluateExpression($matches[3]);
        }

        if (preg_match('/^(.+?)\s+(or|OR|\|\|)\s+(.+)$/', $expression, $matches)) {
            return self::evaluateExpression($matches[1]) || self::evaluateExpression($matches[3]);
        }

        if (preg_match('/^!\s*(.+)$/', $expression, $matches)) {
            return ! self::evaluateExpression($matches[1]);
        }

        return self::parseValue($expression) == true;
    }

    private static function parseValue(string $value)
    {
        $value = trim($value);

        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        if ($value === 'null') {
            return null;
        }

        if (preg_match('/^["\'](.*)["\']\s*$/', $value, $matches)) {
            return $matches[1];
        }

        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        return $value;
    }

    private static function compare($left, string $operator, $right): bool
    {
        switch ($operator) {
            case '==':
                return $left == $right;
            case '!=':
                return $left != $right;
            case '>':
                return $left > $right;
            case '<':
                return $left < $right;
            case '>=':
                return $left >= $right;
            case '<=':
                return $left <= $right;
            default:
                return false;
        }
    }
}
