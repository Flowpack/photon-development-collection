<?php
namespace Flowpack\Photon\ContentRepository\Utility;

use Flowpack\Photon\ContentRepository\Domain\Model\NodeInterface;

class Nodes
{

    public static function walkPath(NodeInterface $node, string $nodePath): ?NodeInterface
    {
        $pathParts = explode('/', $nodePath);
        foreach ($pathParts as $pathPart) {
            $node = $node->getChildNode($pathPart);
            if ($node === null) {
                return null;
            }
        }
        return $node;
    }

}
