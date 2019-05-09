<?php
namespace Flowpack\Photon\Fusion\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Flowpack\Photon\Common\Generator\FileResult;
use Neos\Utility\Files;

class ResourcesPublisherImplementation extends AbstractFusionObject {

    public function evaluate()
    {
        $path = $this->fusionValue('path');
        $filter = $this->fusionValue('filter');
        $target = $this->fusionValue('target');
        $directory = $this->fusionValue('directory');

        $it = new \DirectoryIterator($path);

        $pattern = '#' . str_replace('*', '.*', str_replace('.', '\.', $filter)) . '#';

        $files = [];
        foreach ($it as $entry) {
            if (preg_match($pattern, $entry->getFilename())) {
                $targetPath = Files::concatenatePaths([$directory, $target, $entry->getFilename()]);
                $sourcePath = $entry->getPathname();
                $files[] = $targetPath;

                $dir = dirname($targetPath);
                Files::createDirectoryRecursively($dir);

                copy($sourcePath, $targetPath);
            }
        }

        return array_map(function($file) {
            return new FileResult($file);
        }, $files);
    }

}
