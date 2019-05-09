<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Flowpack\Photon\Common\Generator\FileResult;
use Neos\Utility\Files;

class FilePublisherImplementation extends AbstractFusionObject {

    public function evaluate()
    {
        $filename = $this->fusionValue('filename');
        $content = $this->fusionValue('content');
        $directory = $this->fusionValue('directory');

        $path = Files::concatenatePaths([$directory, $filename]);

        file_put_contents($path, $content);

        return [new FileResult($path)];
    }

}
