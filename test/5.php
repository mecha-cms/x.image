<?php

// Generate blob image directly as response body

$blob = new Image('http://example.com');

status(200);
type($blob->type ?? 'image/png');

// Broken image source should produce transparent image
echo $blob->crop(100)->blob(null, 100);

exit;