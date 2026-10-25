<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Controller\Adminhtml\Order;

use Magelearn\OrderPopup\Model\WhatsApp\ServiceFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Psr\Log\LoggerInterface;

class Order extends AdminOrder
{
    public const ADMIN_RESOURCE = 'Magelearn_OrderPopup::send_whatsapp';

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        private readonly ServiceFactory $serviceFactory
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
    }

    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();

        $order = $this->_initOrder();

        if (!$order) {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Unable to load the order.'),
            ]);
        }

        $phone = trim(
            (string) $this->getRequest()->getParam('phone')
        );

        $message = trim(
            (string) $this->getRequest()->getParam('message')
        );

        if ($phone === '') {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Please enter a phone number.'),
            ]);
        }

        if ($message === '') {
            return $resultJson->setData([
                'success' => false,
                'message' => __('Please enter a message.'),
            ]);
        }

        try {
            $service = $this->serviceFactory->create(
                (int) $order->getStoreId()
            );

            $success = $service->sendMessage(
                $phone,
                $message,
                (int) $order->getStoreId()
            );

            if (!$success) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Unable to send the WhatsApp message.'),
                ]);
            }

            return $resultJson->setData([
                'success' => true,
                'message' => __('WhatsApp message sent successfully.'),
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to send manual WhatsApp order notification.',
                [
                    'order_id' => $order->getId(),
                    'exception' => $exception,
                ]
            );

            return $resultJson->setData([
                'success' => false,
                'message' => __('An error occurred while sending the message.'),
            ]);
        }
    }
}