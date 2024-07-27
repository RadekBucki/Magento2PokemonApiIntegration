<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Test\Unit\Model\PokeApi;

use Cepdtech\Pokemon\Model\PokeApi\Client;
use Cepdtech\Pokemon\Model\PokeApi\PokemonList;
use GuzzleHttp\Exception\TransferException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class PokemonListTest extends TestCase
{
    private PokemonList $pokemonList;
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

        $this->cache = $this->createMock(CacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->pokemonList = new PokemonList(
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
            ->willReturn(json_encode(['results' => [['name' => 'pikachu']], 'next' => null]));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')
            ->willReturn($stream);
        $response->method('getStatusCode')
            ->willReturn(200);

        $this->client->method('get')
            ->willReturn($response);

        $result = $this->pokemonList->get();

        $this->assertEquals(['pikachu'], $result);
    }

    public function testShouldReturnAllResultsWhenNextIsNotEmpty()
    {
        $this->cache->method('load')
            ->willReturn(null);

        $stream1 = $this->createMock(StreamInterface::class);
        $stream1->method('getContents')
            ->willReturn(json_encode(['results' => [['name' => 'pikachu']], 'next' => 'next']));

        $response1 = $this->createMock(ResponseInterface::class);
        $response1->method('getBody')
            ->willReturn($stream1);
        $response1->method('getStatusCode')
            ->willReturn(200);

        $stream2 = $this->createMock(StreamInterface::class);
        $stream2->method('getContents')
            ->willReturn(json_encode(['results' => [['name' => 'bulbasaur']], 'next' => null]));

        $response2 = $this->createMock(ResponseInterface::class);
        $response2->method('getBody')
            ->willReturn($stream2);

        $this->client->method('get')
            ->willReturn($response1, $response2);

        $result = $this->pokemonList->get();

        $this->assertEquals(['pikachu'], $result);
    }

    public function testShouldReturnApplicationCacheWhenCacheIsNotEmpty()
    {
        $reflection = new \ReflectionClass($this->pokemonList);
        $property = $reflection->getProperty('applicationCache');
        $property->setAccessible(true);
        $property->setValue($this->pokemonList, ['pikachu']);

        $result = $this->pokemonList->get();

        $this->assertEquals(['pikachu'], $result);
    }

    public function testShouldReturnCachedValueWhenCacheIsNotEmpty()
    {
        $this->cache->method('load')
            ->willReturn(json_encode(['pikachu']));

        $result = $this->pokemonList->get();

        $this->assertEquals(['pikachu'], $result);
    }

    public function testShouldLogClientException()
    {
        $this->client->method('get')
            ->willThrowException(new TransferException('Error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error getting pokemon list: Error');

        $result = $this->pokemonList->get();

        $this->assertEmpty($result);
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
            ->with('Error getting pokemon list: Error');

        $result = $this->pokemonList->get();

        $this->assertEmpty($result);
    }
}
