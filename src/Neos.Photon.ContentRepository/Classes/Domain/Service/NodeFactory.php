<?php
namespace Neos\Photon\ContentRepository\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\Photon\ContentRepository\Domain\Model\Context;
use Neos\Photon\ContentRepository\Domain\Model\FileNode;
use Neos\Photon\ContentRepository\Domain\Model\FolderNode;
use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;

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
            return new \Neos\Photon\ContentRepository\Domain\Model\RootNode(
                $ctx,
                $this->staticNodeTypeManager->getNodeType('unstructured')
            );
        }

        return $this->folderNode($ctx, $path);
    }

    public function nodeForFile(Context $ctx, string $pathAndFilename): NodeInterface
    {
        $nodeData = $this->yamlSource->load(rtrim($pathAndFilename, '.yaml'));
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
}
