<?php
namespace Neos\Photon\ContentRepository\Tests\Functional\Repository;

use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;
use Neos\Photon\ContentRepository\Domain\Repository\NodeRepository;

class NodeRepositoryTest extends \Neos\Flow\Tests\FunctionalTestCase
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
    public function rootNode_getNode_with_file(NodeInterface $rootNode)
    {
        $fileNode = $rootNode->getNode('fusion/namespaces/neos-fusion');
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
        $this->assertSame('Neos.Photon.ContentRepository.Testing:Content.FusionNamespaceReference',
            $nodeType->getName(), 'Node type name matches');
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
}
