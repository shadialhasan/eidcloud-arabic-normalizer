<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Normalizes redundant whitespace, non-breaking spaces, and non-printable control characters.
 */
class WhitespaceRule implements RuleInterface
{
    /**
     * Pattern matching zero-width characters (ZWNJ \u{200C}, ZWJ \u{200D}, LTR/RTL marks \u{200E}\u{200F}, BOM \u{FEFF})
     */
    public const ZERO_WIDTH_REGEX = '/[\x{200B}-\x{200F}\x{FEFF}\x{202A}-\x{202E}]/u';

    /**
     * Apply whitespace normalization.
     * Options:
     *  - collapse_spaces: bool (default true) -> collapse multiple spaces into one
     *  - remove_zero_width: bool (default true) -> remove zero-width non-joiner / joiner / direction marks
     *  - trim: bool (default true) -> trim leading and trailing spaces
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string
    {
        $collapseSpaces = $options['collapse_spaces'] ?? true;
        $removeZeroWidth = $options['remove_zero_width'] ?? true;
        $trim = $options['trim'] ?? true;

        // Normalize non-breaking spaces (\u{00A0}) to standard space
        $text = str_replace("\u{00A0}", ' ', $text);

        if ($removeZeroWidth) {
            $text = (string) preg_replace(self::ZERO_WIDTH_REGEX, '', $text);
        }

        if ($collapseSpaces) {
            // Replace multiple spaces/tabs with single space, while preserving newlines
            $text = (string) preg_replace('/[^\S\r\n]+/u', ' ', $text);
        }

        if ($trim) {
            $text = trim($text);
        }

        return $text;
    }
}
