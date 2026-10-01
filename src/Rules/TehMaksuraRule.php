<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Normalizes Teh Marbuta (ة <-> ه) and Alef Maksura (ى <-> ي)
 * for phonetic, semantic, and structural harmonization.
 */
class TehMaksuraRule implements RuleInterface
{
    /**
     * Characters:
     * \u{0629} - ة (Teh Marbuta)
     * \u{0647} - ه (Heh)
     * \u{0649} - ى (Alef Maksura)
     * \u{064A} - ي (Yeh)
     * \u{06CC} - ی (Farsi/Persian Yeh)
     */

    /**
     * Apply Teh Marbuta and Alef Maksura harmonization.
     * Options:
     *  - teh_marbuta_to_heh: bool (default true) -> ة => ه
     *  - heh_to_teh_marbuta: bool (default false) -> ه => ة
     *  - alef_maksura_to_yeh: bool (default true) -> ى => ي
     *  - yeh_to_alef_maksura: bool (default false) -> ي => ى
     *  - unify_farsi_yeh: bool (default true) -> ی (\u{06CC}) => ي (\u{064A})
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string
    {
        $tehToHeh = $options['teh_marbuta_to_heh'] ?? true;
        $hehToTeh = $options['heh_to_teh_marbuta'] ?? false;
        $maksuraToYeh = $options['alef_maksura_to_yeh'] ?? true;
        $yehToMaksura = $options['yeh_to_alef_maksura'] ?? false;
        $unifyFarsiYeh = $options['unify_farsi_yeh'] ?? true;

        $search = [];
        $replace = [];

        if ($unifyFarsiYeh) {
            $search[] = 'ی'; // \u{06CC}
            $replace[] = 'ي'; // \u{064A}
        }

        if ($tehToHeh) {
            $search[] = 'ة';
            $replace[] = 'ه';
        } elseif ($hehToTeh) {
            $search[] = 'ه';
            $replace[] = 'ة';
        }

        if ($maksuraToYeh) {
            $search[] = 'ى';
            $replace[] = 'ي';
        } elseif ($yehToMaksura) {
            $search[] = 'ي';
            $replace[] = 'ى';
        }

        if (empty($search)) {
            return $text;
        }

        return str_replace($search, $replace, $text);
    }
}
