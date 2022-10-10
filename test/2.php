<?php

// Generate image as response body

(new Image(__DIR__ . DS . 'test.jpg'))->crop(100)->draw(null, 50);