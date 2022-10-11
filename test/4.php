<?php

foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $q) {
    $blob = new Image(__DIR__ . D . 'test.jpg');
    $blob->fit(200, 200)->store($file = __DIR__ . DS . 'out' . DS . 'quality' . DS . $q . '.jpg', $q);
    $file = new File($file);
    echo '<p><img src="' . $file->url . '?v=' . $file->time('%s') . '"><br>' . $file->size . ' (' . $q . '%)</p>';
}

exit;