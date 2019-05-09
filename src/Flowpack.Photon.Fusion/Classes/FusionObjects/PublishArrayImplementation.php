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
        return call_user_func_array('array_merge', parent::evaluate());
    }

}
