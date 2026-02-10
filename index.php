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
}

namespace x\image {
    function link($content) {
        // Get URL from `<img>` tag
        if (false !== ($a = \strpos($content, '<img')) && \strspn($content, " \n\r\t", $a + 4)) {
            if (false !== ($b = \strpos($content, '>', $a))) {
                $v = ' ' . \strtr(\trim(\substr($content, $a += 4, $b - $a)), ["\n" => ' ', "\r" => ' ', "\t" => ' ']);
                if (false !== ($c = \strpos($v, ' src='))) {
                    return \htmlspecialchars_decode(\trim(\strstr(\substr($v, $c + 5) . ' ', ' ', true), '\'"'));
                }
            }
        // Get URL from `<video>` tag
        } else if (false !== ($a = \strpos($content, '<video')) && \strspn($content, " \n\r\t", $a + 6)) {
            if (false !== ($b = \strpos($content, '>', $a))) {
                $v = ' ' . \strtr(\trim(\substr($content, $a += 6, $b - $a)), ["\n" => ' ', "\r" => ' ', "\t" => ' ']);
                if (false !== ($c = \strpos($v, ' poster='))) {
                    return \htmlspecialchars_decode(\trim(\strstr(\substr($v, $c + 8) . ' ', ' ', true), '\'"'));
                }
            }
        }
        return null;
    }
    function links($content) {
        $r = [];
        while (false !== ($n = \strpos($content, '<', $i ??= 0))) {
            if (null !== ($link = link(\substr($content, $n)))) {
                $r[] = $link;
            }
            $i = $n + 1;
        }
        return \array_unique($r);
    }
    function page__image($image) {
        // Skip if `image` data has been set!
        if ($image) {
            return \long($image);
        }
        // Get URL from `content` data
        return ($v = $this->content) ? link($v) : null;
    }
    function page__images($images) {
        // Skip if `images` data has been set!
        if ($images) {
            foreach ($images as &$image) {
                $image = \long($image);
            }
            unset($image);
            return $images;
        }
        // Get URL(s) from `content` data
        return ($v = $this->content) ? links($v) : [];
    }
    function route($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        $route = \trim($state->x->image->route ?? 'image', '/');
        if (!\is_file($file = \LOT . \D . 'image' . \D . ($path = \substr($path, \strlen($route) + 1)))) {
            return $content;
        }
        if (false !== \strpos(',' . x() . ',', ',' . \strtolower(\pathinfo($file, \PATHINFO_EXTENSION)) . ',')) {
            return \Hook::fire('route.image', [$content, $path, $query, $hash]);
        }
        return $content;
    }
    function route__image($content, $path) {
        if (null !== $content) {
            return $content;
        }
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
    $path = \trim($url->path ?? $state->route ?? 'index', '/');
    $route = \trim($state->x->image->route ?? 'image', '/');
    if (0 === \strpos($path, $route . '/') && \is_file(\LOT . \D . 'image' . \D . \substr($path, \strlen($route) + 1))) {
        \Hook::set('route', __NAMESPACE__ . "\\route", 0);
        \Hook::set('route.image', __NAMESPACE__ . "\\route__image", 100);
    }
    \Hook::set('page.image', __NAMESPACE__ . "\\page__image", 2.1);
    \Hook::set('page.images', __NAMESPACE__ . "\\page__images", 2.1);
}

namespace x\image\page__image {
    function crop($image, array $lot = []) {
        if (!$lot || !$image || !\is_string($image)) {
            return $image;
        }
        $image = \substr($image, 0, \strcspn($image, '?&#'));
        $w = \ceil($lot[0]);
        $h = \ceil($lot[1] ?? $w);
        $q = $lot[2] ?? -1;
        $x = \strtolower(\pathinfo($image, \PATHINFO_EXTENSION) ?: 'jpg');
        $path = \To::path(\long($image));
        $store = \LOT . \D . 'image' . \D . 't' . \D . $w . ($h !== $w ? \D . $h : "") . \D . \hash('xxh3', $image . '%' . $q) . '.' . $x;
        if (\is_file($store)) {
            $image = \To::link($store); // Return the image cache URL
        } else if (false !== \strpos(',' . \x\image\x() . ',', ',' . $x . ',')) {
            $blob = new \Image(\is_file($path) ? $path : $image);
            // `$page->image($w, $h, $q)`
            $blob->crop($w, $h)->blob($store, $q); // Generate image cache
            $image = \To::link($store); // Return the image cache URL
        } else if (\is_file($path)) {
            $image = \To::link($path);
        }
        // Convert direct image URL from folder `.\lot\image` to its proxy image URL
        \extract(\lot(), \EXTR_SKIP);
        if ($image && 0 === \strpos($image, $v = \long('/lot/image/'))) {
            $image = \substr_replace($image, \long('/' . \trim($state->x->image->route ?? 'image', '/') . '/'), 0, \strlen($v));
        }
        return $image;
    }
    \Hook::set('page.image', __NAMESPACE__ . "\\crop", 2.2);
}

namespace x\image\page__images {
    function crop(array $images, array $lot = []) {
        foreach ($images as &$image) {
            $image = \x\image\page__image\crop($image, $lot);
        }
        return $images;
    }
    \Hook::set('page.images', __NAMESPACE__ . "\\crop", 2.2);
    if (\defined("\\TEST") && 'x.image' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
        require $test;
    }
}