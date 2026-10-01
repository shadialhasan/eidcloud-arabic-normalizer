<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Rules;

/**
 * Expands Unicode Arabic presentation form ligatures (U+FB50-U+FDFF, U+FE70-U+FEFF)
 * such as "ﷲ" (Allah ligature), "ﷺ" (Sallallahu Alayhi Wasallam), "﷼" (Rial),
 * "﷽" (Bismillah), and strips Quranic recitation/annotation marks (U+06D6-U+06ED).
 */
class LigatureRule implements RuleInterface
{
    /**
     * Common Arabic ligatures and presentation forms mapping to canonical sequences
     */
    public const LIGATURE_MAP = [
        // Ligatures
        "\u{FDFA}" => "صلى الله عليه وسلم", // Sallallahou Alayhe Wasallam
        "\u{FDFB}" => "جل جلاله",          // Jallajalalouhou
        "\u{FDFD}" => "بسم الله الرحمن الرحيم", // Bismillah
        "\u{FDF0}" => "صلعم",
        "\u{FDF1}" => "قاسم",
        "\u{FDF2}" => "الله",             // Allah isolated ligature
        "\u{FDF4}" => "محمد",
        "\u{FDF7}" => "رسول",
        "\u{FDF8}" => "عليه",
        "\u{FDF9}" => "وسلم",
        "\u{FDFC}" => "ريال",             // Rial sign

        // Presentation Forms-A and B common ligatures
        "\u{FE80}" => "ء",
        "\u{FE81}" => "آ",
        "\u{FE82}" => "آ",
        "\u{FE83}" => "أ",
        "\u{FE84}" => "أ",
        "\u{FE85}" => "ؤ",
        "\u{FE86}" => "ؤ",
        "\u{FE87}" => "إ",
        "\u{FE88}" => "إ",
        "\u{FE89}" => "ئ",
        "\u{FE8A}" => "ئ",
        "\u{FE8B}" => "ئ",
        "\u{FE8C}" => "ئ",
        "\u{FE8D}" => "ا",
        "\u{FE8E}" => "ا",
        "\u{FE8F}" => "ب",
        "\u{FE90}" => "ب",
        "\u{FE91}" => "ب",
        "\u{FE92}" => "ب",
        "\u{FE93}" => "ة",
        "\u{FE94}" => "ة",
        "\u{FE95}" => "ت",
        "\u{FE96}" => "ت",
        "\u{FE97}" => "ت",
        "\u{FE98}" => "ت",
        "\u{FE99}" => "ث",
        "\u{FE9A}" => "ث",
        "\u{FE9B}" => "ث",
        "\u{FE9C}" => "ث",
        "\u{FE9D}" => "ج",
        "\u{FE9E}" => "ج",
        "\u{FE9F}" => "ج",
        "\u{FEA0}" => "ج",
        "\u{FEA1}" => "ح",
        "\u{FEA2}" => "ح",
        "\u{FEA3}" => "ح",
        "\u{FEA4}" => "ح",
        "\u{FEA5}" => "خ",
        "\u{FEA6}" => "خ",
        "\u{FEA7}" => "خ",
        "\u{FEA8}" => "خ",
        "\u{FEA9}" => "د",
        "\u{FEAA}" => "د",
        "\u{FEAB}" => "ذ",
        "\u{FEAC}" => "ذ",
        "\u{FEAD}" => "ر",
        "\u{FEAE}" => "ر",
        "\u{FEAF}" => "ز",
        "\u{FEB0}" => "ز",
        "\u{FEB1}" => "س",
        "\u{FEB2}" => "س",
        "\u{FEB3}" => "س",
        "\u{FEB4}" => "س",
        "\u{FEB5}" => "ش",
        "\u{FEB6}" => "ش",
        "\u{FEB7}" => "ش",
        "\u{FEB8}" => "ش",
        "\u{FEB9}" => "ص",
        "\u{FEBA}" => "ص",
        "\u{FEBB}" => "ص",
        "\u{FEBC}" => "ص",
        "\u{FEBD}" => "ض",
        "\u{FEBE}" => "ض",
        "\u{FEBF}" => "ض",
        "\u{FEC0}" => "ض",
        "\u{FEC1}" => "ط",
        "\u{FEC2}" => "ط",
        "\u{FEC3}" => "ط",
        "\u{FEC4}" => "ط",
        "\u{FEC5}" => "ظ",
        "\u{FEC6}" => "ظ",
        "\u{FEC7}" => "ظ",
        "\u{FEC8}" => "ظ",
        "\u{FEC9}" => "ع",
        "\u{FECA}" => "ع",
        "\u{FECB}" => "ع",
        "\u{FECC}" => "ع",
        "\u{FECD}" => "غ",
        "\u{FECE}" => "غ",
        "\u{FECF}" => "غ",
        "\u{FED0}" => "غ",
        "\u{FED1}" => "ف",
        "\u{FED2}" => "ف",
        "\u{FED3}" => "ف",
        "\u{FED4}" => "ف",
        "\u{FED5}" => "ق",
        "\u{FED6}" => "ق",
        "\u{FED7}" => "ق",
        "\u{FED8}" => "ق",
        "\u{FED9}" => "ك",
        "\u{FEDA}" => "ك",
        "\u{FEDB}" => "ك",
        "\u{FEDC}" => "ك",
        "\u{FEDD}" => "ل",
        "\u{FEDE}" => "ل",
        "\u{FEDF}" => "ل",
        "\u{FEE0}" => "ل",
        "\u{FEE1}" => "م",
        "\u{FEE2}" => "م",
        "\u{FEE3}" => "م",
        "\u{FEE4}" => "م",
        "\u{FEE5}" => "ن",
        "\u{FEE6}" => "ن",
        "\u{FEE7}" => "ن",
        "\u{FEE8}" => "ن",
        "\u{FEE9}" => "ه",
        "\u{FEEA}" => "ه",
        "\u{FEEB}" => "ه",
        "\u{FEEC}" => "ه",
        "\u{FEED}" => "و",
        "\u{FEEE}" => "و",
        "\u{FEEF}" => "ى",
        "\u{FEF0}" => "ى",
        "\u{FEF1}" => "ي",
        "\u{FEF2}" => "ي",
        "\u{FEF3}" => "ي",
        "\u{FEF4}" => "ي",

        // Lam-Alef forms
        "\u{FEF5}" => "لآ",
        "\u{FEF6}" => "لآ",
        "\u{FEF7}" => "لأ",
        "\u{FEF8}" => "لأ",
        "\u{FEF9}" => "لإ",
        "\u{FEFA}" => "لإ",
        "\u{FEFB}" => "لا",
        "\u{FEFC}" => "لا",
    ];

    /**
     * Quranic recitation / annotation marks range (U+06D6 to U+06ED, plus U+0615-U+061A)
     */
    public const QURANIC_MARKS_REGEX = '/[\x{0615}-\x{061A}\x{06D6}-\x{06ED}\x{0600}-\x{0605}]/u';

    /**
     * Apply ligature expansion and Quranic sign sanitization.
     * Options:
     *  - expand_ligatures: bool (default true)
     *  - strip_quranic_signs: bool (default true)
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function apply(string $text, array $options = []): string
    {
        $expandLigatures = $options['expand_ligatures'] ?? true;
        $stripQuranicSigns = $options['strip_quranic_signs'] ?? true;

        if ($expandLigatures) {
            $text = strtr($text, self::LIGATURE_MAP);
        }

        if ($stripQuranicSigns) {
            $text = (string) preg_replace(self::QURANIC_MARKS_REGEX, '', $text);
        }

        return $text;
    }
}
