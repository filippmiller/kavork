<?php
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');

// Work around PHP 8.2 nested ternary fatal error in yii2-grid ColumnTrait.
spl_autoload_register(function ($class) {
    if ($class !== 'kartik\\grid\\ColumnTrait') {
        return;
    }

    $basePath = dirname(dirname(__DIR__));
    $vendorFile = $basePath . '/vendor/kartik-v/yii2-grid/src/ColumnTrait.php';
    if (!is_file($vendorFile)) {
        return;
    }

    $contents = file_get_contents($vendorFile);
    if ($contents === false) {
        return;
    }

    $needsPatch = strpos(
        $contents,
        "is_array(\$this->format) && isset(\$this->format[1]) ? \$this->format[1] :\n                        isset(\$formatter->currencyCode) ? \$formatter->currencyCode . ' ' : '';"
    ) !== false;

    if (!$needsPatch) {
        require $vendorFile;
        return;
    }

    $patchedDir = $basePath . '/frontend/runtime/compat/kartik-grid';
    $patchedFile = $patchedDir . '/ColumnTrait.php';
    if (!is_dir($patchedDir)) {
        @mkdir($patchedDir, 0775, true);
    }

    if (!is_file($patchedFile) || filemtime($patchedFile) < filemtime($vendorFile)) {
        $patchedContents = str_replace(
            "is_array(\$this->format) && isset(\$this->format[1]) ? \$this->format[1] :\n                        isset(\$formatter->currencyCode) ? \$formatter->currencyCode . ' ' : '';",
            "is_array(\$this->format) && isset(\$this->format[1]) ? \$this->format[1] :\n                        (isset(\$formatter->currencyCode) ? \$formatter->currencyCode . ' ' : '');",
            $contents
        );
        file_put_contents($patchedFile, $patchedContents);
    }

    require $patchedFile;
}, true, true);
