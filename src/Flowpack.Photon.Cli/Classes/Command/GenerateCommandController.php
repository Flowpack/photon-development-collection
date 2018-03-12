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
     * Generate output of a static publishing target with content in a content package
     *
     * @param string $packageKey Package key of a content package
     * @param string $targetName Name of the publishing target to generate
     * @return void
     */
    public function contentCommand(string $packageKey, string $targetName = 'default'): void
    {
        $results = $this->generator->generate($packageKey, $targetName);
        foreach ($results as $result) {
            $this->outputLine((string)$result);
        }
    }
}
