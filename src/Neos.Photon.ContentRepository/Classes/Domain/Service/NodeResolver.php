<?php
namespace Neos\Photon\ContentRepository\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Photon\ContentRepository\Domain\Model\Context;
use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;
use Neos\Photon\ContentRepository\Domain\Model\StaticNodeType;

class NodeResolver
{

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    public function nodeForPath(Context $ctx, string $nodePath): NodeInterface
    {
        $path = realpath($ctx->getRootPath() . '/' . $nodePath);
        return $this->nodeFactory->nodeByPath($ctx, $path);
    }

    public function childNodesInPath(Context $ctx, string $path): array
    {
        $childNodes = [];
        foreach ($this->iterateChildNodesInPath($ctx, $path) as $childNode) {
            $childNodes[] = $childNode;
        }
        return $childNodes;
    }

    private function iterateChildNodesInPath(Context $ctx, string $path): iterable
    {
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

    private function childNodesForNodeType(Context $ctx, StaticNodeType $nodeType, string $pathAndFilename, array $nodeData): iterable
    {
        $nodePath = rtrim($pathAndFilename, '.yaml');
        $childNodes = $nodeType->getConfiguration('childNodes') ?: [];
        foreach ($childNodes as $childNodeName => $childNodeConfiguration) {
            $inline = $childNodeConfiguration['inline'] ?? false;
            if ($inline) {
                $parentResolver = function () use ($ctx, $pathAndFilename) {
                    return $this->nodeForFile($ctx, $pathAndFilename);
                };
                yield $this->inlineNodes($ctx, $childNodeName, $nodeData[$childNodeName], $parentResolver, $childNodeConfiguration['defaultType'] ?? null);
            }
        }
        foreach ($this->iterateChildNodesInPath($ctx, $nodePath) as $childNode) {
            yield $childNode;
        }
    }

    private function inlineNodes(Context $ctx, string $childNodeName, array $childNodesData, callable $parentResolver, ?string $defaultType): iterable
    {

    }

}
