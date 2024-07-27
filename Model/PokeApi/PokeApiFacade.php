<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\PokeApi;

class PokeApiFacade
{
    /**
     * @param PokemonList $pokemonList
     * @param PokemonDetails $pokemonDetails
     */
    public function __construct(
        private readonly PokemonList $pokemonList,
        private readonly PokemonDetails $pokemonDetails
    ) {
    }

    /**
     * @param string $name
     * @return array
     */
    public function getPokemon(string $name): array
    {
        return $this->pokemonDetails->get($name);
    }

    public function getPokemonImageUrl(string $name): string
    {
        $pokemon = $this->getPokemon($name);
        return $pokemon['sprites']['other']['home']['front_default'] ?? $pokemon['sprites']['front_default'];
    }

    /**
     * @return array
     */
    public function getPokemonList(): array
    {
        return $this->pokemonList->get();
    }
}
