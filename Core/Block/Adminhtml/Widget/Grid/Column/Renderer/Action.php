<?php

declare(strict_types=1);

namespace Mirakl\Core\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @inheritdoc
     */
    public function render(DataObject $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        $out = [];
        foreach ($actions as $action) {
            if (is_array($action)) {
                $out[] = $this->_toLinkHtml($action, $row);
            }
        }

        return implode('', $out);
    }
}
