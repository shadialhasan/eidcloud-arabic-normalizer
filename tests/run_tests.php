<?php

declare(strict_types=1);

// Zero-dependency test runner
spl_autoload_register(function (string $class) {
    $prefixes = [
        'EidCloud\\ArabicNormalizer\\Tests\\' => dirname(__DIR__) . '/tests/',
        'EidCloud\\ArabicNormalizer\\' => dirname(__DIR__) . '/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

use EidCloud\ArabicNormalizer\Tests\NormalizerTest;

echo "============================================================\n";
echo " EidCloud Arabic Normalizer - Test Suite (PHP " . PHP_VERSION . ")\n";
echo "============================================================\n";

$tester = new NormalizerTest();
$success = $tester->runAll();

exit($success ? 0 : 1);
