<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Normalizes different forms of Arabic Hamza (أ, إ, آ, ٱ, ء, ئ, ؤ)
 * into standardized canonical forms or unified Alef.
 */
class HamzaRule implements RuleInterface
{
    /**
     * Hamza characters:
     * \u{0623} - أ (Alef with Hamza Above)
     * \u{0625} - إ (Alef with Hamza Below)
     * \u{0622} - آ (Alef with Madda Above)
     * \u{0671} - ٱ (Alef Wasla)
     * \u{0624} - ؤ (Waw with Hamza Above)
     * \u{0626} - ئ (Yeh with Hamza Above)
     * \u{0621} - ء (Isolated Hamza)
     */

    /**
     * Apply Hamza normalization.
     * Options:
     *  - unify_alef: bool (default true) -> [أ, إ, آ, ٱ] => ا
     *  - alef_target: string (default 'ا')
     *  - unify_waw_hamza: bool (default false) -> ؤ => و
     *  - unify_yeh_hamza: bool (default false) -> ئ => ي
     *  - remove_isolated_hamza: bool (default false) -> ء => ''
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string
    {
        $unifyAlef = $options['unify_alef'] ?? true;
        $alefTarget = $options['alef_target'] ?? 'ا';
        $unifyWawHamza = $options['unify_waw_hamza'] ?? false;
        $unifyYehHamza = $options['unify_yeh_hamza'] ?? false;
        $removeIsolatedHamza = $options['remove_isolated_hamza'] ?? false;

        $search = [];
        $replace = [];

        if ($unifyAlef) {
            $search[] = 'أ';
            $replace[] = $alefTarget;
            $search[] = 'إ';
            $replace[] = $alefTarget;
            $search[] = 'آ';
            $replace[] = $alefTarget;
            $search[] = 'ٱ';
            $replace[] = $alefTarget;
        }

        if ($unifyWawHamza) {
            $search[] = 'ؤ';
            $replace[] = 'و';
        }

        if ($unifyYehHamza) {
            $search[] = 'ئ';
            $replace[] = 'ي';
        }

        if ($removeIsolatedHamza) {
            $search[] = 'ء';
            $replace[] = '';
        }

        if (empty($search)) {
            return $text;
        }

        return str_replace($search, $replace, $text);
    }
}
