<?php

echo '<fieldset>';
echo '<legend>';
echo 'Base64';
echo '</legend>';
$blob_base64 = new Image('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAQlBMVEXmGyLlGyLsGyPzHCUDAADtGyPpGyPxHCTwGyTsHCTwHCTnGyPvHCTyHCTvGyToGyMLCwvrHCPuHCTtHCQyY7QAAAD1jjsFAAAA/UlEQVR42uWW6w6CMAxGuW7iCoMP9/6v6moUGpgUNTESz5+xbieQUjqyy8t8V+mDSn8EpY/kzaDQ5LzvroRIQ1CgJkSEMmBUwHA0BVi4ABSlO506afB8U0HH1w7TLRzPO6iKnxX/ufL5g93wkVHAcz3JkAq2kyy3r7S0co6kFI4nFdQ82rVhOV4jpYDHdq20HMd/KCUtXyWVk5JKchUKsywYU8RwOslx2XmTKksTF54VDJFNFb8lglL8hh4KmZ3fS1G2d8pCVbg9WB8E3m62CwYj+Wo2Kk8j1NZHLggc7eiWyKSSQVcYTOztyRAslTeOpDcOvl8/xF9Ufvbn6gqFZLqLl/MzHAAAAABJRU5ErkJggg==');
test([
    'height' => $blob_base64->height,
    'path' => $blob_base64->path,
    'type' => $blob_base64->type,
    'width' => $blob_base64->width
]);
unset($blob);
echo '</fieldset>';

echo '<fieldset>';
echo '<legend>';
echo 'Local';
echo '</legend>';
$blob = new Image(__DIR__ . D . 'test.jpg');
test([
    'height' => $blob->height,
    'path' => $blob->path,
    'type' => $blob->type,
    'width' => $blob->width
]);
unset($blob);
echo '</fieldset>';

echo '<fieldset>';
echo '<legend>';
echo 'Remote';
echo '</legend>';
$blob_url = new Image('https://avatars1.githubusercontent.com/u/1669261');
test([
    'height' => $blob_url->height,
    'path' => $blob_url->path,
    'type' => $blob_url->type,
    'width' => $blob_url->width
]);
unset($blob);
echo '</fieldset>';

exit;