<?php


namespace TheVaultApp\Checkout\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use TheVaultApp\Checkout\Gateway\Config\Config;
use TheVaultApp\Checkout\Model\Adapter\ChargeAmountAdapter;

class ConfigProvider implements ConfigProviderInterface {

    const CODE = 'thevaultapp';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Config $config, Session $checkoutSession, StoreManagerInterface $storeManager) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig() {
        return [
            'payment' => [
                self::CODE => [
                    'store'                     => $this->config->getStore(),
                    'subid'                     => $this->config->getSubId(),
                    'isActive'                  => $this->config->isActive(),
                    'debug_mode'                => $this->config->isDebugMode(),
                    'public_key'                => $this->config->getPublicKey(),
                    'secret_key'                => $this->config->getSecretKey(),                    
                    'quote_value'               => $this->getQuoteValue(),
                    'quote_currency'            => $this->getQuoteCurrency(),
                    'vault_title'               => $this->config->getVaultTitle(),                   
                ],
            ],
        ];
    }

    /**
     * Get a quote value.
     *
     * @return float
     */
    public function getQuoteValue() {
        // Return the quote amount
        $quote = $this->checkoutSession->getQuote()->collectTotals()->save();
        return $quote->getGrandTotal();
    }

    /**
     * Get a quote currency code.
     *
     * @return string
     */
    public function getQuoteCurrency() {
        // Return the quote currency
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }
}