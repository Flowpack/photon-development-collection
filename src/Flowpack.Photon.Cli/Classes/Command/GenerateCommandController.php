<?php
namespace Flowpack\Photon\Cli\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class GenerateCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var \Flowpack\Photon\Common\Generator\GeneratorInterface
     */
    protected $generator;

    /**
     * Generate content
     *
     * Generate output of a static publishing target based on a package declaring the rendering
     * and content (or content from a specific directory)
     *
     * @param string $packageKey Package key of a package declaring the rendering and content (can be overridden by contentPath)
     * @param string $targetName Name of the publishing target to generate
     * @param string $contentPath Optional path to content if not using a package
     * @return void
     */
    public function contentCommand(string $packageKey, string $targetName = 'default', string $contentPath = null): void
    {
        $results = $this->generator->generate($packageKey, $targetName, $contentPath);
        foreach ($results as $result) {
            $this->outputLine((string)$result);
        }
    }
}
