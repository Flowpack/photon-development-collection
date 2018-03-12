<?php
namespace Flowpack\Photon\Fusion\Fusion;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\Files;
use Neos\Fusion\Core\Parser;

/**
 * @Flow\Scope("singleton")
 */
class ConfigurationProvider
{
    /**
     * @Flow\Inject
     * @var Parser
     */
    protected $fusionParser;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Pattern used for determining the Fusion root file for a package
     *
     * @var string
     */
    protected $rootFusionPattern = 'resource://%s/Private/Fusion/Root.fusion';

    /**
     * @Flow\InjectConfiguration("fusion.autoInclude")
     * @var array
     */
    protected $autoIncludeConfiguration = array();

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    public function initializeObject(): void
    {
        $this->fusionParser->setObjectTypeNamespace('default', 'Flowpack.Photon.Fusion');
    }

    public function getMergedFusionObjectTree(string $packageKey): array
    {
        $packageRootFusionPathAndFilename = sprintf($this->rootFusionPattern, $packageKey);
        $packageRootFusionCode = $this->readExternalFusionFile($packageRootFusionPathAndFilename);

        $mergedFusionCode = '';
        $mergedFusionCode .= $this->getFusionIncludes($this->prepareAutoIncludeFusion());
        $mergedFusionCode .= $packageRootFusionCode;

        return $this->fusionParser->parse($mergedFusionCode, $packageRootFusionPathAndFilename);
    }

    /**
     * Reads the Fusion file from the given path and filename.
     * If it doesn't exist, this function will just return an empty string.
     *
     * @param string $pathAndFilename Path and filename of the Fusion file
     * @return string The content of the .fusion file, plus one chr(10) at the end
     */
    protected function readExternalFusionFile($pathAndFilename)
    {
        return (is_file($pathAndFilename)) ? Files::getFileContents($pathAndFilename) . chr(10) : '';
    }

    /**
     * Concatenate the given Fusion resources with include statements
     *
     * @param array $fusionResources An array of Fusion resource URIs
     * @return string A string of include statements for all resources
     */
    protected function getFusionIncludes(array $fusionResources)
    {
        $code = chr(10);
        foreach ($fusionResources as $fusionResource) {
            $code .= 'include: ' . (string)$fusionResource . chr(10);
        }
        $code .= chr(10);
        return $code;
    }

    /**
     * Prepares an array with Fusion paths to auto include before the Site Fusion.
     *
     * @return array
     */
    protected function prepareAutoIncludeFusion()
    {
        $autoIncludeFusion = array();
        foreach (array_keys($this->packageManager->getActivePackages()) as $packageKey) {
            if (isset($this->autoIncludeConfiguration[$packageKey]) && $this->autoIncludeConfiguration[$packageKey] === true) {
                $autoIncludeFusionFile = sprintf($this->rootFusionPattern, $packageKey);
                $autoIncludeFusion[] = $autoIncludeFusionFile;
            }
        }

        return $autoIncludeFusion;
    }

}
