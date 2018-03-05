<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

class Node
{

    /**
     * @var string
     */
    protected $nodeName;

    /**
     * @var StaticNodeType
     */
    private $nodeType;

    /**
     * @var callable
     */
    private $parentResolver;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var iterable
     */
    private $childNodes;

    public function __construct(
        string $nodeName,
        StaticNodeType $nodeType,
        ?callable $parentResolver,
        array $properties,
        iterable $childNodes
    ) {
        $this->nodeName = $nodeName;
        $this->nodeType = $nodeType;
        $this->parentResolver = $parentResolver;
        $this->properties = $properties;
        $this->childNodes = $childNodes;
    }

    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    public function getNodeType(): StaticNodeType
    {
        return $this->nodeType;
    }

    public function getParent(): ?Node
    {
        // TODO Cache Parent?
        return $this->parentResolver !== null ? ($this->parentResolver)() : null;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getChildNodes(): iterable
    {
        return $this->childNodes;
    }

    public function getNode(string $path): ?Node
    {
        return null;
    }

}
