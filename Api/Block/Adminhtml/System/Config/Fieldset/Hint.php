<?php

declare(strict_types=1);

namespace Mirakl\Api\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Mirakl\Api\Helper\ClientHelper\MMP as ApiHelper;
use Mirakl\Api\Model\Client\ClientSettingsInterface;
use Mirakl\Core\Helper\Data as CoreHelper;

/**
 * @method string getError()
 * @method $this  setError(string $error)
 */
class Hint extends Template implements RendererInterface
{
    /**
     * @var string
     * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
     */
    protected $_template = 'Mirakl_Api::system/config/fieldset/hint.phtml';

    /**
     * @var CoreHelper
     */
    private $coreHelper;

    /**
     * @var ApiHelper
     */
    private $apiHelper;

    /**
     * @var ClientSettingsInterface
     */
    private $clientSettings;

    /**
     * @param Context                 $context
     * @param CoreHelper              $coreHelper
     * @param ApiHelper               $apiHelper
     * @param ClientSettingsInterface $clientSettings
     * @param array                   $data
     */
    public function __construct(
        Context $context,
        CoreHelper $coreHelper,
        ApiHelper $apiHelper,
        ClientSettingsInterface $clientSettings,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->coreHelper = $coreHelper;
        $this->apiHelper = $apiHelper;
        $this->clientSettings = $clientSettings;
    }

    /**
     * @return string
     */
    public function getConnectorVersion()
    {
        return $this->coreHelper->getVersion();
    }

    /**
     * @return string
     */
    public function getVersionSDK()
    {
        return $this->coreHelper->getVersionSDK();
    }

    /**
     * @return string|null
     */
    public function getMiraklVersion()
    {
        try {
            $this->clientSettings->getAuthMethod()->validate();

            return $this->apiHelper->getVersion();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }
}
