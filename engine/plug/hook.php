<?php

namespace x\image {
    function x(array $x = []) {
        static $r;
        if (null === $r) {
            $prefix = __NAMESPACE__ . "\\to\\x\\";
            $n = \strlen($prefix);
            $r = [];
            foreach (\get_defined_functions()['user'] as $v) {
                if (0 === \strpos($v, $prefix)) {
                    $v = \substr($v, $n);
                    if (!isset($x[$v]) || $x[$v]) {
                        $r[$v] = 1;
                    }
                }
            }
        }
        $r && \ksort($r);
        return \implode(',', \array_keys($r));
    }
}

namespace x\image\from {
    function blob(string $v, int $w = 1, int $h = 1) {
        if ("" !== $v) {
            $error = 0;
            \set_error_handler(function () use (&$error) {
                $error = 1;
                return true;
            }, \E_WARNING);
            $blob = \imagecreatefromstring($v);
            \restore_error_handler();
            if (!$error && $blob instanceof \GdImage) {
                \imagealphablending($blob, false);
                \imagesavealpha($blob, true);
                \imagefill($blob, 0, 0, \imagecolorallocatealpha($blob, 0, 0, 0, 127));
                return $blob;
            }
        }
        $blob = \imagecreatetruecolor(\max(1, $w), \max(1, $h));
        \imagealphablending($blob, false);
        \imagesavealpha($blob, true);
        \imagefill($blob, 0, 0, \imagecolorallocatealpha($blob, 0, 0, 0, 127));
        return $blob;
    }
}

namespace x\image\from\x {
    function avif($v) {
        return \imagecreatefromavif($v);
    }
    function bmp($v) {
        return \imagecreatefrombmp($v);
    }
    function gif($v) {
        return \imagecreatefromgif($v);
    }
    function jpeg($v) {
        return jpg($v);
    }
    function jpg($v) {
        return \imagecreatefromjpeg($v);
    }
    function png($v) {
        return \imagecreatefrompng($v);
    }
    function webp($v) {
        return \imagecreatefromwebp($v);
    }
}

namespace x\image\to\x {
    function avif(...$v) {
        // <https://www.php.net/function.imageavif>
        $v = \array_slice($v, 0, 4);
        return \imageavif(...$v);
    }
    function bmp(...$v) {
        // <https://www.php.net/function.imagebmp>
        $v = \array_slice($v, 0, 3);
        return \imagebmp(...$v);
    }
    function gif(...$v) {
        // <https://www.php.net/function.imagegif>
        $v = \array_slice($v, 0, 2);
        return \imagegif(...$v);
    }
    function jpeg(...$v) {
        return jpg(...$v);
    }
    function jpg(...$v) {
        // <https://www.php.net/function.imagejpeg>
        $v = \array_slice($v, 0, 3);
        return \imagejpeg(...$v);
    }
    function png(...$v) {
        // <https://www.php.net/function.imagepng>
        $v = \array_slice($v, 0, 4);
        return \imagepng(...$v);
    }
    function webp(...$v) {
        // <https://www.php.net/function.imagewebp>
        $v = \array_slice($v, 0, 3);
        return \imagewebp(...$v);
    }
}