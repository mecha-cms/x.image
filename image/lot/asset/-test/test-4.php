<?php

var_dump(
    Image::inspect(__DIR__ . DS . '2.jpg'),
    Image::inspect(__DIR__ . DS . '2.jpg', 'width', 0),
    Image::inspect(__DIR__ . DS . '2.jpg', 'foo', 0)
);