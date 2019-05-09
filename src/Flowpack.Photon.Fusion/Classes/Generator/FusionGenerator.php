<?php
namespace Flowpack\Photon\Fusion\Generator;

use Flowpack\Photon\Fusion\Exception\GeneratorException;
use Flowpack\Photon\Fusion\Exception\InvalidGeneratorResultException;
use Neos\Flow\Annotations as Flow;
use Flowpack\Photon\Common\Generator\GeneratorInterface;
use Flowpack\Photon\ContentRepository\Domain\Repository\NodeRepository;
use Neos\Fusion\Exception\RuntimeException;

/**
 * @Flow\Scope("singleton")
 */
class FusionGenerator implements GeneratorInterface
{

    /**
     * @Flow\Inject
     * @var \Neos\Fusion\Core\RuntimeFactory
     */
    protected $runtimeFactory;

    /**
     * @Flow\Inject
     * @var \Flowpack\Photon\Fusion\Fusion\ConfigurationProvider
     */
    protected $fusionConfigurationProvider;

    /**
     * @Flow\Inject
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    public function generate(string $packageKey, string $targetName, ?string $contentPath, ?string $outputDirectory): array
    {
        if (!$this->packageManager->isPackageActive($packageKey)) {
            throw new \Flowpack\Photon\Fusion\Exception\InvalidPackageKeyException(sprintf('Package key "%s" is not active',
                $packageKey), 1556284688);
        }

        $fusionConfiguration = $this->fusionConfigurationProvider->getMergedFusionObjectTree($packageKey);
        $runtime = $this->runtimeFactory->create($fusionConfiguration);

        $package = $this->packageManager->getPackage($packageKey);
        if ($contentPath === null) {
            $contentPath = $package->getResourcesPath() . '/Private/Content';
        }
        $rootNode = $this->nodeRepository->getRootNode($contentPath);

        if ($outputDirectory !== null) {
            \Neos\Utility\Files::createDirectoryRecursively($outputDirectory);
        }

        $runtime->pushContextArray([
            'target' => $targetName,
            'root' => $rootNode,
            'outputDirectory' => $outputDirectory
        ]);
        /** @var array $results */
        try {
            $results = $runtime->render('output');
        } catch (RuntimeException $e) {
            throw new GeneratorException(sprintf("Fusion exception at path:\n  %s\n\n%s", $e->getFusionPath(), $e->getPrevious()->getMessage()));
        }
        $runtime->popContext();

        if (!is_array($results)) {
            throw new InvalidGeneratorResultException('results was not an array');
        }

        return $results;
    }

}
