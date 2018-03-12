<?php
namespace Flowpack\Photon\ContentRepository\Tests\Functional\Repository;

use Neos\Flow\Tests\FunctionalTestCase;
use Flowpack\Photon\ContentRepository\Domain\Model\NodeInterface;
use Flowpack\Photon\ContentRepository\Domain\Repository\NodeRepository;
use Flowpack\Photon\ContentRepository\Utility\Nodes;

class NodeRepositoryTest extends FunctionalTestCase
{

    /**
     * @var NodeRepository
     */
    protected $fileNodeRepository;

    public function setUp()
    {
        parent::setUp();
        $this->fileNodeRepository = $this->objectManager->get(NodeRepository::class);
    }

    /**
     * @test
     */
    public function getRootNode_with_path()
    {
        $rootNode = $this->fileNodeRepository->getRootNode(__DIR__ . '/../Fixtures/Content');

        $this->assertNotNull($rootNode, 'Root node was found');

        return $rootNode;
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getNodeName(NodeInterface $rootNode)
    {
        $this->assertSame('', $rootNode->getNodeName());
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getNodeType(NodeInterface $rootNode)
    {
        $nodeType = $rootNode->getNodeType();
        $this->assertNotNull($nodeType, 'Root node returns node type');
        $this->assertSame('unstructured', $nodeType->getName(), 'Node type name matches');

        return $nodeType;
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getChildNodes(NodeInterface $rootNode)
    {
        $childNodes = $rootNode->getChildNodes();

        $this->assertCount(1, $childNodes, 'Root node has one child');
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getParent(NodeInterface $rootNode)
    {
        $parent = $rootNode->getParent();
        $this->assertTrue($parent === null, 'Root node parent is null');
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getNode_with_folder(NodeInterface $rootNode)
    {
        $fileNode = Nodes::walkPath($rootNode, 'fusion/namespaces');
        $this->assertTrue($fileNode !== null, 'Node with path was resolved');
        $this->assertSame('namespaces', $fileNode->getNodeName());
        return $fileNode;
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getNode_with_file(NodeInterface $rootNode)
    {
        $fileNode = Nodes::walkPath($rootNode, 'fusion/namespaces/neos-fusion');
        $this->assertTrue($fileNode !== null, 'Node with path was resolved');
        return $fileNode;
    }

    /**
     * @test
     * @depends rootNode_getNode_with_file
     */
    public function fileNode_getNodeType(NodeInterface $fileNode)
    {
        $nodeType = $fileNode->getNodeType();
        $this->assertNotNull($nodeType, 'File node returns node type');
        $this->assertSame('Flowpack.Photon.ContentRepository.Testing:Content.FusionNamespaceReference', $nodeType->getName(), 'Node type name matches');
    }

    /**
     * @test
     * @depends rootNode_getNode_with_file
     */
    public function fileNode_getProperties(NodeInterface $fileNode)
    {
        $properties = $fileNode->getProperties();
        $this->assertSame([
            'namespace' => 'Neos.Fusion'
        ], $properties);
    }

    /**
     * @test
     * @depends rootNode_getNode_with_file
     */
    public function fileNode_getParent(NodeInterface $fileNode)
    {
        $parent = $fileNode->getParent();
        $this->assertTrue($parent !== null, 'File node parent is not null');
        $this->assertSame('namespaces', $parent->getNodeName());
    }

    /**
     * @test
     * @depends rootNode_getNode_with_folder
     */
    public function folderNode_getParent(NodeInterface $folderNode)
    {
        $parent = $folderNode->getParent();
        $this->assertTrue($parent !== null, 'folder node parent is not null');
        $this->assertSame('fusion', $parent->getNodeName());
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getChildNode_with_inline_child_nodes(NodeInterface $rootNode)
    {
        $fileNode = Nodes::walkPath($rootNode, 'fusion/namespaces/neos-fusion/array');
        $this->assertTrue($fileNode !== null, 'Node with path was resolved');
        return $fileNode;
    }

    /**
     * @test
     * @depends getRootNode_with_path
     * @expectedException \InvalidArgumentException
     */
    public function rootNode_getChildNode_with_path_throws(NodeInterface $rootNode)
    {
        $rootNode->getChildNode('fusion/namespaces/neos-fusion/array');
    }

    /**
     * @test
     * @depends rootNode_getChildNode_with_inline_child_nodes
     */
    public function fileNode_with_inline_getChildNodes(NodeInterface $fileNode)
    {
        $childNodes = $fileNode->getChildNodes();
        $this->assertCount(2, $childNodes, 'Child nodes count matches');

        return $childNodes;
    }

    /**
     * @test
     * @depends rootNode_getChildNode_with_inline_child_nodes
     */
    public function fileNode_with_inline_getChildNode(NodeInterface $fileNode)
    {
        $inlineNode = $fileNode->getChildNode('properties');
        $this->assertTrue($inlineNode !== null, 'Inline child node exists');
        return $inlineNode;
    }

    /**
     * @test
     * @depends fileNode_with_inline_getChildNode
     */
    public function inlineChildNode_getChildNodes(NodeInterface $inlineNode)
    {
        $childNodes = $inlineNode->getChildNodes();
        $this->assertCount(3, $childNodes, 'Child nodes count matches');

        foreach ($childNodes as $childNode) {
            $this->assertSame('Flowpack.Photon.ContentRepository.Testing:Content.PropertyDefinition', $childNode->getNodeType()->getName());
        }

        return $childNodes;
    }

    /**
     * @test
     * @depends fileNode_with_inline_getChildNode
     */
    public function inlineChildNode_getParent_resolves_file_node(NodeInterface $inlineNode)
    {
        $parent = $inlineNode->getParent();
        $this->assertTrue($parent !== null, 'Parent node is not null');
        $this->assertSame('array', $parent->getNodeName());
        $this->assertSame('Flowpack.Photon.ContentRepository.Testing:Content.ObjectDefinition', $parent->getNodeType()->getName());
    }
}
