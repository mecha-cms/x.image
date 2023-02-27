<?php

class Image extends File {

    protected function _resize(int $max_width = null, int $max_height = null, $ratio = true, $crop = false) {
        $blob = imagecreatefromstring($this->blob(null, 100));
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
        imagecopyresampled($new_blob, $blob, 0, 0, $x, $y, (int) $new_width, (int) $new_height, $old_width, $old_height);
        return $new_blob;
    }

    protected function _type(string $blob) {
        if (extension_loaded('fileinfo')) {
            $info = finfo_open();
            return finfo_buffer($info, $blob, FILEINFO_MIME_TYPE);
        }
        // <https://stackoverflow.com/a/9899096>
        $getBytesFromHexString = static function ($data) {
            $bytes = [];
            for ($i = 0; $i < strlen($data); $i += 2) {
                $bytes[] = chr(hexdec(substr($data, $i, 2)));
            }
            return implode($bytes);
        };
        $getImageMimeType = static function ($data) {
            $imageMimeTypes = [
                '424D' => 'bmp',
                '474946' => 'gif',
                'FFD8' => 'jpeg',
                '89504E470D0A1A0A' => 'png',
                '4949' => 'tiff',
                '4D4D' => 'tiff'
            ];
            foreach ($imageMimeTypes as $k => $v) {
                $bytes = $getBytesFromHexString($k);
                if ($bytes === substr($data, 0, strlen($bytes))) {
                    return 'image/' . $v;
                }
            }
            return null;
        };
        return $getImageMimeType($blob);
    }

    protected $h = 0;
    protected $w = 0;

    protected static $cache = [];

    public $blob;
    public $path;
    public $type;

    public function __construct(string $path = null) {
        $from = "x\\image\\from\\";
        if (is_string($path)) {
            // Create image from Base64 URL
            if (0 === strpos($path, 'data:image/') && false !== strpos($path, ';base64,')) {
                $this->blob = imagecreatefromstring($blob = base64_decode(explode(',', $path = rawurldecode($path), 2)[1]));
                $this->type = $this->_type($blob) ?? substr(strtok($path, ';'), 5);
            // Create image from remote URL
            } else if (0 !== strpos($path, PATH) && (false !== strpos($path, '://') || 0 === strpos($path, '/'))) {
                $path = To::path($link = long($path));
                // Load from cache
                if (isset(self::$cache[$link])) {
                    [$this->blob, $this->type] = self::$cache[$link];
                // Local URL
                } else if (0 === strpos($path, PATH) && is_file($path)) {
                    $this->type = mime_content_type($path) ?: $this->_type(file_get_contents($path));
                    if (0 === strpos($this->type, 'image/') && function_exists($fn = $from . explode('/', $this->type)[1])) {
                        $this->blob = call_user_func($fn, $path);
                    }
                    $this->path = $path;
                // Fetch URL
                } else if ($blob = fetch($link)) {
                    self::$cache[$link] = [
                        $this->blob = imagecreatefromstring($blob),
                        $this->type = $this->_type($blob)
                    ];
                    imagealphablending($this->blob, false);
                    imagesavealpha($this->blob, true);
                }
            // Create image from local file
            } else if (is_file($path)) {
                $this->type = mime_content_type($path) ?: $this->_type(file_get_contents($path));
                if (0 === strpos($this->type, 'image/')) {
                    // Try with image type by default
                    if (function_exists($fn = $from . explode('/', $this->type, 2)[1])) {
                        $this->blob = call_user_func($fn, $path);
                    }
                    // Try with image extension if `$this->blob` is `false`
                    if (!$this->blob && function_exists($fn = $from . pathinfo($path, PATHINFO_EXTENSION))) {
                        $this->blob = call_user_func($fn, $path);
                    }
                    // Last try?
                    if (!$this->blob) {}
                }
                $this->path = $path;
            }
        }
        // Else, handle invalid value
        if (!$this->blob && !$this->type) {
            $this->blob = imagecreatetruecolor(1, 1);
            $this->type = 'image/png';
            imagealphablending($this->blob, false);
            imagesavealpha($this->blob, true);
            imagefill($this->blob, 0, 0, imagecolorallocatealpha($this->blob, 0, 0, 0, 127));
        }
        if ($blob = $this->blob) {
            $this->h = imagesy($blob);
            $this->w = imagesx($blob);
        }
    }

    public function __destruct() {
        $this->blob && $this->blob instanceof GdImage && imagedestroy($this->blob);
    }

    public function __toString() {
        return $this->blob(null, 100);
    }

    public function blob(...$lot) {
        $to = "\\x\\image\\to\\";
        $x = 0 === strpos($this->type, 'image/') ? explode('/', $this->type, 2)[1] : 'png';
        if (function_exists($fn = $to . $x)) {
            array_unshift($lot, $this->blob);
            if (is_string($lot[1]) && 0 === strpos($lot[1], PATH . D) && !is_dir($folder = dirname($lot[1]))) {
                mkdir($folder, 0775, true);
            }
            // `->blob('.\path\to\file.jpg', 60)`
            if ('jpeg' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range to 0 – 100
                $lot[2] = b($lot[2], [0, 100]);
            // `->blob('.\path\to\file.png', 60)`
            } else if ('png' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range of 0 – 100 to 0 – 9
                $lot[2] = m(b($lot[2], [0, 100]), [0, 100], [0, 9]);
            }
            ob_start();
            call_user_func($fn, ...$lot);
            imagedestroy($this->blob);
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