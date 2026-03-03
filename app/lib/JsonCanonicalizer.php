<?php

namespace App\Lib;

/**
 * A self-contained class to produce a canonical representation of JSON,
 * based on the work of MmcCook in php-json-canonicalization-scheme.
 * This is compliant with RFC 8785.
 */
class JsonCanonicalizer
{
    public static function canonicalize($data): string
    {
        if (is_float($data)) {
            return self::formatFloat($data);
        }

        if (is_null($data) || is_scalar($data)) {
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (is_array($data) && (array_keys($data) === range(0, count($data) - 1))) {
            $parts = [];
            foreach ($data as $element) {
                $parts[] = self::canonicalize($element);
            }
            return '[' . implode(',', $parts) . ']';
        }

        if (is_object($data)) {
            $data = (array) $data;
        }

        // Must be an object/associative array
        uksort($data, 'strcmp');

        $parts = [];
        foreach ($data as $key => $value) {
            $keyString = json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $parts[] = $keyString . ':' . self::canonicalize($value);
        }
        return '{' . implode(',', $parts) . '}';
    }

    private static function formatFloat(float $f): string
    {
        if (is_nan($f) || is_infinite($f)) {
            return 'null';
        }
        if ($f == 0.0) {
            return '0';
        }

        $s = sprintf('%.15e', $f);
        [$mantissa, $exponent] = explode('e', $s);

        $mantissa = rtrim($mantissa, '0');
        if (substr($mantissa, -1) === '.') {
            $mantissa .= '0';
        }

        $exponent = (int) $exponent;
        if ($exponent === 0) {
            return $mantissa;
        }

        return $mantissa . 'e' . ($exponent > 0 ? '+' : '') . $exponent;
    }
}
