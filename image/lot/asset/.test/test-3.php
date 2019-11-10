<?php

// Local image
$blob = new Image(__DIR__ . DS . 'test.jpg');
test('Local', [
    'path' => $blob->path,
    'width' => $blob->width,
    'height' => $blob->height,
    'type' => $blob->type
]);

// Base64 image
$blob_base64 = new Image('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAQlBMVEXmGyLlGyLsGyPzHCUDAADtGyPpGyPxHCTwGyTsHCTwHCTnGyPvHCTyHCTvGyToGyMLCwvrHCPuHCTtHCQyY7QAAAD1jjsFAAAA/UlEQVR42uWW6w6CMAxGuW7iCoMP9/6v6moUGpgUNTESz5+xbieQUjqyy8t8V+mDSn8EpY/kzaDQ5LzvroRIQ1CgJkSEMmBUwHA0BVi4ABSlO506afB8U0HH1w7TLRzPO6iKnxX/ufL5g93wkVHAcz3JkAq2kyy3r7S0co6kFI4nFdQ82rVhOV4jpYDHdq20HMd/KCUtXyWVk5JKchUKsywYU8RwOslx2XmTKksTF54VDJFNFb8lglL8hh4KmZ3fS1G2d8pCVbg9WB8E3m62CwYj+Wo2Kk8j1NZHLggc7eiWyKSSQVcYTOztyRAslTeOpDcOvl8/xF9Ufvbn6gqFZLqLl/MzHAAAAABJRU5ErkJggg==');
test('Base64', [
    'path' => $blob_base64->path,
    'width' => $blob_base64->width,
    'height' => $blob_base64->height,
    'type' => $blob_base64->type
]);

// Remote image
$blob_url = new Image('https://avatars1.githubusercontent.com/u/1669261');
test('Remote', [
    'path' => $blob_url->path,
    'width' => $blob_url->width,
    'height' => $blob_url->height,
    'type' => $blob_url->type
]);

exit;