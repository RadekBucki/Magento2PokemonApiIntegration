<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\PokeApi;

use Cepdtech\Pokemon\Config\ConfigInterface;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @param ConfigInterface $config
     * @param ClientFactory $clientFactory
     */
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ClientFactory $clientFactory
    ) {
    }

    /**
     * @param string $endpoint
     * @param array $queryParameters
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $endpoint, array $queryParameters = []): ResponseInterface
    {
        /** @var \GuzzleHttp\Client $client */
        $client = $this->clientFactory->create([
            'config' => [
                'timeout' => 10,
                'base_uri' => $this->config->getBaseUrl(),
            ],
        ]);

        return $client->get($endpoint, [
            'query' => $queryParameters,
        ]);
    }
}
