<?php

declare(strict_types=1);

namespace Mirakl\Mci\Helper;

use Magento\Store\Api\Data\StoreInterface;

class Config extends \Mirakl\Connector\Helper\Config
{
    // phpcs:disable
    public const XML_PATH_MCI_ROOT_CATEGORY                      = 'mirakl_mci/hierarchy/root_category';
    public const XML_PATH_MCI_IMPORT_PATH                        = 'mirakl_mci/import_shop_product/import_path';
    public const XML_PATH_MCI_ENABLE_PRODUCT_IMPORT              = 'mirakl_mci/import_shop_product/enable_product_import';
    public const XML_PATH_MCI_DEDUPLICATION_ATTRIBUTES           = 'mirakl_mci/import_shop_product/deduplication_attributes';
    public const XML_PATH_MCI_ENABLE_DEDUPLICATION_MULTIVALUES   = 'mirakl_mci/import_shop_product/enable_deduplication_multivalues';
    public const XML_PATH_MCI_DEDUPLICATION_DELIMITER            = 'mirakl_mci/import_shop_product/deduplication_delimiter';
    public const XML_PATH_MCI_UPDATE_EXISTING_PRODUCTS           = 'mirakl_mci/import_shop_product/update_existing_products';
    public const XML_PATH_MCI_AUTO_CREATE_CONFIGURABLE_PRODUCTS  = 'mirakl_mci/import_shop_product/auto_create_configurable_products';
    public const XML_PATH_MCI_AUTO_ENABLE_PRODUCT                = 'mirakl_mci/import_shop_product/auto_enable_product';
    public const XML_PATH_MCI_AUTO_SYNC_PRODUCT                  = 'mirakl_mci/import_shop_product/auto_flag_product_sync';
    public const XML_PATH_MCI_AUTO_ASSIGN_CATEGORY               = 'mirakl_mci/import_shop_product/auto_assign_category';
    public const XML_PATH_MCI_DEFAULT_VISIBILITY                 = 'mirakl_mci/import_shop_product/default_visibility';
    public const XML_PATH_MCI_DEFAULT_TAX_CLASS                  = 'mirakl_mci/import_shop_product/default_tax_class';
    public const XML_PATH_MCI_CHECK_DATA_HASH                    = 'mirakl_mci/import_shop_product/check_data_hash';
    public const XML_PATH_MCI_SEND_IMPORT_REPORT                 = 'mirakl_mci/import_shop_product/send_import_report';
    public const XML_PATH_MCI_ENABLE_INDEXING_IMPORT             = 'mirakl_mci/product_import_indexing/enable_indexing_import';
    public const XML_PATH_MCI_ENABLE_INDEXING_IMPORT_AFTER       = 'mirakl_mci/product_import_indexing/enable_indexing_import_after';
    public const XML_PATH_MCI_IMAGE_MAX_SIZE                     = 'mirakl_images_import/general/image_max_size';
    public const XML_PATH_MCI_IMAGES_IMPORT_LIMIT                = 'mirakl_images_import/general/limit';
    public const XML_PATH_MCI_IMAGES_IMPORT_HEADERS              = 'mirakl_images_import/general/headers';
    public const XML_PATH_MCI_IMAGES_IMPORT_PROTOCOL_VERSION     = 'mirakl_images_import/general/protocol_version';
    public const XML_PATH_MCI_IMAGES_IMPORT_TIMEOUT              = 'mirakl_images_import/general/timeout';
    public const XML_PATH_ENABLE_SYNC_ATTRIBUTES                 = 'mirakl_sync/attributes/enable_attributes';
    public const XML_PATH_ENABLE_INCREMENTAL_ATTRIBUTES_SYNC     = 'mirakl_sync/attributes/incremental_sync';
    public const XML_PATH_EXPORT_ATTRIBUTES_REQUIREMENT_LEVEL    = 'mirakl_sync/attributes/export_requirement_level';
    public const XML_PATH_ENABLE_SYNC_HIERARCHIES                = 'mirakl_sync/hierarchies/enable_hierarchies';
    public const XML_PATH_ENABLE_SYNC_VALUES_LISTS               = 'mirakl_sync/values_lists/enable_values_lists';
    public const XML_PATH_DEFAULT_DECIMAL_ATTRIBUTES_PRECISION   = 'mirakl_sync/attributes/decimal_precision';
    // phpcs:enable

    /**
     * @var string[]
     */
    protected $locales;

    /**
     * Returns locales that will be used for Mirakl product import data
     *
     * @param array $default
     * @return array
     */
    public function getAllowedLocales($default = ['en_US'])
    {
        if (null === $this->locales) {
            $locales = [];
            foreach ($this->getStoresUsedForProductImport() as $store) {
                $locales[] = $this->getLocale($store);
            }

            if (empty($locales)) {
                $locales = $default; // Default value
            }

            $this->locales = array_unique($locales);
        }

        return $this->locales;
    }

    /**
     * Returns attribute codes used for deduplication
     *
     * @return string[]
     */
    public function getDeduplicationAttributes()
    {
        $attributeCodes = (string) $this->getValue(self::XML_PATH_MCI_DEDUPLICATION_ATTRIBUTES);
        if (strlen($attributeCodes)) {
            $attributeCodes = explode(',', $attributeCodes);
        }

        return (array) $attributeCodes;
    }

    /**
     * Returns MCI products deduplication delimiter
     *
     * @return string
     */
    public function getDeduplicationDelimiter()
    {
        return $this->getValue(self::XML_PATH_MCI_DEDUPLICATION_DELIMITER);
    }

    /**
     * Returns default tax class for product import
     *
     * @return int
     */
    public function getDefaultTaxClass()
    {
        return (int) $this->getValue(self::XML_PATH_MCI_DEFAULT_TAX_CLASS);
    }

    /**
     * Returns default visibility for product import
     *
     * @return int
     */
    public function getDefaultVisibility()
    {
        return (int) $this->getValue(self::XML_PATH_MCI_DEFAULT_VISIBILITY);
    }

    /**
     * Returns root category id to use for hierarchy export
     *
     * @return int
     */
    public function getHierarchyRootCategoryId()
    {
        $rootId = $this->getValue(self::XML_PATH_MCI_ROOT_CATEGORY);

        if (!$rootId) {
            $rootId = $this->storeManager->getDefaultStoreView()->getRootCategoryId();
        }

        return $rootId;
    }

    /**
     * Returns the maximum number of images to import on each process
     *
     * @return int
     */
    public function getImagesImportLimit()
    {
        return (int) $this->getValue(self::XML_PATH_MCI_IMAGES_IMPORT_LIMIT);
    }

    /**
     * Returns image maximum size in kilobytes
     *
     * @return int
     */
    public function getImageMaxSize()
    {
        return (int) $this->getValue(self::XML_PATH_MCI_IMAGE_MAX_SIZE);
    }

    /**
     * Returns images import custom headers
     *
     * @return array
     */
    public function getImagesImportHeaders()
    {
        $value = trim($this->getValue(self::XML_PATH_MCI_IMAGES_IMPORT_HEADERS));

        return $value ? array_map('trim', explode("\n", $value)) : [];
    }

    /**
     * Returns HTTP protocol version to use when downloading images
     *
     * @return string
     */
    public function getImagesImportProtocolVersion()
    {
        return $this->getValue(self::XML_PATH_MCI_IMAGES_IMPORT_PROTOCOL_VERSION);
    }

    /**
     * Returns maximum time for an image to be downloaded
     *
     * @return int
     */
    public function getImagesImportTimeout()
    {
        return (int) $this->getValue(self::XML_PATH_MCI_IMAGES_IMPORT_TIMEOUT);
    }

    /**
     * Returns MCI products import path
     *
     * @return string
     */
    public function getImportPath()
    {
        return $this->getValue(self::XML_PATH_MCI_IMPORT_PATH);
    }

    /**
     * Returns stores that allow product import
     *
     * @return StoreInterface[]
     */
    public function getStoresUsedForProductImport()
    {
        $stores = [];
        foreach ($this->storeManager->getStores(true) as $store) {
            if ($this->isProductImportEnabled($store)) {
                $stores[] = $store;
            }
        }

        return $stores;
    }

    /**
     * @return string
     */
    public function getDecimalAttributesPrecision()
    {
        return $this->getValue(self::XML_PATH_DEFAULT_DECIMAL_ATTRIBUTES_PRECISION);
    }

    /**
     * @return bool
     */
    public function isAutoAssignCategory()
    {
        return $this->getFlag(self::XML_PATH_MCI_AUTO_ASSIGN_CATEGORY);
    }

    /**
     * @return bool
     */
    public function isAutoCreateConfigurableProducts()
    {
        return $this->getFlag(self::XML_PATH_MCI_AUTO_CREATE_CONFIGURABLE_PRODUCTS);
    }

    /**
     * @return bool
     */
    public function isAutoEnableProduct()
    {
        return $this->getFlag(self::XML_PATH_MCI_AUTO_ENABLE_PRODUCT);
    }

    /**
     * @return bool
     */
    public function isAutoSyncProduct()
    {
        return $this->getFlag(self::XML_PATH_MCI_AUTO_SYNC_PRODUCT);
    }

    /**
     * @return bool
     */
    public function isCheckDataHash()
    {
        return $this->getFlag(self::XML_PATH_MCI_CHECK_DATA_HASH);
    }

    /**
     * @return bool
     */
    public function isDeduplicationMultiValues()
    {
        return $this->getFlag(self::XML_PATH_MCI_ENABLE_DEDUPLICATION_MULTIVALUES);
    }

    /**
     * @return bool
     */
    public function isEnabledIndexingImport()
    {
        return $this->getFlag(self::XML_PATH_MCI_ENABLE_INDEXING_IMPORT);
    }

    /**
     * @return bool
     */
    public function isEnabledIndexingImportAfter()
    {
        return $this->getFlag(self::XML_PATH_MCI_ENABLE_INDEXING_IMPORT_AFTER);
    }

    /**
     * @return bool
     */
    public function isAttributeRequirementLevelExported()
    {
        return $this->getFlag(self::XML_PATH_EXPORT_ATTRIBUTES_REQUIREMENT_LEVEL);
    }

    /**
     * @param mixed $store
     * @return bool
     */
    public function isProductImportEnabled($store = null)
    {
        return $this->getFlag(self::XML_PATH_MCI_ENABLE_PRODUCT_IMPORT, $store);
    }

    /**
     * @return bool
     */
    public function isSendImportReport()
    {
        return $this->getFlag(self::XML_PATH_MCI_SEND_IMPORT_REPORT);
    }

    /**
     * @return bool
     */
    public function isSyncAttributes()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_SYNC_ATTRIBUTES);
    }

    /**
     * @return bool
     */
    public function isIncrementalAttributesSyncEnabled()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_INCREMENTAL_ATTRIBUTES_SYNC);
    }

    /**
     * @return bool
     */
    public function isSyncHierarchies()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_SYNC_HIERARCHIES);
    }

    /**
     * @return bool
     */
    public function isSyncValuesLists()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_SYNC_VALUES_LISTS);
    }

    /**
     * @return bool
     */
    public function isUpdateExistingProducts()
    {
        return $this->getFlag(self::XML_PATH_MCI_UPDATE_EXISTING_PRODUCTS);
    }
}
