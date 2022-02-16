Image Extension for [Mecha](https://github.com/mecha-cms/mecha)
===============================================================

![Code Size](https://img.shields.io/github/languages/code-size/mecha-cms/x.image?color=%23444&style=for-the-badge)

This extension provides various API to work with BMP, GIF, JPEG, PNG and WEBP images.

---

Release Notes
-------------

### 2.3.0

 - Bug fixes.
 - [@mecha-cms/mecha#96](https://github.com/mecha-cms/mecha/issues/96)

### 2.2.5

 - Small bug fixes.

### 2.2.4

 - Fix encoded data image URL being passed to the `imagecreatefromstring()` without decoding it first.

### 2.2.3

 - API has been reduced to only have an ability to resize and crop images. Other abilities such as flipping and rotating an image can be enabled in a separate extension. This extension is now focused to help authors in generating image thumbnails.
 - The class constructor now accept remote image URL as well as Base64 image URL to be processed by GD.
 - This extension removes the `page.image` plugin and now has an ability to add `image` and `images` property to a page by default.