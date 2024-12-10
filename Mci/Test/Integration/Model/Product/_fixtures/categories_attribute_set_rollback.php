<?php

use Mirakl\Mci\Helper\Data as MciHelper;

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\Category $category */
$category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Category::class);
$category->load(3); // @phpstan-ignore-line
$category->unsetData(MciHelper::ATTRIBUTE_ATTR_SET);
$category->save(); // @phpstan-ignore-line
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);