<?php
namespace Flowpack\Photon\Neos\FusionObjects\DataProvider;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Neos\Domain\Service\ContentContext;

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
     * @Flow\Inject
     * @var \Neos\Neos\Domain\Repository\SiteRepository
     */
    protected $siteRepository;

    /**
     * @return mixed
     */
    public function evaluate()
    {
        $packageKey = $this->fusionValue('packageKey');
        $site = $this->siteRepository->findOneBySiteResourcesPackageKey($packageKey);

        if ($site === null) {
            throw new \Flowpack\Photon\Neos\Exception\InvalidSiteException(sprintf('Could not find site for package "%s"', $packageKey), 1557555284);
        }

        /** @var ContentContext $context */
        $context = $this->contextFactory->create([
            'currentSite' => $site
        ]);

        $siteNode = $context->getCurrentSiteNode();

        return $siteNode;
    }

}
