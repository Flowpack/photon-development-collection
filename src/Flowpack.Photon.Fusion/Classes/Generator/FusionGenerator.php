<?php
namespace Flowpack\Photon\Fusion\Generator;

use Flowpack\Photon\Fusion\Http\Request;
use Neos\Flow\Annotations as Flow;
use Flowpack\Photon\Fusion\Exception\GeneratorException;
use Flowpack\Photon\Fusion\Exception\InvalidGeneratorResultException;
use Flowpack\Photon\Common\Generator\GeneratorInterface;
use Neos\Flow\Http\Response;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Fusion\Exception\RuntimeException;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Utility\ObjectAccess;

/**
 * @Flow\Scope("singleton")
 */
class FusionGenerator implements GeneratorInterface
{

    /**
     * @Flow\Inject
     * @var \Neos\Fusion\Core\RuntimeFactory
     */
    protected $runtimeFactory;

    /**
     * @Flow\Inject
     * @var \Flowpack\Photon\Fusion\Fusion\ConfigurationProvider
     */
    protected $fusionConfigurationProvider;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Package\PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;


    public function generate(string $packageKey, string $targetName, array $options): array
    {
        if (!$this->packageManager->isPackageAvailable($packageKey)) {
            throw new \Flowpack\Photon\Fusion\Exception\InvalidPackageKeyException(sprintf('Package "%s" is not available',
                $packageKey), 1556284688);
        }

        $fusionConfiguration = $this->fusionConfigurationProvider->getMergedFusionObjectTree($packageKey);

        $controllerContext = $this->createControllerContextFromEnvironment();
        $this->initializeSecurity($controllerContext);
        $runtime = $this->runtimeFactory->create($fusionConfiguration, $controllerContext);

        $outputDirectory = $options['outputDirectory'] ?? '.';
        if ($outputDirectory !== null) {
            \Neos\Utility\Files::createDirectoryRecursively($outputDirectory);
        }

        $runtime->pushContextArray([
            'options' => $options,
            'packageKey' => $packageKey,
            'target' => $targetName,
            'outputDirectory' => $outputDirectory
        ]);

        /** @var array $results */
        try {
            $results = $runtime->render('output');
        } catch (RuntimeException $e) {
            throw new GeneratorException(sprintf("Fusion exception at path:\n  %s\n\n%s", $e->getFusionPath(), $e->getPrevious()->getMessage()));
        }
        $runtime->popContext();

        if (!is_array($results)) {
            throw new InvalidGeneratorResultException('results was not an array');
        }

        return $results;
    }

    private function initializeSecurity(ControllerContext $controllerContext): void
    {
        $request = $controllerContext->getRequest();
        ObjectAccess::setProperty($this->securityContext, 'initialized', true, true);
        $this->securityContext->setRequest($request);
    }

    private function createControllerContextFromEnvironment(): ControllerContext
    {
        $_SERVER['FLOW_REWRITEURLS'] = '1';

        $httpRequest = Request::createFromEnvironment();
        $httpRequest->setBaseUri('/');

        $request = new ActionRequest($httpRequest);
        if ($this->packageManager->isPackageAvailable('Neos.Neos')) {
            $request->setControllerObjectName('Neos\Neos\Controller\Frontend\NodeController');
            $request->setControllerActionName('show');
        }
        $request->setFormat('html');

        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($request);

        return new ControllerContext(
            $request,
            new Response(),
            new Arguments([]),
            $uriBuilder
        );
    }

}
