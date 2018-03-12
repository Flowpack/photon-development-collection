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
    private $nodePath;

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
        string $nodePath,
        StaticNodeType $nodeType,
        array $nodeConfiguration,
        array $nodeData,
        NodeResolver $nodeResolver
    ) {
        $this->ctx = $ctx;

        $this->nodePath = $nodePath;
        $this->nodeName = basename($nodePath);

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
        return $this->nodeResolver->parentNodeForPath($this->ctx, $this->nodePath);
    }

    public function getProperties(): array
    {
        return $this->nodeData;
    }

    public function getChildNodes(): array
    {;
        return $this->nodeResolver->childNodesForInlineNode($this->ctx, $this->nodePath, $this->nodeConfiguration, $this->nodeData['__childNodes'] ?? []);
    }

    public function getChildNode(string $nodeName): ?NodeInterface
    {
        if (strpos($nodeName, '/') !== false) {
            throw new \InvalidArgumentException('nodeName must not be a path');
        }
        // TODO Implement getChildNode for InlineNode
        return null;
    }

}
