<?php
namespace Flowpack\Photon\Neos\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Response;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class HttpMessageSplitterImplementation extends AbstractFusionObject {

    /**
     * @return array
     */
    public function evaluate()
    {
        $message = $this->fusionValue('message');

        $data = [
            'statusCode' => 200,
            'headers' => [],
            'body' => $message
        ];

        if (substr($message, 0, 5) === 'HTTP/') {
            $endOfHeader = strpos($message, "\r\n\r\n");
            if ($endOfHeader !== false) {
                $header = substr($message, 0, $endOfHeader + 4);

                $renderedResponse = Response::createFromRaw($header);

                $body = substr($message, strlen($header));

                $data = [
                    'statusCode' => $renderedResponse->getStatusCode(),
                    'headers' => $renderedResponse->getHeaders()->getAll(),
                    'body' => $body
                ];
            }
        }

        return $data;
    }

}
