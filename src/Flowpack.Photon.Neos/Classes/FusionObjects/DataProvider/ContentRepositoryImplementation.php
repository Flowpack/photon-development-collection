<?php
namespace Flowpack\Photon\Fusion\FusionObjects\DataProvider;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class ContentRepositoryImplementation extends AbstractFusionObject {

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var \Neos\ContentRepository\Domain\Service\ContextFactoryInterface
     */
    protected $contextFactory;

    /**
     * @return mixed
     */
    public function evaluate()
    {
        $packageKey = $this->fusionValue('packageKey');

        return $siteNode;
    }

}
