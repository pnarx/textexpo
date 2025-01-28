<?php 

namespace Http;
class TwigHelpers {
    public static function asset($path)
    {
        return '/public/assets' . $path;
    }
}