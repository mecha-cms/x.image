<?php

// Add custom image support here, by file extension

namespace x\image\from {
    function avif(...$v) {
        return \imagecreatefromavif(...$v); // PHP >= 8.0
    }
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
    function wbmp(...$v) {
        return \imagecreatefromwbmp(...$v);
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
    function avif(...$v) {
        return \imageavif(...$v); // PHP >= 8.0
    }
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
    function wbmp(...$v) {
        return \imagewbmp(...$v);
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
    function avif() {
        return \image_type_to_mime_type(\IMAGETYPE_AVIF); // PHP >= 8
    }
    function bmp() {
        return \image_type_to_mime_type(\IMAGETYPE_BMP);
    }
    function gif() {
        return \image_type_to_mime_type(\IMAGETYPE_GIF);
    }
    function jpeg() {
        return \image_type_to_mime_type(\IMAGETYPE_JPEG);
    }
    function jpg() {
        return \image_type_to_mime_type(\IMAGETYPE_JPEG);
    }
    function png() {
        return \image_type_to_mime_type(\IMAGETYPE_PNG);
    }
    function tiff() {
        // return \image_type_to_mime_type(\IMAGETYPE_TIFF_MM);
        return \image_type_to_mime_type(\IMAGETYPE_TIFF_II);
    }
    function webp() {
        return \image_type_to_mime_type(\IMAGETYPE_WEBP);
    }
    function xbm() {
        return \image_type_to_mime_type(\IMAGETYPE_XBM);
    }
    // function xpm() {
    //     return \image_type_to_mime_type(\IMAGETYPE_XPM);
    // }
}