<?php

if (!extension_loaded('gd')) {
    abort(i('Missing %s extension.', ['PHP <a href="https://www.php.net/manual/en/book.image.php" rel="nofollow" target="_blank">gd</a>']));
}

if (defined('TEST') && 'x.image' === TEST && is_file($test = __DIR__ . D . 'test.php')) {
    require $test;
}

require __DIR__ . D . 'engine' . D . 'r' . D . 'hook.php';