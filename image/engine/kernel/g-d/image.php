<?php namespace GD;

class Image extends \File {

    const state = [
        'path' => null, // Set default image file
        'width' => 72,
        'height' => null, // Same as `width`
        'type' => 'image/png',
        'x' => 'png'
    ];

    private static $fetch;

    protected $h;
    protected $k;
    protected $w;

    protected function _resize(int $max_width = null, int $max_height = null, $ratio = true, $crop = false) {
        $old_width = $this->w;
        $old_height = $this->h;
        $new_width = $max_width ?? self::$state['width'];
        $new_height = $max_height ?? self::$state['height'] ?? $max_width;
        $x = 0;
        $y = 0;
        $current_ratio = \round($old_width / $old_height, 2);
        $desired_ratio_after = \round($max_width / $max_height, 2);
        $desired_ratio_before = \round($max_height / $max_width, 2);
        if ($ratio) {
            // Don’t do anything if the new image size is bigger than the original image size
            if (1 === $ratio && $old_width < $max_width && $old_height < $max_height) {
                return $this;
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
                $x = \floor((($new_width - $max_width) / 2) * $width_ratio);
                $y = \round((($new_height - $max_height) / 2) * $height_ratio);
                $blob = \imagecreatetruecolor($max_width, $max_height);
            } else {
                if ($old_width > $old_height) {
                    $ratio = \max($old_width, $old_height) / \max($max_width, $max_height);
                } else {
                    $ratio = \max($old_width, $old_height) / \min($max_width, $max_height);
                }
                $new_width = $old_width / $ratio;
                $new_height = $old_height / $ratio;
                $blob = \imagecreatetruecolor($new_width, $new_height);
            }
        } else {
            $blob = \imagecreatetruecolor($max_width, $max_height);
        }
        // Draw…
        \imagealphablending($blob, false);
        \imagesavealpha($blob, true);
        \imagecopyresampled($blob, $this->value[0], 0, 0, $x, $y, $new_width, $new_height, $old_width, $old_height);
        $this->value[0] = $blob;
        return $this;
    }

    protected function _type(string $content) {
        if (\extension_loaded('fileinfo')) {
            $info = \finfo_open();
            return \finfo_buffer($info, $content, \FILEINFO_MIME_TYPE);
        }
        // <https://stackoverflow.com/a/9899096>
        $getBytesFromHexString = static function($data) {
            $bytes = [];
            for ($i = 0; $i < \strlen($data); $i += 2) {
                $bytes[] = \chr(\hexdec(\substr($data, $i, 2)));
            }
            return \implode($bytes);
        };
        $getImageMimeType = static function($data) {
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
                if ($bytes === \substr($data, 0, \strlen($bytes))) {
                    return 'image/' . $v;
                }
            }
            return null;
        };
        return $getImageMimeType($content);
    }

    public function __construct(string $path = null) {
        $blob = $type = null;
        $w = self::$state['width'];
        $h = self::$state['height'] ?? $w;
        $this->exist = false;
        $from = "\\x\\image\\from\\";
        if (\is_string($path)) {
            // Create image from string
            if (0 === \strpos($path, 'data:image/') && false !== \strpos($path, ';base64,')) {
                $blob = \imagecreatefromstring($content = \base64_decode(\explode(',', $path = \rawurldecode($path), 2)[1]));
                $type = $this->_type($content) ?? \substr(explode(';', $path, 2)[0], 5);
            // Create image from remote URL
            } else if (false !== \strpos($path, '://') || 0 === \strpos($path, '/') && 0 !== \strpos($path, \ROOT)) {
                $path = \To::path($url = \URL::long($path));
                // Local URL
                if (0 === \strpos($path, \ROOT . \DS) && \is_file($path)) {
                    $type = \mime_content_type($path) ?: $this->_type(\file_get_contents($path));
                    if (0 === \strpos($type, 'image/') && \function_exists($fn = $from . \explode('/', $type)[1])) {
                        $blob = \call_user_func($fn, $path);
                    }
                    $this->exist = true;
                    $this->path = $path;
                // Load from cache
                } else if (isset(self::$fetch[$url])) {
                    $fetch = self::$fetch[$url];
                    $blob = $fetch[0];
                    $type = $fetch[1];
                // Fetch URL
                } else if ($out = \fetch($url)) {
                    $k = "\\x\\image\\type\\";
                    self::$fetch[$url] = [
                        $blob = \imagecreatefromstring($out),
                        $type = $this->_type($out)
                    ];
                    \imagealphablending($blob, false);
                    \imagesavealpha($blob, true);
                }
            // Create image from local file
            } else if (\is_file($path)) {
                $type = \mime_content_type($path) ?: $this->_type(\file_get_contents($path));
                if (0 === \strpos($type, 'image/')) {
                    // Try with image type by default
                    if (\function_exists($fn = $from . \explode('/', $type, 2)[1])) {
                        $blob = \call_user_func($fn, $path);
                    }
                    // Try with image extension if `$blob` is `false`
                    if (!$blob && \function_exists($fn = $from . \pathinfo($path, \PATHINFO_EXTENSION))) {
                        $blob = \call_user_func($fn, $path);
                    }
                    // Last try?
                    if (!$blob) {}
                }
                $this->exist = true;
                $this->path = $path;
            }
        }
        // Else, handle invalid input
        if (!$blob && !$type) {
            if (
                isset(self::$state['path']) &&
                \is_file($path = self::$state['path']) &&
                \function_exists($fn = $from . \pathinfo($path, \PATHINFO_EXTENSION))
            ) {
                $blob = \call_user_func($fn, $path);
                \imagealphablending($blob, false);
                \imagesavealpha($blob, true);
            } else {
                $blob = \imagecreate(1, 1);
                if (\is_string($path)) {
                    $hex = \str_split(\substr(\md5($path), 0, 6), 2);
                    \imagecolorallocate($blob, \hexdec($hex[0]), \hexdec($hex[1]), \hexdec($hex[2]));
                } else {
                    \imagecolorallocate($blob, 0, 0, 0);
                }
            }
        }
        $this->k = $type ?? self::$state['type'];
        if ($blob) {
            $this->w = \imagesx($blob);
            $this->h = \imagesy($blob);
        }
        $this->value[0] = $blob;
    }

    public function crop(...$lot) {
        // `->crop(72, 72)`
        if (\count($lot) < 3) {
            $w = $lot[0] ?? self::$state['width'];
            $h = $lot[1] ?? self::$state['height'] ?? $w;
            return $this->_resize($w, $h, 1, true);
        }
        // `->crop(4, 4, 72, 72)`
        $x = (int) $lot[0];
        $y = (int) ($lot[1] ?? $x);
        $w = (int) ($lot[2] ?? $this->w);
        $h = (int) ($lot[3] ?? $this->h);
        $blob = \imagecreatetruecolor($w, $h);
        \imagecopy($blob, $this->value[0], 0, 0, $x, $y, $w, $h);
        $this->value[0] = $blob;
        return $this;
    }

    public function draw(...$lot) {
        $to = "\\x\\image\\to\\";
        $x = 0 === \strpos($this->k, 'image/') ? \explode('/', $this->k, 2)[1] : self::$state['x'];
        $k = \function_exists($fn = "\\x\\image\\type\\" . $x) ? \call_user_func($fn) : self::$state['type'];
        if (\function_exists($fn = $to . $x)) {
            \array_unshift($lot, $this->value[0]);
            // `->draw('.\path\to\file.jpg', 60)`
            if ('jpeg' === $x && isset($lot[2]) && \is_int($lot[2])) {
                // Normalize range to 0–100
                $lot[2] = \b($lot[2], [0, 100]);
            // `->draw('.\path\to\file.png', 60)`
            } else if ('png' === $x && isset($lot[2]) && \is_int($lot[2])) {
                // Normalize range of 0–9 to 0–100
                $lot[2] = \m(\b($lot[2], [0, 100]), [0, 100], [0, 9]);
            }
            \header('Content-Type: ' . ($lot[3] ?? $k));
            \call_user_func($fn, ...$lot);
            \imagedestroy($this->value[0]);
        }
        exit;
    }

    public function fit(...$lot) {
        $w = $lot[0] ?? $this->w;
        $h = $lot[1] ?? $this->h;
        return $this->_resize($w, $h, 1, false);
    }

    public function height($i = null) {
        return $this->h;
    }

    public function resize(...$lot) {
        $w = $lot[0] ?? $this->w;
        $h = $lot[1] ?? $this->h;
        return $this->_resize($w, $h, false, false);
    }

    public function scale(int $i) {
        $i = \b($i, [0]) / 100;
        $w = \ceil($i * $this->w);
        $h = \ceil($i * $this->h);
        return $this->_resize($w, $h, true, false);
    }

    public function store(...$lot) {
        $lot[0] = \strtr($lot[0], '/', DS);
        $out = [null];
        $x = \pathinfo($lot[0], \PATHINFO_EXTENSION);
        if (\is_file($lot[0])) {
            // Return `false` if file already exists
            $out[1] = false;
        } else {
            if (!\is_dir($d = \dirname($lot[0]))) {
                \mkdir($d, 0775, true);
            }
            // Return `$v` on success, `null` on error
            $to = "\\x\\image\\to\\";
            if (\function_exists($fn = $to . $x)) {
                \array_unshift($lot, $this->value[0]);
                // `->store('.\path\to\file.jpg', 60)`
                if ('jpeg' === $x && isset($lot[2]) && \is_int($lot[2])) {
                    // Normalize range to 0–100
                    $lot[2] = \b($lot[2], [0, 100]);
                // `->store('.\path\to\file.png', 60)`
                } else if ('png' === $x && isset($lot[2]) && \is_int($lot[2])) {
                    // Normalize range of 0–9 to 0–100
                    $lot[2] = \m(\b($lot[2], [0, 100]), [0, 100], [0, 9]);
                }
                $out[1] = \call_user_func($fn, ...$lot) ? $lot[1] : null;
                \imagedestroy($this->value[0]);
            }
        }
        $this->value[1] = $out;
        return $this;
    }

    public function type() {
        return $this->k ?? parent::type();
    }

    public function width($i = null) {
        return $this->w;
    }

    public static $state = self::state;

}