<?php

/**
 * Dotpay action /dotpay/processing/widget
 */

namespace tpay\com\Controller\Processing;

use tpay\com\Controller\tpayCom;
use tpay\com\Model\payment;
class pay extends tpayCom {
    
    protected $_coreRegistry;
    
    protected $_resultPageFactory;
    
    protected $agreementByLaw = '';
    
    protected $agreementPersonalData = '';
    private $objectManager;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Dotpay\Dotpay\Model\Payment $model
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context
        , \Magento\Customer\Model\Session $customerSession
        , \Magento\Checkout\Model\Session $checkoutSession
        , \Magento\Sales\Model\OrderFactory $orderFactory
        , \Dotpay\Dotpay\Model\Payment $model
        , \Magento\Framework\Locale\Resolver $localeResolver
        , \Magento\Framework\Registry $coreRegistry
        , \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $pageFactory;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $orderFactory,
            $model,
            $localeResolver
        );
    }






    public function execute() {

    echo 'podstrona /com/Processing/pay';


        /**
         *
         */
//        $this->agreementByLaw = $this->getDotpayAgreement('bylaw');
//        $this->agreementPersonalData = $this->getDotpayAgreement('personal_data');

        /**
         *
         */
//        $agreements = array(
//            'bylaw' => $this->agreementByLaw,
//            'personal_data' => $this->agreementPersonalData,
//        );

        /**
         * hidden fields One-Click, MasterPass, BLIK, Dotpay
         */
//        $hiddenFields = array();
////        $disabledChannels = array();
//
//
//
//
//        /**
//         * Dotpay
//         */
//        $hiddenFields['dotpay'] = array(
////            'active' => $this->_model->isDotpayWidget(),
//                'fields' => $this->getHiddenFields(),
//
////            'agreements' => $agreements,
////            'icon' => $this->_model->getPaymentDotpayImageUrl(),
//                'text' => '',
////            'action' => $this->getDotAction()
//        );
//        $hiddenFields2['dotpay2'] = array( 'fields' => $this->getRedirectionFormData(),
//        );

        /**
         *
         */
//        foreach($hiddenFields as $key => $val) {
//            $oneclickCardTest = 'oneclick_card';
//            $keySubstr = substr($key, 0, strlen($oneclickCardTest));

//            if($oneclickCardTest === $keySubstr) {
//                $chk = $this->buildSignature4Request($oneclickCardTest, null, null, $val['fields']['credit_card_customer_id']);
//            } else {
//                $chk = $this->buildSignature4Request($key);
//            }

//            $hiddenFields[$key]['fields']['chk'] = $chk;
//        }

        /**
         *
         */
//        if($this->_model->isDotpayOneClick() || $this->_model->isDotpayMasterPass() || $this->_model->isDotpayBlik() || $this->_model->isDotpayWidget()) {
//            $txtP = __('You chose payment by Dotpay. Select a payment channel and click Continue do proceed');
//        } else {
//            $txtP = __('You chose payment by Dotpay. Click Continue do proceed');
//        }
//
//        $this->_coreRegistry->register('dataWidget', array(
//            'oneclick' => $this->_model->isDotpayOneClick(),
//            'oneclickTxtValid' => __('6 or more characters'),
//            'oneclickTxtPlaceholder' => __('Card title 6 or more characters'),
//            'oneclickTxtSaveCard' => __('Remember your data card (Your card details are safely stored in Dotpay. There will be no need for them to enter the next payment in the store.)'),
//            'mp' => $this->_model->isDotpayMasterPass(),
//            'blik' => $this->_model->isDotpayBlik(),
//            'blikTxtValid' => __('Only 6 digits'),
//            'blikTxtPlaceholder' => __('Blik code 6 digits'),
//            'widget' => $this->_model->isDotpayWidget(),
//            'txtP' => $txtP,
//            'txtSubmit' => __('Continue'),
//            'hiddenFields' => $hiddenFields,
//            'signatureUrl' => $this->getDotUrlSignature(),
//            'oneclickRegisterUrl' => $this->getDotUrlOneClickRegister(),
//            'txtSelectedChannel' => __('Selected payment channel'),
//            'txtChangeChannel' => __('change the channel'),
//            'txtAvChannels' => __('Available channels'),
//            'disabledChannels' => implode(',', $disabledChannels),
//        ));
//
//        /**
//         * must be before return?
//         */
//        $this->_view->getPage()->getConfig()->getTitle()->set(__('Dotpay channels payment'));
//
//        return $this->_resultPageFactory->create();

    }
    

}
