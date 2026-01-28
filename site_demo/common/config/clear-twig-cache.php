<?php
/**
 * Clear Twig cache on deployment
 * This script is executed during bootstrap to ensure fresh templates
 */

$runtimePaths = [
    __DIR__ . '/../../frontend/runtime/Twig/cache',
    __DIR__ . '/../../backend/runtime/Twig/cache',
];

foreach ($runtimePaths as $cachePath) {
    if (is_dir($cachePath)) {
        $files = glob($cachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "Cleared Twig cache: $cachePath\n";
    }
}
