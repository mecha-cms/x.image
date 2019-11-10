<?php

if (!extension_loaded('zip')) {
    if (defined('DEBUG') && DEBUG) {
        Guard::abort(i('Missing %s extension.', 'PHP <code>gd</code>'));
    }
} else {
    if (null !== State::get('x.page')) {
        require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'hook.php';
    }
}

// require __DIR__ . DS . 'lot' . DS . 'asset' . DS . '.test' . DS . 'test-1.php';