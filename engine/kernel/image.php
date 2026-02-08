<?php

class Image extends File {

    protected function _8__5() {
        return version_compare(PHP_VERSION, '8.5', '>=');
    }

    protected function _resize(?int $max_width = null, ?int $max_height = null, $ratio = true, $crop = false) {
        $blob = x\image\from($this->blob(null, 100));
        $old_height = $this->h;
        $old_width = $this->w;
        $new_height = $max_height = $max_height ?? $old_height;
        $new_width = $max_width = $max_width ?? $old_width;
        $x = 0;
        $y = 0;
        $current_ratio = round($old_width / $old_height, 2);
        $desired_ratio_after = round($max_width / $max_height, 2);
        $desired_ratio_before = round($max_height / $max_width, 2);
        if ($ratio) {
            // Don’t do anything if the new image size is bigger than the original image size
            if (1 === $ratio && $old_width < $max_width && $old_height < $max_height) {
                return $blob;
            }
            if ($crop) {
                // Wider than the thumbnail (in aspect ratio sense)
                if ($current_ratio > $desired_ratio_after) {
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
                $new_blob = imagecreatetruecolor((int) $max_width, (int) $max_height);
            } else {
                if ($old_width > $old_height) {
                    $ratio = max($old_width, $old_height) / max($max_width, $max_height);
                } else {
                    $ratio = max($old_width, $old_height) / min($max_width, $max_height);
                }
                $new_width = $old_width / $ratio;
                $new_height = $old_height / $ratio;
                $new_blob = imagecreatetruecolor((int) $new_width, (int) $new_height);
            }
        } else {
            $new_blob = imagecreatetruecolor((int) $max_width, (int) $max_height);
        }
        // Draw…
        imagealphablending($new_blob, false);
        imagesavealpha($new_blob, true);
        imagefill($new_blob, 0, 0, imagecolorallocatealpha($new_blob, 0, 0, 0, 127));
        imagecopyresampled($new_blob, $blob, 0, 0, $x, $y, (int) $new_width, (int) $new_height, $old_width, $old_height);
        return $new_blob;
    }

    protected function _type(string $blob) {
        if (extension_loaded('fileinfo')) {
            $info = finfo_open();
            return finfo_buffer($info, $blob, FILEINFO_MIME_TYPE);
        }
        static $get_byte_from_hex_string, $get_image_type;
        // <https://stackoverflow.com/a/9899096>
        $get_byte_from_hex_string ??= function ($data) {
            $bytes = [];
            for ($i = 0; $i < strlen($data); $i += 2) {
                $bytes[] = chr(hexdec(substr($data, $i, 2)));
            }
            return implode($bytes);
        };
        $get_image_type ??= function ($data) {
            foreach ([
                '424D' => 'bmp',
                '474946' => 'gif',
                'FFD8' => 'jpeg',
                '89504E470D0A1A0A' => 'png',
                '4949' => 'tiff',
                '4D4D' => 'tiff'
            ] as $k => $v) {
                $bytes = $get_byte_from_hex_string($k);
                if ($bytes === substr($data, 0, strlen($bytes))) {
                    return 'image/' . $v;
                }
            }
            return null;
        };
        return $get_image_type($blob);
    }

    protected $h = 0;
    protected $w = 0;

    protected static $cache = [];

    public $blob;
    public $path;
    public $type;

    public function __construct(?string $path = null) {
        $blob = $type = false;
        $prefix = "x\\image\\from\\x\\";
        if (is_string($path)) {
            // Create image from Base64 URL
            if (0 === strpos($path, 'data:image/') && false !== strpos($path, ';base64,')) {
                $this->blob = $blob = x\image\from($v = base64_decode(substr(strstr($path = rawurldecode($path), ','), 1)));
                $this->type = $type = $this->_type($v) ?? substr(strstr($path, ';', true), 5);
            // Create image from remote URL
            } else if (0 !== strpos($path, PATH) && (false !== strpos($path, '://') || 0 === strpos($path, '/'))) {
                $path = To::path($link = long($path));
                // Load from cache
                if (isset(self::$cache[$link])) {
                    [$blob, $type] = self::$cache[$link];
                    $this->blob = $blob;
                    $this->type = $type;
                // Local URL
                } else if (0 === strpos($path, PATH) && is_file($path)) {
                    $this->type = $type = mime_content_type($path) ?: $this->_type(file_get_contents($path));
                    if (0 === strpos($type, 'image/') && function_exists($task = $prefix . substr(strstr($type, '/'), 1))) {
                        $this->blob = $blob = call_user_func($task, $path);
                    }
                    $this->path = $path;
                // Fetch URL
                } else if ($v = fetch($link)) {
                    self::$cache[$link] = [
                        $this->blob = $blob = x\image\from($v),
                        $this->type = $type = $this->_type($v)
                    ];
                    // Broken image URL
                    if (0 !== strpos($type, 'image/')) {
                        self::$cache[$link][1] = $this->type = $type = 'image/png';
                    }
                    $this->blob = $blob;
                }
            // Create image from local file
            } else if (is_file($path)) {
                $this->type = $type = mime_content_type($path) ?: $this->_type(file_get_contents($path));
                if (0 === strpos($type, 'image/')) {
                    // Try with image type by default
                    if (function_exists($task = $prefix . substr(strstr($type, '/'), 1))) {
                        $this->blob = $blob = call_user_func($task, $path);
                    }
                    // Try with image extension if `$this->blob` is `false`
                    if (!$blob && function_exists($task = $prefix . pathinfo($path, PATHINFO_EXTENSION))) {
                        $this->blob = $blob = call_user_func($task, $path);
                    }
                    // Last try?
                    if (!$blob) {}
                }
                $this->path = $path;
            }
        }
        // Else, handle invalid value
        if (!$blob && !$type) {
            $this->blob = $blob = imagecreatetruecolor(1, 1);
            $this->type = $type = 'image/png';
            imagealphablending($blob, false);
            imagesavealpha($blob, true);
            imagefill($blob, 0, 0, imagecolorallocatealpha($blob, 0, 0, 0, 127));
        }
        if ($blob) {
            $this->h = imagesy($blob);
            $this->w = imagesx($blob);
        }
        $this->blob = $blob;
        $this->type = $type;
    }

    public function __destruct() {
        if (!$this->_8__5()) {
            return;
        }
        $this->blob && $this->blob instanceof GdImage && imagedestroy($this->blob);
    }

    public function __toString() {
        return $this->blob(null, 100);
    }

    public function blob(...$lot) {
        $prefix = "\\x\\image\\to\\x\\";
        $x = 0 === strpos($type = $this->type, 'image/') ? substr($type, 6) : 'png';
        if (function_exists($task = $prefix . $x)) {
            array_unshift($lot, $this->blob);
            if (is_string($lot[1] ?? 0) && 0 === strpos($lot[1], PATH . D) && !is_dir($folder = dirname($lot[1]))) {
                mkdir($folder, 0775, true);
            }
            // `->blob('.\path\to\file.jpg', 60)`
            if ('jpeg' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range to 0–100
                $lot[2] = b($lot[2], [0, 100]);
            // `->blob('.\path\to\file.png', 60)`
            } else if ('png' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range of 0–100 to 0–9
                $lot[2] = m(b($lot[2], [0, 100]), [0, 100], [0, 9]);
            }
            ob_start();
            call_user_func($task, ...$lot);
            if (!$this->_8__5()) {
                imagedestroy($this->blob);
            }
            return ob_get_clean();
        }
        return "";
    }

    public function crop(...$lot) {
        // `->crop(72, 72)`
        if (count($lot) < 3) {
            $w = (int) ($lot[0] ?? $this->w);
            $h = (int) ($lot[1] ?? $w);
            $this->blob = $this->_resize($w, $h, 1, true);
            return $this;
        }
        // `->crop(4, 4, 72, 72)`
        $x = (int) $lot[0];
        $y = (int) ($lot[1] ?? $x);
        $w = (int) ($lot[2] ?? $this->w);
        $h = (int) ($lot[3] ?? $this->h);
        $blob = imagecreatetruecolor($w, $h);
        imagecopy($blob, $this->blob, 0, 0, $x, $y, $w, $h);
        $this->blob = $blob;
        return $this;
    }

    public function fit(...$lot) {
        $w = $lot[0] ?? $this->w;
        $h = $lot[1] ?? $this->h;
        $this->blob = $this->_resize($w, $h, 1, false);
        return $this;
    }

    public function height() {
        return $this->h;
    }

    public function resize(...$lot) {
        $w = $lot[0] ?? $this->w;
        $h = $lot[1] ?? $this->h;
        $this->blob = $this->_resize($w, $h, false, false);
        return $this;
    }

    public function scale(int $percent) {
        $percent = b($percent, [0]) / 100;
        $w = ceil($percent * $this->w);
        $h = ceil($percent * $this->h);
        $this->blob = $this->_resize($w, $h, true, false);
        return $this;
    }

    public function type() {
        return $this->type ?? parent::type();
    }

    public function width() {
        return $this->w;
    }

}