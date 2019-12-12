<?php

namespace _\lot\x\page\images {
    function crop(array $images, array $lot = []) {
        foreach ($images as &$image) {
            $image = \_\lot\x\page\image\crop($image, $lot);
        }
        return $images;
    }
    \Hook::set('page.images', __NAMESPACE__ . "\\crop", 2.2);
}

namespace _\lot\x\page\image {
    function crop($image, array $lot = []) {
        if (!$lot || !$image || !\is_string($image)) {
            return $image;
        }
        if (0 === \strpos($image, \To::URL(\LOT) . '/asset/.cache/')) {
            return $image;
        }
        $w = \ceil($lot[0]);
        $h = \ceil($lot[1] ?? $w);
        $q = $lot[2] ?? null;
        $x = \Path::X($image) ?? 'jpg';
        $path = \To::path(\URL::long($image));
        $cache = \LOT . \DS . 'asset' . \DS . '.cache' . \DS . \trim(\chunk_split(\md5($w . '.' . $h . '.' . $q . '.' . $image), 2, \DS), \DS) . '.' . $x;
        if ($image && null !== \State::get('x.image')) {
            $blob = new \Image(\is_file($path) ? $path : $image);
            // `$page->image($width, $height, $quality)`
            $blob->crop($w, $h)->store($cache, $q); // Generate image cache
            $image = \To::URL($cache); // Return the image cache
        } else {
            $image = \To::URL($path);
        }
        return $image;
    }
    \Hook::set('page.image', __NAMESPACE__ . "\\crop", 2.2);
}

namespace _\lot\x\page {
    function image($image) {
        // Skip if `image` data has been set!
        if ($image) {
            return \URL::long($image);
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
                $image = \URL::long($image);
            }
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