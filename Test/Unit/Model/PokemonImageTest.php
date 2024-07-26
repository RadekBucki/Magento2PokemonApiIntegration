<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Test\Unit\Model;

use Cepdtech\Pokemon\Dictionary\Attribute as AttributeDictionary;
use Cepdtech\Pokemon\Model\PokemonImage;
use Magento\Framework\Data\Collection;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;

class PokemonImageTest extends TestCase
{
    private FileDriver $fileDriver;
    private Curl $curl;
    private PokemonImage $pokemonImage;

    protected function setUp(): void
    {
        $directoryList = $this->createMock(DirectoryList::class);
        $this->fileDriver = $this->createMock(FileDriver::class);
        $this->curl = $this->createMock(Curl::class);

        $directoryList->method('getPath')
            ->with(DirectoryList::MEDIA)
            ->willReturn('/media');

        $this->pokemonImage = new PokemonImage($directoryList, $this->fileDriver, $this->curl);
    }

    /**
     * @dataProvider uploadFromUrlDataProvider
     */
    public function testUploadFromUrl(bool $directoryExists, bool $fileExists): void
    {
        $url = 'http://example.com/image.png';
        $imageName = 'image.png';
        $imageDirectory = '/media/pokemon';
        $imagePath = $imageDirectory . '/' . $imageName;
        $imageContent = 'image content';

        $this->fileDriver->method('isFile')
            ->with($imagePath)
            ->willReturn($fileExists);

        $this->fileDriver->method('isExists')
            ->with($imageDirectory)
            ->willReturn($directoryExists);

        if (!$fileExists && !$directoryExists) {
            $this->fileDriver->expects($this->once())
                ->method('createDirectory')
                ->with($imageDirectory);
        }

        if (!$fileExists) {
            $this->curl->expects($this->once())
                ->method('get')
                ->with($url);

            $this->curl->method('getBody')
                ->willReturn($imageContent);

            $this->fileDriver->expects($this->once())
                ->method('filePutContents')
                ->with($imagePath, $imageContent);
        }

        $result = $this->pokemonImage->uploadFromUrl($url);

        $this->assertEquals($imagePath, $result);
    }

    /**
     * @return array
     */
    public function uploadFromUrlDataProvider(): array
    {
        return [
            'directory exists, file exists' => [true, true],
            'directory exists, file does not exist' => [true, false],
            'directory does not exist, file exists' => [false, true],
            'directory does not exist, file does not exist' => [false, false],
        ];
    }

    /**
     * @dataProvider addImageToMediaGalleryDataProvider
     */
    public function testAddImageToMediaGallery(bool $imageExist): void
    {
        $product = $this->createMock(Product::class);
        $imagePath = '/media/pokemon/image.png';
        $pokemonImage = 'image.png';

        $product->method('getData')
            ->with(AttributeDictionary::POKEMON_IMAGE)
            ->willReturn($imageExist ? $pokemonImage : null);

        if ($imageExist) {
            $images = [
                $this->createImageMock('image1.png'),
                $this->createImageMock('image2.png'),
                $this->createImageMock('image3.png'),
            ];
            $images[] = $this->createImageMock($pokemonImage);

            $product->method('getMediaGalleryEntries')
                ->willReturn($images);

            $product->expects($this->once())
                ->method('setMediaGalleryEntries')
                ->with(array_slice($images, 0, 3));
        }

        $product->expects($this->once())
            ->method('addImageToMediaGallery')
            ->with($imagePath, [AttributeDictionary::POKEMON_IMAGE], false, false);

        $this->pokemonImage->addImageToMediaGallery($product, $imagePath);
    }

    /**
     * @return array
     */
    public function addImageToMediaGalleryDataProvider(): array
    {
        return [
            'image exists' => [true],
            'image does not exist' => [false],
        ];
    }

    /**
     * @param string $fileName
     * @return ProductAttributeMediaGalleryEntryInterface
     */
    private function createImageMock(string $fileName): ProductAttributeMediaGalleryEntryInterface
    {
        $image = $this->createMock(ProductAttributeMediaGalleryEntryInterface::class);
        $image->method('getFile')
            ->willReturn($fileName);

        return $image;
    }
}
