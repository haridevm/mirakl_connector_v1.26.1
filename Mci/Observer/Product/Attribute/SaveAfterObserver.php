<?php

declare(strict_types=1);

namespace Mirakl\Mci\Observer\Product\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAfterObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->isApiEnabled()) {
            return;
        }

        /** @var EavAttribute $attribute */
        $attribute = $observer->getEvent()->getAttribute();

        if ($this->mciConfigHelper->isSyncValuesLists() && $this->valueListHelper->isAttributeExportable($attribute)) {
            // Export attribute options on Mirakl
            $this->valueListHelper->update($attribute);
        }

        if (
            $this->mciConfigHelper->isSyncAttributes()
            && $this->mciConfigHelper->isIncrementalAttributesSyncEnabled()
        ) {
            if ($option = $attribute->getOption()) {
                $values = $option['value'] ?? [];
                $delete = array_keys($option['delete'] ?? [], '1', true);
                if (count($values) === count($delete)) {
                    return; // Abort here, not needed to update the attribute if we are only deleting options
                }
            }
            // Update attribute on Mirakl
            $this->attributeHelper->exportAttribute($attribute);
        }
    }
}
