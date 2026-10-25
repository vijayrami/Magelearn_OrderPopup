<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Api;

interface WhatsAppServiceInterface
{
    /**
     * Send WhatsApp message.
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function sendMessage(
        string $phoneNumber,
        string $message,
        ?int $storeId = null
    ): bool;
}