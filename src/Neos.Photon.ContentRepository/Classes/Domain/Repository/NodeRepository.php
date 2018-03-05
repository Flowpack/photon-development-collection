<?php
namespace Neos\Photon\ContentRepository\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Photon\ContentRepository\Domain\Model\Context;
use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;
use Neos\Photon\ContentRepository\Domain\Service\NodeResolver;

class NodeRepository
{

    /**
     * @Flow\Inject
     * @var NodeResolver
     */
    protected $nodeResolver;

    public function getRootNode(string $path): NodeInterface
    {
        $ctx = Context::forRoot($path);
        return $this->nodeResolver->nodeForPath($ctx, '');
    }

}
