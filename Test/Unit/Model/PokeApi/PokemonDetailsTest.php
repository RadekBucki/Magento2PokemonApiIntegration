<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Test\Unit\Model\PokeApi;

use Cepdtech\Pokemon\Exception\PokeApiException;
use Cepdtech\Pokemon\Model\PokeApi\Client;
use Cepdtech\Pokemon\Model\PokeApi\PokemonDetails;
use GuzzleHttp\Exception\TransferException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class PokemonDetailsTest extends TestCase
{
    private PokemonDetails $pokemonDetails;
    private Client $client;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private CacheInterface $cache;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($value) => json_decode($value, true));
        $this->serializer->method('serialize')
            ->willReturnCallback(fn($value) => json_encode($value));

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->pokemonDetails = new PokemonDetails(
            $this->client,
            $this->serializer,
            $this->logger,
            $this->cache
        );
    }

    public function testShouldReturnApiResultWhenCacheIsEmpty()
    {
        $this->cache->method('load')
            ->willReturn(null);

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')
            ->willReturn(json_encode(['name' => 'pikachu']));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn($stream);
        $response->method('getStatusCode')
            ->willReturn(200);

        $this->client->method('get')
            ->willReturn($response);

        $result = $this->pokemonDetails->get('pikachu');

        $this->assertEquals(['name' => 'pikachu'], $result);
    }

    public function testShouldReturnApplicationCacheWhenCacheIsNotEmpty()
    {
        $reflection = new \ReflectionClass($this->pokemonDetails);
        $property = $reflection->getProperty('applicationCache');
        $property->setAccessible(true);
        $property->setValue($this->pokemonDetails, ['pikachu' => ['name' => 'pikachu']]);

        $result = $this->pokemonDetails->get('pikachu');

        $this->assertEquals(['name' => 'pikachu'], $result);
    }

    public function testShouldReturnCachedValueWhenCacheIsNotEmpty()
    {
        $this->cache->method('load')
            ->willReturn(json_encode(['pikachu' => ['name' => 'pikachu']]));

        $result = $this->pokemonDetails->get('pikachu');

        $this->assertEquals(['name' => 'pikachu'], $result);
    }

    public function testShouldLogClientException()
    {
        $this->client->method('get')
            ->willThrowException(new TransferException('Error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error getting pokemon details: Error');

        $this->expectException(PokeApiException::class);
        $this->pokemonDetails->get('pikachu');
    }

    public function testShouldLogApiError()
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')
            ->willReturn('Error');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn($stream);
        $response->method('getStatusCode')
            ->willReturn(500);

        $this->client->method('get')
            ->willReturn($response);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error getting pokemon details: Error');

        $this->expectException(PokeApiException::class);
        $this->pokemonDetails->get('pikachu');
    }
}
