<?php
namespace Neos\Photon\ContentRepository\Utility;

class Arrays
{

    public static function iterable_to_array(iterable $it): array
    {
        return iterator_to_array((function () use ($it) {
            yield from $it;
        })());
    }

}
