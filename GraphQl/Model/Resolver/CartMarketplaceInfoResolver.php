<?php

declare(strict_types=1);

namespace Mirakl\GraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Mirakl\Connector\Model\Quote\Updater as QuoteUpdater;

class CartMarketplaceInfoResolver implements ResolverInterface
{
    /**
     * @var QuoteUpdater
     */
    protected $quoteUpdater;

    /**
     * @param QuoteUpdater $quoteUpdater
     */
    public function __construct(QuoteUpdater $quoteUpdater)
    {
        $this->quoteUpdater = $quoteUpdater;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Quote $quote */
        $quote = $value['model'];

        // Synchronize current Magento cart with Mirakl (call API SH02)
        $this->quoteUpdater->synchronize($quote);

        $baseCurrency = $quote->getBaseCurrencyCode();
        $currency = $quote->getQuoteCurrencyCode();

        return [
            'model'                           => $quote,
            'shipping_zone'                   => (string)$quote->getMiraklShippingZone(),
            'is_offer_incl_tax'               => (bool)$quote->getMiraklIsOfferInclTax(),
            'is_shipping_incl_tax'            => (bool)$quote->getMiraklIsShippingInclTax(),
            'base_shipping_excl_tax'          => [
                'value'    => $quote->getMiraklBaseShippingExclTax(),
                'currency' => $baseCurrency,
            ],
            'shipping_excl_tax'               => [
                'value'    => $quote->getMiraklShippingExclTax(),
                'currency' => $currency,
            ],
            'base_shipping_incl_tax'          => [
                'value'    => $quote->getMiraklBaseShippingInclTax(),
                'currency' => $baseCurrency,
            ],
            'shipping_incl_tax'               => [
                'value'    => $quote->getMiraklShippingInclTax(),
                'currency' => $currency,
            ],
            'base_shipping_tax_amount'        => [
                'value'    => $quote->getMiraklBaseShippingTaxAmount(),
                'currency' => $baseCurrency,
            ],
            'shipping_tax_amount'             => [
                'value'    => $quote->getMiraklShippingTaxAmount(),
                'currency' => $currency,
            ],
            'base_custom_shipping_tax_amount' => [
                'value'    => $quote->getMiraklBaseCustomShippingTaxAmount(),
                'currency' => $baseCurrency,
            ],
            'custom_shipping_tax_amount'      => [
                'value'    => $quote->getMiraklCustomShippingTaxAmount(),
                'currency' => $currency,
            ],
        ];
    }
}
