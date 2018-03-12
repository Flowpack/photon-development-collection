<?php
namespace Flowpack\Photon\ContentRepository\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Flowpack\Photon\ContentRepository\Domain\Model\Context;
use Flowpack\Photon\ContentRepository\Domain\Model\NodeInterface;
use Flowpack\Photon\ContentRepository\Domain\Service\NodeResolver;
use Flowpack\Photon\ContentRepository\Exception;

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

    public function findByParentAndNodeTypeRecursive(NodeInterface $parentNode, string $nodeTypeName): array
    {
        throw new Exception('Not implemented');
    }

}
