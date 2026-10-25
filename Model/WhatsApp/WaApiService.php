<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Model\WhatsApp;

use Magelearn\OrderPopup\Api\WhatsAppServiceInterface;
use Magelearn\OrderPopup\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class WaApiService implements WhatsAppServiceInterface
{
    private const API_URL =
        'https://waapi.app/api/v1/instances/%s/client/action/send-message';

    public function __construct(
        private readonly Curl $curl,
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(
        string $phoneNumber,
        string $message,
        ?int $storeId = null
    ): bool {
        $instanceId = $this->config->getWaApiInstanceId($storeId);
        $apiToken = $this->config->getWaApiToken($storeId);

        $this->logger->info(
            'WaAPI configuration loaded.',
            [
                'instance_id' => $instanceId,
                'token_length' => strlen($apiToken),
                'token_is_encrypted' => str_starts_with($apiToken, '0:'),
            ]
        );

        if ($instanceId === '' || $apiToken === '') {
            $this->logger->error(
                'WaAPI configuration is incomplete.'
            );

            throw new LocalizedException(
                __('WaAPI configuration is incomplete.')
            );
        }

        $chatId = $this->buildChatId($phoneNumber);

        $url = sprintf(self::API_URL, $instanceId);

        $payload = [
            'chatId' => $chatId,
            'message' => $message,
        ];

        try {
            $this->logger->info('WaAPI: before setHeaders.');
            $this->curl->setHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
            ]);
            $this->logger->info('WaAPI: headers configured.');
            $this->curl->setOption(CURLOPT_TIMEOUT, 15);
            $this->logger->info('WaAPI: before POST.', [
                'url' => $url,
                'chat_id' => $chatId,
            ]);

            $this->curl->post(
                $url,
                json_encode($payload, JSON_THROW_ON_ERROR)
            );
            $this->logger->info('WaAPI: POST completed.');
            $status = $this->curl->getStatus();
            $response = $this->curl->getBody();

            $this->logger->info(
                'WaAPI response received.',
                [
                    'status' => $status,
                    'response' => $response,
                ]
            );

            if ($status < 200 || $status >= 300) {
                $this->logger->info(
                    'WaAPI request failed.',
                    [
                        'status' => $status,
                        'response' => $response,
                    ]
                );

                return false;
            }

            $responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            return ($responseData['data']['status'] ?? '') === 'success';
        } catch (\Throwable $exception) {
            $$this->logger->info(
                'Unable to send WhatsApp message through WaAPI.',
                [
                    'exception_class' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]
            );

            return false;
        }
    }

    private function buildChatId(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/\D+/', '', $phoneNumber);

        if ($phoneNumber === '') {
            throw new LocalizedException(
                __('Invalid WhatsApp phone number.')
            );
        }

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = ltrim($phoneNumber, '0');
        }

        $countryCode = preg_replace(
            '/\D+/',
            '',
            $this->config->getCountryCode()
        );

        if (
            $countryCode !== ''
            && !str_starts_with($phoneNumber, $countryCode)
        ) {
            $phoneNumber = $countryCode . $phoneNumber;
        }

        return $phoneNumber . '@c.us';
    }
}