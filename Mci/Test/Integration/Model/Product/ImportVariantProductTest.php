<?php

declare(strict_types=1);

namespace Mirakl\Mci\Test\Integration\Model\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product;
use Mirakl\Mci\Helper\Data as MciDataHelper;
use Mirakl\Mci\Test\Integration\Model\Product\AbstractImportProductTestCase as MiraklBaseTestCase;

/**
 * @group MCI
 * @group import
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ImportVariantProductTest extends MiraklBaseTestCase
{
    /**
     * @dataProvider importVariantMciDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/send_import_report 0
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_enable_product 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_visibility 4
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/deduplication_attributes ean
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/update_existing_products 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_flag_product_sync 1
     *
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/product_attributes.php

     * @param string $productVariant
     * @param array  $eans
     */
    public function testVariantProductMciImport($productVariant, $eans)
    {
        $this->runImport('2010', $productVariant);

        $values = [
            'EAN1234' => [
                'category'       => 3,
                'brand'          => 'Lacoste',
                'name'           => 'Slim Fit Polo 1',
                'description'    => 'This variant 1 ...',
                'color'          => '50',
                'size'           => '91',
                'ean'            => 'EAN1234',
                'mirakl_image_1' => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
                'status'         => Status::STATUS_ENABLED,
                'mirakl_sync'    => '1',
            ],
            'EAN1235' => [
                'category'       => 3,
                'brand'          => 'Lacoste',
                'name'           => 'Fat Fit Polo 2',
                'description'    => 'This variant 2 ...',
                'color'          => '53',
                'size'           => '169',
                'ean'            => 'EAN1235',
                'mirakl_image_1' => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
                'status'         => Status::STATUS_ENABLED,
                'mirakl_sync'    => '1',
            ],
        ];

        foreach ($eans as $ean) {
            $newProduct = $this->validateAllProductValues('2010', $values[$ean]);
            $this->validateVariantParentProduct($newProduct);
        }
    }

    /**
     * @dataProvider importCreateParentVariantMciDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/send_import_report 0
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_enable_product 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_visibility 4
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/deduplication_attributes ean
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/update_existing_products 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_flag_product_sync 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/check_data_hash 0
     *
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/product_attributes.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/single_product.php
     *
     * @param string $variantProductFile
     */
    public function testCreateParentVariantProductMciImport($variantProductFile)
    {
        // Import #1
        $this->runImport('2010', $variantProductFile);

        $values = [
            'category'       => 3,
            'brand'          => 'Lacoste',
            'name'           => 'Slim Fit Polo',
            'description'    => 'This ... UPDATE',
            'color'          => '50',
            'size'           => '91',
            'ean'            => 'EAN1234',
            'mirakl_image_1' => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
            'status'         => Status::STATUS_ENABLED,
            'mirakl_sync'    => '1',
            'shop_skus'      => '2010|SHOPSKU',
        ];

        $newProduct = $this->validateAllProductValues('2010', $values);
        $this->validateVariantParentProduct($newProduct);

        $imageProcess = $this->createImageCommandProcess([$newProduct->getId()]);
        $imageProcess->setQuiet(true);
        $imageProcess->run();

        $this->assertStringContainsString('Downloading images...', $imageProcess->getOutput());
        $this->assertStringContainsString('Saving images for product', $imageProcess->getOutput());
    }

    /**
     * @dataProvider importUpdateVariantMciDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/send_import_report 0
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_enable_product 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_visibility 4
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/deduplication_attributes ean
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/update_existing_products 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_flag_product_sync 1
     *
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/product_attributes.php
     *
     * @param string $updateVariantProductFile
     * @param array  $eans
     */
    public function testUpdateVariantProductMciImport($updateVariantProductFile, $eans)
    {
        // Import #1
        $this->runImport('2010', $updateVariantProductFile);

        $values = [
            'EAN1234' => [
                'category'       => 3,
                'brand'          => 'Lacoste UPDATE',
                'name'           => 'Slim Fit Polo 1 UPDATE',
                'description'    => 'This variant 1 ... UPDATE',
                'color'          => '50',
                'size'           => '91',
                'ean'            => 'EAN1234',
                'mirakl_image_1' => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
                'status'         => Status::STATUS_ENABLED,
                'mirakl_sync'    => '1',
            ],
            'EAN1235' => [
                'category'       => 3,
                'brand'          => 'Lacoste UPDATE 2',
                'name'           => 'Fat Fit Polo 2 UPDATE',
                'description'    => 'This variant 2 ... UPDATE',
                'color'          => '53',
                'size'           => '169',
                'ean'            => 'EAN1235',
                'mirakl_image_1' => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
                'status'         => Status::STATUS_ENABLED,
                'mirakl_sync'    => '1',
            ],
            'EAN1236' => [
                'category'       => 3,
                'brand'          => 'Lacoste NEW 2',
                'name'           => 'Fat Fit Polo 2 NEW',
                'description'    => 'This variant 3 ... NEW',
                'color'          => '55',
                'size'           => '95',
                'ean'            => 'EAN1236',
                'mirakl_image_1' => 'https://magento.mirakl.net/public/ms02-gray_main_1.jpg',
                'status'         => Status::STATUS_ENABLED,
                'mirakl_sync'    => '1',
            ],
        ];

        foreach ($eans as $ean) {
            $newProduct = $this->validateAllProductValues('2010', $values[$ean]);
            $this->validateVariantParentProduct($newProduct);
        }
    }

    /**
     * @dataProvider importUpdateVariantAlreadyPresentMciDataProvider
     *
     * @magentoConfigFixture current_store mirakl_api/general/enable 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/send_import_report 0
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_enable_product 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_tax_class 2
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/default_visibility 4
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/deduplication_attributes ean
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/update_existing_products 1
     * @magentoConfigFixture current_store mirakl_mci/import_shop_product/auto_flag_product_sync 1
     *
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/attributes_variant.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/categories_attribute_set.php
     * @magentoDataFixture Mirakl_Mci::Test/Integration/Model/Product/_fixtures/product_attributes.php
     *
     * @param string $updateVariantProductFile
     */
    public function testUpdateAlreadyVariantProductMciImport($updateVariantProductFile)
    {
        $process = $this->runImport('2010', $updateVariantProductFile);

        $this->assertStringContainsString(
            'A variant product already exists with the same variants as provided data',
            $process->getOutput()
        );
    }

    /**
     * @return array
     */
    public function importVariantMciDataProvider()
    {
        return [
            ['multi_product_variant.csv', ['EAN1234', 'EAN1235']],
        ];
    }

    /**
     * @return array
     */
    public function importCreateParentVariantMciDataProvider()
    {
        return [
            ['create_parent_variant_product.csv', 'EAN1234'],
        ];
    }

    /**
     * @return array
     */
    public function importUpdateVariantMciDataProvider()
    {
        return [
            ['update_multi_product_variant.csv', ['EAN1234', 'EAN1235', 'EAN1236']],
        ];
    }

    /**
     * @return array
     */
    public function importUpdateVariantAlreadyPresentMciDataProvider()
    {
        return [
            ['update_single_product_variant_already_present.csv'],
        ];
    }

    /**
     * @param Product $newProduct
     */
    protected function validateVariantParentProduct($newProduct)
    {
        // Test parent creation
        $parentProduct = $this->coreHelper->getParentProduct($newProduct);
        $this->assertNotNull($parentProduct);

        $this->productResourceFactory->create()->load($parentProduct, $parentProduct->getId());
        $this->assertEquals('2010|VARIANT', $parentProduct->getData(MciDataHelper::ATTRIBUTE_VARIANT_GROUP_CODES));
    }
}
