<?php

declare(strict_types=1);

namespace Mirakl\FrontendDemo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Connector\Helper\Order as OrderHelper;

class UpdateEmailOrderShippingObserver implements ObserverInterface
{
    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @param OrderHelper $orderHelper
     */
    public function __construct(OrderHelper $orderHelper)
    {
        $this->orderHelper = $orderHelper;
    }

    /**
     * Set shipping description for marketplace order
     *
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\DataObject $transport */
        $transport = $observer->getEvent()->getData('transportObject');

        if (!$transport) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $transport->getData('order');

        if (!$this->orderHelper->isMiraklOrder($order)) {
            return;
        }

        $mixed = false;
        $shippingType = false;

        foreach ($order->getAllItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->isDeleted() || $item->getParentItemId()) {
                continue;
            }

            if ($item->getMiraklShopId()) {
                if ($shippingType && $shippingType != $item->getMiraklShippingTypeLabel()) {
                    $mixed = true;
                    break;
                }

                $shippingType = $item->getMiraklShippingTypeLabel();
            } else {
                $mixed = true;
                break;
            }
        }

        if ($mixed) {
            $order->setShippingDescription(__('Mixed'));
        } else {
            $order->setShippingDescription(__($shippingType));
        }
    }
}
