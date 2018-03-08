<?php
namespace Neos\Photon\Fusion\Generator;

use Neos\Flow\Annotations as Flow;
use Neos\Photon\Common\Generator\FileResult;
use Neos\Photon\Common\Generator\GeneratorInterface;
use Neos\Photon\ContentRepository\Domain\Repository\NodeRepository;

/**
 * @Flow\Scope("singleton")
 */
class FusionGenerator implements GeneratorInterface {

    /**
     * @Flow\Inject
     * @var \Neos\Fusion\Core\RuntimeFactory
     */
    protected $runtimeFactory;

    /**
     * @Flow\Inject
     * @var \Neos\Photon\Fusion\Fusion\ConfigurationProvider
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

    public function generate(string $packageKey, string $targetName): array
    {
        $fusionConfiguration = $this->fusionConfigurationProvider->getMergedFusionObjectTree($packageKey);
        $runtime = $this->runtimeFactory->create($fusionConfiguration);

        $package = $this->packageManager->getPackage($packageKey);
        $packageContentPath = $package->getResourcesPath() . '/Private/Content';
        $rootNode = $this->nodeRepository->getRootNode($packageContentPath);

        $runtime->pushContextArray([
            'target' => $targetName,
            'root' => $rootNode
        ]);
        /** @var array $results */
        $results = $runtime->render('output');
        $runtime->popContext();

        return $results;
    }

}
