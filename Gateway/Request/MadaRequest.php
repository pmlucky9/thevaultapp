<?php

 
namespace TheVaultApp\Magento2\Gateway\Request;

use Magento\Customer\Model\Session as CustomerSession;
use TheVaultApp\Magento2\Gateway\Config\Config;
use TheVaultApp\Magento2\Gateway\Helper\SubjectReader;

class MadaRequest extends AbstractRequest {

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config, 
        SubjectReader $subjectReader,
        CustomerSession $customerSession
    ) {
        parent::__construct($config, $subjectReader);
        $this->customerSession = $customerSession;
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \InvalidArgumentException
     */
    public function build(array $buildSubject) {
        // Prepare the output
        $arr = ['udf1' => ''];

        // Add a flag for the MADA charge
        $isMadaBin = isset($this->customerSession->getData('checkoutSessionData')['isMadaBin']) ? $this->customerSession->getData('checkoutSessionData')['isMadaBin'] : null;
        if ($this->config->isMadaEnabled() && $isMadaBin) {
            $arr = ['udf1' => 'MADA'];
        }

        return $arr;
    }
}
