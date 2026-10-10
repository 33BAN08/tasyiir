<?php

namespace App\Support;

/**
 * wa.me links. There is no WhatsApp API here and no account, no provider and
 * no cost: the app only builds the URL, the browser hands it to WhatsApp
 * (Desktop or web) and a human presses Send. That is also why this keeps
 * working in the offline local edition — the app itself calls nothing.
 */
class WhatsApp
{
    /**
     * A phone number as wa.me wants it: digits only, country code included,
     * no "+" and no leading zeros.
     *
     * Moroccan numbers are the common case, so "06…", "07…" and "05…" written
     * the national way become 212…. Anything else is kept as typed, which lets
     * a center store a foreign number, and anything that cannot be a real
     * international number comes back null rather than producing a dead link.
     */
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            // 00 is the international prefix typed out: 00212… → 212…
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            // A national Moroccan number is exactly ten digits (0 + 9).
            // A leading zero with any other length is malformed, not a number
            // we are willing to guess a country code for.
            if (strlen($digits) !== 10) {
                return null;
            }

            $digits = '212'.substr($digits, 1);
        }

        return strlen($digits) >= 11 && strlen($digits) <= 15 ? $digits : null;
    }

    /** The full wa.me link, or null when the number cannot be dialled. */
    public static function link(?string $phone, string $message = ''): ?string
    {
        $number = self::normalize($phone);

        if ($number === null) {
            return null;
        }

        return 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    public static function isValid(?string $phone): bool
    {
        return self::normalize($phone) !== null;
    }
}
