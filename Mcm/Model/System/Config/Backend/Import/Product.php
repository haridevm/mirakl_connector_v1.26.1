<?php
namespace Mirakl\Mcm\Model\System\Config\Backend\Import;

use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb as ResourceCollection;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mirakl\Core\Controller\Adminhtml\RawMessagesTrait;
use Mirakl\Core\Helper\Csv as CsvHelper;
use Mirakl\Mcm\Helper\Config as McmConfig;
use Mirakl\Mcm\Model\Product\Import\Handler\Csv as McmHandler;
use Mirakl\Process\Helper\Data as ProcessHelper;
use Mirakl\Process\Model\Process;
use Mirakl\Process\Model\ProcessFactory;
use Mirakl\Process\Model\ResourceModel\ProcessFactory as ProcessResourceFactory;

class Product extends Value
{
    use RawMessagesTrait;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ProcessResourceFactory
     */
    private $processResourceFactory;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var CsvHelper
     */
    private $csvHelper;

    /**
     * @var McmConfig
     */
    private $mcmConfig;

    /**
     * @param   Context                 $context
     * @param   Registry                $registry
     * @param   ScopeConfigInterface    $config
     * @param   CacheTypeListInterface  $cacheTypeList
     * @param   ProcessFactory          $processFactory
     * @param   ProcessResourceFactory  $processResourceFactory
     * @param   ProcessHelper           $processHelper
     * @param   StoreManagerInterface   $storeManager
     * @param   MessageManagerInterface $messageManager
     * @param   CsvHelper               $csvHelper
     * @param   McmConfig               $mcmConfig
     * @param   AbstractResource|null   $resource
     * @param   ResourceCollection|null $resourceCollection
     * @param   array                   $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        CacheTypeListInterface $cacheTypeList,
        ProcessFactory $processFactory,
        ProcessResourceFactory $processResourceFactory,
        ProcessHelper $processHelper,
        StoreManagerInterface $storeManager,
        MessageManagerInterface $messageManager,
        CsvHelper $csvHelper,
        McmConfig $mcmConfig,
        AbstractResource $resource = null,
        ResourceCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->processFactory = $processFactory;
        $this->processResourceFactory = $processResourceFactory;
        $this->processHelper = $processHelper;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->csvHelper = $csvHelper;
        $this->mcmConfig = $mcmConfig;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Do not save value
     *
     * @return  $this
     */
    public function beforeSave()
    {
        $this->setValue('');
        parent::beforeSave();

        return $this;
    }

    /**
     * Import products from uploaded file if present
     *
     * @return  $this
     * @throws  \Exception
     */
    public function afterSave()
    {
        $fileName     = @$_FILES['groups']['name']['import_product']['fields']['file']['value'];
        $uploadedFile = @$_FILES['groups']['tmp_name']['import_product']['fields']['file']['value'];

        if (!$fileName) {
            return $this;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION) ?? null;
        $file = $this->processHelper->saveFile($uploadedFile, $extension);

        if (!$file) {
            throw new \Exception('File is empty or could not be loaded.');
        }

        $process = $this->processFactory->create()
            ->setType(Process::TYPE_ADMIN)
            ->setFile($file);

        $process->setName('MCM products import')
            ->setHelper(McmHandler::class)
            ->setCode(McmHandler::CODE)
            ->setParams([null])
            ->setMethod('run');

        $this->processResourceFactory->create()->save($process);

        if ($this->isAdmin()) {
            $this->messageManager->addSuccessMessage(
                __('File has been uploaded successfully. Products will be imported in background.')
            );

            if ($process->getId()) {
                $this->addRawSuccessMessage(
                    __('Click <a href="%1">here</a> to view process output.', $process->getUrl())
                );
            }
        }

        parent::afterSave();

        return $this;
    }

    /**
     * @return  bool
     */
    private function isAdmin()
    {
        return $this->getScopeId() == Store::DEFAULT_STORE_ID;
    }
}
