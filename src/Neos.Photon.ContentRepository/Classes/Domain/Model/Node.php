<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

class Node implements NodeInterface
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
    private $childNodesIterable;

    public function __construct(
        string $nodeName,
        StaticNodeType $nodeType,
        ?callable $parentResolver,
        array $properties,
        iterable $childNodeGenerator
    ) {
        $this->nodeName = $nodeName;
        $this->nodeType = $nodeType;
        $this->parentResolver = $parentResolver;
        $this->properties = $properties;
        $this->childNodesIterable = $childNodeGenerator;
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
        // TODO Cache Parent?
        return $this->parentResolver !== null ? ($this->parentResolver)() : null;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getChildNodes(): array
    {
        $iterable = $this->childNodesIterable;
        return iterator_to_array((function () use ($iterable) {
            yield from $iterable;
        })());
    }

    public function getNode(string $path): ?NodeInterface
    {
        return null;
    }

}
