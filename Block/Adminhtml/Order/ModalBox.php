<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Block\Adminhtml\Order;

use Magelearn\OrderPopup\Model\MessageTemplate;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class ModalBox extends Template
{
    private ?OrderInterface $order = null;

    public function __construct(
        Context $context,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly MessageTemplate $messageTemplate,
        private readonly RequestInterface $request,
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
        return $this->getUrl(
            'orderpopup/order/order',
            [
                'order_id' => $this->getOrder()
                    ? (int) $this->getOrder()->getEntityId()
                    : 0,
            ]
        );
    }

    public function getOrder(): ?OrderInterface
    {
        if ($this->order !== null) {
            return $this->order;
        }

        $orderId = (int) $this->request->getParam('order_id');

        if ($orderId <= 0) {
            return null;
        }

        try {
            $this->order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException) {
            return null;
        }

        return $this->order;
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