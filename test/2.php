<?php

// Generate blob image directly as response body

$blob = new Image(__DIR__ . D . 'test.jpg');

status(200, ['content-type' => $blob->type]);

echo $blob->crop(100)->blob(null, 100);

exit;