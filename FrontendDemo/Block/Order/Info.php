<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Block\Order;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Mirakl\Api\Helper\Order as OrderApi;
use Mirakl\Connector\Helper\Order as OrderHelper;

class Info extends \Magento\Sales\Block\Order\Info
{
    /**
     * @var OrderApi
     */
    protected $orderApi;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @param TemplateContext $context
     * @param Registry        $registry
     * @param PaymentHelper   $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param FormKey         $formKey
     * @param OrderApi        $orderApi
     * @param OrderHelper     $orderHelper
     * @param array           $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        FormKey $formKey,
        OrderApi $orderApi,
        OrderHelper $orderHelper,
        array $data = []
    ) {
        $this->formKey      = $formKey;
        $this->orderApi     = $orderApi;
        $this->orderHelper  = $orderHelper;
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
    }

    /**
     * @return \Mirakl\MMP\FrontOperator\Domain\Order
     */
    public function getMiraklOrder()
    {
        return $this->coreRegistry->registry('mirakl_order');
    }

    /**
     * @return void
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getMiraklOrder()->getId()));
        $infoBlock = $this->paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
    }

    /**
     * @return bool
     */
    public function canReceiveOrder()
    {
        return $this->orderHelper->canReceiveOrder($this->getMiraklOrder());
    }

    /**
     * @return string
     */
    public function getMarkAsReceivedUrl()
    {
        return $this->getUrl('*/order/receive', [
            'order_id'  => $this->getOrder()->getId(),
            'remote_id' => $this->getMiraklOrder()->getId(),
            'form_key'  => $this->formKey->getFormKey(),
        ]);
    }
}
