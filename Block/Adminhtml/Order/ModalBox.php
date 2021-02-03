<?php

namespace Magelearn\OrderPopup\Block\Adminhtml\Order;

/**
 * Class ModalBox
 *
 * @package Magelearn\OrderPopup\Block\Adminhtml\Order
 */
use Magento\Cms\Model\PageFactory;
class ModalBox extends \Magento\Backend\Block\Template
{
	/**
     * Page factory.
     *
     * @var PageFactory
     */
    private $pageFactory;
	private $blockFactory;
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
	    PageFactory $pageFactory,
	    \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
    	$this->pageFactory = $pageFactory;
    	$this->blockFactory = $blockFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        //Your block cod
        return __('My Title!! ');
    }
    public function getFormUrl()
    {
        $orderId = false;
        if($this->hasData('order')){
            $orderId = $this->getOrder()->getId();
        }
        return $this->getUrl('orderpopup/order/order',[
            'order_id' => $orderId
        ]);
    }
	public function createBlock()
    {
        $testBlock = [
		    'title' => 'Test block title',
		    'identifier' => 'test-block',
		    'stores' => [0],
		    'is_active' => 1,
		];
		return $this->blockFactory->create()->setData($testBlock)->save();
    }
}

