<?php

namespace App\Support;

/**
 * Indonesian national ID number (NIK) helpers.
 */
class Nik
{
    /** Strip the spaces, dots and dashes people type between digit groups. */
    public static function normalize(string $nik): string
    {
        return (string) preg_replace('/[\s.\-]/', '', $nik);
    }

    public static function mask(string $nik): string
    {
        return str_repeat('*', max(strlen($nik) - 4, 0)).substr($nik, -4);
    }

    /** Keyed hash used to detect the same NIK on another account without decrypting. */
    public static function hash(string $nik): string
    {
        return hash_hmac('sha256', self::normalize($nik), (string) config('app.key'));
    }
}
