<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class LocalResourcePublishingImplementation extends AbstractFusionObject {

    /**
     * @Flow\Inject
     * @var \Neos\Flow\ResourceManagement\ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\InjectConfiguration(package="Neos.Flow", path="resource")
     * @var array
     */
    protected $resourceOriginalConfiguration;

    /**
     * @Flow\InjectConfiguration(package="Flowpack.Photon.Common", path="resourceOverride")
     * @var array
     */
    protected $resourceOverrideConfiguration;

    /**
     * @return array
     */
    public function evaluate()
    {
        $this->resetResourceManager();

        $this->publishResources();

        $results = $this->runtime->evaluate($this->path . '/renderer', $this);

        return $results;
    }

    protected function publishResources(): void
    {
        $publishCollections = $this->fusionValue('publishCollections');

        /** @var \Neos\Flow\ResourceManagement\Collection $collection */
        foreach ($this->resourceManager->getCollections() as $collection) {
            if ($publishCollections !== null && !in_array($collection->getName(), $publishCollections, true)) {
                continue;
            }

            $target = $collection->getTarget();
            $target->publishCollection($collection);
        }
    }

    protected function resetResourceManager(): void
    {
        $configuration = $this->getResourceConfiguration();

        $this->resourceManager->injectSettings(['resource' => $configuration]);

        \Neos\Utility\ObjectAccess::setProperty($this->resourceManager, 'initialized', false, true);
        \Neos\Utility\ObjectAccess::setProperty($this->resourceManager, 'storages', null, true);
        \Neos\Utility\ObjectAccess::setProperty($this->resourceManager, 'targets', null, true);
        \Neos\Utility\ObjectAccess::setProperty($this->resourceManager, 'collections', null, true);
    }

    /**
     * @return array
     */
    protected function getResourceConfiguration(): array
    {
        $outputDirectory = $this->fusionValue('outputDirectory');
        // Make sure output directory has trailing slash if not empty
        if ((string)$outputDirectory !== '') {
            $outputDirectory = rtrim($outputDirectory, '/') . '/';
        }

        $configuration = \Neos\Utility\Arrays::arrayMergeRecursiveOverrule(
            $this->resourceOriginalConfiguration,
            $this->resourceOverrideConfiguration
        );

        array_walk_recursive($configuration, function (&$value, $key) use ($outputDirectory) {
            // TODO Do not hardcode keys
            if ($key === 'path') {
                $value = str_replace('{outputDirectory}', $outputDirectory, $value);
            }
        });
        return $configuration;
    }

}
