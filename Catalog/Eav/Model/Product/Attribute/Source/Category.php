<?php

declare(strict_types=1);

namespace Mirakl\Catalog\Eav\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Mirakl\Core\Model\System\Config\Source\Attribute\Category as CategorySource;

class Category extends AbstractSource
{
    /**
     * @var CategorySource
     */
    protected $categorySource;

    /**
     * @param CategorySource $categorySource
     */
    public function __construct(CategorySource $categorySource)
    {
        $this->categorySource = $categorySource;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        return $this->categorySource->getAllOptions();
    }
}
