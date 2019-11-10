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
        if (0 === \strpos($image, \To::URL(\ASSET) . '/.cache/')) {
            return $image;
        }
        $w = \ceil($lot[0]);
        $h = \ceil($lot[1] ?? $w);
        $q = $lot[2] ?? null;
        $x = \Path::X($image) ?? 'jpg';
        $path = \To::path(\URL::long($image));
        $path = \ASSET . \DS . '.cache' . \DS . \md5($image . ';' . $w . 'x' . $h . ';' . ($q ?? "\0")) . '.' . $x;
        if (\is_file($path)) {
            return \To::URL($path);
        } else if ($image && null !== \State::get('x.image')) {
            $blob = new \Image($image);
            $blob->crop($w, $h)->store($path, $q);
            return \To::URL($path);
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
            if (false !== \strpos($content, '<img ')) {
                $parser = new \DOMDocument('1.0', 'UTF-8');
                $parser->strictErrorChecking = false;
                $parser->validateOnParse = true;
                @$parser->loadHTML($content); // TODO
                if ($img = $parser->getElementsByTagName('img')->item(0)) {
                    if ("" !== ($src = $img->getAttribute('src'))) {
                        return $src;
                    }
                }
            // Get URL from `<video>` tag
            } else if (false !== \strpos($content, '<video ')) {
                $parser = new \DOMDocument('1.0', 'UTF-8');
                $parser->strictErrorChecking = false;
                $parser->validateOnParse = true;
                @$parser->loadHTML($content); // TODO
                if ($video = $parser->getElementsByTagName('video')->item(0)) {
                    if ("" !== ($poster = $video->getAttribute('poster'))) {
                        return $poster;
                    }
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
            if (false !== \strpos($content, '<img ')) {
                $parser = new \DOMDocument('1.0', 'UTF-8');
                $parser->strictErrorChecking = false;
                $parser->validateOnParse = true;
                @$parser->loadHTML($content); // TODO
                foreach ($parser->getElementsByTagName('img') as $img) {
                    if ("" !== ($src = $img->getAttribute('src'))) {
                        $images[] = $src;
                    }
                }
            // Get URL from `<video>` tag
            } else if (false !== \strpos($content, '<video ')) {
                $parser = new \DOMDocument('1.0', 'UTF-8');
                $parser->strictErrorChecking = false;
                $parser->validateOnParse = true;
                @$parser->loadHTML($content); // TODO
                foreach ($parser->getElementsByTagName('video') as $video) {
                    if ("" !== ($poster = $video->getAttribute('poster'))) {
                        $images[] = $poster;
                    }
                }
            }
        }
        return $images;
    }
    \Hook::set('page.image', __NAMESPACE__ . "\\image", 2.1);
    \Hook::set('page.images', __NAMESPACE__ . "\\images", 2.1);
}