<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\Attribute\Pokemon;

use Cepdtech\Pokemon\Model\PokemonImage;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\Filesystem\DirectoryList;
use Cepdtech\Pokemon\Dictionary\Attribute as AttributeDictionary;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

class Backend extends AbstractBackend
{
    /**
     * @param PokemonImage $pokemonImageUploader
     */
    public function __construct(
        private readonly PokemonImage $pokemonImage,
    ) {
    }

    /**
     * @param Product $object
     * @return Backend
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function beforeSave($object): Backend
    {
        $url = 'https://static.posters.cz/image/1300/plakaty/pokemon-pikachu-neon-i71936.jpg';

        $imagePath = $this->pokemonImage->uploadFromUrl($url);
        $this->pokemonImage->addImageToMediaGallery($object, $imagePath);

        return parent::beforeSave($object);
    }
}
