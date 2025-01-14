<?php

declare(strict_types=1);

namespace Mirakl\Connector\Plugin\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Mirakl\Connector\Helper\Order as OrderHelper;
use Psr\Log\LoggerInterface;

class OrderSavePlugin
{
    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param OrderHelper     $orderHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderHelper $orderHelper,
        LoggerInterface $logger
    ) {
        $this->orderHelper = $orderHelper;
        $this->logger = $logger;
    }

    /**
     * Mirakl Order is created in this plugin because order item id used for setOrderLineId may not be set in
     * sales_order_save_after event and we also need taxes to be registered in @see \Magento\Tax\Model\Plugin\OrderSave
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface           $order
     * @return OrderInterface
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        try {
            /** @var Order $order */
            $this->orderHelper->autoCreateMiraklOrder($order);
        } catch (\Exception $e) {
            // Ignore to avoid errors in frontend
            $this->logger->warning($e->getMessage());
        }

        return $order;
    }
}
