<?php

declare(strict_types=1);

namespace SSIS\Helpers;

/**
 * Unique, non-guessable public identifiers for records.
 */
final class IdGenerator
{
    /** Random public ID such as "usr_9f2c81ab44de0a17". */
    public static function publicId(string $prefix = ''): string
    {
        return $prefix . bin2hex(random_bytes(8));
    }

    /** Human-friendly request number such as "REQ-2026-A7KM2P" (I/O-safe alphabet). */
    public static function requestCode(): string
    {
        // Excludes 0, O, 1, I to avoid transcription mistakes.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $random = '';
        for ($i = 0; $i < 6; $i++) {
            $random .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return sprintf('REQ-%s-%s', gmdate('Y'), $random);
    }
}
