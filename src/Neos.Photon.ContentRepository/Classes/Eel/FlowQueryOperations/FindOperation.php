<?php
namespace Neos\Photon\ContentRepository\Eel\FlowQueryOperations;

use Neos\Eel\FlowQuery\FizzleParser;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\FlowQueryException;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;
use Neos\Photon\ContentRepository\Domain\Model\NodeInterface;
use Neos\Photon\ContentRepository\Domain\Repository\NodeRepository;
use Neos\Photon\ContentRepository\Utility\Nodes;

/**
 * "find" operation working on ContentRepository nodes. This operation allows for retrieval
 * of nodes specified by a path, identifier or node type (recursive).
 *
 * Example (node name):
 *
 * 	q(node).find('main')
 *
 * Example (relative path):
 *
 * 	q(node).find('main/text1')
 *
 * Example (absolute path):
 *
 * 	q(node).find('/sites/my-site/home')
 *
 * Example (node type):
 *
 * 	q(node).find('[instanceof Neos.NodeTypes:Text]')
 *
 * Example (multiple node types):
 *
 * 	q(node).find('[instanceof Neos.NodeTypes:Text],[instanceof Neos.NodeTypes:Image]')
 *
 * Example (node type with filter):
 *
 * 	q(node).find('[instanceof Neos.NodeTypes:Text][text*="Neos"]')
 *
 */
class FindOperation extends AbstractOperation
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected static $shortName = 'find';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    protected static $priority = 100;

    /**
     * @Flow\Inject
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * {@inheritdoc}
     *
     * @param array (or array-like object) $context onto which this operation should be applied
     * @return boolean TRUE if the operation can be applied onto the $context, FALSE otherwise
     */
    public function canEvaluate($context)
    {
        if (count($context) === 0) {
            return true;
        }

        foreach ($context as $contextNode) {
            if (!$contextNode instanceof NodeInterface) {
                return false;
            }
        }
        return true;
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
        $context = $flowQuery->getContext();
        if (!isset($context[0]) || empty($arguments[0])) {
            return;
        }

        $result = array();
        $selectorAndFilter = $arguments[0];

        $parsedFilter = null;
        $parsedFilter = FizzleParser::parseFilterGroup($selectorAndFilter);
        foreach ($parsedFilter['Filters'] as $filter) {
            $filterResults = array();
            $generatedNodes = false;
            if (isset($filter['PropertyNameFilter']) || isset($filter['PathFilter'])) {
                $nodePath = isset($filter['PropertyNameFilter']) ? $filter['PropertyNameFilter'] : $filter['PathFilter'];
                foreach ($context as $contextNode) {
                    $node = Nodes::walkPath($contextNode, $nodePath);
                    if ($node !== null) {
                        array_push($filterResults, $node);
                    }
                }
                $generatedNodes = true;
            }

            if (isset($filter['AttributeFilters']) && $filter['AttributeFilters'][0]['Operator'] === 'instanceof') {
                foreach ($context as $contextNode) {
                    $nodeType = $filter['AttributeFilters'][0]['Operand'];
                    $filterResults = array_merge($filterResults, $this->nodeRepository->findByParentAndNodeTypeRecursive($contextNode, $nodeType));
                }
                unset($filter['AttributeFilters'][0]);
                $generatedNodes = true;
            }
            if (isset($filter['AttributeFilters']) && count($filter['AttributeFilters']) > 0) {
                if (!$generatedNodes) {
                    throw new FlowQueryException('find() needs a path or instanceof filter for the first filter part', 1436884196);
                }
                $filterQuery = new FlowQuery($filterResults);
                foreach ($filter['AttributeFilters'] as $attributeFilter) {
                    $filterQuery->pushOperation('filter', array($attributeFilter['text']));
                }
                $filterResults = $filterQuery->get();
            }
            $result = array_merge($result, $filterResults);
        }

        $flowQuery->setContext(array_unique($result));
    }

    /**
     * Check if the parsed filters only contain instanceof filters (e.g. "[instanceof Foo],[instanceof Bar]")
     *
     * @param array $filters
     * @return boolean
     */
    protected function hasOnlyInstanceOfFilters(array $filters)
    {
        foreach ($filters as $filter) {
            if (!isset($filter['AttributeFilters']) || count($filter['AttributeFilters']) !== 1 || $filter['AttributeFilters'][0]['Operator'] !== 'instanceof') {
                return false;
            }
        }
        return true;
    }
}
