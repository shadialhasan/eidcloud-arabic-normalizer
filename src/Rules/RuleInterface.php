<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Interface for Arabic text normalization rules.
 */
interface RuleInterface
{
    /**
     * Apply normalization rule to the input text.
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string;
}
