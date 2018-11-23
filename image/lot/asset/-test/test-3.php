<?php

// draw directly
Image::open(__DIR__ . DS . '2.jpg')
     ->resize(100)->draw();