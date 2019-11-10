<?php

$from = __DIR__ . DS . 'test.jpg';
$to = dirname($from) . DS . 'out' . DS;

// Fit
(new Image($from))
    ->fit(200, 200)
    ->store($to . 'test.fit.200.jpg');

// Scale
(new Image($from))
    ->scale(50)
    ->store($to . 'test.scale.50.jpg');

// Scale
(new Image($from))
    ->scale(200)
    ->store($to . 'test.scale.200.jpg');

// Resize
(new Image($from))
    ->resize(200, 200)
    ->store($to . 'test.resize.200,200.jpg');

// Crop
(new Image($from))
    ->crop(72, 72)
    ->store($to . 'test.crop.72,72.jpg');

// Crop
(new Image($from))
    ->crop(130, 50, 100, 100)
    ->store($to . 'test.crop.130,50,100,100.jpg');

// Base64 image
(new Image('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAQlBMVEXmGyLlGyLsGyPzHCUDAADtGyPpGyPxHCTwGyTsHCTwHCTnGyPvHCTyHCTvGyToGyMLCwvrHCPuHCTtHCQyY7QAAAD1jjsFAAAA/UlEQVR42uWW6w6CMAxGuW7iCoMP9/6v6moUGpgUNTESz5+xbieQUjqyy8t8V+mDSn8EpY/kzaDQ5LzvroRIQ1CgJkSEMmBUwHA0BVi4ABSlO506afB8U0HH1w7TLRzPO6iKnxX/ufL5g93wkVHAcz3JkAq2kyy3r7S0co6kFI4nFdQ82rVhOV4jpYDHdq20HMd/KCUtXyWVk5JKchUKsywYU8RwOslx2XmTKksTF54VDJFNFb8lglL8hh4KmZ3fS1G2d8pCVbg9WB8E3m62CwYj+Wo2Kk8j1NZHLggc7eiWyKSSQVcYTOztyRAslTeOpDcOvl8/xF9Ufvbn6gqFZLqLl/MzHAAAAABJRU5ErkJggg=='))
    ->crop(30, 30)
    ->store($to . 'test-base64.crop.30,30.jpg');

// Remote image
// $_SERVER['HTTP_USER_AGENT'] = 'Mecha/2.2.0 (+https://mecha-cms.com)';
(new Image('https://avatars1.githubusercontent.com/u/1669261'))
    ->crop(30, 30)
    ->store($to . 'test-remote.crop.30,30.jpg');

// Generate result…
if (is_dir($to)) {
    Hook::set('get', function() use($from, $to) {
        echo '<figure style="background:#ccc;border:1px solid;padding:1em;margin:0 0 1em;text-align:center;">';
        echo '<img src="' . To::URL($from) . '?v=' . filemtime($from) . '">';
        echo '<figcaption style="margin-top:1em;">' . basename($from) . '</figcaption>';
        echo '</figure>';
        foreach (glob($to . DS . '*.*') as $v) {
            echo '<figure style="border:1px solid;padding:1em;margin:0 0 1em;text-align:center;">';
            echo '<img src="' . To::URL($v) . '?v=' . filemtime($v) . '">';
            echo '<figcaption style="margin-top:1em;">' . basename($v) . '</figcaption>';
            echo '</figure>';
        }
        exit;
    });
}