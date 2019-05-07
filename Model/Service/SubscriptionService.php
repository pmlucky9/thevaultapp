<?php


namespace TheVaultApp\Magento2\Model\Service;

use Magento\Framework\HTTP\ZendClient;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\ClientException;
use TheVaultApp\Magento2\Gateway\Http\TransferFactory;
use TheVaultApp\Magento2\Gateway\Config\Config;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Message\ManagerInterface;
use TheVaultApp\Magento2\Helper\Watchdog;

class SubscriptionService {

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var GatewayConfig
     */
    protected $gatewayConfig;

    /**
     * @var TransferFactory
     */
    protected $transferFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    public function __construct(Config $gatewayConfig, TransferFactory $transferFactory, Logger $logger, ManagerInterface $messageManager, Watchdog $watchdog) {
        $this->logger                   = $logger;
        $this->gatewayConfig            = $gatewayConfig;
        $this->transferFactory          = $transferFactory;
        $this->messageManager           = $messageManager;
        $this->watchdog                 = $watchdog;
    }

    public function create($data) {
        // Set the request parameters
        $url = 'charges/card';
        $method = 'POST';
        $transfer = $this->transferFactory->create([
            'paymentPlans' => array(
                array(
                    'planId'   => $data['plan_id'],
                    'startDate'   => $data['start_date'], 
                )
            )
        ]);

        // Handle the request
        $this->_handleRequest($url, $method, $transfer);
    }

    public function update($data) {
        // Set the request parameters
        $url = 'recurringPayments/plans/' . $data['planId'];
        $method = 'PUT';
        $transfer = $this->transferFactory->create([
            'name'   => $data['plan_name'],
            'planTrackId'   => $data['track_id'], 
            'autoCapTime'   => $data['auto_cap_time'],
            'value'   => $data['plan_value']*100,
        ]);

        // Handle the request
        $this->_handleRequest($url, $method, $transfer); 
    }

    public function cancel($data) {
        // Set the request parameters
        $url = 'recurringPayments/plans/' . $data['planId'];
        $method = 'DELETE';
        $transfer = $this->transferFactory->create([]);

        // Handle the request
        $this->_handleRequest($url, $method, $transfer);  
    }

    public function get($data) {
 
    }

    protected function _handleRequest($url, $method, $transfer) {

        // Prepare log data
        $log = [
            'request'           => $transfer->getBody(),
            'request_uri'       => $url,
            'request_headers'   => $transfer->getHeaders(),
            'request_method'    => $method,
        ];

        try {
            // Send the request
            $response           = $this->getHttpClient($url, $transfer)->request();
            $result             = json_decode($response->getBody(), true);

            // Handle the response
            $this->_handleResponse($result);

            // Log the response
            $log['response']    = $result;
        }
        catch (Zend_Http_Client_Exception $e) {
            throw new ClientException(__($e->getMessage()));
        }
        finally {
            $this->logger->debug($log);
        } 
    }

    protected function _handleResponse($response) {

        // Get the plan id
        $planId = isset($response['paymentPlans'][0]['planId']) ? $response['paymentPlans'][0]['planId']: null;

        // If the plan id exists
        if (($planId) && !empty($planId) && substr($planId, 0, 3 ) === 'rp_') {
            $this->messageManager->addSuccess(__('The item has been successfully saved.'));
        }
        elseif (isset($response['errorCode'])) {
            $this->messageManager->addNotice(__('The item has been saved but an error occured while uptating the Hub data. Please save again or activate the debug mode.'));
        }
        elseif ($response['message'] == 'ok') {
            $this->messageManager->addNotice(__('The data has been successfully updated.'));
        }

        // Debug info
        $this->watchdog->bark($response);
    }

    /**
     * Returns prepared HTTP client.
     *
     * @param string $endpoint
     * @param TransferInterface $transfer
     * @return ZendClient
     * @throws \Exception
     */
    private function getHttpClient($endpoint, TransferInterface $transfer) {
        $client = new ZendClient($this->gatewayConfig->getApiUrl() . $endpoint);
        $client->setMethod('POST');
        $client->setRawData( json_encode( $transfer->getBody()) ) ;
        $client->setHeaders($transfer->getHeaders());
        
        return $client;
    }
}
