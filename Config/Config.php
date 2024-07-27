<?php
declare(strict_types=1);

namespace Cepdtech\Pokemon\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'pokemon/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getBaseUrl(): string
    {
        return $this->scopeConfig->getValue(
            'pokemon/general/base_url',
            ScopeInterface::SCOPE_STORE
        );
    }
}
