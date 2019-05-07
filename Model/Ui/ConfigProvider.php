<?php


namespace TheVaultApp\Magento2\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use TheVaultApp\Magento2\Gateway\Config\Config;
use TheVaultApp\Magento2\Model\Adapter\ChargeAmountAdapter;

class ConfigProvider implements ConfigProviderInterface {

    const CODE = 'thevaultapp';

    const CC_VAULT_CODE = 'thevaultapp_cc_vault';

    const THREE_DS_CODE = 'thevaultapp_3ds';

    const CODE_ADMIN_METHOD = 'thevaultapp_admin_method';

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
                    'store'                  => $this->config->getStore(),
                    'businessname'                  => $this->config->getBusinessName(),
                    'subid'                  => $this->config->getSubId(),
                    'isActive'                  => $this->config->isActive(),
                    'debug_mode'                => $this->config->isDebugMode(),
                    'public_key'                => $this->config->getPublicKey(),
                    'secret_key'                => $this->config->getSecretKey(),
                    'hosted_url'                => $this->config->getHostedUrl(),
                    'embedded_url'              => $this->config->getEmbeddedUrl(),
                    'countrySpecificCardTypes'  => $this->config->getCountrySpecificCardTypeConfig(),
                    'availableCardTypes'        => $this->config->getAvailableCardTypes(),
                    'useCvv'                    => $this->config->isCvvEnabled(),
                    'ccTypesMapper'             => $this->config->getCcTypesMapper(),
                    'ccVaultCode'               => self::CC_VAULT_CODE,
                    Config::CODE_3DSECURE       => [
                        'enabled' => $this->config->isVerify3DSecure(),
                    ],
                    'attemptN3D' => $this->config->isAttemptN3D(),
                    'integration'               => [
                        'type'          => $this->config->getIntegration(),
                        'isHosted'      => $this->config->isHostedIntegration(),
                    ],
                    'priceAdapter' => ChargeAmountAdapter::getConfigArray(),
                    'design_settings' => $this->config->getDesignSettings(),
                    'accepted_currencies' => $this->config->getAcceptedCurrencies(),
                    'payment_mode' => $this->config->getPaymentMode(),
                    'quote_value' => $this->getQuoteValue(),
                    'quote_currency' => $this->getQuoteCurrency(),
                    'embedded_theme' => $this->config->getEmbeddedTheme(),
                    'embedded_css' => $this->config->getEmbeddedCss(),
                    'css_file' => $this->config->getCssFile(),
                    'custom_css' => $this->config->getCustomCss(),
                    'vault_title' => $this->config->getVaultTitle(),
                    'order_creation' => $this->config->getOrderCreation(),
                    'card_autosave' => $this->config->isCardAutosave(),
                    'integration_language' => $this->config->getIntegrationLanguage()
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

    /**
     * Returns the success URL override.
     *
     * @return string
     */
    public function getSuccessUrl() {
        $url = $this->storeManager->getStore()->getBaseUrl() . 'thevaultapp/payment/verify';
        return $url;
    }

    /**
     * Returns the fail URL override.
     *
     * @return string
     */
    public function getFailUrl() {
        $url = $this->storeManager->getStore()->getBaseUrl() . 'thevaultapp/payment/fail';
        return $url;
    }
}