<?php

class Image extends File {

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

    protected function doResize(int $max_width = null, int $max_height = null, $ratio = true, $crop = false) {
        $old_width = $this->w;
        $old_height = $this->h;
        $new_width = $max_width ?? self::$state['width'];
        $new_height = $max_height ?? self::$state['height'] ?? $max_width;
        $x = 0;
        $y = 0;
        $current_ratio = round($old_width / $old_height, 2);
        $desired_ratio_after = round($max_width / $max_height, 2);
        $desired_ratio_before = round($max_height / $max_width, 2);
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
                $x = floor((($new_width - $max_width) / 2) * $width_ratio);
                $y = round((($new_height - $max_height) / 2) * $height_ratio);
                $blob = imagecreatetruecolor($max_width, $max_height);
            } else {
                if ($old_width > $old_height) {
                    $ratio = max($old_width, $old_height) / max($max_width, $max_height);
                } else {
                    $ratio = max($old_width, $old_height) / min($max_width, $max_height);
                }
                $new_width = $old_width / $ratio;
                $new_height = $old_height / $ratio;
                $blob = imagecreatetruecolor($new_width, $new_height);
            }
        } else {
            $blob = imagecreatetruecolor($max_width, $max_height);
        }
        // Draw…
        imagealphablending($blob, false);
        imagesavealpha($blob, true);
        imagecopyresampled($blob, $this->value[0], 0, 0, $x, $y, $new_width, $new_height, $old_width, $old_height);
        $this->value[0] = $blob;
        return $this;
    }

    protected static function getType(string $x) {
        if (defined($v = 'IMAGETYPE_' . strtoupper('jpg' === $x ? 'jpeg' : $x))) {
            return image_type_to_mime_type(constant($v));
        }
        return self::$state['type'];
    }

    protected static function getX(string $type) {
        $x = explode('/', $type, 2)[1];
        if (defined($v = 'IMAGETYPE_' . strtoupper('jpg' === $x ? 'jpeg' : $x))) {
            return image_type_to_extension(constant($v), false);
        }
        return self::$state['x'];
    }

    public function __construct(string $path = null) {
        $blob = $type = null;
        $w = self::$state['width'];
        $h = self::$state['height'] ?? $w;
        $this->exist = false;
        if (is_string($path)) {
            // Create image from string
            if (0 === strpos($path, 'data:image/') && false !== strpos($path, ';base64,')) {
                $blob = imagecreatefromstring(base64_decode(explode(',', $path, 2)[1]));
                $type = substr(explode(';', $path, 2)[0], 5);
            // Create image from remote URL
            } else if (0 === strpos($path, '/') || false !== strpos($path, '://')) {
                $path = To::path($url = URL::long($path));
                // Local URL
                if (0 === strpos($path, ROOT . DS) && is_file($path)) {
                    $type = mime_content_type($path);
                    if (0 === strpos($type, 'image/') && function_exists($fn = 'imagecreatefrom' . explode('/', $type)[1])) {
                        $blob = call_user_func($fn, $path);
                    }
                    $this->exist = true;
                    $this->path = $path;
                // Load from cache
                } else if (isset(self::$fetch[$url])) {
                    $fetch = self::$fetch[$url];
                    $blob = $fetch[0];
                    $type = $fetch[1];
                // Fetch URL
                } else if ($out = fetch($url)) {
                    self::$fetch[$url] = [
                        $blob = imagecreatefromstring($out),
                        $type = get_headers($url, 1)['Content-Type'] ?? self::getType(pathinfo($url, PATHINFO_EXTENSION))
                    ];
                    imagealphablending($blob, false);
                    imagesavealpha($blob, true);
                }
            // Create image from local file
            } else if (is_file($path)) {
                $type = mime_content_type($path);
                if (0 === strpos($type, 'image/') && function_exists($fn = 'imagecreatefrom' . explode('/', $type)[1])) {
                    $blob = call_user_func($fn, $path);
                }
                $this->exist = true;
                $this->path = $path;
            }
        }
        // Else, handle invalid input
        if (!$blob && !$type) {
            if (
                isset(self::$state['path']) &&
                is_file($path = self::$state['path']) &&
                function_exists($fn = 'imagecreatefrom' . explode('/', $type = mime_content_type($path))[1])
            ) {
                $blob = call_user_func($fn, $path);
                imagealphablending($blob, false);
                imagesavealpha($blob, true);
            } else {
                $blob = imagecreate(1, 1);
                if (is_string($path)) {
                    $hex = str_split(substr(md5($path), 0, 6), 2);
                    imagecolorallocate($blob, hexdec($hex[0]), hexdec($hex[1]), hexdec($hex[2]));
                } else {
                    imagecolorallocate($blob, 0, 0, 0);
                }
            }
        }
        $this->k = $type ?? self::$state['type'];
        $this->w = imagesx($blob);
        $this->h = imagesy($blob);
        $this->value[0] = $blob;
    }

    public function crop(...$lot) {
        // `->crop(72, 72)`
        if (count($lot) < 3) {
            $w = $lot[0] ?? self::$state['width'];
            $h = $lot[1] ?? self::$state['height'] ?? $w;
            return $this->doResize($w, $h, 1, true);
        }
        // `->crop(4, 4, 72, 72)`
        $x = (int) $lot[0];
        $y = (int) ($lot[1] ?? $x);
        $w = (int) ($lot[2] ?? $this->w);
        $h = (int) ($lot[3] ?? $this->h);
        $blob = imagecreatetruecolor($w, $h);
        imagecopy($blob, $this->value[0], 0, 0, $x, $y, $w, $h);
        $this->value[0] = $blob;
        return $this;
    }

    public function draw(...$lot) {
        if (function_exists($fn = 'image' . ($x = self::getX($k = $this->k)))) {
            array_unshift($lot, $this->value[0]);
            // `->draw('.\path\to\file.jpg', 60)`
            if ('jpeg' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range to 0–100
                $lot[2] = b($lot[2], [0, 100]);
            // `->draw('.\path\to\file.png', 60)`
            } else if ('png' === $x && isset($lot[2]) && is_int($lot[2])) {
                // Normalize range of 0–9 to 0–100
                $lot[2] = m(b($lot[2], [0, 100]), [0, 100], [0, 9]);
            }
            header('Content-Type: ' . ($lot[3] ?? $k));
            call_user_func($fn, ...$lot);
            imagedestroy($this->value[0]);
        }
        exit;
    }

    public function fit(...$lot) {
        $w = $lot[0] ?? $this->w;
        $h = $lot[1] ?? $this->h;
        return $this->doResize($w, $h, 1, false);
    }

    public function height($i = null) {
        return $this->h;
    }

    public function resize(...$lot) {
        $w = $lot[0] ?? $this->w;
        $h = $lot[1] ?? $this->h;
        return $this->doResize($w, $h, false, false);
    }

    public function scale(int $i) {
        $i = b($i, [0]) / 100;
        $w = ceil($i * $this->w);
        $h = ceil($i * $this->h);
        return $this->doResize($w, $h, true, false);
    }

    public function store(...$lot) {
        $lot[0] = strtr($lot[0], '/', DS);
        $out = [null];
        $x = self::getX($this->k);
        if (is_file($lot[0])) {
            // Return `false` if file already exists
            $out[1] = false;
        } else {
            if (!is_dir($d = dirname($lot[0]))) {
                mkdir($d, 0775, true);
            }
            // Return `$v` on success, `null` on error
            if (function_exists($fn = 'image' . $x)) {
                array_unshift($lot, $this->value[0]);
                // `->store('.\path\to\file.jpg', 60)`
                if ('jpeg' === $x && isset($lot[2]) && is_int($lot[2])) {
                    // Normalize range to 0–100
                    $lot[2] = b($lot[2], [0, 100]);
                // `->store('.\path\to\file.png', 60)`
                } else if ('png' === $x && isset($lot[2]) && is_int($lot[2])) {
                    // Normalize range of 0–9 to 0–100
                    $lot[2] = m(b($lot[2], [0, 100]), [0, 100], [0, 9]);
                }
                $out[1] = call_user_func($fn, ...$lot) ? $lot[1] : null;
                imagedestroy($this->value[0]);
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