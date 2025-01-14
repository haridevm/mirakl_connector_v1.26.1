<?php

declare(strict_types=1);

namespace Mirakl\Connector\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Mirakl\Connector\Helper\Config as ConnectorConfig;
use Mirakl\Core\Controller\Adminhtml\RedirectRefererTrait;

abstract class AbstractReset extends Action implements HttpGetActionInterface
{
    use RedirectRefererTrait;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Mirakl_Config::sync';

    /**
     * @var ConnectorConfig
     */
    protected $connectorConfig;

    /**
     * @param Context         $context
     * @param ConnectorConfig $connectorConfig
     */
    public function __construct(
        Context $context,
        ConnectorConfig $connectorConfig
    ) {
        parent::__construct($context);
        $this->connectorConfig = $connectorConfig;
    }
}
