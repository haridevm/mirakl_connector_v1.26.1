<?php

declare(strict_types=1);

namespace Mirakl\Connector\Model;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Registry;
use Mirakl\Connector\Model\ResourceModel\Offer\Collection as OfferCollection;
use Mirakl\Core\Model\ResourceModel\Offer\State\Collection as OfferStateCollection;
use Mirakl\Core\Model\ResourceModel\Offer\State\CollectionFactory as StateCollectionFactory;
use Mirakl\Core\Model\ResourceModel\ShopFactory as ShopResourceFactory;
use Mirakl\Core\Model\Shop;
use Mirakl\Core\Model\ShopFactory;
use Mirakl\MMP\Common\Domain\Collection\DiscountRangeCollection;
use Mirakl\MMP\Common\Domain\Discount;

/**
 * @method string   getAvailableStartDate()
 * @method string   getAvailableEndDate()
 * @method string   getChannels()
 * @method string   getCurrencyIsoCode()
 * @method string   getDescription()
 * @method string   getDiscountEndDate()
 * @method string   getDiscountStartDate()
 * @method float    getDiscountPrice()
 * @method string   getDiscountRanges()
 * @method int      getFavoriteRank()
 * @method string   getLogisticClass()
 * @method int      getMaxOrderQuantity()
 * @method int      getMinOrderQuantity()
 * @method float    getMinShippingPrice()
 * @method float    getMinShippingPriceAdditional()
 * @method string   getMinShippingType()
 * @method string   getMinShippingZone()
 * @method int      getOfferId()
 * @method float    getOriginPrice()
 * @method int      getPackageQuantity()
 * @method float    getPrice()
 * @method string   getPriceAdditionalInfo()
 * @method string   getProductSku()
 * @method string   getProductTaxCode()
 * @method int      getQuantity()
 * @method int      getShopId()
 * @method int|null getStoreId()
 * @method $this    setStoreId(int $storeId)
 * @method string   getShopName()
 * @method int      getStateCode()
 * @method float    getTotalPrice()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class Offer extends AbstractModel implements SaleableInterface
{
    public const OFFER_ID = 'offer_id'; // We define the id fieldname

    public const CHANNELS_SEPARATOR = '|';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mirakl_offer';

    /**
     * Parameter name in event
     * In observe method you can use $observer->getEvent()->getOffer() in this case
     *
     * @var string
     */
    protected $_eventObject = 'offer';

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Factory
     */
    protected $priceInfoFactory;

    /**
     * @var ProductType
     */
    protected $catalogProductType;

    /**
     * @var OfferStateCollection
     */
    protected $states;

    /**
     * @var StateCollectionFactory
     */
    protected $stateCollectionFactory;

    /**
     * @var ShopFactory
     */
    protected $shopFactory;

    /**
     * @var ShopResourceFactory
     */
    protected $shopResourceFactory;

    /**
     * @param Context                $context
     * @param Registry               $registry
     * @param ResourceModel\Offer    $resource
     * @param OfferCollection        $resourceCollection
     * @param ProductType            $catalogProductType
     * @param PriceInfoFactory       $priceInfoFactory
     * @param StateCollectionFactory $stateCollectionFactory
     * @param ShopFactory            $shopFactory
     * @param ShopResourceFactory    $shopResourceFactory
     * @param array                  $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel\Offer $resource,
        OfferCollection $resourceCollection,
        ProductType $catalogProductType,
        PriceInfoFactory $priceInfoFactory,
        StateCollectionFactory $stateCollectionFactory,
        ShopFactory $shopFactory,
        ShopResourceFactory $shopResourceFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->catalogProductType     = $catalogProductType;
        $this->priceInfoFactory       = $priceInfoFactory;
        $this->stateCollectionFactory = $stateCollectionFactory;
        $this->shopFactory            = $shopFactory;
        $this->shopResourceFactory    = $shopResourceFactory;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\Offer::class);
        $this->setIdFieldName(self::OFFER_ID);
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getActive()
    {
        return 'true' === $this->_getData('active');
    }

    /**
     * @return array
     */
    public function getActiveChannels()
    {
        $channels = $this->getChannels();

        return !empty($channels) ? explode(self::CHANNELS_SEPARATOR, $channels) : [];
    }

    /**
     * @return array
     */
    public function getAdditionalInfo()
    {
        if (!$this->hasData('additional_info_cache')) {
            $this->_data['additional_info_cache'] = [];
            if ($value = $this->_getData('additional_info')) {
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }
                $this->_data['additional_info_cache'] = $value;
            }
        }

        return $this->_data['additional_info_cache'];
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getAllowQuoteRequests()
    {
        return 'true' === $this->_getData('allow_quote_requests');
    }

    /**
     * For backward compatibility
     *
     * @return string
     * @deprecated Use getCurrencyIsoCode() instead
     */
    public function getCurrencyCode()
    {
        return $this->_getData('currency_iso_code');
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDeleted()
    {
        return 'true' === $this->_getData('deleted');
    }

    /**
     * @return Discount
     */
    public function getDiscount()
    {
        if (!$this->hasData('discount')) {
            $discount = new Discount([
                'origin_price'   => (float) $this->_getData('origin_price'),
                'discount_price' => (float) $this->_getData('discount_price'),
                'ranges'         => $this->_getData('discount_ranges'),
            ]);

            $startDate = $this->_getData('discount_start_date');
            if ($startDate != '0000-00-00 00:00:00') {
                $discount->setStartDate($startDate);
            }

            $endDate = $this->_getData('discount_end_date');
            if ($endDate != '0000-00-00 00:00:00') {
                $discount->setEndDate($endDate);
            }

            $this->setData('discount', $discount);
        }

        return $this->_getData('discount');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_getData('offer_id');
    }

    /**
     * @return int|null
     */
    public function getLeadtimeToShip()
    {
        $value = $this->_getData('leadtime_to_ship');

        return ('' === $value || null === $value) ? null : (int) $value;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPremium()
    {
        return 'true' === $this->_getData('premium');
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getProfessional()
    {
        return 'true' === $this->_getData('professional');
    }

    /**
     * Returns price info container of saleable item
     *
     * @return PriceInfoInterface
     */
    public function getPriceInfo()
    {
        if (!$this->priceInfo) {
            $this->priceInfo = $this->priceInfoFactory->create($this);
        }

        return $this->priceInfo;
    }

    /**
     * @return DiscountRangeCollection
     */
    public function getPriceRanges()
    {
        $ranges = new DiscountRangeCollection();

        if ($rangesString = $this->_getData('price_ranges')) {
            foreach (explode(',', $rangesString) as $range) {
                list($qty, $price) = explode('|', $range);
                $ranges->add([
                    'price' => (float) $price,
                    'quantity_threshold' => (int) $qty,
                ]);
            }
        }

        return $ranges;
    }

    /**
     * @param array $priceRanges
     * @return $this
     */
    public function setPriceRanges(array $priceRanges)
    {
        $ranges = new DiscountRangeCollection();

        foreach ($priceRanges as $priceRange) {
            $ranges->add([
                'price' => (float) $priceRange['price'],
                'quantity_threshold' => (int) $priceRange['qty'],
            ]);
        }

        return $this->setData('price_ranges', (string) $ranges);
    }

    /**
     * @param array $discountRanges
     * @return $this
     */
    public function setDiscountRanges(array $discountRanges)
    {
        $ranges = new DiscountRangeCollection();

        foreach ($discountRanges as $discountRange) {
            $ranges->add([
                'price' => (float) $discountRange['price'],
                'quantity_threshold' => (int) $discountRange['qty'],
            ]);
        }

        return $this->setData('discount_ranges', (string) $ranges);
    }

    /**
     * Returns quantity of saleable item
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getQuantity();
    }

    /**
     * For backward compatibility
     *
     * @return int
     * @deprecated Use getStateCode() instead
     */
    public function getStateId()
    {
        return $this->_getData('state_code');
    }

    /**
     * Returns type identifier of saleable item
     *
     * @return string
     */
    public function getTypeId()
    {
        return ProductType::TYPE_SIMPLE;
    }

    /**
     * Returns offer state matching specified state id
     *
     * @param int $stateId
     * @return string
     */
    public function getConditionNameById($stateId)
    {
        /** @var \Mirakl\Core\Model\Offer\State $state */
        $state = $this->getAllConditions()->getItemById($stateId);

        return $state ? $state->getName() : $stateId;
    }

    /**
     * Returns offer state collection
     *
     * @return OfferStateCollection
     */
    public function getAllConditions()
    {
        if (null === $this->states) {
            $this->states = $this->stateCollectionFactory->create();
        }

        return $this->states;
    }

    /**
     * Returns offer state matching this specific offer state id
     *
     * @return string
     */
    public function getConditionName()
    {
        return $this->getConditionNameById($this->getStateId());
    }

    /**
     * Returns shop of specified offer if available
     *
     * @return Shop
     */
    public function getShop()
    {
        $shop = $this->shopFactory->create();
        $this->shopResourceFactory->create()->load($shop, $this->getShopId());

        return $shop;
    }

    /**
     * Returns true if discount price is valid for current date, false otherwise
     *
     * @return bool
     */
    public function isDiscountPriceValid()
    {
        $discount = $this->getDiscount();
        if (!$discount->getDiscountPrice() && (!$discount->getRanges() || !$discount->getRanges()->count())) {
            return false;
        }

        $from = $discount->getStartDate();
        $to = $discount->getEndDate();

        if (!$from && !$to) {
            return true;
        }

        $currentDate = new \DateTime();

        if (!$from) {
            return $currentDate <= $to;
        } elseif (!$to) {
            return $currentDate >= $from;
        }

        return $currentDate >= $from && $currentDate <= $to;
    }
}
