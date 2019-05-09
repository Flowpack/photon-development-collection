<?php
namespace Flowpack\Photon\Fusion\Eel;

use Ausi\SlugGenerator\SlugGenerator;
use Neos\Eel\ProtectedContextAwareInterface;

class SlugHelper implements ProtectedContextAwareInterface
{

    public function generate(string $text): string
    {
        $generator = new SlugGenerator();
        return $generator->generate($text);
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }

}
