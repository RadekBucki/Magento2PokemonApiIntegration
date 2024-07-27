<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\PokeApi;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class PokemonDetails
{
    private const string ENDPOINT = 'pokemon/%s';

    private const string CACHE_KEY = 'pokemon_details';
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
     * @param string $name
     * @return array|null
     */
    public function get(string $name): ?array
    {
        if (isset($this->applicationCache[$name])) {
            return $this->applicationCache[$name];
        }

        if ($this->applicationCache === null) {
            $cachedValue = $this->cache->load(self::CACHE_KEY);
            $this->applicationCache = $this->serializer->unserialize($cachedValue ?? '[]');
            if (isset($this->applicationCache[$name])) {
                return $this->applicationCache[$name];
            }
        }

        $result = $this->getResponseArray($name);

        $this->cache->save(
            $this->serializer->serialize($this->applicationCache),
            self::CACHE_KEY,
            lifeTime: 3600
        );
        $this->applicationCache[$name] = $result;

        return $result;
    }

    /**
     * @param string $name
     * @return array|null
     */
    private function getResponseArray(string $name): ?array
    {
        try {
            $result = $this->client->get(sprintf(self::ENDPOINT, $name));
        } catch (GuzzleException $e) {
            $this->logger->error('Error getting pokemon details: ' . $e->getMessage());
            return null;
        }

        if ($result->getStatusCode() !== 200) {
            $this->logger->error('Error getting pokemon details: ' . $result->getBody()->getContents());
            return null;
        }

        return $this->serializer->unserialize($result->getBody()->getContents());
    }
}
