<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magelearn\OrderPopup\Model\MessageTemplate;
use Magelearn\OrderPopup\Model\Config;

class ModalBox extends Template
{
    public function __construct(
        Context $context,
        private readonly Registry $registry,
        private readonly Config $config,
        private readonly MessageTemplate $messageTemplate,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getInfo(): string
    {
        return (string) __('Send WhatsApp Order Notification');
    }

    public function getFormUrl(): string
    {
        $order = $this->getOrder();

        return $this->getUrl(
            'orderpopup/order/order',
            [
                'order_id' => $order ? (int) $order->getId() : 0,
            ]
        );
    }

    public function getOrder(): ?Order
    {
        $order = $this->registry->registry('current_order');

        return $order instanceof Order ? $order : null;
    }

    public function getOrderPhone(): string
    {
        $order = $this->getOrder();

        if (!$order) {
            return '';
        }

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

    public function getMessage(): string
    {
        $order = $this->getOrder();

        if (!$order) {
            return '';
        }

        return $this->messageTemplate->getMessage($order);
    }
}