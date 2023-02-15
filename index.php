<?php

if (!extension_loaded('gd')) {
    abort(i('Missing %s extension.', ['PHP <a href="https://www.php.net/manual/en/book.image.php" rel="nofollow" target="_blank">gd</a>']));
}

if (!is_dir($folder = LOT . D . 'image')) {
    mkdir($folder, 0775, true);
}

if (defined('TEST') && 'x.image' === TEST && is_file($test = __DIR__ . D . 'test.php')) {
    require $test;
}

require __DIR__ . D . 'engine' . D . 'r' . D . 'page.php';
require __DIR__ . D . 'engine' . D . 'r' . D . 'route.php';