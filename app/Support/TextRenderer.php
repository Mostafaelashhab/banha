<?php

namespace App\Support;

class TextRenderer
{
    /**
     * Convert #tag → clickable links. Escapes everything else (XSS-safe).
     */
    public static function renderHashtags(string $text): string
    {
        $escaped = e($text);
        return preg_replace_callback(
            '/(?<=^|\s)#([\p{L}\p{N}_]{2,40})/u',
            function ($m) {
                $tag = mb_strtolower($m[1]);
                $url = route('hashtag.show', $tag);
                return '<a href="'.$url.'" class="text-coral-600 font-bold hover:underline">#'.$m[1].'</a>';
            },
            $escaped
        );
    }
}
