<?php

declare(strict_types=1);

namespace Mirakl\Catalog\Helper;

class Config extends \Mirakl\Connector\Helper\Config
{
    public const XML_PATH_ATTRIBUTE_STORE        = 'mirakl_connector/product_attributes/products_heading/store';
    public const XML_PATH_ATTRIBUTE_BRAND        = 'mirakl_connector/product_attributes/products_heading/brand';
    public const XML_PATH_ATTRIBUTE_IDENTIFIERS  = 'mirakl_connector/product_attributes/products_heading/identifiers';
    public const XML_PATH_ENABLE_SYNC_CATEGORIES = 'mirakl_sync/categories/enable_categories';
    public const XML_PATH_ENABLE_SYNC_PRODUCTS   = 'mirakl_sync/products/enable_products';

    /**
     * @return int
     */
    public function getStoreId()
    {
        return (int) $this->getValue(self::XML_PATH_ATTRIBUTE_STORE);
    }

    /**
     * @return string
     */
    public function getBrandAttributeCode()
    {
        return $this->getValue(self::XML_PATH_ATTRIBUTE_BRAND);
    }

    /**
     * @return array
     */
    public function getIdentifiersAttributeCodes()
    {
        $value = $this->getValue(self::XML_PATH_ATTRIBUTE_IDENTIFIERS);

        return !empty($value) ? explode(',', $value) : [];
    }

    /**
     * @return bool
     */
    public function isSyncCategories()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_SYNC_CATEGORIES);
    }

    /**
     * @return bool
     */
    public function isSyncProducts()
    {
        return $this->getFlag(self::XML_PATH_ENABLE_SYNC_PRODUCTS);
    }
}
