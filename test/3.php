<?php

echo '<fieldset>';
echo '<legend>';
echo 'Base64';
echo '</legend>';
$blob_base64 = new Image('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4XiDAAAAQlBMVEXmGyLlGyLsGyPzHCUDAADtGyPpGyPxHCTwGyTsHCTwHCTnGyPvHCTyHCTvGyToGyMLCwvrHCPuHCTtHCQyY7QAAAD1jjsFAAAA/UlEQVR42uWW6w6CMAxGuW7iCoMP9/6v6moUGpgUNTESz5+xbieQUjqyy8t8V+mDSn8EpY/kzaDQ5LzvroRIQ1CgJkSEMmBUwHA0BVi4ABSlO506afB8U0HH1w7TLRzPO6iKnxX/ufL5g93wkVHAcz3JkAq2kyy3r7S0co6kFI4nFdQ82rVhOV4jpYDHdq20HMd/KCUtXyWVk5JKchUKsywYU8RwOslx2XmTKksTF54VDJFNFb8lglL8hh4KmZ3fS1G2d8pCVbg9WB8E3m62CwYj+Wo2Kk8j1NZHLggc7eiWyKSSQVcYTOztyRAslTeOpDcOvl8/xF9Ufvbn6gqFZLqLl/MzHAAAAABJRU5ErkJggg==');
echo '<pre style="background: #000; color: #fff; font: 1em/1 monospace; margin: 0; padding: 0.5em;">';
echo json_encode([
    'height' => $blob_base64->height,
    'link' => ((string) $blob_base64->link) ?: null,
    'links' => map($blob_base64->links ?? [], function ($link) {
        return ((string) $link) ?: null;
    }),
    'path' => $blob_base64->path,
    'type' => $blob_base64->type,
    'width' => $blob_base64->width,
    'x' => $blob_base64->x
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo '</pre>';
unset($blob);
echo '</fieldset>';

echo '<fieldset>';
echo '<legend>';
echo 'Local';
echo '</legend>';
$blob = new Image(__DIR__ . D . 'test.jpg');
echo '<pre style="background: #000; color: #fff; font: 1em/1 monospace; margin: 0; padding: 0.5em;">';
echo json_encode([
    'height' => $blob->height,
    'link' => ((string) $blob->link) ?: null,
    'links' => map($blob->links ?? [], function ($link) {
        return ((string) $link) ?: null;
    }),
    'path' => $blob->path,
    'type' => $blob->type,
    'width' => $blob->width,
    'x' => $blob->x
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo '</pre>';
unset($blob);
echo '</fieldset>';

echo '<fieldset>';
echo '<legend>';
echo 'Remote';
echo '</legend>';
$blob_link = new Image('https://avatars1.githubusercontent.com/u/1669261');
echo '<pre style="background: #000; color: #fff; font: 1em/1 monospace; margin: 0; padding: 0.5em;">';
echo json_encode([
    'height' => $blob_link->height,
    'link' => ((string) $blob_link->link) ?: null,
    'links' => map($blob_link->links ?? [], function ($link) {
        return ((string) $link) ?: null;
    }),
    'path' => $blob_link->path,
    'type' => $blob_link->type,
    'width' => $blob_link->width,
    'x' => $blob_link->x
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo '</pre>';
unset($blob);
echo '</fieldset>';

exit;