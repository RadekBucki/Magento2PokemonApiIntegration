<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Config;

interface ConfigInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return string
     */
    public function getBaseUrl(): string;
}
