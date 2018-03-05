<?php
namespace Neos\Photon\Fusion\Generator;

use Neos\Flow\Annotations as Flow;
use Neos\Photon\Common\Generator\FileResult;
use Neos\Photon\Common\Generator\GeneratorInterface;

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

    public function generate(string $packageKey, string $targetName): array
    {
        $fusionConfiguration = $this->fusionConfigurationProvider->getMergedFusionObjectTree($packageKey);
        $runtime = $this->runtimeFactory->create($fusionConfiguration);

        $runtime->pushContext('target', $targetName);
        /** @var array $results */
        $results = $runtime->render('output');
        $runtime->popContext();

        return $results;
    }

}
