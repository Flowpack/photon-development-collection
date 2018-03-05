<?php
namespace Neos\Photon\ContentRepository\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\Photon\ContentRepository\Domain\Model\Context;
use Neos\Photon\ContentRepository\Domain\Model\FileNode;
use Neos\Photon\ContentRepository\Domain\Model\FolderNode;
use Neos\Photon\ContentRepository\Domain\Model\InlineNode;
use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;
use Neos\Photon\ContentRepository\Domain\Model\RootNode;
use Neos\Photon\ContentRepository\Utility\Strings;

class NodeFactory
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

    public function nodeByPath(Context $ctx, string $path): NodeInterface
    {
        $path = rtrim($path, '/');
        $pathAndFilename = $path . '.yaml';
        if (file_exists($pathAndFilename)) {
            return $this->nodeForFile($ctx, $pathAndFilename);
        }

        if ($path === $ctx->getRootPath()) {
            return new RootNode(
                $ctx,
                $this->staticNodeTypeManager->getNodeType('unstructured')
            );
        }

        // TODO Check if folder actually exists
        return $this->folderNode($ctx, $path);
    }

    public function nodeForFile(Context $ctx, string $pathAndFilename): NodeInterface
    {
        $nodeData = $this->yamlSource->load(Strings::stripSuffix($pathAndFilename, '.yaml'));
        $nodeTypeName = $nodeData['__type'] ?? 'unstructured';
        unset($nodeData['__type']);
        $nodeType = $this->staticNodeTypeManager->getNodeType($nodeTypeName);

        return new FileNode(
            $ctx,
            $pathAndFilename,
            $nodeType,
            $nodeData
        );
    }

    public function folderNode(Context $ctx, string $path): NodeInterface
    {
        return new FolderNode(
            $ctx,
            $path,
            $this->staticNodeTypeManager->getNodeType('unstructured')
        );
    }

    public function inlineNode(Context $ctx, string $path, string $childNodeName, array $childNodesData, array $childNodeConfiguration)
    {
        $nodeTypeName = $childNodeConfiguration['type'] ?? 'unstructured';
        $nodeType = $this->staticNodeTypeManager->getNodeType($nodeTypeName);

        return new InlineNode(
            $ctx,
            $path . '/' . $childNodeName,
            $nodeType,
            $childNodeConfiguration,
            $childNodesData
        );
    }
}
