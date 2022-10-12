<?php

if (!is_dir($folder = __DIR__ . D . 'out' . D . 'quality')) {
    mkdir($folder, 0775, true);
}

foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $v) {
    $blob = new Image(__DIR__ . D . 'test.jpg');
    $blob->fit(200, 200)->blob($file = $folder . D . $v . '.jpg', $v);
    $file = new File($file);
    echo '<figure>';
    echo '<img src="/' . $sub . short($file->url) . '?v=' . $file->time('%s') . '">';
    echo '<figcaption>';
    echo $file->size . ' (' . $v . '%)';
    echo '</figcaption>';
    echo '</figure>';
}

exit;