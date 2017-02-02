<?php

/**
 *
 * @category    payment gateway
 * @package     tpaycom_tpay
 * @author      tpay.com
 * @copyright   (https://tpay.com)
 */

namespace tpaycom\tpay\Block\Payment\tpay;

/**
 * Class Redirect
 * @package tpaycom\tpay\Block\Payment\tpay
 */

class Redirect extends \Magento\Framework\View\Element\AbstractBlock
{

    const NAME = 'name';

    const VALUE = 'value';

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var string
     */
    private $orderId;

    private $additionalPaymentInformation;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderModel;

    /**
     * @var \tpaycom\tpay\Model\Tpay
     */
    private $tpayModel;

    /**
     * Redirect constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param \Magento\Sales\Model\Order $OrderModel
     * @param \tpaycom\tpay\Model\Tpay $TpayModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Sales\Model\Order $orderModel,
        \tpaycom\tpay\Model\Tpay $tpayModel,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->objectManager = $objectmanager;
        $this->orderModel = $orderModel;
        $this->tpayModel = $tpayModel;
        parent::__construct(
            $context,
            $data
        );
    }

    /**Get form Html
     *
     * @param $currentOrderId
     * @param $additionalPaymentInformation
     * @return bool|string
     */

    public function getFormHtml($currentOrderId, $additionalPaymentInformation)
    {
        $this->additionalPaymentInformation = $additionalPaymentInformation;
        $this->orderId = $currentOrderId;

        return $this->toFormHtml();
    }

    /** Create a page  with form  which redirecting to tpay.com
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function toFormHtml()
    {
        $currentOrderId = $this->orderId !== null ?
            $this->orderId : $this->objectManager->get('Magento\Checkout\Model\Session')->getLastRealOrderId();

        if ($currentOrderId === null) {
            return false;
        }

        $order = $this->orderModel->loadByIncrementId($currentOrderId);
        $order->addStatusToHistory(
            \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
            __('Waiting for tpay.com payment.')
        );
        $order->setSendEmail(true);
        $order->save();

        $form = $this->createForm($currentOrderId);

        return <<<HTML
                <html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>
        <div align="center">
        {__("W celu dokończenia płatności zostaniesz przekierowany do tpay.com.
                    Wybierz przycisk jeśli przekierowanie nie nastąpiło automatycznie")}</br>
        {$form->toHtml()}
        <script type="text/javascript">document.getElementById("tpaycom_tpay_checkout").submit();</script>
         </div></body></html>
HTML;


    }

    /** Create a form with order data
     *
     * @param $currentOrderId
     * @return mixed
     */

    private function createForm($currentOrderId)
    {

        $form = $this->formFactory->create();
        $form->setAction($this->tpayModel->getRedirectURL())
            ->setId('tpaycom_tpay_checkout')
            ->setName('tpaycom_tpay_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);

        foreach ($this->tpayModel->getTpayFormData($currentOrderId) as $field => $value) {
            $form->addField($field, 'hidden', [static::NAME => $field, static::VALUE => $value]);
        }

        foreach ($this->additionalPaymentInformation as $field => $value) {
            if (!empty($value)) {
                $form->addField($field, 'hidden', [static::NAME => $field, static::VALUE => $value]);
            }
        }

        $form->addField(
            'sumbit',
            'submit',
            [static::NAME => 'redirect', static::VALUE => __('Przekierowanie do  tpay.com.'), 'align' => 'center']
        );

        return $form;
    }
}