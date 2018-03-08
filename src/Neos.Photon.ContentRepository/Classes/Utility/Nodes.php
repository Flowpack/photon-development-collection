<?php
namespace Neos\Photon\ContentRepository\Utility;

use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;

class Nodes
{

    public static function walkPath(NodeInterface $node, string $nodePath): ?NodeInterface
    {
        $pathParts = explode('/', $nodePath);
        foreach ($pathParts as $pathPart) {
            $node = $node->getChildNode($pathPart);
            var_dump($node->getNodeName(), get_class($node), $node->getNodeType()->getName());
            if ($node === null) {
                return null;
            }
        }
        return $node;
    }

}
