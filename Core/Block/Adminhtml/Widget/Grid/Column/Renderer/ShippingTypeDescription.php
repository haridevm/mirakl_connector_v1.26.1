<?php
namespace Mirakl\Core\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;

class ShippingTypeDescription extends AbstractRenderer
{
    /**
     * @var Json
     */
    private $json;

    public function __construct(
        Context $context,
        Json $json,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->json = $json;
    }

    /**
     * @param   DataObject  $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        $value = $this->_getValue($row);
        if (empty($value)) {
            return '';
        }

        $labelsByLocale = $this->json->unserialize($value);

        $output = '';
        foreach ($labelsByLocale as $locale => $label) {
            if (!empty($label)) {
                $output .= sprintf(
                    '<tr><td>%s :</td><td>%s</td></tr>',
                    $locale,
                    $label
                );
            }
        }

        return $output ? '<table class="mirakl-shipping-types-table mirakl-shipping-type-description">' . $output . '</table>' : '';
    }
}