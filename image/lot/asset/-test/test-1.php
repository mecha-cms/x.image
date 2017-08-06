<?php

$source = __DIR__ . DS . '2.jpg';
$destination = dirname($source) . DS . 'result' . DS;

// resize
Image::take($source)
     ->resize(200, 200)
     ->saveTo($destination . 'flower-resize-200.jpg');

// resize dis-proportional
Image::take($source)
     ->resize(200, 200, false)
     ->saveTo($destination . 'flower-resize-200x200.jpg');

// crop
Image::take($source)
     ->crop(72, 72)
     ->saveTo($destination . 'flower-crop-72-72.jpg');

// crop no resize
Image::take($source)
     ->crop(130, 50, 100, 100)
     ->saveTo($destination . 'flower-crop-130-50-100-100.jpg');

// brightness
Image::take($source)
     ->brightness(50)
     ->saveTo($destination . 'flower-brightness-50.jpg');

// contrast
Image::take($source)
     ->contrast(50)
     ->saveTo($destination . 'flower-contrast-50.jpg');

// colorize
Image::take($source)
     ->colorize('#FF0A14', .4)
     ->saveTo($destination . 'flower-colorize-ff0a14-4.jpg');

// grayscale
Image::take($source)
     ->grayscale()
     ->saveTo($destination . 'flower-grayscale.jpg');

// negate
Image::take($source)
     ->negate()
     ->saveTo($destination . 'flower-negate.jpg');

// emboss
Image::take($source)
     ->emboss()
     ->saveTo($destination . 'flower-emboss.jpg');

// blur
Image::take($source)
     ->blur(4)
     ->saveTo($destination . 'flower-blur-4.jpg');

// sharpen
Image::take($source)
     ->sharpen(1)
     ->saveTo($destination . 'flower-sharpen-1.jpg');

// pixelate 1
Image::take($source)
     ->pixelate(2)
     ->saveTo($destination . 'flower-pixelate-2.jpg');

// pixelate 2
Image::take($source)
     ->pixelate(2, true)
     ->saveTo($destination . 'flower-pixelate-2-alt.jpg');

// rotate 1
Image::take(dirname($source) . DS . '3.png')
     ->rotate(45)
     ->saveTo($destination . 'mecha-rotate-45.png');

// rotate 2
Image::take(dirname($source) . DS . '3.png')
     ->rotate(45, '#ffa500', .5)
     ->saveTo($destination . 'mecha-rotate-45-alt.png');

// rotate 3
Image::take($source)
     ->rotate(90)
     ->saveTo($destination . 'flower-rotate-90.png');

// flip h
Image::take($source)
     ->flip('h')
     ->saveTo($destination . 'flower-flip-h.jpg');

// flip v
Image::take($source)
     ->flip('v')
     ->saveTo($destination . 'flower-flip-v.jpg');

// flip b
Image::take($source)
     ->flip('b')
     ->saveTo($destination . 'flower-flip-b.jpg');

// merge 1
Image::take(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge()
     ->saveTo($destination . DS . 'icons-merge-1.png');

// merge 2
Image::take(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge(10)
     ->saveTo($destination . DS . 'icons-merge-2.png');

// merge 3
Image::take(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge(10, 'h')
     ->saveTo($destination . DS . 'icons-merge-3.png');

// merge 4
Image::take(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge(10, 'v', '#ffa500', .5)
     ->saveTo($destination . DS . 'icons-merge-4.png');

// Generate result…
if (file_exists($destination)) {
    foreach (glob($destination . DS . '*.*') as $img) {
        echo '<figure style="border:1px solid;padding:1em;margin:0 0 1em;text-align:center;">';
        echo Asset::png($img);
        echo '<figcaption style="margin-top:1em;">' . basename($img) . '</figcaption>';
        echo '</figure>';
    }
}