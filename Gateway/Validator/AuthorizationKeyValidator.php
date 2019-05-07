<?php

 
namespace TheVaultApp\Magento2\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use TheVaultApp\Magento2\Model\Validator\Rule;
use TheVaultApp\Magento2\Gateway\Config\Config;
        
class AuthorizationKeyValidator extends ResponseValidator {

    /**
     * @var Config
     */
    protected $gatewayConfig;

    /**
     * AuthorizationKeyValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param Config $config
     */
    public function __construct(ResultInterfaceFactory $resultFactory, Config $config) {
        parent::__construct($resultFactory);

        $this->gatewayConfig = $config;
    }

    /**
     * Returns the array of the rules.
     *
     * @return Rule[]
     */
    protected function rules() {
        return [
            new Rule('Authorization Key Exists', function(array $subject) {
                return isset($subject['headers']['Authorization']) AND ! empty($subject['headers']['Authorization']);
            }, __('TheVaultApp response secret key is empty.') ),
            
            new Rule('Authorization Key Correct', function(array $subject) {
                $authKey = $subject['headers']['Authorization'];
                
                return $authKey === $this->gatewayConfig->getPrivateSharedKey();
            }, __('TheVaultApp response secret key does not match to the configured.')),
        ];
    }
    
}