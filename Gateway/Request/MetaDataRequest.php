<?php

 
namespace TheVaultApp\Magento2\Gateway\Request;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TheVaultApp\Magento2\Helper\Helper;

class MetaDataRequest implements BuilderInterface {

    /**
     * @var ProductMetadataInterface
     */
    protected $metadata;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MetaDataRequest constructor.
     * @param ProductMetadataInterface $metadata
     * @param ModuleListInterface $moduleList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ProductMetadataInterface $metadata, Helper $helper, ModuleListInterface $moduleList, StoreManagerInterface $storeManager) {
        $this->metadata        = $metadata;
        $this->helper          = $helper;
        $this->moduleList      = $moduleList;
        $this->storeManager    = $storeManager;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject) {
        return [
            'metadata' => [
                'magento_name'      => $this->metadata->getName(),
                'magento_edition'   => $this->metadata->getEdition(),
                'magento_version'   => $this->metadata->getVersion(),
                'setup_version'     => $this->moduleList->getOne('TheVaultApp_Magento2')['setup_version'],
                'module_version'    => $this->helper->getModuleVersion(),
                'store_id'          => $this->storeManager->getStore()->getId(),
            ],
        ];
    }


    

}
