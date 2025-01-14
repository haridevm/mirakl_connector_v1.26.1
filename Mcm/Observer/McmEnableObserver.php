<?php

declare(strict_types=1);

namespace Mirakl\Mcm\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirakl\Mcm\Helper\Config;

class McmEnableObserver implements ObserverInterface
{
    /**
     * @var array
     */
    public static $sourcesToBlock = [
        \Mirakl\Catalog\Helper\Product::EXPORT_SOURCE,
        \Mirakl\Catalog\Helper\Category::EXPORT_SOURCE,
    ];

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $isMcmEnabled = $this->config->isMcmEnabled() || $this->config->isAsyncMcmEnabled();

        /** @var DataObject $input */
        $input  = $observer->getEvent()->getInput();
        $source = $observer->getEvent()->getSource();

        if ($isMcmEnabled && $input && in_array($source, self::$sourcesToBlock)) {
            $input->setData('enabled', false);
        }
    }
}
