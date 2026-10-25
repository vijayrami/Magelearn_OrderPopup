<?php

declare(strict_types=1);

namespace Magelearn\OrderPopup\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Provider implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'waapi',
                'label' => __('WaAPI'),
            ],
        ];
    }
}