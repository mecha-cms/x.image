<?php

// Default error handling for missing image
(new Image(ROOT . DS . uniqid() . '.jpg'))->crop(72)->draw(null, 50);