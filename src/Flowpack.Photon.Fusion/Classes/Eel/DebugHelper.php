<?php
namespace Flowpack\Photon\Fusion\Eel;

use Neos\Eel\ProtectedContextAwareInterface;

class DebugHelper implements ProtectedContextAwareInterface
{

    public function dump($value, $title = null): string
    {
        if ($value instanceof \JsonSerializable) {
            $title = get_class($value);
            $value = $value->jsonSerialize();
        }
        return \Neos\Flow\var_dump($value, $title, true);
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }

}
