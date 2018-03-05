<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

use Neos\Photon\ContentRepository\Domain\Service\NodeResolver;
use Neos\Photon\ContentRepository\Utility\Strings;

class InlineNode implements NodeInterface
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
     * @var array
     */
    private $nodeConfiguration;

    /**
     * @var array
     */
    private $nodeData;

    /**
     * @var NodeResolver
     */
    private $nodeResolver;

    public function __construct(
        Context $ctx,
        string $path,
        StaticNodeType $nodeType,
        array $nodeConfiguration,
        array $nodeData,
        NodeResolver $nodeResolver
    ) {
        $this->ctx = $ctx;

        $this->path = $path;
        $this->nodeName = basename($path);

        $this->nodeType = $nodeType;
        $this->nodeConfiguration = $nodeConfiguration;
        $this->nodeData = $nodeData;
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
        return $this->nodeResolver->parentNodeForPath($this->ctx, $this->path);
    }

    public function getProperties(): array
    {
        return $this->nodeData;
    }

    public function getChildNodes(): array
    {
        return $this->nodeResolver->childNodesForInlineNode($this->ctx, $this->path, $this->nodeConfiguration, $this->nodeData['__childNodes'] ?? []);
    }

    public function getNode(string $path): ?NodeInterface
    {
        // TODO Implement getNode for InlineNode
        return null;
    }

}
