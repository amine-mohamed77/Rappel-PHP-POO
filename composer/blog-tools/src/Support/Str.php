<?php
namespace App\Support;

// Class Str: Help deal with strings
final class Str
{
    // Parse string to slug
    public static function slug(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $text);
        return trim($text, '-');
    }

    // Shorten the text and remove HTML tags
    public static function excerpt(string $content, int $max = 180): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
        return mb_strlen($clean) <= $max ? $clean : mb_substr($clean, 0, $max - 1) . '…';
    }
}
?>