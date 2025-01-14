<?php

declare(strict_types=1);

namespace Mirakl\Event\Controller\Adminhtml\Event;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;

class View extends AbstractEventAction implements HttpGetActionInterface
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context  $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $event = $this->getEvent();

        if (!$event->getId()) {
            return $this->redirectError(__('This event no longer exists.'));
        }

        $this->coreRegistry->register('event', $event);

        $this->_view->loadLayout();
        $this->_setActiveMenu('Mirakl_Event::event');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Event #%1', $event->getId()));
        $this->_view->renderLayout();

        return $this->_response;
    }
}
