<?php
namespace Flowpack\Photon\Cli\Command;

use Flowpack\Photon\Fusion\Exception\GeneratorException;
use Flowpack\Photon\Fusion\Exception\InvalidGeneratorResultException;
use Flowpack\Photon\Fusion\Exception\InvalidPackageKeyException;
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
     * @param string $contentPath Optional path to static content if not using a package
     * @param string $outputDirectory Output base directory
     * @return void
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     */
    public function contentCommand(string $packageKey, string $targetName = 'default', string $contentPath = null, string $outputDirectory = null): void
    {
        try {
            $results = $this->generator->generate($packageKey, $targetName, $contentPath, $outputDirectory);
        } catch (InvalidGeneratorResultException $e) {
            $this->outputFormatted('<error>Target "%s" did not produce valid results</error>', [$targetName]);
            $this->quit(1);
        } catch (InvalidPackageKeyException $e) {
            $this->outputFormatted('<error>Invalid package key: %s</error>', [$e->getMessage()]);
            $this->quit(2);
        } catch (GeneratorException $e) {
            $this->outputFormatted('<error>Error generating output: %s</error>', [$e->getMessage()]);
            $this->quit(3);
        } catch (\Exception $e) {
            $this->outputFormatted('<error>Unexpected error: %s</error>', [$e->getMessage()]);
            $this->quit(255);
        }

        // TODO Stream result to console
        foreach ($results as $result) {
            $this->outputLine((string)$result);
        }
    }
}
