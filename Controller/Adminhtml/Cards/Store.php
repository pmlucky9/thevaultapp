<?php

 
namespace TheVaultApp\Magento2\Controller\Adminhtml\Cards;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use TheVaultApp\Magento2\Model\Service\StoreCardService;

class Store extends Action {

    /**
     * @var StoreCardService
     */
    protected $storeCardService;

    /**
     * Store constructor.
     * @param Context $context
     * @param StoreCardService $storeCardService
     */
    public function __construct(Context $context, StoreCardService $storeCardService) {
        parent::__construct($context);

        $this->storeCardService = $storeCardService;
    }

    /**
     * Handles the controller method.
     *
     * Saves the credit card
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute() {
        // Get the card token from request
        $ckoCardToken = $this->getCardToken();

        // Get the customer id from request
        $customerId = $this->getCustomerId();

        try {
            $this->storeCardService
                 ->setCardToken($ckoCardToken)
                 ->setCustomerId($customerId)
                 ->setCustomerEmail()
                 ->test()
                 ->setCardData()
                 ->save();

            $this->messageManager->addSuccessMessage( __('The payment card has been stored successfully.'));
        }
        catch(\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        
        return $this->_redirect('vault/cards/listAction');
    }    

    public function getCustomerId() {
        return (int) $this->getRequest()->getParam('cid');
    }

    public function getCardToken() {

        $params = array_keys($this->getRequest()->getParams());
        $params = json_decode($params[0]);

        return $params->ckoCardToken;
    }

}
