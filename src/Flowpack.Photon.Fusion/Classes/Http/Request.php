<?php
namespace Flowpack\Photon\Fusion\Http;

use Neos\Flow\Http\Request as BaseRequest;

class Request extends BaseRequest
{

    public function getScriptRequestPath()
    {
        return '/';
    }

}
