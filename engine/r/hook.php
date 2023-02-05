<?php

namespace x\page\images {
    function crop(array $images, array $lot = []) {
        foreach ($images as &$image) {
            $image = \x\page\image\crop($image, $lot);
        }
        return $images;
    }
    \Hook::set('page.images', __NAMESPACE__ . "\\crop", 2.2);
}

namespace x\page\image {
    function crop($image, array $lot = []) {
        if (!$lot || !$image || !\is_string($image)) {
            return $image;
        }
        $width = \ceil($lot[0]);
        $height = \ceil($lot[1] ?? $width);
        $quality = $lot[2] ?? -1;
        $x = \pathinfo($image, \PATHINFO_EXTENSION) ?: 'jpg';
        $path = \To::path(\long($image));
        $store = \LOT . \D . 'image' . \D . \md5($width . '.' . $height . '.' . $quality . '.' . $image) . '.' . $x;
        if (\is_file($store)) {
            $image = \To::URL($store); // Return the image cache
        } else if (null !== \State::get('x.image')) {
            $blob = new \Image(\is_file($path) ? $path : $image);
            // `$page->image($width, $height, $quality)`
            $blob->crop($width, $height)->blob($store, $quality); // Generate image cache
            $image = \To::URL($store); // Return the image cache
        } else if (\is_file($path)) {
            $image = \To::URL($path);
        }
        return $image;
    }
    \Hook::set('page.image', __NAMESPACE__ . "\\crop", 2.2);
}

namespace x\page {
    if (!\is_dir($folder = \LOT . \D . 'image')) {
        \mkdir($folder, 0775, true);
    }
    if (!\is_file($file = $folder . \D . '.htaccess')) {
        \file_put_contents($file, 'Allow from all');
    }
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