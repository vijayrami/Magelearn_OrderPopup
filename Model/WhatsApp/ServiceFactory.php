<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Model\WhatsApp;

use Magelearn\OrderPopup\Api\WhatsAppServiceInterface;
use Magelearn\OrderPopup\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class ServiceFactory
{
    public function __construct(
        private readonly Config $config,
        private readonly WaApiService $waApiService
    ) {
    }

    public function create(?int $storeId = null): WhatsAppServiceInterface
    {
        $provider = $this->config->getProvider($storeId);

        return match ($provider) {
            'waapi' => $this->waApiService,
            default => throw new LocalizedException(
                __('Unsupported WhatsApp provider: %1', $provider)
            ),
        };
    }
}