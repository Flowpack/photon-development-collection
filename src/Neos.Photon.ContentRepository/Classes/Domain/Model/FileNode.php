<?php
namespace Neos\Photon\ContentRepository\Domain\Model;

use Neos\Photon\ContentRepository\Domain\Service\NodeResolver;
use Neos\Photon\ContentRepository\Utility\Strings;

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
    private $nodeData;

    /**
     * @var NodeResolver
     */
    private $nodeResolver;

    public function __construct(
        Context $ctx,
        $pathAndFilename,
        StaticNodeType $nodeType,
        array $nodeData,
        NodeResolver $nodeResolver
    ) {
        $this->ctx = $ctx;
        $this->pathAndFilename = $pathAndFilename;

        $this->path = Strings::stripSuffix($pathAndFilename, '.yaml');
        $this->nodeName = basename($this->path);

        $this->nodeType = $nodeType;
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
        return array_merge(
            $this->nodeResolver->childNodesInPath($this->ctx, $this->path),
            $this->nodeResolver->childNodesForNodeType($this->ctx, $this->nodeType, $this->path, $this->nodeData['__childNodes'] ?? [])
        );
    }

    public function getNode(string $path): ?NodeInterface
    {
        $inlineChildNode = $this->nodeResolver->childNodeForNodeType($this->ctx, $path, $this->nodeType, $this->path, $this->nodeData['__childNodes'] ?? []);
        if ($inlineChildNode !== null) {
            return $inlineChildNode;
        }
        return $this->nodeResolver->nodeForPath($this->ctx, $path);
    }

}
