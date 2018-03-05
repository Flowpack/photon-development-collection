<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

use Neos\Photon\ContentRepository\Domain\Service\NodeResolver;

class FolderNode implements NodeInterface
{

    /**
     * @var Context
     */
    private $ctx;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $nodeName;

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
        string $path,
        StaticNodeType $nodeType,
        NodeResolver $nodeResolver
    ) {
        $this->ctx = $ctx;
        $this->path = $path;

        $this->nodeName = basename($path);

        $this->nodeType = $nodeType;
        $this->nodeResolver = $nodeResolver;
    }

    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    public function getNodeType(): StaticNodeType
    {
        return $this->nodeType;
    }

    public function getParent(): ?NodeInterface
    {
        return $this->nodeResolver->getParentByPath($this->ctx, $this->path);
    }

    public function getProperties(): array
    {
        return [];
    }

    public function getChildNodes(): array
    {
        return $this->nodeResolver->childNodesInPath($this->ctx, $this->path);
    }

    public function getNode(string $path): ?NodeInterface
    {
        return $this->nodeResolver->nodeForPath($this->ctx, $path);
    }

}
