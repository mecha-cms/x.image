<?php

if (!extension_loaded('gd')) {
    abort('Missing %s extension.', ['PHP <a href="https://www.php.net/manual/en/book.image.php" rel="nofollow" target="_blank">gd</a>']);
}

require __DIR__ . D . 'engine' . D . 'f.php';

if (defined('TEST') && 'x.image' === TEST) {
    require __DIR__ . D . 'test.php';
    exit;
}