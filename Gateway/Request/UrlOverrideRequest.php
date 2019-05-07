<?php

 
namespace TheVaultApp\Magento2\Gateway\Request;

use TheVaultApp\Magento2\Model\Ui\ConfigProvider;

class UrlOverrideRequest extends AbstractRequest {

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    public function __construct(ConfigProvider $configProvider) {
        $this->configProvider  = $configProvider;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws \InvalidArgumentException
     */
    public function build(array $buildSubject) {

        $data = [
            'successUrl' => $this->configProvider->getSuccessUrl(),
            'failUrl'  => $this->configProvider->getFailUrl(),
        ];

        return $data;
    }

}