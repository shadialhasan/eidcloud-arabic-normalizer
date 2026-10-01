<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Strips Tatweel (Kashida `ـ` \u{0640}) used for aesthetic text elongation.
 */
class TatweelRule implements RuleInterface
{
    public const TATWEEL_CHAR = 'ـ'; // \u{0640}

    /**
     * Remove Tatweel / Kashida characters.
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string
    {
        return str_replace(self::TATWEEL_CHAR, '', $text);
    }
}
