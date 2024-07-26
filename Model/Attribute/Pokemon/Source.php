<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Model\Attribute\Pokemon;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Source extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        return [
            ['value' => '', 'label' => __('Please select a Pokemon')],
            ['value' => 'pikachu', 'label' => __('Pikachu')],
            ['value' => 'charmander', 'label' => __('Charmander')],
            ['value' => 'bulbasaur', 'label' => __('Bulbasaur')],
            ['value' => 'squirtle', 'label' => __('Squirtle')],
            ['value' => 'jigglypuff', 'label' => __('Jigglypuff')],
            ['value' => 'meowth', 'label' => __('Meowth')],
            ['value' => 'psyduck', 'label' => __('Psyduck')],
            ['value' => 'snorlax', 'label' => __('Snorlax')],
            ['value' => 'mewtwo', 'label' => __('Mewtwo')],
            ['value' => 'mew', 'label' => __('Mew')],
        ];
    }
}
