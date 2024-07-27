<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\Attribute\Pokemon;

use Cepdtech\Pokemon\Model\PokeApi\PokeApiFacade;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Source extends AbstractSource
{
    /**
     * @param PokeApiFacade $pokeApiFacade
     */
    public function __construct(private readonly PokeApiFacade $pokeApiFacade)
    {
    }

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        return [
            ['value' => '', 'label' => __('Please select a Pokemon')],
            ...array_map(
                fn($name) => ['value' => $name, 'label' => $name],
                $this->pokeApiFacade->getPokemonList()
            )
        ];
    }
}
