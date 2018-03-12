<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Flowpack\Photon\Common\Generator\FileResult;

class FilePublisherImplementation extends AbstractFusionObject {

    public function evaluate()
    {
        $filename = $this->fusionValue('filename');
        $content = $this->fusionValue('content');

        // TODO Allow to specify output directory
        file_put_contents($filename, $content);

        return [new FileResult($filename)];
    }

}
