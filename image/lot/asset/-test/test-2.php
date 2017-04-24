<?php

// chainning
Image::take(__DIR__ . DS . '2.jpg')
     ->resize(300)
     ->colorize('#ffa500', .5)
     ->saveTo(__DIR__ . DS . 'result' . DS . 'flower-image-test-2.jpg');

echo Asset::jpg(__DIR__ . DS . 'result' . DS . 'flower-image-test-2.jpg');