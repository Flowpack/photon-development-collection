<?php
namespace Flowpack\Photon\ContentRepository\Domain\Model;

use Flowpack\Photon\ContentRepository\Domain\Service\NodeResolver;
use Flowpack\Photon\ContentRepository\Utility\Strings;

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
     * @var string
     */
    private $nodePath;

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

        if (strpos($pathAndFilename, $ctx->getRootPath()) !== 0) {
            throw new \InvalidArgumentException('path must be inside root path');
        }
        $this->pathAndFilename = $pathAndFilename;

        $this->path = Strings::stripSuffix($pathAndFilename, '.yaml');
        $this->nodeName = basename($this->path);
        $this->nodePath = substr($this->path, strlen($ctx->getRootPath()) + 1);

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

    public function getChildNode(string $nodeName): ?NodeInterface
    {
        if (strpos($nodeName, '/') !== false) {
            throw new \InvalidArgumentException('nodeName must not be a path');
        }

        // First try to find an inline node by node name
        $inlineChildNode = $this->nodeResolver->childNodeForNodeType($this->ctx, $nodeName, $this->nodeType, $this->nodePath, $this->nodeData['__childNodes'] ?? []);
        if ($inlineChildNode !== null) {
            return $inlineChildNode;
        }
        return $this->nodeResolver->nodeForPath($this->ctx, $this->nodePath . '/' . $nodeName);
    }

}
