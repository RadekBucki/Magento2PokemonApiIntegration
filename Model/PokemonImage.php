<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model;

use Cepdtech\Pokemon\Dictionary\Attribute as AttributeDictionary;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\HTTP\Client\Curl;

class PokemonImage
{
    /** @var string */
    private readonly string $pokemonMediaDirectory;

    /**
     * @param DirectoryList $directoryList
     * @param FileDriver $fileDriver
     * @param Curl $curl
     * @throws FileSystemException
     */
    public function __construct(
        DirectoryList $directoryList,
        private readonly FileDriver $fileDriver,
        private readonly Curl $curl
    ) {
        $this->pokemonMediaDirectory = $directoryList->getPath(DirectoryList::MEDIA) . '/pokemon';
    }

    /**
     * @param string $url
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function uploadFromUrl(string $url): string
    {
        $imageName = array_last(explode('/', $url));
        $imagePath = $this->pokemonMediaDirectory . '/' . $imageName;

        if (!$this->fileDriver->isFile($imagePath)) {
            if (!$this->fileDriver->isExists($this->pokemonMediaDirectory)) {
                $this->fileDriver->createDirectory($this->pokemonMediaDirectory);
            }

            $this->curl->get($url);
            $image = $this->curl->getBody();

            $this->fileDriver->filePutContents($imagePath, $image);
        }

        return $imagePath;
    }

    /**
     * @param Product $product
     * @param string $imagePath
     * @return void
     * @throws LocalizedException
     */
    public function addImageToMediaGallery(Product $product, string $imagePath): void
    {
        $pokemonImage = $product->getData(AttributeDictionary::POKEMON_IMAGE);
        if ($pokemonImage !== null) {
            /** @var ProductAttributeMediaGalleryEntryInterface[] $images */
            $images = $product->getMediaGalleryEntries();
            foreach ($images as $key => $image) {
                if ($image->getFile() === $pokemonImage) {
                    unset($images[$key]);
                    break;
                }
            }
            $product->setMediaGalleryEntries($images);
        }

        $product->addImageToMediaGallery(
            $imagePath,
            [AttributeDictionary::POKEMON_IMAGE],
            exclude: false
        );
    }
}
