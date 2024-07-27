<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\PokeApi;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class PokemonList
{
    private const ENDPOINT = 'pokemon';

    private const CACHE_KEY = 'pokemon_list';
    private ?array $applicationCache = null;

    /**
     * @param Client $client
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly Client $client,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        if ($this->applicationCache !== null) {
            return $this->applicationCache;
        }

        $cachedValue = $this->cache->load(self::CACHE_KEY);
        if ($cachedValue) {
            $this->applicationCache = $this->serializer->unserialize($cachedValue);
            return $this->applicationCache;
        }

        $results = [];
        $offset = 0;
        do {
            $responseArray = $this->getResponseArray($offset);
            $offset += 10000;
            $results = array_merge(
                $results,
                array_map(
                    fn($item) => $item['name'],
                    $responseArray['results'] ?? []
                )
            );
        } while (!empty($responseArray['next']));

        $this->cache->save($this->serializer->serialize($results), self::CACHE_KEY, lifeTime: 3600);
        $this->applicationCache = $results;

        return $results;
    }

    /**
     * @param int $offset
     * @return array
     */
    private function getResponseArray(int $offset): array
    {
        try {
            $response = $this->client->get(
                self::ENDPOINT,
                [
                    'offset' => $offset,
                    'limit' => 10000,
                ]
            );
        } catch (GuzzleException $e) {
            $this->logger->error('Error getting pokemon list: ' . $e->getMessage());
            return [];
        }

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Error getting pokemon list: ' . $response->getBody()->getContents());
            return [];
        }

        return $this->serializer->unserialize($response->getBody()->getContents());
    }
}
