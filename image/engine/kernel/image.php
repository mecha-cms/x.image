<?php

class Image extends Genome {

    public $open = null;
    public $o = null;
    public $placeholder = null;

    public $GD = false;

    public function gen($file = null) {
        if (!isset($file)) {
            if (!file_exists($this->placeholder)) {
                File::open($this->o)->copyTo($this->placeholder);
            }
            $file = $this->placeholder;
        }
        $x = Path::X($file);
        if ($x === 'jpg') {
            $x = 'jpeg';
        }
        $fn = 'imagecreatefrom' . $x;
        if (is_callable($fn)) {
            $this->GD = call_user_func($fn, $file);
        }
        return $this;
    }

    public function twin($resource, $x = null) {
        $file = $this->placeholder;
        $nx = Path::X($file);
        if (isset($x)) {
            $file = preg_replace('#\.[a-z\d]+$#i', '.' . $x, $file);
            File::open($this->placeholder)->delete();
            $this->placeholder = $file;
            $nx = $x;
        }
        $a = [$resource, $file];
        if ($nx === 'jpg' || $nx === 'jpeg') {
            $nx = 'jpeg';
            $a[] = 100;
        }
        $fn = 'image' . $nx;
        if (is_callable($fn)) {
            call_user_func($fn, ...$a);
        }
        return $this;
    }

    public function __construct($file, $fail = false) {
        if (!extension_loaded('gd')) {
            if (defined('DEBUG') && DEBUG) {
                Guardian::abort('<a href="http://www.php.net/manual/en/book.image.php" title="PHP &#x2013; Image Processing and GD" rel="nofollow" target="_blank">PHP GD</a> extension is not installed on your web server.');
            }
            return $fail;
        }
        if (is_array($file)) {
            $this->open = [];
            foreach ($file as $v) {
                $this->open[] = To::path($v);
            }
        } else {
            $this->open = To::path($file);
        }
        $file = is_array($this->open) ? $this->open[0] : $this->open;
        $this->o = $file;
        $this->placeholder = Path::D($file) . DS . '_' . uniqid() . '.' . Path::B($file);
    }

    public static function take($file, $fail = false) {
        return new static($file, $fail);
    }

    public function saveTo($destination) {
        if (Is::D($destination)) {
            $destination .= DS . Path::B($this->o);
        }
        $ox = Path::X($this->o);
        $nx = Path::X($destination);
        if ($ox !== $nx || !file_exists($this->placeholder)) {
            $this->gen()->twin($this->GD, $nx);
        }
        File::open($this->placeholder)->moveTo($destination);
        imagedestroy($this->GD);
    }

    public function saveAs($name = 'image-%{id}%.png') {
        return $this->saveTo(Path::D($this->o) . DS . candy($name, ['id' => time()]));
    }

    // Save away…
    public function save() {
        return $this->saveTo($this->o);
    }

    public function draw($save = false) {
        $this->gen();
        $image = file_get_contents($this->placeholder);
        if ($save !== false) {
            $save = To::path($save);
            File::set($image)->saveTo($save);
        }
        header('Content-Type: ' . self::inspect($this->open, 'mime'));
        File::open($this->placeholder)->delete();
        imagedestroy($this->GD);
        echo $image;
        exit;
    }

    public static function inspect($file, $key = null, $fail = false) {
        if (is_array($file)) {
            $output = [];
            foreach ($file as $v) {
                $s = getimagesize($v);
                $output[] = concat(File::inspect($v), [
                    'width' => $s[0],
                    'height' => $s[1],
                    'bit' => $s['bits'],
                    'mime' => $s['mime']
                ]);
            }
            if (isset($key)) {
                $output = array_values(array_filter($output, function($v, $k) use($key) {
                    return $k === $key;
                }));
                return !empty($output) ? $output : $fail;
            }
            return $output;
        }
        $s = getimagesize($file);
        $output = concat(File::inspect($file), array(
            'width' => $s[0],
            'height' => $s[1],
            'bit' => $s['bits'],
            'mime' => $s['mime']
        ));
        if (isset($key)) {
            return array_key_exists($key, $output) ? $output[$key] : $fail;
        }
        return $output;
    }

    public function resize($max_width = 100, $max_height = null, $proportional = true, $crop = false) {
        $this->gen();
        if (!isset($max_height)) {
            $max_height = $max_width;
        }
        $info = self::inspect($this->open);
        $old_width = $info['width'];
        $old_height = $info['height'];
        $new_width = $max_width;
        $new_height = $max_height;
        $x = 0;
        $y = 0;
        $current_ratio = round($old_width / $old_height, 2);
        $desired_ratio_after = round($max_width / $max_height, 2);
        $desired_ratio_before = round($max_height / $max_width, 2);
        if ($proportional) {
            // Don’t do anything if the new image size is bigger than the original image size
            if($old_width < $max_width && $old_height < $max_height) {
                return $this->twin($this->GD);
            }
            if ($crop) {
                // Wider than the thumbnail (in aspect ratio sense)
                if($current_ratio > $desired_ratio_after) {
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
        imagecopyresampled($pallete, $this->GD, 0, 0, $x, $y, $new_width, $new_height, $old_width, $old_height);
        $this->twin($pallete);
        imagedestroy($pallete);
        return $this;
    }

    public function crop($x = 72, $y = null, $width = null, $height = null) {
        if (!isset($width)) {
            if (!isset($y)) {
                $y = $x;
            }
            return $this->resize($x, $y, true, true);
        }
        if (!isset($height)) {
            $height = $width;
        }
        $this->gen();
        $pallete = imagecreatetruecolor($width, $height);
        imagecopy($pallete, $this->GD, 0, 0, $x, $y, $width, $height);
        $this->twin($pallete);
        imagedestroy($pallete);
        return $this;
    }

    public function brightness($level = 1) {
        $this->gen();
        // -255 = min brightness, 0 = no change, +255 = max brightness
        imagefilter($this->GD, IMG_FILTER_BRIGHTNESS, $level);
        return $this->twin($this->GD);
    }

    public function contrast($level = 1) {
        $this->gen();
        // -100 = max contrast, 0 = no change, +100 = min contrast (it’s inverted)
        imagefilter($this->GD, IMG_FILTER_CONTRAST, $level * -1);
        return $this->twin($this->GD);
    }

    protected static function _RGB($rgba, $output = null) {
        if (is_string($rgba) && preg_match('#^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*([\d.]+))?\s*\)$#i', $rgba, $m)) {
            return [(int) $m[1], (int) $m[2], (int) $m[3], (float) (isset($m[4]) ? $m[4] : 1)];
        }
        return false;
    }

    protected static function _HEX($hex) {
        if (is_string($hex) && preg_match('#\#?([a-f\d]{3,6})#i', $hex, $m)) {
            $color = $m[1];
            if (strlen($color) !== 3 && strlen($color) !== 6) {
                return false;
            }
            if (strlen($color) === 3) {
                $color = preg_replace('#(.)#', '$1$1', $color);
            }
            $s = str_split($color, 2);
            return [(int) hexdec($s[0]), (int) hexdec($s[1]), (int) hexdec($s[2]), (float) 1];
        }
        return false;
    }

    public function colorize($r = 255, $g = 255, $b = 255, $a = 1) {
        $this->gen();
        // For red, green and blue: -255 = min, 0 = no change, +255 = max
        if (is_array($r)) {
            if (count($r) === 3) {
                $r[] = 1; // fix missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($r);
        } else {
            $bg = (string) $r;
            if ($bg[0] === '#' && $color = self::_HEX($r)) {
                $a = $g;
                list($r, $g, $b) = $color;
            } else if ($color = self::_RGB($r)) {
                list($r, $g, $b, $a) = $color;
            }
        }
        // For alpha: 127 = transparent, 0 = opaque
        $a = 127 - ($a * 127);
        imagefilter($this->GD, IMG_FILTER_COLORIZE, $r, $g, $b, $a);
        return $this->twin($this->GD);
    }

    public function grayscale() {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_GRAYSCALE);
        return $this->twin($this->GD);
    }

    public function negate() {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_NEGATE);
        return $this->twin($this->GD);
    }

    public function emboss($level = 1) {
        $level = round($level);
        for ($i = 0; $i < $level; ++$i) {
            $this->gen();
            imagefilter($this->GD, IMG_FILTER_EMBOSS);
            $this->twin($this->GD);
        }
        return $this;
    }

    public function blur($level = 1) {
        $level = round($level);
        for ($i = 0; $i < $level; ++$i) {
            $this->gen();
            imagefilter($this->GD, IMG_FILTER_GAUSSIAN_BLUR);
            $this->twin($this->GD);
        }
        return $this;
    }

    public function sharpen($level = 1) {
        $level = round($level);
        $matrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1],
        ];
        $divisor = array_sum(array_map('array_sum', $matrix));
        for ($i = 0; $i < $level; ++$i) {
            $this->gen();
            imageconvolution($this->GD, $matrix, $divisor, 0);
            $this->twin($this->GD);
        }
        return $this;
    }

    public function pixelate($level = 1, $advance = false) {
        $this->gen();
        imagefilter($this->GD, IMG_FILTER_PIXELATE, $level, $advance);
        return $this->twin($this->GD);
    }

    public function rotate($angle = 0, $bg = false, $alpha_for_hex = 1) {
        $this->gen();
        if ($bg === false) {
            $bg = [0, 0, 0, 0]; // transparent
        }
        if (is_array($bg)) {
            if (count($bg) === 3) {
                $bg[] = 1; // fix missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($bg);
        } else {
            $bg = (string) $bg;
            if ($bg[0] === '#' && $color = self::_HEX($bg)) {
                list($r, $g, $b) = $color;
                $a = $alpha_for_hex;
            } else if ($color = self::_RGB($bg)) {
                list($r, $g, $b, $a) = $color;
            }
        }
        $a = 127 - ($a * 127);
        $bg = imagecolorallocatealpha($this->GD, $r, $g, $b, $a);
        imagealphablending($this->GD, false);
        imagesavealpha($this->GD, true);
        // The angle value in `imagerotate` function is also inverted
        $rotated = imagerotate($this->GD, (floor($angle) * -1), $bg, 0);
        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        $this->twin($rotated);
        imagedestroy($rotated);
        return $this;
    }

    public function flip($direction = 'horizontal') {
        $this->gen();
        switch (strtolower($direction[0])) {
            // `horizontal`, `vertical` or `both` ?
            case 'h': imageflip($this->GD, IMG_FLIP_HORIZONTAL); break;
            case 'v': imageflip($this->GD, IMG_FLIP_VERTICAL); break;
            case 'b': imageflip($this->GD, IMG_FLIP_BOTH); break;
        }
        return $this->twin($this->GD);
    }

    public function merge($gap = 0, $direction = 'vertical', $bg = false, $alpha_for_hex = 1) {
        $bucket = $max_width = $max_height = [];
        $width = $height = 0;
        $direction = strtolower($direction);
        $this->open = (array) $this->open;
        foreach (self::inspect($this->open) as $info) {
            $bucket[] = [
                'width' => $info['width'],
                'height' => $info['height']
            ];
            $max_width[] = $info['width'];
            $max_height[] = $info['height'];
            $width += $info['width'] + $gap;
            $height += $info['height'] + $gap;
        }
        if (!$bg) {
            $bg = [0, 0, 0, 0]; // transparent
        }
        if (is_array($bg)) {
            if (count($bg) === 3) {
                $bg[] = 1; // fix missing alpha channel
            }
            list($r, $g, $b, $a) = array_values($bg);
        } else {
            $bg = (string) $bg;
            if ($bg[0] === '#' && $color = self::_HEX($bg)) {
                list($r, $g, $b) = $color;
                $a = $alpha_for_hex;
            } else if ($color = self::_RGB($bg)) {
                list($r, $g, $b, $a) = $color;
            }
        }
        $a = 127 - ($a * 127);
        if ($direction[0] === 'v') {
            $pallete = imagecreatetruecolor(max($max_width), $height - $gap);
        } else {
            $pallete = imagecreatetruecolor($width - $gap, max($max_height));
        }
        $bg = imagecolorallocatealpha($pallete, $r, $g, $b, $a);
        imagefill($pallete, 0, 0, $bg);
        imagealphablending($pallete, true);
        imagesavealpha($pallete, true);
        $start_width_from = $start_height_from = 0;
        for ($i = 0, $count = count($this->open); $i < $count; ++$i) {
            $this->gen($this->open[$i]);
            imagealphablending($this->GD, false);
            imagesavealpha($this->GD, true);
            imagecopyresampled($pallete, $this->GD, $start_width_from, $start_height_from, 0, 0, $bucket[$i]['width'], $bucket[$i]['height'], $bucket[$i]['width'], $bucket[$i]['height']);
            $start_width_from += $direction[0] === 'h' ? $bucket[$i]['width'] + $gap : 0;
            $start_height_from += $direction[0] === 'v' ? $bucket[$i]['height'] + $gap : 0;
        }
        return $this->twin($pallete, 'png');
    }

}