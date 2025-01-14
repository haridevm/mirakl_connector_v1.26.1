<?php

declare(strict_types=1);

namespace Mirakl\Core\Block\Adminhtml\Shipping\Zone;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * @var Registry
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_coreRegistry;

    /**
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Apply" button
     * Add "Save and Continue" button
     *
     * @inheritdoc
     * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Mirakl_Core';
        $this->_controller = 'adminhtml_shipping_zone';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Editing'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );
    }
}
