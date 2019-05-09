<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractCollectionImplementation;

class DebugImplementation extends AbstractCollectionImplementation {

    /**
     * @return mixed
     */
    public function evaluate()
    {
        $message = $this->fusionValue('message');

        printf("%s\n", $message);

        return $this->fusionValue('value');
    }

}
