<?php
namespace Neos\Photon\ContentRepository\Eel\FlowQueryOperations;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;
use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;

/**
 * "next" operation working on ContentRepository nodes. It iterates over all
 * context elements and returns the immediately following sibling.
 * If an optional filter expression is provided, it only returns the node
 * if it matches the given expression.
 */
class NextOperation extends AbstractOperation
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected static $shortName = 'next';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    protected static $priority = 100;

    /**
     * {@inheritdoc}
     *
     * @param array (or array-like object) $context onto which this operation should be applied
     * @return boolean TRUE if the operation can be applied onto the $context, FALSE otherwise
     */
    public function canEvaluate($context)
    {
        return count($context) === 0 || (isset($context[0]) && ($context[0] instanceof NodeInterface));
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return void
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        $output = array();
        $outputNodePaths = array();
        foreach ($flowQuery->getContext() as $contextNode) {
            $nextNode = $this->getNextForNode($contextNode);
            if ($nextNode !== null && !isset($outputNodePaths[$nextNode->getPath()])) {
                $outputNodePaths[$nextNode->getPath()] = true;
                $output[] = $nextNode;
            }
        }
        $flowQuery->setContext($output);

        if (isset($arguments[0]) && !empty($arguments[0])) {
            $flowQuery->pushOperation('filter', $arguments);
        }
    }

    /**
     * @param NodeInterface $contextNode The node for which the preceding node should be found
     * @return NodeInterface The following node of $contextNode or NULL
     */
    protected function getNextForNode($contextNode)
    {
        $nodesInContext = $contextNode->getParent()->getChildNodes();
        for ($i = 1; $i < count($nodesInContext); $i++) {
            if ($nodesInContext[$i - 1] === $contextNode) {
                return $nodesInContext[$i];
            }
        }
        return null;
    }
}
