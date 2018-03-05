<?php
namespace Neos\Photon\ContentRepository\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\Photon\ContentRepository\Domain\Model\Context;
use Neos\Photon\ContentRepository\Domain\Model\Node;
use Neos\Photon\ContentRepository\Domain\Model\StaticNodeType;
use Neos\Photon\ContentRepository\Domain\Service\StaticNodeTypeManager;

class FileNodeRepository
{

    /**
     * @Flow\Inject
     * @var YamlSource
     */
    protected $yamlSource;

    /**
     * @Flow\Inject
     * @var StaticNodeTypeManager
     */
    protected $staticNodeTypeManager;

    public function getRootNode(string $path): Node
    {
        $ctx = Context::forRoot($path);
        return $this->folderNode($ctx, '', $path);
    }

    private function childNodesInPath(Context $ctx, string $path): iterable
    {
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            } elseif ($fileInfo->isFile()) {
                yield $this->nodeForFile($ctx, $fileInfo->getPathname());
            } elseif ($fileInfo->isDir()) {
                // Only create a folder node if no file exists with the same name
                if (!file_exists($fileInfo->getPathname() . '.yaml')) {
                    $nodeName = $fileInfo->getFilename();
                    yield $this->folderNode($ctx, $nodeName, $fileInfo->getPathname());
                }
            }
        }
    }

    private function nodeForFile(Context $ctx, string $pathAndFilename): Node
    {
        $nodeData = $this->yamlSource->load($pathAndFilename);
        $nodeTypeName = $nodeData['__type'] ?? 'unstructured';
        unset($nodeData['__type']);
        $nodeType = $this->staticNodeTypeManager->getNodeType($nodeTypeName);
        $nodeName = basename($pathAndFilename, '.yaml');

        $parentResolver = function () use ($ctx, $pathAndFilename) {
            $parentPath = dirname($pathAndFilename);
            return $this->nodeByPath($ctx, $parentPath);
        };

        return new Node(
            $nodeName,
            $nodeType,
            $parentResolver,
            $nodeData,
            $this->childNodesForNodeType($ctx, $nodeType, $pathAndFilename, $nodeData)
        );
    }

    private function folderNode(Context $ctx, string $nodeName, string $path): Node
    {
        $parentPath = dirname($path);
        $startsWithRootPath = strpos($parentPath, $ctx->getRootPath()) === 0;
        if ($startsWithRootPath) {
            $parentResolver = function () use ($ctx, $parentPath) {
                return $this->nodeByPath($ctx, $parentPath);
            };
        } else {
            $parentResolver = null;
        }
        return new Node(
            $nodeName,
            $this->staticNodeTypeManager->getNodeType('unstructured'),
            $parentResolver,
            [],
            $this->childNodesInPath($ctx, $path)
        );
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
        foreach ($this->childNodesInPath($ctx, $nodePath) as $childNode) {
            yield $childNode;
        }
    }

    private function nodeByPath(Context $ctx, string $path): Node
    {
        $path = rtrim($path, '/');
        $pathAndFilename = $path . '.yaml';
        if (file_exists($pathAndFilename)) {
            return $this->nodeForFile($ctx, $pathAndFilename);
        }

        $nodeName = $path === $ctx->getRootPath() ? '' : basename($path);
        return $this->folderNode($ctx, $nodeName, $path);
    }

    private function inlineNodes(Context $ctx, string $childNodeName, array $childNodesData, callable $parentResolver, ?string $defaultType): iterable
    {

    }

}
