<?php

namespace App\Support;

/**
 * Detects marketing claims banned by docs/ui-ux/copywriting.md.
 * The phrase list lives in the content.prohibited_phrases setting.
 */
class ProhibitedClaims
{
    /**
     * @return list<string> Prohibited phrases found in the text, in list order.
     */
    public static function find(string $text): array
    {
        $haystack = mb_strtolower($text);

        return array_values(array_filter(
            Settings::get('content.prohibited_phrases'),
            fn (string $phrase) => str_contains($haystack, mb_strtolower($phrase)),
        ));
    }
}
