<?php

namespace TheVaultApp\Checkout\Gateway\Config;

use Magento\Payment\Gateway\Config\Config as BaseConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Encryption\EncryptorInterface;
use TheVaultApp\Checkout\Model\Adminhtml\Source\Environment;

class Config extends BaseConfig {

    const CONFIG_PATH = 'payment/thevaultapp/';
    const KEY_STORE = 'store';
    const KEY_SUBID = 'subid1';

    const KEY_ENVIRONMENT = 'environment';
    const KEY_ACTIVE = 'active';
    const KEY_DEBUG = 'debug';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_USE_CVV = 'useccv';        
    const KEY_PUBLIC_KEY = 'public_key';
    const KEY_SECRET_KEY = 'secret_key';
    const MIN_AUTO_CAPTURE_TIME = 0;
    const MAX_AUTO_CAPTURE_TIME = 168;
    
    /**
     * ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * Config constructor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        LocaleResolver $localeResolver,
        EncryptorInterface $encryptor,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);

        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->encryptor = $encryptor;
    } 
    
    /**
     * Returns the environment type.
     *
     * @return string
     */
    public function getEnvironment() {
        return (string) $this->getValue(
            self::KEY_ENVIRONMENT,
            $this->storeManager->getStore()
        );
    }

    /**
     * Returns the vault option title.
     *
     * @return string
     */
    public function getVaultTitle() {
        return (string) $this->scopeConfig->getValue(
            'payment/thevaultapp_cc_vault/title',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the new order status.
     *
     * @return string
     */
    public function getNewOrderStatus() {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH . self::KEY_NEW_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the hosted logo URL.
     *
     * @return string
     */
    public function getLogoUrl() {
        $logoUrl = $this->scopeConfig->getValue(
            self::CONFIG_PATH . self::KEY_LOGO_URL,
            ScopeInterface::SCOPE_STORE
        );

        return (string) (isset($logoUrl) && !empty($logoUrl)) ? $logoUrl : 'none';
    }

    /**
     * Determines if the environment is set as sandbox mode.
     *
     * @return bool
     */
    public function isSandbox() {
        return $this->getEnvironment() === Environment::ENVIRONMENT_SANDBOX;
    }

    /**
     * Determines if the environment is set as live (production) mode.
     *
     * @return bool
     */
    public function isLive() {
        return $this->getEnvironment() === Environment::ENVIRONMENT_LIVE;
    }

    /**
     * Determines if the gateway is active.
     *
     * @return bool
     */
    public function isActive() {
        if (!$this->scopeConfig->getValue(
            self::CONFIG_PATH . self::KEY_ACTIVE,
            ScopeInterface::SCOPE_STORE
        )) {
            return false;
        }
        return true;
    }

    /**
     * Returns the store name.
     *
     * @return string
     */
    public function getStoreName() {
        $storeName = $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );

        trim($storeName);

        if (empty($storeName)) {
            $storeName = parse_url($this->storeManager->getStore()->getBaseUrl())['host'] ;
        }

        return (string) $storeName;
    }


    /**
     * Determines if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebugMode() {
        return (bool) $this->scopeConfig->getValue(
            self::CONFIG_PATH . self::KEY_DEBUG,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the public key for client-side functionality.
     *
     * @return string
     */
    public function getPublicKey() {
        return (string) $this->scopeConfig->getValue(
            'payment/thevaultapp/public_key',
            ScopeInterface::SCOPE_STORE
        );
     }

    /**
     * Returns the store name.
     *
     * @return string
     */
    public function getStore() {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH . self::KEY_STORE,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Returns the sub id
     *
     * @return string
     */
    public function getSubId() {
        return (string) $this->getValue(
            self::KEY_SUBID,
            $this->storeManager->getStore()
        );
    }

    /**
     * Returns the secret key for server-side functionality.
     *
     * @return string
     */
    public function getSecretKey() {
        return (string) $this->scopeConfig->getValue(
            self::CONFIG_PATH . self::KEY_SECRET_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

}
