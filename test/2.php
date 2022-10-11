<?php

// Generate image as response body

(new Image(__DIR__ . D . 'test.jpg'))->crop(100)->blob(null, 50);