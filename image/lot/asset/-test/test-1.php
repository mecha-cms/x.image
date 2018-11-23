<?php

$source = __DIR__ . DS . '2.jpg';
$destination = dirname($source) . DS . 'result' . DS;

// resize
Image::open($source)
     ->resize(200, 200)
     ->saveTo($destination . 'flower.resize.200.jpg');

// resize dis-proportional
Image::open($source)
     ->resize(200, 200, false)
     ->saveTo($destination . 'flower.resize.200,200.jpg');

// crop
Image::open($source)
     ->crop(72, 72)
     ->saveTo($destination . 'flower.crop.72,72.jpg');

// crop no resize
Image::open($source)
     ->crop(130, 50, 100, 100)
     ->saveTo($destination . 'flower.crop.130,50,100,100.jpg');

// brightness
Image::open($source)
     ->bright(50)
     ->saveTo($destination . 'flower.bright.50.jpg');

// contrast
Image::open($source)
     ->contrast(50)
     ->saveTo($destination . 'flower.contrast.50.jpg');

// colorize
Image::open($source)
     ->color('#FF0A14', .4)
     ->saveTo($destination . 'flower.color.ff0a14,0.4.jpg');

// grayscale
Image::open($source)
     ->filter('grayscale')
     ->saveTo($destination . 'flower.grayscale.jpg');

// negate
Image::open($source)
     ->filter('invert')
     ->saveTo($destination . 'flower.invert.jpg');

// emboss
Image::open($source)
     ->filter('emboss')
     ->saveTo($destination . 'flower.emboss.jpg');

// blur
Image::open($source)
     ->filter('blur', 4)
     ->saveTo($destination . 'flower.blur,4.jpg');

// sharpen
Image::open($source)
     ->filter('sharp', 1)
     ->saveTo($destination . 'flower.sharp,1.jpg');

// pixelate 1
Image::open($source)
     ->filter('pixelate', 2)
     ->saveTo($destination . 'flower.pixelate,2.jpg');

// pixelate 2
Image::open($source)
     ->filter('pixelate', 2, true)
     ->saveTo($destination . 'flower.pixelate,2-alt.jpg');

// rotate 1
Image::open(dirname($source) . DS . '3.png')
     ->rotate(45)
     ->saveTo($destination . 'mecha.rotate.45.png');

// rotate 2
Image::open(dirname($source) . DS . '3.png')
     ->rotate(45, '#ffa500', .5)
     ->saveTo($destination . 'mecha.rotate.45-alt.png');

// rotate 3
Image::open($source)
     ->rotate(90)
     ->saveTo($destination . 'flower.rotate.90.png');

// flip h
Image::open($source)
     ->flip('h')
     ->saveTo($destination . 'flower.flip.h.jpg');

// flip v
Image::open($source)
     ->flip('v')
     ->saveTo($destination . 'flower.flip.v.jpg');

// flip b
Image::open($source)
     ->flip('b')
     ->saveTo($destination . 'flower.flip.b.jpg');

// merge 1
Image::open(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge()
     ->saveTo($destination . DS . 'icons.merge.png');

// merge 2
Image::open(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge(10)
     ->saveTo($destination . DS . 'icons.merge.10.png');

// merge 3
Image::open(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge(10, 'h')
     ->saveTo($destination . DS . 'icons.merge.10,h.png');

// merge 4
Image::open(glob(dirname($source) . DS . '1' . DS . '*.png'))
     ->merge(10, 'v', '#ffa500', .5)
     ->saveTo($destination . DS . 'icons.merge.10,v,ffa500,0.5.png');

// Generate result…
if (file_exists($destination)) {
    foreach (glob($destination . DS . '*.*') as $img) {
        echo '<figure style="border:1px solid;padding:1em;margin:0 0 1em;text-align:center;">';
        echo Asset::png($img);
        echo '<figcaption style="margin-top:1em;">' . basename($img) . '</figcaption>';
        echo '</figure>';
    }
}