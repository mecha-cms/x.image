<?php namespace x\image;

function route($content, $path) {
    $age = 60 * 60 * 24 * 30; // Cache output for 30 day(s)
    \status(200, [
        'cache-control' => 'max-age=' . $age . ', private',
        'expires' => \gmdate('D, d M Y H:i:s', $age + $_SERVER['REQUEST_TIME']) . ' GMT',
        'pragma' => 'private'
    ]);
    \type(\mime_content_type($file = \LOT . \D . 'image' . \D . \trim($path ?? "", '/')));
    echo \file_get_contents($file);
    exit;
}

$path = \trim($url->path ?? "", '/');
$route = \trim($state->x->image->route ?? 'image', '/');
if (0 === \strpos($path, $route . '/') && \is_file($file = \LOT . \D . 'image' . \D . \substr($path, \strlen($route) + 1))) {
    \Hook::set('route.image', __NAMESPACE__ . "\\route", 100);
    \Hook::set('route', function ($content, $path, $query, $hash) use ($file, $route) {
        if (false !== \strpos(',apng,avif,bmp,gif,jpeg,jpg,png,webp,xbm,xpm,', ',' . \pathinfo($file, \PATHINFO_EXTENSION) . ',')) {
            return \Hook::fire('route.image', [$content, \substr($path, \strlen($route) + 1), $query, $hash]);
        }
        return $content;
    }, 0);
}