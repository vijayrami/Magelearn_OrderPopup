<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

class MessageTemplate
{
    public function __construct(
        private readonly Config $config
    ) {
    }

    public function getMessage(
        OrderInterface $order
    ): string {
        $storeId = (int) $order->getStoreId();

        $template = $this->config->getMessageTemplate($storeId);

        $customerName = trim(
            (string) $order->getCustomerFirstname()
            . ' '
            . (string) $order->getCustomerLastname()
        );

        return strtr(
            $template,
            [
                '{{customer_name}}' => $customerName,
                '{{increment_id}}' => (string) $order->getIncrementId(),
                '{{order_total}}' => (string) $order->getGrandTotal(),
                '{{currency}}' => (string) $order->getOrderCurrencyCode(),
                '{{store_name}}' => (string) $order->getStore()->getName(),
                '{{shipping_method}}' => $this->getShippingMethod($order),
                '{{payment_method}}' => $this->getPaymentMethod($order),
                '{{billing_address}}' => $this->getBillingAddress($order),
                '{{shipping_address}}' => $this->getShippingAddress($order),
            ]
        );
    }

    private function getShippingMethod(OrderInterface $order): string
    {
        return (string) $order->getShippingDescription();
    }

    private function getPaymentMethod(OrderInterface $order): string
    {
        $payment = $order->getPayment();

        if (!$payment) {
            return '';
        }

        return (string) $payment->getAdditionalInformation('method_title');
    }

    private function getBillingAddress(OrderInterface $order): string
    {
        $address = $order->getBillingAddress();

        if (!$address) {
            return '';
        }

        return $this->formatAddress($address);
    }

    private function getShippingAddress(OrderInterface $order): string
    {
        $address = $order->getShippingAddress();

        if (!$address) {
            return '';
        }

        return $this->formatAddress($address);
    }

    private function formatAddress(
        OrderAddressInterface $address
    ): string
    {
        $lines = [
            trim(
                (string) $address->getFirstname()
                . ' '
                . (string) $address->getLastname()
            ),
            ...$address->getStreet(),
            trim(
                (string) $address->getCity()
                . ', '
                . (string) $address->getRegion()
                . ' '
                . (string) $address->getPostcode()
            ),
            (string) $address->getCountryId(),
            (string) $address->getTelephone(),
        ];

        return implode(
            PHP_EOL,
            array_filter(
                $lines,
                static fn ($line): bool => trim((string) $line) !== ''
            )
        );
    }
}