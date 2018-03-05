<?php
namespace Neos\Photon\ContentRepository\Tests\Functional\Repository;

use Neos\Photon\ContentRepository\Domain\Model\Node;
use Neos\Photon\ContentRepository\Domain\Repository\FileNodeRepository;

class FileNodeRepositoryTest extends \Neos\Flow\Tests\FunctionalTestCase {

    /**
     * @var FileNodeRepository
     */
    protected $fileNodeRepository;

    public function setUp()
    {
        parent::setUp();
        $this->fileNodeRepository = $this->objectManager->get(FileNodeRepository::class);
    }

    /**
     * @test
     */
    public function getRootNode_with_path() {
        $rootNode = $this->fileNodeRepository->getRootNode(__DIR__ . '/../Fixtures/Content');

        $this->assertNotNull($rootNode, 'Root node was found');

        return $rootNode;
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getNodeName(Node $rootNode)
    {
        $this->assertSame('', $rootNode->getNodeName());
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getNodeType(Node $rootNode)
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
    public function rootNode_getChildNodes(Node $rootNode) {
        $childNodes = [];
        foreach ($rootNode->getChildNodes() as $childNode) {
            $childNodes[] = $childNode;
        }

        $this->assertCount(1, $childNodes, 'Root node has one child');
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getParent(Node $rootNode) {
        $parent = $rootNode->getParent();
        $this->assertTrue($parent === null, 'Root node parent is null');
    }

    /**
     * @test
     * @depends getRootNode_with_path
     */
    public function rootNode_getNode_with_file(Node $rootNode) {
        $this->markTestIncomplete('getNode by path not yet done');

        $fileNode = $rootNode->getNode('fusion/namespaces/neos-fusion');
        $this->assertTrue($fileNode !== null, 'Node with path was resolved');
    }
}
