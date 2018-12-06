<?php

if (!extension_loaded('gd')) {
    if (defined('DEBUG') && DEBUG) {
        Guardian::abort('<a href="http://www.php.net/manual/en/book.image.php" title="PHP &#x2013; Image Processing and GD" rel="nofollow" target="_blank">PHP GD</a> extension is not installed on your web server.');
    }
} else {}

/*
require EXTEND . DS . 'image' . DS . 'lot' . DS . 'asset' . DS . '-test' . DS . 'test-5.php';

exit;
*/