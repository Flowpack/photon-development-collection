<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractCollectionImplementation;

class PublishCollectionImplementation extends AbstractCollectionImplementation {

    /**
     * Evaluate publishers in the collection and merge results
     *
     * @return array
     */
    public function evaluate()
    {
        return call_user_func_array('array_merge', parent::evaluateAsArray());
    }

}
