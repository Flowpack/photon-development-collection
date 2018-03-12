<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

class Context
{

    /**
     * @var string
     */
    private $rootPath;

    public static function forRoot(string $path)
    {
        return new Context(realpath($path));
    }

    protected function __construct($path)
    {
        $this->rootPath = $path;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

}
