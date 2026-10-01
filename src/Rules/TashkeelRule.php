<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Handles extraction, isolation, and stripping of Arabic diacritics (Tashkeel / Harakat):
 * - Fatha (\u{064E})
 * - Damma (\u{064F})
 * - Kasra (\u{0650})
 * - Sukun (\u{0652})
 * - Shadda (\u{0651})
 * - Fathatan (\u{064B})
 * - Dammatan (\u{064C})
 * - Kasratan (\u{064D})
 * - Superscript Alef / Dagger Alef (\u{0670})
 * - Small Waw (\u{06E5})
 * - Small Yeh (\u{06E6})
 * - Small High Yeh (\u{06E7})
 */
class TashkeelRule implements RuleInterface
{
    /**
     * Unicode code points of all Tashkeel / Harakat
     */
    public const TASHKEEL_CHARS = [
        "\u{064B}", // Fathatan
        "\u{064C}", // Dammatan
        "\u{064D}", // Kasratan
        "\u{064E}", // Fatha
        "\u{064F}", // Damma
        "\u{0650}", // Kasra
        "\u{0651}", // Shadda
        "\u{0652}", // Sukun
        "\u{0653}", // Maddah Above
        "\u{0654}", // Hamza Above mark
        "\u{0655}", // Hamza Below mark
        "\u{0656}", // Subscript Alef
        "\u{0670}", // Dagger Alef (Superscript Alef)
    ];

    /**
     * Pattern matching any Tashkeel mark (including Shadda)
     */
    public const TASHKEEL_REGEX = '/[\x{064B}-\x{0656}\x{0670}]/u';

    /**
     * Pattern matching Tashkeel excluding Shadda (\u{0651})
     */
    public const TASHKEEL_EXCEPT_SHADDA_REGEX = '/[\x{064B}-\x{0650}\x{0652}-\x{0656}\x{0670}]/u';

    /**
     * Strip Tashkeel according to options.
     * Options:
     *  - keep_shadda: bool (default false) -> keep Shadda (\u{0651})
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string
    {
        $keepShadda = $options['keep_shadda'] ?? false;

        if ($keepShadda) {
            return (string) preg_replace(self::TASHKEEL_EXCEPT_SHADDA_REGEX, '', $text);
        }

        return str_replace(self::TASHKEEL_CHARS, '', $text);
    }

    /**
     * Extract diacritics from text along with their character positions.
     *
     * @param string $text
     * @return array<int, array{char: string, index: int, codepoint: string}>
     */
    public function extractDiacritics(string $text): array
    {
        $diacritics = [];
        $chars = mb_str_split($text, 1, 'UTF-8');
        foreach ($chars as $idx => $char) {
            if (in_array($char, self::TASHKEEL_CHARS, true)) {
                $diacritics[] = [
                    'char' => $char,
                    'index' => $idx,
                    'codepoint' => sprintf('U+%04X', mb_ord($char, 'UTF-8')),
                ];
            }
        }
        return $diacritics;
    }
}
