<?php

class Image extends File {

    protected $b;
    protected $blob;
    protected $links;
    protected $lot;

    protected function _blob(): void {
        if ($this->b instanceof GdImage) {
            return;
        }
        $prefix = "x\\image\\from\\x\\";
        $this->_lot();
        $x = $this->lot['x'] ?? 'png';
        if ($blob = $this->blob) {
            $b = x\image\from\blob($blob);
        } else if ($link = $this->links[0] ?? 0) {
            if ($blob = fetch($link)) {
                $b = x\image\from\blob($this->blob = $blob);
            }
        } else if ($path = $this->path) {
            if (function_exists($task = $prefix . $x)) {
                $b = $task($path);
            }
        }
        if (empty($b)) {
            $b = x\image\from\blob("");
            $lot = [];
            $lot['height'] = $lot['width'] = 1;
            $lot['type'] = 'image/png';
            $lot['x'] = 'png';
            $this->lot = $lot;
            ob_start();
            x\image\to\x\png($b);
            $this->blob = ob_get_clean();
        }
        $this->b = $b;
    }

    protected function _lot(): void {
        if (array_key_exists('x', $this->lot)) {
            return;
        }
        $data = $lot = [];
        if (($blob = $this->blob) && ($r = getimagesizefromstring($blob, $data))) {
            [$width, $height, $x] = $r;
        } else if (($link_or_path = $this->links[0] ?? $this->path)) {
            if (false !== strpos($link_or_path, '://') && parse_url($link_or_path, PHP_URL_HOST) === ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']) && ($path = To::path($link_or_path))) {
                $this->path = $link_or_path = $path;
            }
            if ($r = getimagesize($link_or_path, $data)) {
                [$width, $height, $x] = $r;
            }
        } else {
            [$width, $height, $x] = ($r = [1, 1, null]);
        }
        $x = is_int($x) ? image_type_to_extension($x, false) : $x;
        $lot['bits'] = $r['bits'] ?? null;
        $lot['channels'] = $r['channels'] ?? null;
        $lot['data'] = $data;
        $lot['height'] = max(1, $height);
        $lot['type'] = $r['mime'] ?? null;
        $lot['width'] = max(1, $width);
        $lot['x'] = 'jpeg' === $x ? 'jpg' : ($x ?: null);
        $this->lot = $lot;
    }

    protected function _resize(?int $max_width = null, ?int $max_height = null, $ratio = true, $crop = false) {
        $this->_blob();
        $old_b = $this->b;
        $old_height = $this->lot['height'];
        $old_width = $this->lot['width'];
        $new_height = $max_height = max(1, $max_height ?? $old_height);
        $new_width = $max_width = max(1, $max_width ?? $old_width);
        $x = $y = 0;
        if ($ratio) {
            // Don’t do anything if the new image size is bigger than the original image size
            if (1 === $ratio && $old_width < $max_width && $old_height < $max_height) {
                return $old_b;
            }
            if ($crop) {
                $old_ratio = $old_width / $old_height;
                $new_ratio = $max_width / $max_height;
                // Wider than the thumbnail (in aspect ratio sense)
                if ($old_ratio > $new_ratio) {
                    $new_width = $old_width * $max_height / $old_height;
                    // Wider than the image
                } else {
                    $new_height = $old_height * $max_width / $old_width;
                }
                // Calculate where to crop based on the center of the image
                $width_ratio = $old_width / $new_width;
                $height_ratio = $old_height / $new_height;
                $x = round((($new_width - $max_width) / 2) * $width_ratio);
                $y = round((($new_height - $max_height) / 2) * $height_ratio);
                $new_b = imagecreatetruecolor((int) $max_width, (int) $max_height);
            } else {
                if ($old_width > $old_height) {
                    $ratio = max($old_width, $old_height) / max($max_width, $max_height);
                } else {
                    $ratio = max($old_width, $old_height) / min($max_width, $max_height);
                }
                $new_width = $old_width / $ratio;
                $new_height = $old_height / $ratio;
                $new_b = imagecreatetruecolor((int) $new_width, (int) $new_height);
            }
        } else {
            $new_b = imagecreatetruecolor((int) $max_width, (int) $max_height);
        }
        // Draw…
        imagealphablending($new_b, false);
        imagesavealpha($new_b, true);
        imagefill($new_b, 0, 0, imagecolorallocatealpha($new_b, 0, 0, 0, 127));
        imagecopyresampled($new_b, $old_b, 0, 0, $x, $y, $new_width = (int) $new_width, $new_height = (int) $new_height, $old_width, $old_height);
        $this->lot['height'] = $new_height;
        $this->lot['width'] = $new_width;
        return $new_b;
    }

    protected function _v85() {
        static $v85;
        return $v85 ??= version_compare(PHP_VERSION, '8.5', '>=');
    }

    public function __construct($path = null) {
        $this->links = $this->lot = [];
        if (is_string($path)) {
            // Base64
            if (0 === strpos($path, 'data:image/') && false !== strpos($path, ';base64,')) {
                $this->blob = base64_decode(substr(strstr($path = rawurldecode($path), ','), 1));
                $this->lot['type'] = substr(strstr($path, ';', true), 5);
                $path = null;
            // Link
            } else if (false !== strpos($path, '://') || 0 === strpos($path, '/') && 0 !== strpos($path, PATH . D)) {
                $this->links[] = long($path);
                $path = null;
            // Path
            } else if (0 === strpos($path, PATH . D) && is_file($path)) {
                $path = stream_resolve_include_path($path);
            }
        }
        parent::__construct($path);
    }

    public function __destruct() {
        !$this->_v85() && $this->b instanceof GdImage && imagedestroy($this->b);
        $this->blob = null;
        $this->lot = [];
    }

    public function __get(string $key): mixed {
        return parent::__get($key) ?? $this->lot[$key] ?? null;
    }

    public function __toString() {
        return $this->blob(null, 100);
    }

    public function blob(...$lot) {
        $this->_blob();
        $prefix = "\\x\\image\\to\\x\\";
        if (function_exists($task = $prefix . ($x = $this->lot['x'] ?? 'png'))) {
            array_unshift($lot, $this->b);
            if (is_string($file = $lot[1] ?? 0) && 0 === strpos($file, PATH . D) && !is_dir($folder = dirname($file))) {
                mkdir($folder, 0775, true);
            }
            // `->blob('.\path\to\file.jpg', 60)`
            if ('jpg' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range to 0–100
                $lot[2] = b($lot[2], [0, 100]);
            // `->blob('.\path\to\file.png', 60)`
            } else if ('png' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range of 0–100 to 0–9
                $lot[2] = m(b($lot[2], [0, 100]), [0, 100], [0, 9]);
            }
            ob_start();
            $task(...$lot);
            if ($file && !$this->_v85() && $this->b instanceof GdImage) {
                imagedestroy($this->b);
                $this->b = null;
            }
            return ob_get_clean();
        }
        return "";
    }

    public function crop(...$lot) {
        $this->_blob();
        // `->crop(72, 72)`
        if (count($lot) < 3) {
            $w = (int) max(1, $lot[0] ?? 1);
            $h = (int) max(1, $lot[1] ?? $w);
            $this->b = $this->_resize($w, $h, 1, true);
            return $this;
        }
        // `->crop(4, 4, 72, 72)`
        $x = (int) max(0, $lot[0]);
        $y = (int) max(0, $lot[1] ?? $x);
        $w = (int) max(1, $lot[2] ?? 1);
        $h = (int) max(1, $lot[3] ?? $w);
        $b = imagecreatetruecolor($w, $h);
        imagealphablending($b, false);
        imagesavealpha($b, true);
        imagefill($b, 0, 0, imagecolorallocatealpha($b, 0, 0, 0, 127));
        imagecopy($b, $this->b, 0, 0, $x, $y, $this->lot['width'] = $w, $this->lot['height'] = $h);
        $this->b = $b;
        return $this;
    }

    public function fit(...$lot) {
        $this->_blob();
        $w = max(1, $lot[0] ?? $this->lot['width']);
        $h = max(1, $lot[1] ?? $this->lot['height']);
        $this->b = $this->_resize($w, $h, 1, false);
        return $this;
    }

    public function links() {
        if ($links = $this->links) {
            $r = [];
            foreach ($links as $link) {
                $r[] = new Link($link);
            }
            return $r;
        }
        return null;
    }

    public function type() {
        if (!array_key_exists($key = __FUNCTION__, $this->lot)) {
            $this->_lot();
        }
        return $this->lot[$key] ?? parent::type();
    }

    public function height() {
        if (!array_key_exists($key = __FUNCTION__, $this->lot)) {
            $this->_lot();
        }
        return $this->lot[$key] ?? null;
    }

    public function resize(...$lot) {
        $this->_blob();
        $w = max(1, $lot[0] ?? $this->lot['width']);
        $h = max(1, $lot[1] ?? $this->lot['height']);
        $this->b = $this->_resize($w, $h, false, false);
        return $this;
    }

    public function scale(int $percent) {
        $this->_blob();
        $percent = b($percent, [0]) / 100;
        $w = ceil($percent * max(1, $this->lot['width']));
        $h = ceil($percent * max(1, $this->lot['height']));
        $this->b = $this->_resize($w, $h, true, false);
        return $this;
    }

    public function width() {
        if (!array_key_exists($key = __FUNCTION__, $this->lot)) {
            $this->_lot();
        }
        return $this->lot[$key] ?? null;
    }

    public function x() {
        if (!array_key_exists($key = __FUNCTION__, $this->lot)) {
            $this->_lot();
        }
        return $this->lot[$key] ?? parent::x();
    }

}