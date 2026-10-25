<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Plugin\Magento\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    public function beforeSetLayout(
        OrderView $subject,
        $layout
    ): array {
        $subject->addButton(
            'sendorderwhatsapp',
            [
                'label' => __('Send WhatsApp'),
                'onclick' => 'require(["jquery"], function ($) {
                    $("#popup-modal").modal("openModal");
                });',
                'class' => 'action-default',
            ]
        );

        return [$layout];
    }
}