<?php

foreach ([
    'blur' => function($level = 1) {
        $level = round($level);
        for ($i = 0; $i < $level; ++$i) {
            imagefilter($this->blob, IMG_FILTER_GAUSSIAN_BLUR);
        }
        return $this;
    },
    'emboss' => function() {
        imagefilter($this->blob, IMG_FILTER_EMBOSS);
        return $this;
    },
    'grayscale' => function() {
        imagefilter($this->blob, IMG_FILTER_GRAYSCALE);
        return $this;
    },
    'invert' => function() {
        imagefilter($this->blob, IMG_FILTER_NEGATE);
        return $this;
    },
    'pixelate' => function(...$lot) {
        imagefilter($this->blob, IMG_FILTER_PIXELATE, ...$lot);
        return $this;
    },
    'sharp' => function($level = 1) {
        $level = round($level);
        for ($i = 0; $i < $level; ++$i) {
            imageconvolution($this->blob, [
                [-1, -1, -1],
                [-1, 16, -1],
                [-1, -1, -1],
            ], 8, 0);
        }
        return $this;
    },
    'smooth' => function(...$lot) {
        imagefilter($this->blob, IMG_FILTER_SMOOTH, ...$lot);
        return $this;
    }
] as $k => $v) {
    Image::_('filter.' . $k, $v);
}