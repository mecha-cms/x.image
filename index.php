<?php

namespace {
    if (!\extension_loaded('gd')) {
        \abort(\i('Missing %s extension.', ['PHP <a href="https://www.php.net/manual/en/book.image.php" rel="nofollow" target="_blank">gd</a>']));
    }
    function image(...$lot) {
        return \Image::from(...$lot);
    }
    if (!\is_dir($folder = \LOT . \D . 'image')) {
        \mkdir($folder, 0775, true);
    }
    if (\defined('TEST') && 'x.image' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
        require $test;
    }
}

namespace x\image {
    function route($content, $path) {
        $age = 60 * 60 * 24 * 365; // Cache output for 1 year
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
}

namespace x\image\page {
    function image($image) {
        // Skip if `image` data has been set!
        if ($image) {
            return \long($image);
        }
        // Get URL from `content` data
        if ($content = $this->content) {
            // Get URL from `<img>` tag
            if (false !== \strpos($content, '<img ') && \preg_match('/<img(\s[^>]+)>/', $content, $m)) {
                if (false !== \strpos($m[1], ' src=')) {
                    return \htmlspecialchars_decode(\trim(\strstr(\substr(\strstr($m[1], ' src='), 5) . ' ', ' ', true), '\'"'));
                }
            // Get URL from `<video>` tag
            } else if (false !== \strpos($content, '<video ') && \preg_match('/<video(\s[^>]+)>/', $content, $m)) {
                if (false !== \strpos($m[1], ' poster=')) {
                    return \htmlspecialchars_decode(\trim(\strstr(\substr(\strstr($m[1], ' poster='), 8) . ' ', ' ', true), '\'"'));
                }
            }
        }
        return null;
    }
    function images($images) {
        // Skip if `images` data has been set!
        if ($images) {
            foreach ($images as &$image) {
                $image = \long($image);
            }
            unset($image);
            return $images;
        }
        $images = [];
        // Get URL from `content` data
        if ($content = $this->content) {
            // Get URL from `<img>` tag
            if (false !== \strpos($content, '<img ') && \preg_match_all('/<img(\s[^>]+)>/', $content, $m)) {
                foreach ($m[1] as $v) {
                    if (false !== \strpos($v, ' src=')) {
                        $images[] = \htmlspecialchars_decode(\trim(\strstr(\substr(\strstr($v, ' src='), 5) . ' ', ' ', true), '\'"'));
                    }
                }
            // Get URL from `<video>` tag
            } else if (false !== \strpos($content, '<video ') && \preg_match_all('/<video(\s[^>]+)>/', $content, $m)) {
                foreach ($m[1] as $v) {
                    if (false !== \strpos($v, ' poster=')) {
                        $images[] = \htmlspecialchars_decode(\trim(\strstr(\substr(\strstr($v, ' poster='), 8) . ' ', ' ', true), '\'"'));
                    }
                }
            }
        }
        return $images;
    }
    \Hook::set('page.image', __NAMESPACE__ . "\\image", 2.1);
    \Hook::set('page.images', __NAMESPACE__ . "\\images", 2.1);
}

namespace x\image\page\image {
    function crop($image, array $lot = []) {
        if (!$lot || !$image || !\is_string($image)) {
            return $image;
        }
        $width = \ceil($lot[0]);
        $height = \ceil($lot[1] ?? $width);
        $quality = $lot[2] ?? -1;
        $x = \pathinfo($image, \PATHINFO_EXTENSION) ?: 'jpg';
        $path = \To::path(\long($image));
        $store = \LOT . \D . 'image' . \D . 't' . \D . $width . ($height !== $width ? \D . $height : "") . \D . \dechex(\crc32($image . $quality)) . '.' . $x;
        if (\is_file($store)) {
            $image = \To::URL($store); // Return the image cache URL
        } else if (null !== \State::get('x.image')) {
            $blob = new \Image(\is_file($path) ? $path : $image);
            // `$page->image($width, $height, $quality)`
            $blob->crop($width, $height)->blob($store, $quality); // Generate image cache
            $image = \To::URL($store); // Return the image cache URL
        } else if (\is_file($path)) {
            $image = \To::URL($path);
        }
        // Convert direct image URL from folder `.\lot\image` to its proxy image URL
        \extract($GLOBALS, \EXTR_SKIP);
        if ($image && 0 === \strpos($image, $url . '/lot/image/')) {
            $image = \substr_replace($image, $url . '/' . \trim($state->x->image->route ?? 'image', '/') . '/', 0, \strlen($url . '/lot/image/'));
        }
        return $image;
    }
    \Hook::set('page.image', __NAMESPACE__ . "\\crop", 2.2);
}

namespace x\image\page\images {
    function crop(array $images, array $lot = []) {
        foreach ($images as &$image) {
            $image = \x\image\page\image\crop($image, $lot);
        }
        return $images;
    }
    \Hook::set('page.images', __NAMESPACE__ . "\\crop", 2.2);
}