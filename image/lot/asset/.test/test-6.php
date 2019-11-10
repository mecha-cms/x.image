<?php

// Custom error handling for missing image
Image::$state['path'] = __DIR__ . DS . 'error.png';

(new Image(ROOT . DS . uniqid() . '.jpg'))->crop(72)->draw(null, 50);