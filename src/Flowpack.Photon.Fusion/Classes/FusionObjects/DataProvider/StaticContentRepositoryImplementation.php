<?php
namespace Flowpack\Photon\Fusion\FusionObjects\DataProvider;

use Neos\Flow\Annotations as Flow;
use Flowpack\Photon\ContentRepository\Domain\Repository\NodeRepository;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class StaticContentRepositoryImplementation extends AbstractFusionObject {

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @return mixed
     */
    public function evaluate()
    {
        $contentPath = $this->fusionValue('contentPath');
        $packageKey = $this->fusionValue('packageKey');

        $package = $this->packageManager->getPackage($packageKey);
        if ($contentPath === null) {
            $contentPath = $package->getResourcesPath() . '/Private/Content';
        }

        $rootNode = $this->nodeRepository->getRootNode($contentPath);

        return $rootNode;
    }

}
