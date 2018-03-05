<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

use Neos\Photon\ContentRepository\Domain\Service\NodeResolver;

class RootNode implements NodeInterface
{

    /**
     * @var Context
     */
    private $ctx;

    /**
     * @var StaticNodeType
     */
    private $nodeType;

    /**
     * @var NodeResolver
     */
    private $nodeResolver;

    public function __construct(
        Context $ctx,
        StaticNodeType $nodeType,
        NodeResolver $nodeResolver
    ) {
        $this->ctx = $ctx;
        $this->nodeType = $nodeType;
        $this->nodeResolver = $nodeResolver;
    }

    public function getNodeName(): string
    {
        return '';
    }

    public function getNodeType(): StaticNodeType
    {
        return $this->nodeType;
    }

    public function getParent(): ?NodeInterface
    {
        return null;
    }

    public function getProperties(): array
    {
        return [];
    }

    public function getChildNodes(): array
    {
        return $this->nodeResolver->childNodesInPath($this->ctx, $this->ctx->getRootPath());
    }

    public function getNode(string $path): ?NodeInterface
    {
        return $this->nodeResolver->nodeForPath($this->ctx, $path);
    }

}
