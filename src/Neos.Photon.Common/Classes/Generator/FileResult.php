<?php
namespace Neos\Photon\Common\Generator;

final class FileResult {

    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function __toString()
    {
        return $this->path;
    }

}
