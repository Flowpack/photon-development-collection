<?php
namespace Neos\Photon\ContentRepository\Utility;

class Strings {

    public static function stripSuffix(string $string, string $suffix)
    {
        return substr($string, 0, strrpos($string, $suffix));
    }
}
