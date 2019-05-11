<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Fusion\FusionObjects\RawArrayImplementation;

class PublishArrayImplementation extends RawArrayImplementation {

    /**
     * Evaluate publishers in the array and merge results
     *
     * @return array
     */
    public function evaluate()
    {
        $results = parent::evaluate();
        if ($results === null || $results === []) {
            return [];
        }
        return call_user_func_array('array_merge', $results);
    }

}
