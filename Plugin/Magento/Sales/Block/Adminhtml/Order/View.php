<?php

namespace Magelearn\OrderPopup\Plugin\Magento\Sales\Block\Adminhtml\Order;

/**
 * Class View
 *
 * @package Magelearn\OrderPopup\Plugin\Magento\Sales\Block\Adminhtml\Order
 */
class View
{

    public function beforeSetLayout(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $layout
    ) {
        $subject->addButton(
            'sendordersms',
            [
                'label' => __('Create Warranty Order'),
                'onclick' => "",
                'class' => 'action-default action-warranty-order',
            ]
        );
        return [$layout];
    }

    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {
        if($subject->getNameInLayout() == 'sales_order_edit'){
            $customBlockHtml = $subject->getLayout()->createBlock(
                \Magelearn\OrderPopup\Block\Adminhtml\Order\ModalBox::class,
                $subject->getNameInLayout().'_modal_box'
            )->setOrder($subject->getOrder())
                ->setTemplate('Magelearn_OrderPopup::order/modalbox.phtml')
                ->toHtml();
            return $result.$customBlockHtml;
        }
        return $result;
    }
}

