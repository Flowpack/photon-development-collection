<?php
namespace Neos\Photon\ContentRepository\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Photon\ContentRepository\Domain\Model\Context;
use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;
use Neos\Photon\ContentRepository\Domain\Model\StaticNodeType;
use Neos\Photon\ContentRepository\Utility\Arrays;

class NodeResolver
{

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    public function nodeForPath(Context $ctx, string $nodePath): NodeInterface
    {
        $path = $ctx->getRootPath() . '/' . $nodePath;
        return $this->nodeFactory->nodeByPath($ctx, $path);
    }

    public function parentNodeForPath(Context $ctx, string $path): NodeInterface
    {
        $parentPath = dirname($path);
        return $this->nodeForPath($ctx, $parentPath);
    }

    public function childNodesInPath(Context $ctx, string $path): array
    {
        return Arrays::iterable_to_array(
            $this->iterateChildNodesInPath($ctx, $path)
        );
    }

    private function iterateChildNodesInPath(Context $ctx, string $path): iterable
    {
        if (is_dir($path)) {
            foreach (new \DirectoryIterator($path) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                } elseif ($fileInfo->isFile()) {
                    yield $this->nodeFactory->nodeForFile($ctx, $fileInfo->getPathname());
                } elseif ($fileInfo->isDir()) {
                    // Only create a folder node if no file exists with the same name
                    if (!file_exists($fileInfo->getPathname() . '.yaml')) {
                        yield $this->nodeFactory->folderNode($ctx, $fileInfo->getPathname());
                    }
                }
            }
        }
    }

    public function childNodesForNodeType(
        Context $ctx,
        StaticNodeType $nodeType,
        string $path,
        array $childNodesData
    ): array {
        return Arrays::iterable_to_array(
            $this->iterateChildNodesForNodeType(
                $ctx,
                $nodeType->getConfiguration('childNodes') ?: [],
                $path,
                $childNodesData
            )
        );
    }

    private function iterateChildNodesForNodeType(
        Context $ctx,
        array $childNodesConfiguration,
        string $path,
        array $childNodesData
    ): iterable {
        foreach ($childNodesConfiguration as $childNodeName => $childNodeConfiguration) {
            $inline = $childNodeConfiguration['inline'] ?? false;
            if ($inline) {
                yield $this->nodeFactory->inlineNode(
                    $ctx,
                    $path,
                    $childNodeName,
                    $childNodesData[$childNodeName] ?? [],
                    $childNodeConfiguration
                );
            }
        }
    }

    public function childNodeForNodeType(
        $ctx,
        string $nodeName,
        StaticNodeType $nodeType,
        string $path,
        array $childNodesData
    ): ?NodeInterface
    {
        $childNodesConfiguration = $nodeType->getConfiguration('childNodes') ?: [];
        if (!isset($childNodesConfiguration[$nodeName])) {
            return null;
        }
        $childNodeConfiguration = $childNodesConfiguration[$nodeName];

        $inline = $childNodeConfiguration['inline'] ?? false;
        if ($inline) {
            return $this->nodeFactory->inlineNode(
                $ctx,
                $path,
                $nodeName,
                $childNodesData[$nodeName] ?? [],
                $childNodeConfiguration
            );
        }

        return null;
    }

    public function childNodesForInlineNode(Context $ctx, string $path, array $nodeConfiguration, array $childNodesData): array
    {
        $childNodeConfiguration = [];
        if (isset($nodeConfiguration['defaultType'])) {
            $childNodeConfiguration['type'] = $nodeConfiguration['defaultType'];
        }

        $childNodes = [];
        foreach ($childNodesData as $childNodeName => $childNodeData) {
            $childNodes[$childNodeName] = $this->nodeFactory->inlineNode(
                $ctx,
                $path,
                $childNodeName,
                $childNodeData,
                $childNodeConfiguration
            );
        }
        return $childNodes;
    }

}
