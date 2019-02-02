<?php

foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $quality) {
    Image::open(__DIR__ . DS . '2.jpg')->resize(100)->saveTo($file = __DIR__ . DS . 'result' . DS . 'quality-test' . DS . $quality . '.jpg', $quality);
    echo '<p>' . Asset::jpg($file) . '<br>' . (new File($file))->size() . '</p>';
}