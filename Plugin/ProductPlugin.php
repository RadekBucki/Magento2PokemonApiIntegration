<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Plugin;

use Magento\Catalog\Model\Product;
use Cepdtech\Pokemon\Dictionary\Attribute as AttributeDictionary;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Images;

class ProductPlugin
{
    /**
     * @param Product $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetName(Product $subject, callable $proceed): string
    {
        $pokemonName = $subject->getCustomAttribute(AttributeDictionary::POKEMON_NAME)
            ?->getValue();
        if (empty($pokemonName)) {
            return $proceed();
        }

        return $pokemonName;
    }

    /**
     * @param Product $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetImage(Product $subject, callable $proceed): string
    {
        return $subject->getCustomAttribute(AttributeDictionary::POKEMON_IMAGE)?->getValue()
            ?? $proceed();
    }

    /**
     * @param Product $subject
     * @param callable $proceed
     * @param $key
     * @param $index
     * @return mixed|null
     */
    public function aroundGetData(Product $subject, callable $proceed, $key = '', $index = null)
    {
        if ($key === Images::CODE_SMALL_IMAGE) {
            return $subject->getCustomAttribute(AttributeDictionary::POKEMON_IMAGE)?->getValue()
                ?? $proceed($key, $index);
        }

        return $proceed($key, $index);
    }
}
