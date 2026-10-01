<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer;

use EidCloud\ArabicNormalizer\Rules\RuleInterface;
use EidCloud\ArabicNormalizer\Rules\HamzaRule;
use EidCloud\ArabicNormalizer\Rules\TashkeelRule;
use EidCloud\ArabicNormalizer\Rules\TatweelRule;
use EidCloud\ArabicNormalizer\Rules\NumeralRule;
use EidCloud\ArabicNormalizer\Rules\TehMaksuraRule;
use EidCloud\ArabicNormalizer\Rules\LigatureRule;
use EidCloud\ArabicNormalizer\Rules\WhitespaceRule;

/**
 * Enterprise-grade, high-speed Arabic text normalizer, diacritics sanitizer,
 * and orthographic harmonizer engine in pure PHP 8.2+.
 */
class Normalizer
{
    public const VERSION = '1.0.0';

    private LigatureRule $ligatureRule;
    private TashkeelRule $tashkeelRule;
    private TatweelRule $tatweelRule;
    private HamzaRule $hamzaRule;
    private TehMaksuraRule $tehMaksuraRule;
    private NumeralRule $numeralRule;
    private WhitespaceRule $whitespaceRule;

    /**
     * @var array<string, mixed>
     */
    private array $defaultOptions = [
        // Ligatures & Quranic marks
        'expand_ligatures' => true,
        'strip_quranic_signs' => true,

        // Tashkeel / Diacritics
        'strip_tashkeel' => true,
        'keep_shadda' => false,

        // Tatweel / Kashida
        'strip_tatweel' => true,

        // Hamza Unification
        'unify_hamza' => true,
        'unify_alef' => true,
        'alef_target' => 'ا',
        'unify_waw_hamza' => false,
        'unify_yeh_hamza' => false,
        'remove_isolated_hamza' => false,

        // Teh Marbuta & Alef Maksura
        'teh_marbuta_to_heh' => true,
        'heh_to_teh_marbuta' => false,
        'alef_maksura_to_yeh' => true,
        'yeh_to_alef_maksura' => false,
        'unify_farsi_yeh' => true,

        // Numerals
        'normalize_numerals' => true,
        'numeral_target' => 'western', // 'western' (0-9), 'eastern' (٠-٩), 'persian' (۰-۹), or null/false

        // Whitespace & Zero-width
        'normalize_whitespace' => true,
        'collapse_spaces' => true,
        'remove_zero_width' => true,
        'trim' => true,
    ];

    /**
     * @param array<string, mixed> $customOptions
     */
    public function __construct(array $customOptions = [])
    {
        $this->ligatureRule = new LigatureRule();
        $this->tashkeelRule = new TashkeelRule();
        $this->tatweelRule = new TatweelRule();
        $this->hamzaRule = new HamzaRule();
        $this->tehMaksuraRule = new TehMaksuraRule();
        $this->numeralRule = new NumeralRule();
        $this->whitespaceRule = new WhitespaceRule();

        if (!empty($customOptions)) {
            $this->defaultOptions = array_merge($this->defaultOptions, $customOptions);
        }
    }

    /**
     * Factory method for convenience.
     *
     * @param array<string, mixed> $options
     */
    public static function create(array $options = []): self
    {
        return new self($options);
    }

    /**
     * Primary normalization method.
     *
     * @param string $text
     * @param array<string, mixed> $options
     * @return string
     */
    public function normalize(string $text, array $options = []): string
    {
        if ($text === '') {
            return '';
        }

        $opts = array_merge($this->defaultOptions, $options);

        // 1. Expand ligatures & strip Quranic recitation symbols
        if ($opts['expand_ligatures'] || $opts['strip_quranic_signs']) {
            $text = $this->ligatureRule->apply($text, $opts);
        }

        // 2. Strip Tatweel (Kashida)
        if ($opts['strip_tatweel']) {
            $text = $this->tatweelRule->apply($text, $opts);
        }

        // 3. Strip Tashkeel (Diacritics)
        if ($opts['strip_tashkeel']) {
            $text = $this->tashkeelRule->apply($text, $opts);
        }

        // 4. Hamza Unification
        if ($opts['unify_hamza']) {
            $text = $this->hamzaRule->apply($text, $opts);
        }

        // 5. Teh Marbuta and Alef Maksura Harmonization
        $text = $this->tehMaksuraRule->apply($text, $opts);

        // 6. Numeral Normalization
        if ($opts['normalize_numerals'] && !empty($opts['numeral_target'])) {
            $text = $this->numeralRule->apply($text, ['target' => $opts['numeral_target']]);
        }

        // 7. Whitespace & zero-width characters sanitization
        if ($opts['normalize_whitespace']) {
            $text = $this->whitespaceRule->apply($text, $opts);
        }

        return $text;
    }

    /**
     * Strip diacritics only.
     *
     * @param string $text
     * @param bool $keepShadda
     * @return string
     */
    public function stripTashkeel(string $text, bool $keepShadda = false): string
    {
        return $this->tashkeelRule->apply($text, ['keep_shadda' => $keepShadda]);
    }

    /**
     * Strip Tatweel (Kashida) only.
     */
    public function stripTatweel(string $text): string
    {
        return $this->tatweelRule->apply($text);
    }

    /**
     * Normalize numerals only.
     *
     * @param string $text
     * @param string $target 'western'|'eastern'|'persian'
     * @return string
     */
    public function normalizeNumerals(string $text, string $target = 'western'): string
    {
        return $this->numeralRule->apply($text, ['target' => $target]);
    }

    /**
     * Extract diacritics with positions.
     *
     * @param string $text
     * @return array<int, array{char: string, index: int, codepoint: string}>
     */
    public function extractDiacritics(string $text): array
    {
        return $this->tashkeelRule->extractDiacritics($text);
    }

    /**
     * Stream cleaning for large files or gigabyte-scale datasets line by line.
     *
     * @param resource $inputHandle
     * @param resource $outputHandle
     * @param array<string, mixed> $options
     * @return array{lines: int, bytes_in: int, bytes_out: int, elapsed_sec: float}
     */
    public function streamNormalize($inputHandle, $outputHandle, array $options = []): array
    {
        $startTime = microtime(true);
        $lines = 0;
        $bytesIn = 0;
        $bytesOut = 0;

        while (($line = fgets($inputHandle)) !== false) {
            $lines++;
            $bytesIn += strlen($line);

            // Clean line while preserving line breaks
            $cleaned = $this->normalize(rtrim($line, "\r\n"), $options);
            $written = fwrite($outputHandle, $cleaned . "\n");
            if ($written !== false) {
                $bytesOut += $written;
            }
        }

        $elapsed = microtime(true) - $startTime;

        return [
            'lines' => $lines,
            'bytes_in' => $bytesIn,
            'bytes_out' => $bytesOut,
            'elapsed_sec' => round($elapsed, 4),
        ];
    }

    /**
     * Fast batch processing of an array of strings.
     *
     * @param array<int, string> $texts
     * @param array<string, mixed> $options
     * @return array<int, string>
     */
    public function batchNormalize(array $texts, array $options = []): array
    {
        $result = [];
        foreach ($texts as $key => $text) {
            $result[$key] = $this->normalize($text, $options);
        }
        return $result;
    }
}
