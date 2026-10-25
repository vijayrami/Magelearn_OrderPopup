<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class MessageTemplate extends Field
{
    /**
     * Render the message template field.
     */
    protected function _getElementHtml(
        AbstractElement $element
    ): string {
        $html = parent::_getElementHtml($element);

        $variables = [
            '{{customer_name}}' => 'Customer first name and last name',
            '{{increment_id}}' => 'Magento order number',
            '{{order_total}}' => 'Order grand total',
            '{{currency}}' => 'Order currency code, e.g. USD or CAD',
            '{{store_name}}' => 'Magento store name',
            '{{shipping_method}}' => 'Selected shipping method',
            '{{payment_method}}' => 'Selected payment method',
            '{{billing_address}}' => 'Customer billing address',
            '{{shipping_address}}' => 'Customer shipping address',
        ];

        $html .= '<div class="magelearn-message-template-help">';
        $html .= '<strong>Available Variables</strong>';
        $html .= '<p>Use the following variables in your message template. '
            . 'They will be automatically replaced with the order information '
            . 'when the message is sent.</p>';

        $html .= '<table class="admin__table-secondary">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Variable</th>';
        $html .= '<th>Description</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($variables as $variable => $description) {
            $html .= '<tr>';
            $html .= '<td><code>'
                . $this->escapeHtml($variable)
                . '</code></td>';
            $html .= '<td>'
                . $this->escapeHtml($description)
                . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}