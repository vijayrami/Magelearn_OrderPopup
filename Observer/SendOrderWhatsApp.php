<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Observer;

use Magelearn\OrderPopup\Model\Config;
use Magelearn\OrderPopup\Model\WhatsApp\ServiceFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magelearn\OrderPopup\Model\MessageTemplate;
use Psr\Log\LoggerInterface;

class SendOrderWhatsApp implements ObserverInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly ServiceFactory $serviceFactory,
        private readonly MessageTemplate $messageTemplate,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        /** @var OrderInterface|null $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order || !$order->getEntityId()) {
            return;
        }

        $storeId = (int) $order->getStoreId();

        if (
            !$this->config->isEnabled($storeId)
            || !$this->config->isAutoSendEnabled($storeId)
        ) {
            return;
        }

        $phoneNumber = $this->getOrderPhoneNumber($order);

        if ($phoneNumber === '') {
            $this->logger->warning(
                'Unable to send WhatsApp order notification because phone number is missing.',
                [
                    'order_id' => $order->getEntityId(),
                ]
            );

            return;
        }

        $message = $this->messageTemplate->getMessage($order);

        try {
            $service = $this->serviceFactory->create($storeId);

            $service->sendMessage(
                $phoneNumber,
                $message,
                $storeId
            );
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to send automatic WhatsApp order notification.',
                [
                    'order_id' => $order->getEntityId(),
                    'exception' => $exception,
                ]
            );
        }
    }

    private function getOrderPhoneNumber(OrderInterface $order): string
    {
        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress && $shippingAddress->getTelephone()) {
            return (string) $shippingAddress->getTelephone();
        }

        $billingAddress = $order->getBillingAddress();

        if ($billingAddress && $billingAddress->getTelephone()) {
            return (string) $billingAddress->getTelephone();
        }

        return '';
    }
}