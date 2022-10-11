<?php

// Local image
$blob = new Image(__DIR__ . D . 'test.jpg');
test('Local', [
    'height' => $blob->height,
    'path' => $blob->path,
    'type' => $blob->type,
    'width' => $blob->width
]);

// Base64 image
$blob_base64 = new Image('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAQlBMVEXmGyLlGyLsGyPzHCUDAADtGyPpGyPxHCTwGyTsHCTwHCTnGyPvHCTyHCTvGyToGyMLCwvrHCPuHCTtHCQyY7QAAAD1jjsFAAAA/UlEQVR42uWW6w6CMAxGuW7iCoMP9/6v6moUGpgUNTESz5+xbieQUjqyy8t8V+mDSn8EpY/kzaDQ5LzvroRIQ1CgJkSEMmBUwHA0BVi4ABSlO506afB8U0HH1w7TLRzPO6iKnxX/ufL5g93wkVHAcz3JkAq2kyy3r7S0co6kFI4nFdQ82rVhOV4jpYDHdq20HMd/KCUtXyWVk5JKchUKsywYU8RwOslx2XmTKksTF54VDJFNFb8lglL8hh4KmZ3fS1G2d8pCVbg9WB8E3m62CwYj+Wo2Kk8j1NZHLggc7eiWyKSSQVcYTOztyRAslTeOpDcOvl8/xF9Ufvbn6gqFZLqLl/MzHAAAAABJRU5ErkJggg==');
test('Base64', [
    'height' => $blob_base64->height,
    'path' => $blob_base64->path,
    'type' => $blob_base64->type,
    'width' => $blob_base64->width
]);

// Remote image
$blob_url = new Image('https://avatars1.githubusercontent.com/u/1669261');
test('Remote', [
    'height' => $blob_url->height,
    'path' => $blob_url->path,
    'type' => $blob_url->type,
    'width' => $blob_url->width
]);

exit;