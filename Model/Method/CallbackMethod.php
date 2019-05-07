<?php


namespace TheVaultApp\Magento2\Model\Method;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use TheVaultApp\Magento2\Model\GatewayResponseTrait;

class CallbackMethod extends Adapter {

    use GatewayResponseTrait;

    /**
     * @var MethodInterface
     */
    private $gatewayProvider;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CallbackMethod constructor.
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param MethodInterface $gatewayProvider
     * @param ValidatorInterface $validator
     * @param CommandPoolInterface|null $commandPool
     * @param CommandManagerInterface|null $commandExecutor
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        MethodInterface $gatewayProvider,
        ValidatorInterface $validator,
        CommandPoolInterface $commandPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {
        $this->gatewayProvider  = $gatewayProvider;
        $this->validator        = $validator;

        $code           = $this->gatewayProvider->getCode();
        $formBlockType  = $this->gatewayProvider->getFormBlockType();
        $infoBlockType  = $this->gatewayProvider->getInfoBlockType();

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            null,
            $commandExecutor
        );
    }

    /**
     * @inheritdoc
     */
    public function validate() {
        $validator = $this->validator->validate($this->gatewayResponse);

        if( ! $validator->isValid() ) {
            throw new LocalizedException(
                __(implode("\n", $validator->getFailsDescription()))
            );
        }

        return $this;
    }

}
