<?php

declare(strict_types=1);

namespace EidCloud\ArabicNormalizer\Tests;

use EidCloud\ArabicNormalizer\Normalizer;
use EidCloud\ArabicNormalizer\Rules\HamzaRule;
use EidCloud\ArabicNormalizer\Rules\TashkeelRule;
use EidCloud\ArabicNormalizer\Rules\TatweelRule;
use EidCloud\ArabicNormalizer\Rules\NumeralRule;
use EidCloud\ArabicNormalizer\Rules\TehMaksuraRule;
use EidCloud\ArabicNormalizer\Rules\LigatureRule;
use EidCloud\ArabicNormalizer\Rules\WhitespaceRule;

class NormalizerTest
{
    private int $passed = 0;
    private int $failed = 0;
    /** @var array<int, string> */
    private array $errors = [];

    public function runAll(): bool
    {
        $this->testHamzaUnification();
        $this->testTashkeelStripping();
        $this->testTashkeelExtraction();
        $this->testTatweelRemoval();
        $this->testNumeralNormalization();
        $this->testTehMarbutaAndAlefMaksura();
        $this->testLigaturesAndQuranic();
        $this->testWhitespaceNormalization();
        $this->testFullWorkflow();
        $this->testBatchAndStream();

        $total = $this->passed + $this->failed;
        echo "\n------------------------------------------------------------\n";
        echo "Tests Run: {$total}, \033[32mPassed: {$this->passed}\033[0m, \033[31mFailed: {$this->failed}\033[0m\n";
        echo "------------------------------------------------------------\n";

        if ($this->failed > 0) {
            echo "\033[31mFailures:\033[0m\n";
            foreach ($this->errors as $err) {
                echo "  - {$err}\n";
            }
            return false;
        }

        echo "\033[32m✔ ALL ARABIC NORMALIZATION TESTS PASSED WITH 100% SUCCESS!\033[0m\n";
        return true;
    }

    private function assertSame(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected === $actual) {
            $this->passed++;
            echo "\033[32m✔\033[0m {$message}\n";
        } else {
            $this->failed++;
            $errorDetail = "FAILED: {$message} | Expected: " . var_export($expected, true) . ", got: " . var_export($actual, true);
            $this->errors[] = $errorDetail;
            echo "\033[31m✘ {$errorDetail}\033[0m\n";
        }
    }

    public function testHamzaUnification(): void
    {
        $rule = new HamzaRule();

        // Standard Alef Unification: [أ, إ, آ, ٱ] -> ا
        $input = "أحمد إبراهيم آمنة ٱستغفر";
        $expected = "احمد ابراهيم امنة استغفر";
        $this->assertSame($expected, $rule->apply($input), "Unify various Alef Hamzas to canonical Alef");

        // Custom Waw / Yeh Hamza Unification
        $input2 = "مسؤول بئر";
        $out = $rule->apply($input2, ['unify_waw_hamza' => true, 'unify_yeh_hamza' => true]);
        $this->assertSame("مسوول بير", $out, "Unify Waw and Yeh Hamzas");

        // Isolated Hamza removal option
        $input3 = "سماء";
        $out = $rule->apply($input3, ['remove_isolated_hamza' => true]);
        $this->assertSame("سما", $out, "Remove isolated Hamza");
    }

    public function testTashkeelStripping(): void
    {
        $rule = new TashkeelRule();

        $input = "السَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللهِ وَبَرَكَاتُهُ، مَرْحَبًا بِكُمْ ٱلَّذِينَ";
        $expected = "السلام عليكم ورحمة الله وبركاته، مرحبا بكم ٱلذين";
        $this->assertSame($expected, $rule->apply($input), "Strip all diacritics including Shadda, Tanwin, and Dagger Alef");

        // Keep Shadda option
        $withShadda = $rule->apply("السَّلَامُ", ['keep_shadda' => true]);
        $this->assertSame("السّلام", $withShadda, "Keep Shadda when requested");
    }

    public function testTashkeelExtraction(): void
    {
        $rule = new TashkeelRule();
        $input = "مَرْحَبًا";
        $extracted = $rule->extractDiacritics($input);

        $this->assertSame(4, count($extracted), "Extract 4 diacritics from 'مَرْحَبًا'");
        $this->assertSame("\u{064E}", $extracted[0]['char'], "First diacritic is Fatha");
        $this->assertSame("\u{0652}", $extracted[1]['char'], "Second diacritic is Sukun");
        $this->assertSame("\u{064E}", $extracted[2]['char'], "Third diacritic is Fatha");
        $this->assertSame("\u{064B}", $extracted[3]['char'], "Fourth diacritic is Fathatan");
    }

    public function testTatweelRemoval(): void
    {
        $rule = new TatweelRule();
        $input = "عـــــربـــــي";
        $this->assertSame("عربي", $rule->apply($input), "Remove Tatweel/Kashida elongations");
    }

    public function testNumeralNormalization(): void
    {
        $rule = new NumeralRule();

        $eastern = "رقم الهاتف: ٠٥٠١٢٣٤٥٦٧ و ٨٩";
        $western = "رقم الهاتف: 0501234567 و 89";
        $this->assertSame($western, $rule->toWestern($eastern), "Convert Eastern numerals to Western (0-9)");

        $persian = "شماره: ۰۹۱۲۳۴۵۶۷۸۹";
        $this->assertSame("شماره: 09123456789", $rule->toWestern($persian), "Convert Persian numerals to Western (0-9)");

        $this->assertSame("0501234567", $rule->apply("٠٥٠١٢٣٤٥٦٧", ['target' => 'western']), "NumeralRule apply western");
        $this->assertSame("٠٥٠١٢٣٤٥٦٧", $rule->apply("0501234567", ['target' => 'eastern']), "NumeralRule apply eastern");
        $this->assertSame("۰۵۰۱۲۳۴۵۶۷", $rule->apply("0501234567", ['target' => 'persian']), "NumeralRule apply persian");
    }

    public function testTehMarbutaAndAlefMaksura(): void
    {
        $rule = new TehMaksuraRule();

        // ة -> ه and ى -> ي
        $input = "مدرسة على الهدى";
        $expected = "مدرسه علي الهدي";
        $this->assertSame($expected, $rule->apply($input), "Convert Teh Marbuta to Heh and Alef Maksura to Yeh");

        // Farsi Yeh (ی) -> Arabic Yeh (ي)
        $farsiInput = "فارسی";
        $this->assertSame("فارسي", $rule->apply($farsiInput), "Harmonize Farsi Yeh to standard Arabic Yeh");

        // Reverse conversion: ه -> ة
        $rev = $rule->apply("مدرسه", ['teh_marbuta_to_heh' => false, 'heh_to_teh_marbuta' => true]);
        $this->assertSame("مدرسة", $rev, "Convert Heh to Teh Marbuta");
    }

    public function testLigaturesAndQuranic(): void
    {
        $rule = new LigatureRule();

        // Ligatures
        $bismillah = "\u{FDFD}";
        $this->assertSame("بسم الله الرحمن الرحيم", $rule->apply($bismillah), "Expand Bismillah ligature");

        $saw = "\u{FDFA}";
        $this->assertSame("صلى الله عليه وسلم", $rule->apply($saw), "Expand Sallallahou Alayhe Wasallam ligature");

        $rial = "\u{FDFC}";
        $this->assertSame("ريال", $rule->apply($rial), "Expand Rial ligature");

        // Quranic recitation mark
        $quranic = "قالَ\u{06D6} رَبِّ"; // Small high ligatures / pause marks
        $cleaned = $rule->apply($quranic);
        $this->assertSame("قالَ رَبِّ", $cleaned, "Strip Quranic recitation pause marks");
    }

    public function testWhitespaceNormalization(): void
    {
        $rule = new WhitespaceRule();

        // Multiple spaces, non-breaking space, zero-width spaces
        $input = "   كلمة    أخرى\u{00A0}وثالثة \u{200C}ورابعة   ";
        $expected = "كلمة أخرى وثالثة ورابعة";
        $this->assertSame($expected, $rule->apply($input), "Normalize multiple spaces, NBSP, zero-width chars and trim");
    }

    public function testFullWorkflow(): void
    {
        $norm = new Normalizer();

        // A complex mixed Arabic sentence
        $raw = "   بِسْمِ اللَّـهِ الرَّحْمَٰنِ الرَّحِيمِ ... أَنَا أُحِبُّ اللُّغَـةَ العَـرَبِيَّـةَ فِي سَنَةِ ٢٠٢٦م وَتَكْلِفَةُ الكِتَابِ ١٠٠﷼!   ";
        $cleaned = $norm->normalize($raw);

        // Expected transformations:
        // - بِسْمِ اللَّـهِ -> بسم الله
        // - الرَّحْمَٰنِ -> الرحمن
        // - أَنَا -> انا
        // - أُحِبُّ -> احب
        // - اللُّغَـةَ -> اللغه
        // - العَـرَبِيَّـةَ -> العربيه
        // - فِي -> في
        // - سَنَةِ -> سنه
        // - ٢٠٢٦م -> 2026م
        // - وَتَكْلِفَةُ -> وتكلفه
        // - الكِتَابِ -> الكتاب
        // - ١٠٠﷼ -> 100ريال
        $expected = "بسم الله الرحمن الرحيم ... انا احب اللغه العربيه في سنه 2026م وتكلفه الكتاب 100ريال!";
        $this->assertSame($expected, $cleaned, "Full end-to-end normalization pipeline");
    }

    public function testBatchAndStream(): void
    {
        $norm = new Normalizer();

        $batch = [
            "أَحْمَدُ",
            "مَـدْرَسَـةٌ",
            "عَلَىٰ",
        ];
        $expected = ["احمد", "مدرسه", "علي"];
        $this->assertSame($expected, $norm->batchNormalize($batch), "Batch normalize array of strings");

        // Stream normalize
        $streamIn = fopen('php://memory', 'r+');
        $streamOut = fopen('php://memory', 'r+');
        fwrite($streamIn, "السَّطْرُ الأَوَّلُ\nالسَّطْرُ الثَّانِي ٠١٢٣٤\n");
        rewind($streamIn);

        $stats = $norm->streamNormalize($streamIn, $streamOut);
        rewind($streamOut);
        $outputContent = stream_get_contents($streamOut);

        fclose($streamIn);
        fclose($streamOut);

        $expectedOut = "السطر الاول\nالسطر الثاني 01234\n";
        $this->assertSame($expectedOut, $outputContent, "Streaming line-by-line normalization");
        $this->assertSame(2, $stats['lines'], "Processed 2 lines in stream");
    }
}
