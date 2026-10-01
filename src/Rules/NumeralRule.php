<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Normalizes Eastern Arabic (٠-٩) and Persian/Urdu (۰-۹) numerals
 * to Western Arabic digits (0-9) and vice versa.
 */
class NumeralRule implements RuleInterface
{
    /**
     * Eastern Arabic numerals (Indic): \u{0660} - \u{0669}
     */
    public const EASTERN_ARABIC = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    /**
     * Persian / Extended Arabic-Indic numerals: \u{06F0} - \u{06F9}
     */
    public const PERSIAN_ARABIC = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    /**
     * Western Arabic digits: 0-9
     */
    public const WESTERN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    /**
     * Normalize numerals.
     * Options:
     *  - target: 'western' | 'eastern' | 'persian' (default 'western')
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string
    {
        $target = $options['target'] ?? 'western';

        return match ($target) {
            'eastern' => $this->toEastern($text),
            'persian' => $this->toPersian($text),
            default => $this->toWestern($text),
        };
    }

    /**
     * Convert any Arabic/Persian numerals to Western digits (0-9).
     */
    public function toWestern(string $text): string
    {
        // Replace Eastern digits
        $text = str_replace(self::EASTERN_ARABIC, self::WESTERN_DIGITS, $text);
        // Replace Persian digits
        return str_replace(self::PERSIAN_ARABIC, self::WESTERN_DIGITS, $text);
    }

    /**
     * Convert Western & Persian digits to Eastern Arabic numerals (٠-٩).
     */
    public function toEastern(string $text): string
    {
        // First unify persian to western
        $text = str_replace(self::PERSIAN_ARABIC, self::WESTERN_DIGITS, $text);
        return str_replace(self::WESTERN_DIGITS, self::EASTERN_ARABIC, $text);
    }

    /**
     * Convert Western & Eastern digits to Persian numerals (۰-۹).
     */
    public function toPersian(string $text): string
    {
        // First unify eastern to western
        $text = str_replace(self::EASTERN_ARABIC, self::WESTERN_DIGITS, $text);
        return str_replace(self::WESTERN_DIGITS, self::PERSIAN_ARABIC, $text);
    }
}
