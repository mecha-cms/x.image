<?php

$from = __DIR__ . D . 'test.jpg';
$folder = dirname($from) . D . 'out';

if (!is_dir($folder)) {
    mkdir($folder, 0775, true);
}

// Base64
file_put_contents($folder . D . 'base64.png', (new Image(file_get_contents(__DIR__ . D . 'test.txt')))->crop(50, 50));
// Remote
file_put_contents($folder . D . 'link.png', (new Image('https://avatars1.githubusercontent.com/u/1669261'))->crop(50, 50));

// Crop X:Y:W:H
file_put_contents($folder . D . 'crop.100,50,100,100.jpg', (new Image($from))->crop(100, 50, 100, 100));
// Crop W:H
file_put_contents($folder . D . 'crop.72,72.jpg', (new Image($from))->crop(72, 72));
// Fit
file_put_contents($folder . D . 'fit.200.jpg', (new Image($from))->fit(200, 200));
// Resize
file_put_contents($folder . D . 'resize.200,200.jpg', (new Image($from))->resize(200, 200));
// Scale
file_put_contents($folder . D . 'scale.50.jpg', (new Image($from))->scale(50));

echo '<fieldset>';
echo '<legend>';
echo 'Input(s)';
echo '</legend>';
echo '<figure>';
echo '<img alt="" src="/' . $sub . short(To::URL($from)) . '">';
echo '<figcaption>';
echo $from;
echo '</figcaption>';
echo '</figure>';
echo '<figure>';
echo '<img alt="" src="' . file_get_contents(__DIR__ . D . 'test.txt') . '">';
echo '<figcaption>';
echo substr(file_get_contents(__DIR__ . D . 'test.txt'), 0, 100) . '&hellip;';
echo '</figcaption>';
echo '</figure>';
echo '<figure>';
echo '<img alt="" src="https://avatars1.githubusercontent.com/u/1669261">';
echo '<figcaption>';
echo 'https://avatars1.githubusercontent.com/u/1669261';
echo '</figcaption>';
echo '</figure>';
echo '</fieldset>';

echo '<fieldset>';
echo '<legend>';
echo 'Output(s)';
echo '</legend>';
foreach (glob($folder . D . '*.{jpg,png}', GLOB_BRACE) as $v) {
    echo '<figure>';
    echo '<img alt="" src="/' . $sub . short(To::URL($v)) . '?v=' . filemtime($v) . '">';
    echo '<figcaption>';
    echo basename($v);
    echo '</figcaption>';
    echo '</figure>';
}
echo '</fieldset>';