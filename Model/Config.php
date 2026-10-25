<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'magelearn_orderpopup/whatsapp/enabled';
    private const XML_PATH_AUTO_SEND = 'magelearn_orderpopup/whatsapp/auto_send';
    private const XML_PATH_PROVIDER = 'magelearn_orderpopup/whatsapp/provider';

    private const XML_PATH_WAAPI_INSTANCE_ID =
        'magelearn_orderpopup/waapi/instance_id';

    private const XML_PATH_WAAPI_API_TOKEN =
        'magelearn_orderpopup/waapi/api_token';

    private const XML_PATH_COUNTRY_CODE =
        'magelearn_orderpopup/whatsapp/country_code';

    private const XML_PATH_MESSAGE_TEMPLATE =
        'magelearn_orderpopup/whatsapp/message_template';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isAutoSendEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_AUTO_SEND,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getProvider(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PROVIDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getWaApiInstanceId(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_WAAPI_INSTANCE_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getWaApiToken(?int $storeId = null): string
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_WAAPI_API_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === '') {
            return '';
        }

        if (!str_starts_with($value, '0:')) {
            return $value;
        }

        return $this->encryptor->decrypt($value);
    }

    public function getCountryCode(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMessageTemplate(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_MESSAGE_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}