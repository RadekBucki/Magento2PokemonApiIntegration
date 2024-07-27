<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\Attribute\Pokemon;

use Cepdtech\Pokemon\Model\PokeApi\PokeApiFacade;
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
     * @param PokemonImage $pokemonImage
     * @param PokeApiFacade $pokeApiFacade
     */
    public function __construct(
        private readonly PokemonImage $pokemonImage,
        private readonly PokeApiFacade $pokeApiFacade,
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
        $value = $object->getData($this->getAttribute()->getName());
        $url = $this->pokeApiFacade->getPokemonImageUrl($value);

        $imagePath = $this->pokemonImage->uploadFromUrl($url);
        $this->pokemonImage->addImageToMediaGallery($object, $imagePath);

        return parent::beforeSave($object);
    }
}
