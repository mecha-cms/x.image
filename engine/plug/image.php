<?php

// Add custom image support here, by file extension

namespace x\image\from {
    function bmp(...$v) {
        return \imagecreatefrombmp(...$v);
    }
    function gif(...$v) {
        return \imagecreatefromgif(...$v);
    }
    function jpeg(...$v) {
        return \imagecreatefromjpeg(...$v);
    }
    function jpg(...$v) {
        return \imagecreatefromjpeg(...$v);
    }
    function png(...$v) {
        return \imagecreatefrompng(...$v);
    }
    function webp(...$v) {
        return \imagecreatefromwebp(...$v);
    }
    function xbm(...$v) {
        return \imagecreatefromxbm(...$v);
    }
    function xpm(...$v) {
        return \imagecreatefromxpm(...$v);
    }
}

namespace x\image\to {
    function bmp(...$v) {
        return \imagebmp(...$v);
    }
    function gif(...$v) {
        return \imagegif(...$v);
    }
    function jpeg(...$v) {
        return \imagejpeg(...$v);
    }
    function jpg(...$v) {
        return \imagejpeg(...$v);
    }
    function png(...$v) {
        return \imagepng(...$v);
    }
    function webp(...$v) {
        return \imagewebp(...$v);
    }
    function xbm(...$v) {
        return \imagexbm(...$v);
    }
    // function xpm(...$v) {
    //     return \imagexpm(...$v);
    // }
}

namespace x\image\type {
    function bmp() {
        return \image_type_to_mime_type(\IMAGETYPE_BMP);
    }
    function gif(...$v) {
        return \image_type_to_mime_type(\IMAGETYPE_GIF);
    }
    function jpeg(...$v) {
        return \image_type_to_mime_type(\IMAGETYPE_JPEG);
    }
    function jpg(...$v) {
        return \image_type_to_mime_type(\IMAGETYPE_JPEG);
    }
    function png(...$v) {
        return \image_type_to_mime_type(\IMAGETYPE_PNG);
    }
    function webp(...$v) {
        return \image_type_to_mime_type(\IMAGETYPE_WEBP);
    }
    function xbm(...$v) {
        return \image_type_to_mime_type(\IMAGETYPE_XBM);
    }
    // function xpm(...$v) {
    //     return \image_type_to_mime_type(\IMAGETYPE_XPM);
    // }
}

namespace {
    function image(...$lot) {
        return new Image(...$lot);
    }
}