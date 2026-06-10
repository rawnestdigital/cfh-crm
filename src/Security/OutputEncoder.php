<?php

namespace App\Security;

/**
 * Output Encoding
 * Prevents XSS attacks by properly encoding output
 */
class OutputEncoder
{
    /**
     * HTML encode for safe display
     */
    public static function html(string $string, int $flags = ENT_QUOTES): string
    {
        return htmlspecialchars($string, $flags, 'UTF-8');
    }

    /**
     * Encode for HTML attributes
     */
    public static function attribute(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * URL encode
     */
    public static function url(string $string): string
    {
        return urlencode($string);
    }

    /**
     * JavaScript encode
     */
    public static function javascript(string $string): string
    {
        return json_encode(
            $string,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * CSS encode
     */
    public static function css(string $string): string
    {
        // Escape special characters
        $string = preg_replace_callback(
            '/[a-z0-9]/i',
            function ($matches) {
                return '\\' . dechex(ord($matches[0]));
            },
            $string
        );

        return $string;
    }

    /**
     * CSV encode
     */
    public static function csv(string $string): string
    {
        if (strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) {
            return '"' . str_replace('"', '""', $string) . '"';
        }
        return $string;
    }

    /**
     * JSON encode safely
     */
    public static function json(mixed $data): string
    {
        return json_encode(
            $data,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * XML encode
     */
    public static function xml(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1, 'UTF-8');
    }

    /**
     * LDAP encode
     */
    public static function ldap(string $string): string
    {
        // Escape LDAP special characters
        $escapeChars = ['\\', '*', '(', ')', '\x00'];
        $replacements = ['\\5c', '\\2a', '\\28', '\\29', '\\00'];

        return str_replace($escapeChars, $replacements, $string);
    }
}
