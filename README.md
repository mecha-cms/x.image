Image Extension for Mecha
=========================

Release Notes
-------------

### 2.2.3

 - API has been reduced to only have an ability to resize and crop images. Other abilities such as flipping and rotating an image can be enabled in a separate extension. This extension is now focused to help authors in generating image thumbnails.
 - The class constructor now accept remote image URL as well as Base64 image URL to be processed by GD.
 - This extension removes the `page.image` plugin and now has an ability to add `image` and `images` property to a page by default.
