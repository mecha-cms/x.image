<?php

class Images extends Files {

    public function file(string $path): \ArrayAccess {
        return $this->image($path);
    }

    public function image(string $path) {
        return new Image($path);
    }

    // TODO
    public static function from(...$lot) {}

}