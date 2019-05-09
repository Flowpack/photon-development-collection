<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Utility\Files;

class StaticAssetImplementation extends AbstractFusionObject {

    /**
     * @return string
     */
    public function evaluate()
    {
        $source = $this->fusionValue('source');
        $target = $this->fusionValue('target');
        $directory = $this->fusionValue('directory');

        $targetPath = Files::concatenatePaths([$directory, $target]);
        $contentPath = $target;

        if (substr($target, -1, 1) === '/') {
            Files::createDirectoryRecursively($targetPath);
            $targetPath = Files::concatenatePaths([$targetPath, basename($source)]);
            $contentPath = Files::concatenatePaths([$contentPath, basename($source)]);
        }

        copy($source, $targetPath);

        // TODO Emit AssetResult

        return $contentPath;
    }

}
