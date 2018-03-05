<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

use Neos\Photon\ContentRepository\Domain\Service\NodeResolver;

class FileNode implements NodeInterface
{

    /**
     * @var Context
     */
    private $ctx;

    /**
     * @var string
     */
    private $pathAndFilename;

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
    private $properties;

    /**
     * @var NodeResolver
     */
    private $nodeResolver;

    public function __construct(
        Context $ctx,
        $pathAndFilename,
        StaticNodeType $nodeType,
        array $properties,
        NodeResolver $nodeResolver
    ) {
        $this->ctx = $ctx;
        $this->pathAndFilename = $pathAndFilename;

        $this->path = rtrim($pathAndFilename, '.yaml');
        $this->nodeName = basename($this->path);

        $this->nodeType = $nodeType;
        $this->properties = $properties;
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
        return $this->properties;
    }

    public function getChildNodes(): array
    {
        // TODO Also resolve configured child nodes from properties!

        return $this->nodeResolver->childNodesInPath($this->ctx, $this->path);
    }

    public function getNode(string $path): ?NodeInterface
    {
        return $this->nodeResolver->nodeForPath($this->ctx, $path);
    }

}
