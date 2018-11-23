<?php

class Image extends Genome {

    public $path = null;
    public $o = [];
    public $blob = null;

    // Cache!
    private static $error = null;
    private static $inspect = [];

    public function __construct($path = null, $fail = false) {
        if (!extension_loaded('gd')) {
            if (defined('DEBUG') && DEBUG) {
                Guardian::abort('<a href="http://www.php.net/manual/en/book.image.php" title="PHP &#x2013; Image Processing and GD" rel="nofollow" target="_blank">PHP GD</a> extension is not installed on your web server.');
            }
            return $fail;
        }
        if (is_array($path)) {
            foreach ($path as $v) {
                if (is_file($v)) {
                    $this->o[] = realpath($v);
                }
            }
            return parent::__construct();
        }
        if (!isset(self::$error)) {
            $img = imagecreate(72, 72);
            $font = 1;
            $x = (72 - imagefontwidth($font) * 5) / 2;
            $y = (72 - imagefontheight($font)) / 2;
            imagecolorallocate($img, 255, 0, 0);
            imagestring($img, $font, $x, $y, 'ERROR', imagecolorallocate($img, 255, 255, 255));
            self::$error = $img;
        }
        $blob = self::$error;
        if (is_resource($path)) {
            $blob = $path;
        } else if (is_string($path)) {
            // Create image from string
            if (strpos($path, 'data:image/') === 0 && strpos($path, ';base64,') !== false) {
                $blob = imagecreatefromstring(base64_decode(explode(',', $path, 2)[1]));
            // Create image from file
            } else if (is_file($path)) {
                $this->path = realpath($path);
                $type = mime_content_type($path);
                if (strpos($type, 'image/') === 0 && function_exists($fn = 'imagecreatefrom' . explode('/', $type)[1])) {
                    $blob = call_user_func($fn, $path);
                }
            }
        }
        $this->blob = $blob;
        parent::__construct();
    }

    public function saveTo(...$lot) {
        $path = $lot[0];
        if (!is_dir($dir = dirname($path))) {
            mkdir($dir, 0775, true);
        }
        $type = alt(pathout($path, PATHout_EXTENSION), ['jpg' => 'jpeg']);
        if (function_exists($fn = 'image' . $type)) {
            array_unshift($lot, $this->blob);
            call_user_func($fn, ...$lot);
            imagedestroy($this->blob);
        }
    }

    public function saveAs(...$lot) {
        if (!isset($this->path)) {
            Guardian::abort('The <code>' . __FUNCTION__ . '</code> method can only be used for file.');
        }
        $lot[0] = dirname($this->path) . DS . basename($lot[0]);
        return $this->saveTo(...$lot);
    }

    // Save away…
    public function save(...$lot) {
        if (!isset($this->path)) {
            Guardian::abort('The <code>' . __FUNCTION__ . '</code> method can only be used for file.');
        }
        array_unshift($lot, $this->path);
        return $this->saveTo(...$lot);
    }

    public function draw(...$lot) {
        $type = array_shift($lot) ?? 'image/png';
        header('Content-Type: ' . $type);
        if (function_exists($fn = 'image' . explode('/', $type)[1])) {
            array_unshift($lot, $this->blob);
            call_user_func($fn, ...$lot);
            imagedestroy($this->blob);
            exit;
        }
        echo self::$error;
        exit;
    }

    public function resize(int $max_width = 72, int $max_height = 72, $ratio = true, $crop = false) {
        $old_width = imagesx($this->blob);
        $old_height = imagesy($this->blob);
        $new_width = $max_width;
        $new_height = $max_height ?? $max_width;
        $x = 0;
        $y = 0;
        $current_ratio = round($old_width / $old_height, 2);
        $desired_ratio_after = round($max_width / $max_height, 2);
        $desired_ratio_before = round($max_height / $max_width, 2);
        if ($ratio) {
            // Don’t do anything if the new image size is bigger than the original image size
            if($old_width < $max_width && $old_height < $max_height) {
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
                $pallete = imagecreatetruecolor($max_width, $max_height);
            } else {
                if ($old_width > $old_height) {
                    $ratio = max($old_width, $old_height) / max($max_width, $max_height);
                } else {
                    $ratio = max($old_width, $old_height) / min($max_width, $max_height);
                }
                $new_width = $old_width / $ratio;
                $new_height = $old_height / $ratio;
                $pallete = imagecreatetruecolor($new_width, $new_height);
            }
        } else {
            $pallete = imagecreatetruecolor($max_width, $max_height);
        }
        // Draw…
        imagealphablending($pallete, false);
        imagesavealpha($pallete, true);
        imagecopyresampled($pallete, $this->blob, 0, 0, $x, $y, $new_width, $new_height, $old_width, $old_height);
        $this->blob = $pallete;
        return $this;
    }

    public function crop(...$lot) {
        $width = (int) ($lot[0] ?? 72);
        $height = (int) ($lot[1] ?? $width);
        // `->crop(72, 72)`
        if (count($lot) < 3) {
            return $this->resize($width, $height, true, true);
        }
        // `->crop(4, 4, 72, 72)`
        $x = (int) $lot[0];
        $y = (int) ($lot[1] ?? $x);
        $width = (int) ($lot[2] ?? 72);
        $height = (int) ($lot[3] ?? $width);
        $pallete = imagecreatetruecolor($width, $height);
        imagecopy($pallete, $this->blob, 0, 0, $x, $y, $width, $height);
        $this->blob = $pallete;
        return $this;
    }

    public function rotate(int $angle = 0, $background = false, float $a = 1) {
        $background = self::RGBA($background, $a);
        // For alpha: 127 = transparent, 0 = opaque
        $background[3] = 127 - ($background[3] * 127);
        $background = imagecolorallocatealpha($this->blob, ...$background);
        imagealphablending($this->blob, false);
        imagesavealpha($this->blob, true);
        // The angle value in `imagerotate` function is inverted
        $this->blob = imagerotate($this->blob, $angle * -1, $background, 0);
        imagealphablending($this->blob, false);
        imagesavealpha($this->blob, true);
        return $this;
    }

    public function flip(string $dir = 'h') {
        $flip = alt(strtolower($dir[0]), [
            'h' => IMG_FLIP_HORIZONTAL,
            'v' => IMG_FLIP_VERTICAL,
            'b' => IMG_FLIP_BOTH
        ], 'h');
        imageflip($this->blob, $flip);
        return $this;
    }

    public function merge(int $gap = 0, string $dir = 'v', $background = false, float $a = 1) {
        $bucket = $max_width = $max_height = [];
        $width = $height = 0;
        $dir = strtolower($dir)[0];
        foreach ($this->o as $v) {
            $v = array_slice(getimagesize($v), 0, 2);
            $bucket[] = $v;
            $max_width[] = $v[0];
            $max_height[] = $v[1];
            $width += $v[0] + $gap;
            $height += $v[1] + $gap;
        }
        if ($dir === 'v') {
            $pallete = imagecreatetruecolor(max($max_width), $height - $gap);
        } else {
            $pallete = imagecreatetruecolor($width - $gap, max($max_height));
        }
        $background = self::RGBA($background, $a);
        // For alpha: 127 = transparent, 0 = opaque
        $background[3] = 127 - ($background[3] * 127);
        $background = imagecolorallocatealpha($pallete, ...$background);
        imagefill($pallete, 0, 0, $background);
        imagealphablending($pallete, true);
        imagesavealpha($pallete, true);
        $start_width_from = $start_height_from = 0;
        foreach (array_values($this->o) as $k => $v) {
            $blob = (new static($v))->blob;
            imagealphablending($blob, false);
            imagesavealpha($blob, true);
            imagecopyresampled($pallete, $blob, $start_width_from, $start_height_from, 0, 0, $bucket[$k][0], $bucket[$k][1], $bucket[$k][0], $bucket[$k][1]);
            $start_width_from += $dir === 'h' ? $bucket[$k][0] + $gap : 0;
            $start_height_from += $dir === 'v' ? $bucket[$k][1] + $gap : 0;
        }
        $this->blob = $pallete;
        return $this;
    }

    public function bright($level = 1) {
        // -255 = min brightness, 0 = no change, +255 = max brightness
        $level = self::range($level, [-255, 255], [0, 100]); // normalized to (0–100)
        imagefilter($this->blob, IMG_FILTER_BRIGHTNESS, $level);
        return $this;
    }

    public function contrast($level = 1) {
        // -100 = max contrast, 0 = no change, +100 = min contrast (it’s inverted)
        $level = self::range($level, [-100, 100], [0, 100]); // normalized to (0–100)
        imagefilter($this->blob, IMG_FILTER_CONTRAST, $level * -1);
        return $this;
    }

    public function color($color, $a = 1) {
        $color = self::RGBA($color, $a);
        // For alpha: 127 = transparent, 0 = opaque
        $color[3] = 127 - ($color[3] * 127);
        imagefilter($this->blob, IMG_FILTER_COLORIZE, ...$color);
        return $this;
    }

    public function filter(...$lot) {
        $kin = array_shift($lot);
        if (self::_('filter.' . $kin)) {
            return parent::__call('filter.' . $kin, $lot);
        }
        return $this;
    }

    public static function open($path, $fail = false) {
        return new static($path, $fail);
    }

    public static function inspect(string $path, string $key = null, $fail = false) {
        $id = json_encode(func_get_args());
        if (isset(self::$inspect[$id])) {
            $out = self::$inspect[$id];
            return isset($key) ? Anemon::get($out, $key, $fail) : $out;
        }
        $out = File::inspect($path);
        $z = getimagesize($path);
        $out['width'] = $z[0] ?? null;
        $out['height'] = $z[1] ?? null;
        $out['bit'] = $z['bits'] ?? null;
        $out['channel'] = $z['channels'] ?? null;
        self::$inspect[$id] = $out;
        return isset($key) ? Anemon::get($out, $key, $fail) : $out;
    }

    // <https://stackoverflow.com/a/14224813/1163000>
    public static function range($value, $a, $b) {
        return ($value - $a[0]) * ($b[1] - $b[0]) / ($a[1] - $a[0]) + $b[0];
    }

    public static function RGBA($in, float $a = 1) {
        if (is_array($in)) {
            return extend([0, 0, 0, (float) $a], $in);
        } else if (is_string($in)) {
            if (strpos($in, '#') === 0 && ctype_xdigit(substr($in, 1))) {
                $in = substr($in, 1);
                if (strlen($in) === 3) {
                    $in = preg_replace('#.#', '$0$0', $in);
                }
                $in = str_split($in, 2);
                return [(int) hexdec($in[0]), (int) hexdec($in[1]), (int) hexdec($in[2]), (float) $a];
            // <https://www.regular-expressions.out/numericranges.html>
            } else if (strpos($in, 'rgb') === 0 && preg_match('#^rgba?\(\s*([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5])\s*,\s*([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5])\s*,\s*([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5])(?:\s*,\s*([01]|0?\.\d+))?\)$#', $in, $m)) {
                return [(int) $m[1], (int) $m[2], (int) $m[3], (float) ($m[4] ?? $a)];
            }
        }
        return [0, 0, 0, 0]; // Transparent
    }

}