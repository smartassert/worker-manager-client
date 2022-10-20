<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidResponseContentException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\ResponseDecoder;
use SmartAssert\WorkerManagerClient\Model\BadCreateMachineResponse;
use SmartAssert\WorkerManagerClient\Model\Machine;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
        private readonly ObjectFactory $objectFactory,
        private readonly ResponseDecoder $responseDecoder,
    ) {
    }

    /**
     * @param non-empty-string $userToken
     * @param non-empty-string $machineId
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function createMachine(
        string $userToken,
        string $machineId
    ): null|Machine|BadCreateMachineResponse {
        try {
            $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
                (new Request('POST', $this->createUrl('/machine/' . $machineId)))
                    ->withAuthentication(new BearerAuthentication($userToken))
            );

            return $this->objectFactory->createMachineFromArray($responseData);
        } catch (NonSuccessResponseException $exception) {
            $response = $exception->response;

            if (400 === $exception->getCode() && 'application/json' === $response->getHeaderLine('content-type')) {
                $badRequestResponseData = $this->responseDecoder->decodedJsonResponse($response);

                return $this->objectFactory->createBadCreateMachineResponseFromArray($badRequestResponseData);
            }

            throw $exception;
        }
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private function createUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . $path;
    }
}
