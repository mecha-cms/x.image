<?php

if (!extension_loaded('gd') && !extension_loaded('imagick')) {
    if (defined('DEBUG') && DEBUG) {
        Guard::abort(i('Missing %s extension.', 'PHP <code>gd</code> or <code>imagick</code>'));
    }
} else {
    if (extension_loaded('gd')) {
        class_alias("GD\\Image", 'Image');
    // TODO: Add support for `ImageMagick`
    } else if (extension_loaded('imagick')) {
        class_alias("ImageMagick\\Image", 'Image');
    }
    if (null !== State::get('x.page')) {
        require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'hook.php';
    }
}

require __DIR__ . DS . 'engine' . DS . 'f.php';

// require __DIR__ . DS . 'lot' . DS . 'asset' . DS . '.test' . DS . 'test-1.php';