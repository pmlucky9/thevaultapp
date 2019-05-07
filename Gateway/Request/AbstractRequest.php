<?php

 
namespace TheVaultApp\Magento2\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TheVaultApp\Magento2\Gateway\Config\Config;
use TheVaultApp\Magento2\Gateway\Helper\SubjectReader;

abstract class AbstractRequest implements BuilderInterface {

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * AbstractRequest constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader) {
        $this->config           = $config;
        $this->subjectReader    = $subjectReader;
    }

}
