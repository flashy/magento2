<?php
namespace Flashy\Integration\Helper;

use Flashy;
use Magento\Setup\Exception;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const COOKIE_DURATION = 86400; // lifetime in seconds
    const FLASHY_CONNECTED_STRING_PATH = 'flashy/flashy/flashy_connected';
    const FLASHY_ID_STRING_PATH = 'flashy/flashy/flashy_id';
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var Flashy
     */
    public $flashy;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $_configWriter;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $_customerCollectionFactory;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    protected $_subscriberCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Flashy\Integration\Model\CarthashFactory
     */
    protected $_carthashFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cartModel;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $_imageHelperFactory;

    /**
     * @var \Flashy\Integration\Logger\Logger
     */
    protected $_flashyLogger;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $_coupon;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directorylist;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param Flashy\Integration\Model\CarthashFactory $carthashFactory
     * @param \Magento\Checkout\Model\Cart $cartModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Flashy\Integration\Logger\Logger $flashyLogger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Flashy\Integration\Model\CarthashFactory $carthashFactory,
        \Magento\Checkout\Model\Cart $cartModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Flashy\Integration\Logger\Logger $flashyLogger,
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magento\Framework\App\Filesystem\DirectoryList $directorylist
    )
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $v = explode('.',$productMetadata->getVersion());
        if($v[1] > 1) {
            $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        } else {
            $scopeConfig = $context->getScopeConfig();
        }
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_productMetadata = $productMetadata;
        $this->_configWriter = $configWriter;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_registry = $registry;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->_subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_carthashFactory = $carthashFactory;
        $this->_cartModel = $cartModel;
        $this->_productFactory = $productFactory;
        $this->_formKey = $formKey;
        $this->_imageHelperFactory = $imageHelperFactory;
        $this->_flashyLogger = $flashyLogger;
        $this->_coupon = $coupon;
        $this->_directorylist = $directorylist;

        parent::__construct($context);
    }

    /**
     * Get base url.
     *
     * @param $scope_id
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrlByScopeId($scope_id)
    {
        return $this->_storeManager->getStore($scope_id)->getBaseUrl();
    }

    /**
     * Get current currency code.
     *
     * @param $store_id
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyByStoreId($store_id)
    {
        return $this->_storeManager->getStore($store_id)->getCurrentCurrencyCode();
    }

    /**
     * Get flashy active.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFlashyActive()
    {
        return $this->_scopeConfig->getValue('flashy/flashy/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
    }

    /**
     * Get flashy id from Flashy.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFlashyId()
    {
        if($this->getFlashyActive()) {
            return $this->_scopeConfig->getValue('flashy/flashy/flashy_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        } else {
            return false;
        }
    }

    /**
     * Get flashy purchase from Config.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFlashyPurchase()
    {
        return $this->_scopeConfig->getValue('flashy/flashy/purchase', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
    }

    /**
     * Get flashy log from Config.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFlashyLog()
    {
        return $this->_scopeConfig->getValue('flashy/flashy/log', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
    }

    /**
     * Check if Flashy api key is valid.
     *
     * @param $api_key
     * @return mixed
     */
    public function checkApiKey($api_key)
    {
        try {
            $this->flashy = new Flashy($api_key);
            $info = $this->flashy->account->info();

            return $info['success'];
        } catch (\Flashy_Error $e) {
            return null;
        }
    }

    /**
     * Get Flashy api key.
     *
     * @param $scope
     * @param $scopeId
     * @return mixed
     */
    public function getFlashyKey($scope, $scopeId)
    {
        return $this->_scopeConfig->getValue('flashy/flashy/flashy_key', $scope, $scopeId);
    }

    /**
     * Get store name.
     *
     * @param $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreName($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getName();
    }

    /**
     * Get general contact email address.
     *
     * @param $scope
     * @param $scopeId
     * @return mixed
     */
    public function getStoreEmail($scope, $scopeId)
    {
        return $this->_scopeConfig->getValue('trans_email/ident_general/email', $scope, $scopeId);
    }

    /**
     * Get Flashy Connected.
     *
     * @param $scope
     * @param $scopeId
     * @return bool
     */
    public function getFlashyConnected($scope, $scopeId)
    {
        return $this->_scopeConfig->getValue('flashy/flashy/flashy_connected',$scope, $scopeId) == '1';
    }

    /**
     * Get Flashy list.
     *
     * @param $storeId
     * @return mixed
     */
    public function getFlashyList($storeId)
    {
        return $this->_scopeConfig->getValue(
            'flashy/flashy_lists/flashy_list',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get current order data.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderDetails()
    {
        $this->addLog('getOrderDetails');
        $data = array();
        try {
            $this->flashy = new Flashy($this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId()));

            $orderId = $this->_checkoutSession->getLastRealOrderId();

            $order = $this->_orderFactory->create()->loadByIncrementId($orderId);

            $contactData = [
                'email' => $order->getCustomerEmail(),
                'first_name' => $order->getShippingAddress()->getFirstname(),
                'last_name' => $order->getShippingAddress()->getLastname(),
                'phone' => $order->getShippingAddress()->getTelephone(),
                'city' => $order->getShippingAddress()->getCity(),
                'gender' => $order->getCustomerGender()
            ];

            $this->addLog('Contact Data ' . print_r($contactData, true));

            $this->flashy->contacts->create($contactData);
            $this->addLog('Flashy contact created');

            $total = (float) $order->getSubtotal();
            $this->addLog('Order total=' . $total);

            $items = $order->getAllItems();
            $this->addLog('Getting order items');

            $products = [];

            foreach ($items as $i):
                $products[] = $i->getProductId();
            endforeach;
            $this->addLog('Getting product ids');

            $data = array(
                "order_id"  => $order->getIncrementId(),
                "value"   => $total,
                "content_ids"  => $products,
                "status" => $order->getStatus(),
                "email" => $contactData['email'],
                "currency"  => $order->getOrderCurrencyCode()
            );
            $this->addLog('Data=' . print_r($data, true));
        } catch (\Flashy_Error $e) {
        }
        return $data;
    }

    /**
     * Place order.
     *
     * @param $order
     * @throws \Flashy_Error
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function orderPlace($order)
    {
        $this->addLog('salesOrderPlaceAfter');
        $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if($this->getFlashyActive() && !empty($flashy_key) && $this->getFlashyPurchase()) {

            $this->flashy = new Flashy($flashy_key);

            $account_id = $this->getFlashyId();

            $this->addLog('account_id='.$account_id);

            $contactData = [
                'email' => $order->getCustomerEmail(),
                'first_name' => $order->getShippingAddress()->getFirstname(),
                'last_name' => $order->getShippingAddress()->getLastname(),
                'phone' => $order->getShippingAddress()->getTelephone(),
                'city' => $order->getShippingAddress()->getCity(),
                'gender' => $order->getCustomerGender()
            ];

            $this->addLog('Contact Data ' . print_r($contactData, true));

            $this->flashy->contacts->create($contactData);
            $this->addLog('Flashy contact created');

            $total = (float) $order->getSubtotal();
            $this->addLog('Order total=' . $total);

            $items = $order->getAllItems();
            $this->addLog('Getting order items');

            $products = [];

            foreach($items as $i):
                $products[] = $i->getProductId();
            endforeach;
            $this->addLog('Getting product ids');

            $currency = $order->getOrderCurrencyCode();
            $this->addLog('Currency='.$currency);

            $data = array(
                "account_id" => $account_id,
                "email" => $contactData['email'],
                "order_id"  => $order->getIncrementId(),
                "value"   => $total,
                "content_ids"  => $products,
                "status" => $order->getStatus(),
                "currency"  => $currency
            );

            $this->addLog('Data=' . print_r($data, true));

            $track = $this->flashy->thunder->track($account_id, $contactData['email'], "Purchase", $data);

            $this->addLog('Purchase sent.');
            $this->addLog(json_encode($track));
        }
    }

    /**
     * Get current product data.
     *
     * @return array
     */
    public function getProductDetails()
    {
        $product = $this->_registry->registry('current_product');
        $products = [];
        $products[] = $product->getId();
        $data = array(
            "content_ids" => $products
        );
        return $data;
    }

    /**
     * Get cart data.
     *
     * @return array|bool|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCart()
    {
        $cart = $this->_checkoutSession->getQuote();

        $tracking = [];

        foreach ($cart->getAllVisibleItems() as $item) {
            $tracking['content_ids'][] = $item->getProductId();
        }

        $tracking['value'] = intval($cart->getGrandTotal());

        if ($tracking['value'] <= 0) return false;

        $tracking['currency'] = $cart->getQuoteCurrencyCode();

        $tracking = json_encode($tracking);

        return $tracking;
    }

    /**
     * Set flashy cart cache in cookie.
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setFlashyCartCache()
    {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_DURATION);
        $this->_cookieManager->setPublicCookie(
            'flashy_cart_cache',
            base64_encode($this->getCart()),
            $metadata
        );
    }

    /**
     * Get flashy cart cache from cookie.
     *
     * @return null|string
     */
    public function getFlashyCartCache()
    {
        return $this->_cookieManager->getCookie('flashy_cart_cache');
    }

    /**
     * Get flashy id from cookie.
     *
     * @return null|string
     */
    public function getFlashyIdCookie()
    {
        return $this->_cookieManager->getCookie('flashy_id');
    }

    /**
     * Check if customer is logged in.
     *
     * @return bool
     */
    public function customerIsLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Get customer email.
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->_customerSession->getCustomer()->getEmail();
    }

    /**
     * Get lists from Flashy.
     *
     * @return array
     */
    public function getFlashyListOptions()
    {
        $options = array();
        $store_id = $this->_request->getParam("store", 0);
        try {
            $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
            $this->flashy = new Flashy($flashy_key);

            $lists = $this->flashy->lists->all();
            if(isset($lists['lists'])) {
                foreach ($lists['lists'] as $list) {
                    $options[] = array(
                        'value' => strval($list['id']),
                        'label' => $list['title']
                    );
                }
            }

            if( count( $options ) == 0 ) {
                $options[] = array(
                    'value' => strval(''),
                    'label' => 'Choose a list'
                );
            }
        } catch (\Flashy_Error $e) {
        }
        return $options;
    }

    /**
     * Get catalogs from Flashy.
     *
     * @return array
     */
    public function getFlashyCatalogOptions()
    {
        $store_id = $this->_request->getParam("store", 0);
        $options = array();
        try {
            $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
            $this->flashy = new Flashy($flashy_key);

            $catalogs = $this->flashy->catalogs->get();

            foreach ($catalogs['catalogs'] as $catalog) {
                $options[] = array(
                    'value' => strval($catalog['id']),
                    'label' => $catalog['title']
                );
            }
        } catch (\Flashy_Error $e) {
        }
        return $options;
    }

    /**
     * Get lists as associative array from Flashy.
     *
     * @return array
     */
    public function getFlashyListOptionsArray()
    {
        $options = array();
        $store_id = $this->_request->getParam("store", 0);
        try {
            $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);

            $this->flashy = new Flashy($flashy_key);

            $lists = $this->flashy->lists->all();

            foreach ($lists['lists'] as $list) {
                $options[$list['id']] = $list['title'];
            }

            if( count( $options ) == 0 ) {
                $options[''] = 'Choose a list';
            }
        } catch (\Flashy_Error $e) {
        }
        return $options;
    }

    /**
     * Send subscriber email to Flashy.
     *
     * @param $subscriberEmail
     * @param $storeId
     */
    public function subscriberSend($subscriberEmail, $storeId)
    {
        try {
            $list_id = $this->getFlashyList($storeId);
            $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

            if (!empty($list_id) && !empty($flashy_key)) {
                $this->flashy = new Flashy($flashy_key);

                if ( $list_id != '' ) {
                    $this->flashy->lists->subscribe($list_id, array(
                        "email" => $subscriberEmail,
                    ));
                } else {
                    $this->addLog('Newsletter new subscriber: lists is not exists');
                }
            } else {
                $this->addLog('Newsletter new subscriber: Flashy API Key="' . $flashy_key . '" list id="' . $list_id.'"');
            }
        } catch (\Flashy_Error $e) {
        }
    }

    /**
     * Send order data to Flashy.
     *
     * @param $order
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function orderSend($observer,$order)
    {
        try {
            $this->addLog('salesOrderChange');
            $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
            if($this->getFlashyActive() && !empty($flashy_key)) {

                $this->flashy = new Flashy($flashy_key);
                $account_id = $this->getFlashyId();

                if ($order->getStatus() != $order->getOrigData('status')) {

                    $email = $order->getCustomerEmail();

                    $data = array(
                        "order_id" => $order->getIncrementId(),
                        "status" => $order->getStatus()
                    );

                    $shipmentCollection = $order->getShipmentsCollection();

                    foreach($shipmentCollection as $shipment) {

                        foreach ($shipment->getAllTracks() as $track) {
                            $trackNumber = $track->getData()['track_number'];
                            $this->addLog("Adding track number: $trackNumber");
                            $data['tracking_id'] = $trackNumber;
                        }
                    }

                    $track = $this->flashy->thunder->track($account_id, $email, "PurchaseUpdated", $data);

                    $this->addLog("Purchase Updated with data for $account_id and $email:" . json_encode($track));
                }
            }
        } catch (\Flashy_Error $e) {
        }
    }

    public function getProductsTotalCount($store_id) {
        $products = $this->_productCollectionFactory->create();
        $products->addAttributeToSelect('*');
        $products->addStoreFilter($store_id);

        return $products->getSize();

    }

    /**
     * Get exported products
     *
     * @param $store_id
     * @param $limit
     * @param $page
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function exportProducts($store_id, $limit, $page)
    {
        $products = $this->_productCollectionFactory->create();
        $products->addAttributeToSelect('*');
        $products->addStoreFilter($store_id);

        if($limit){
            $products->setPageSize($limit);
            if($page){
                $products->setCurPage($page);
            }
        }

        $export_products = array();

        $i = 0;

        $currency = $this->getCurrencyByStoreId($store_id);

        foreach ($products as $_product) {
            try {
                $export_products[$i] = array(
                    'id' => $_product->getId(),
                    'link' => $_product->getProductUrl($_product),
                    'title' => $_product->getName(),
                    'description' => $_product->getShortDescription(),
                    'price' => $_product->getPrice(),
                    'final_price'	=> $_product->getFinalPrice(),
                    'sale_price'	=> $_product->getSpecialPrice(),
                    'sale_price_effective_date'	=> date('Y-m-d\TH:i:sO', strtotime($_product->getSpecialFromDate())).'/'.date('Y-m-d\TH:i:sO', strtotime($_product->getSpecialToDate())),
                    'currency' => $currency,
                    'tags' => $_product->getMetaKeyword()
                );

                if ($_product->getImage() && $_product->getImage() != 'no_selection') {
                    $export_products[$i]['image_link'] = $this->_imageHelperFactory->create()->init($_product, 'product_thumbnail_image')->getUrl();
                }

                $categoryCollection = $_product->getCategoryCollection()->addAttributeToSelect('name');

                $export_products[$i]['product_type'] = "";

                foreach ($categoryCollection as $_cat) {
                    $export_products[$i]['product_type'] .= $_cat->getName() . '>';
                }

                $export_products[$i]['product_type'] = substr($export_products[$i]['product_type'], 0, -1);

                $i++;
            } catch (\Exception $e) {
                continue;
            }
        }
        $page_size = $products->getPageSize();
        $current_page = $products->getCurPage();
        $total = $this->getProductsTotalCount($store_id);
        $size = $products->getSize();

        $flashy_pagination = false;
        $next_url = null;
        if($limit){
            if(ceil($size/$page_size) > $current_page){
                $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
                $base_url = $this->getBaseUrlByScopeId($store_id);
                $nextpage = $current_page + 1;
                $next_url = $base_url . "flashy?export=products&store_id=$store_id&limit=$limit&page=$nextpage&flashy_key=$flashy_key";
            }
            if($size > $limit){
                $flashy_pagination = true;
            }
        }

        return array(
            "data" => $export_products,
            "store_id" => $store_id,
            "size" => $size,
            "page_size" => $page_size,
            "current_page" => $current_page,
            "count"=> count($export_products),
            "total"=> $total,
            "flashy_pagination"=> $flashy_pagination,
            "next_page"=> $next_url,
            "success" => true
        );
    }

    /**
     * Send exported products to Flashy.
     *
     * @param $store_id
     * @param $catalog_id
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function exportProductsSend($store_id, $catalog_id)
    {
        try {
            $this->flashy = new Flashy($this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id));
            return $this->flashy->import->products($this->exportProducts($store_id), $catalog_id);
        } catch (\Flashy_Error $e) {
        }
    }

    /**
     * get Customers Total Count
     *
     * @param $store_id
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomersTotalCount($store_id) {
        //get website id from  store id
        $websiteId = $this->_storeManager->getStore($store_id)->getWebsiteId();

        $customers = $this->_customerCollectionFactory->create();

        //get all attributes
        $customers->addAttributeToSelect('*');

        //filter by website
        if($websiteId > 0) {
            $customers->addAttributeToFilter("website_id", array("eq" => $websiteId));
        }
        return $customers->getSize();
    }

    /**
     * get Subscibers Total Count
     *
     * @param $store_id
     * @return int
     */
    public function getSubscibersTotalCount($store_id) {
        //get subscriber collection
        $subscribers = $this->_subscriberCollectionFactory->create();

        //filter by store id
        if($store_id > 0) {
            $subscribers->addStoreFilter($store_id);
        }

        //get only guest subscribers as customers are included already
        $subscribers->addFieldToFilter('main_table.customer_id', ['eq' => 0]);
        return $subscribers->getSize();
    }

    /**
     * Get exported customers and subscribers
     *
     * @param $store_id
     * @param $limit
     * @param $page
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function exportContacts($store_id, $limit, $page)
    {
        $total1 = $this->getCustomersTotalCount($store_id);
        $total2 = $this->getSubscibersTotalCount($store_id);

        $c = true;
        $s = true;
        $offset = 0;
        $limit1 = $limit;
        if($limit) {
            if (($page * $limit) <= $total1) {
                //we'll show only customers
                $s = false;
            } else {
                $offset = $page * $limit - $total1;
                if ($offset < $limit) {
                    //we'll show both customers and subscribers
                    $limit1 = $offset;
                    $offset = 0;
                } else {
                    //we'll show only subscribers
                    $c = false;
                    $offset -= $limit;
                }

            }
        }

        $i = 0;
        $export_customers = array();
        if($c) {
            //get website id from  store id
            $websiteId = $this->_storeManager->getStore($store_id)->getWebsiteId();

            $customers = $this->_customerCollectionFactory->create();

            //get all attributes
            $customers->addAttributeToSelect('*');

            //filter by website
            if($websiteId > 0) {
                $customers->addAttributeToFilter("website_id", array("eq" => $websiteId));
            }

            if ($limit) {
                $customers->setPageSize($limit);
                if ($page) {
                    $customers->setCurPage($page);
                }
            }

            foreach ($customers as $_customer) {
                //add customer fields
                $export_customers[$i] = array(
                    'email' => $_customer->getEmail(),
                    'first_name' => $_customer->getFirstname(),
                    'last_name' => $_customer->getLastname()
                );

                //get default shipping address of customer
                $address = $_customer->getDefaultShippingAddress();

                //add address fields
                if ($address) {
                    $export_customers[$i]['phone'] = $address->getTelephone();
                    $export_customers[$i]['city'] = $address->getCity();
                    $export_customers[$i]['country'] = $address->getCountry();
                }
                $i++;
            }
        }

        if($s) {
            //get subscriber collection
            $subscribers = $this->_subscriberCollectionFactory->create();

            //filter by store id
            if($store_id > 0) {
                $subscribers->addStoreFilter($store_id);
            }

            //get only guest subscribers as customers are included already
            $subscribers->addFieldToFilter('main_table.customer_id', ['eq' => 0]);

            if($limit1) {
                $select = $subscribers->getSelect();

                $select->limit($limit1, $offset);
            }

            foreach ($subscribers as $subscriber) {
                //add subscriber email, no other fields are available by default
                $export_customers[$i]['email'] = $subscriber->getEmail();
                $i++;
            }
        }

        $page_size = $limit;
        $current_page = $page;
        $total = $total1 + $total2;

        $flashy_pagination = false;
        $next_url = null;
        if($limit){
            if(ceil($total/$page_size) > $current_page){
                $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
                $base_url = $this->getBaseUrlByScopeId($store_id);
                $nextpage = $current_page + 1;
                $next_url = $base_url . "flashy?export=contacts&store_id=$store_id&limit=$limit&page=$nextpage&flashy_key=$flashy_key";
            }
            if($total > $limit){
                $flashy_pagination = true;
            }
        }

        return array(
            "data" => $export_customers,
            "store_id" => $store_id,
            "size" => $total,
            "page_size" => $page_size,
            "current_page" => $current_page,
            "count"=> count($export_customers),
            "total"=> $total,
            "flashy_pagination"=> $flashy_pagination,
            "next_page"=> $next_url,
            "success" => true
        );
    }

    /**
     * get Orders Total Count
     *
     * @param $store_id
     * @return int
     */
    public function getOrdersTotalCount($store_id) {
        //get order collection
        $orders = $this->_orderCollectionFactory->create();

        //get all attributes
        $orders->addAttributeToSelect('*');

        //filter by store id
        if($store_id > 0) {
            $orders->addFieldToFilter('main_table.store_id', ['eq' => $store_id]);
        }
        return $orders->getSize();
    }

    /**
     * Get exported orders
     *
     * @param $store_id
     * @param $limit
     * @param $page
     * @return array
     */
    public function exportOrders($store_id, $limit, $page)
    {
        //get order collection
        $orders = $this->_orderCollectionFactory->create();

        //get all attributes
        $orders->addAttributeToSelect('*');

        //filter by store id
        if($store_id > 0) {
            $orders->addFieldToFilter('main_table.store_id', ['eq' => $store_id]);
        }

        if($limit){
            $orders->setPageSize($limit);
            if($page){
                $orders->setCurPage($page);
            }
        }

        $i = 0;
        $export_orders = array();
        foreach ($orders as $order) {
            $items = $order->getAllItems();

            $products = [];

            foreach ($items as $item):
                $products[] = $item->getProductId();
            endforeach;

            $export_orders[$i] = array(
                "email" => $order->getCustomerEmail(),
                "order_id" => $order->getId(),
                "order_increment_id" => $order->getIncrementId(),
                "value" => (float)$order->getGrandTotal(),
                "date" => strtotime($order->getCreatedAt()),
                "content_ids" => implode(',',$products),
                "currency" => $order->getOrderCurrencyCode()
            );
            $i++;
        }

        $page_size = $orders->getPageSize();
        $current_page = $orders->getCurPage();
        $total = $this->getOrdersTotalCount($store_id);

        $flashy_pagination = false;
        $next_url = null;
        if($limit){
            if(ceil($total/$page_size) > $current_page){
                $flashy_key = $this->getFlashyKey(\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
                $base_url = $this->getBaseUrlByScopeId($store_id);
                $nextpage = $current_page + 1;
                $next_url = $base_url . "flashy?export=orders&store_id=$store_id&limit=$limit&page=$nextpage&flashy_key=$flashy_key";
            }
            if($total > $limit){
                $flashy_pagination = true;
            }
        }

        return array(
            "data" => $export_orders,
            "store_id" => $store_id,
            "size" => $orders->getSize(),
            "page_size" => $page_size,
            "current_page" => $current_page,
            "count"=> count($export_orders),
            "total"=> $total,
            "flashy_pagination"=> $flashy_pagination,
            "next_page"=> $next_url,
            "success" => true
        );
    }

    /**
     * Set Flashy Connected
     *
     * @param $value
     * @param $scope
     * @param $scope_id
     */
    public function setFlashyConnected($value, $scope, $scope_id)
    {
        $this->_configWriter->save(self::FLASHY_CONNECTED_STRING_PATH, $value?1:0, $scope, $scope_id);
        $this->_configWriter->save(self::FLASHY_ID_STRING_PATH, $value, $scope, $scope_id);
    }

    /**
     * Remove Flashy Connected
     *
     * @param $scope
     * @param $scope_id
     */
    public function removeFlashyConnected($scope, $scope_id)
    {
        $this->_configWriter->delete(self::FLASHY_CONNECTED_STRING_PATH, $scope, $scope_id);
        $this->_configWriter->delete(self::FLASHY_ID_STRING_PATH, $scope, $scope_id);
    }

    /**
     * Set Flashy Id
     *
     * @param $value
     * @param $scope
     * @param $scope_id
     */
    public function setFlashyConnectedId($value, $scope, $scope_id)
    {
        $this->_configWriter->save(self::FLASHY_ID_STRING_PATH, $value, $scope, $scope_id);
    }

    /**
     * Remove Flashy Id
     *
     * @param $scope
     * @param $scope_id
     */
    public function removeFlashyConnectedId($scope, $scope_id)
    {
        $this->_configWriter->delete(self::FLASHY_ID_STRING_PATH, $scope, $scope_id);
    }

    /**
     * Do connection request to Flashy.
     *
     * @param $api_key
     * @param $scope
     * @param $scope_id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function connectionRequest($api_key, $scope, $scope_id)
    {
        $store_email = $this->getStoreEmail($scope, $scope_id);
        $store_name = $this->getStoreName($scope_id);
        $base_url = $this->getBaseUrlByScopeId($scope_id);


        $data = array(
            "profile" => array(
                "from_name" => $store_name,
                "from_email" => $store_email,
                "reply_to" => $store_email,
            ),
            "store"	=> array(
                "platform" => "magento",
                "api_key" => $api_key,
                "store_name" => $store_name,
                "store" => $base_url,
                "debug" => array(
                    "magento" => $this->_productMetadata->getVersion(),
                    "php" => phpversion(),
                    "memory_limit" => ini_get('memory_limit'),
                ),
            )
        );
        $urls = array("contacts", "products", "orders");
        foreach ($urls as $url) {
            $data[$url] = array(
                "url" => $base_url . "flashy?export=$url&store_id=$scope_id&limit=100&page=1&flashy_pagination=true&flashy_key=" . $api_key,
                "format" => "json_url",
            );
        }

        try {
            $this->addLog("Connection Request Data => " . print_r($data, true));
            $this->flashy->account->connect($data);

            $info = $this->flashy->account->info();

            return $info['account']['id'];
        } catch (\Flashy_Error $e) {
            return 0;
        }
    }

    /**
     * Update Flashy Cart Hash
     *
     * @param $cart
     */
    public function updateFlashyCartHash($cart)
    {
        //cart hash will not be updated
        $updateCart = false;

        //get key from cookie
        $key = $this->getFlashyIdCookie();

        //if key exists
        if($key) {
            //create flashy cart hash
            $cartHash = $this->_carthashFactory->create();

            //load cart hash by key
            $cartHash->load($key, 'key');

            //get quote from cart
            $quote = $cart->getQuote();

            //get all visible items of the cart
            $items = $quote->getAllVisibleItems();

            //cart items data
            $cartItems = array();

            //loop through cart visible items
            foreach ($items as $item) {
                //get product options
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                //update qty
                $options['info_buyRequest']['qty'] = $item->getQty();

                // unset uenc from cart item data
                unset($options['info_buyRequest']['uenc']);

                //add info to cart items
                $cartItems[] = $options['info_buyRequest'];

                //cart hash will be updated
                $updateCart = true;
            }

            //check if cart will be updated
            if($updateCart) {
                try {
                    //save cart hash data
                    $cartHash->setKey($key);
                    $cartHash->setCart(json_encode($cartItems));
                    $cartHash->save();
                } catch (\Exception $e) {
                    $this->addLog("Could not save flashy cart hash key=" . $cartHash->getKey() . " cart=" . $cartHash->getCart());
                }
            }
        }
    }

    /**
     * Restore Flashy Cart Hash
     *
     * @param $id
     * @return array
     */
    public function restoreFlashyCartHash($id)
    {
        //get flashy cart hash
        $cartHash = $this->_carthashFactory->create()->load($id,'key');

        $messages = array();
        if($cartHash) {
            try {
                //get cart data from hash
                $cart = json_decode($cartHash->getCart(), true);

                //empty the cart
                $this->_cartModel->truncate();

                //loop through cart items from hash
                foreach ($cart as $cart_item) {
                    //load product
                    $product = $this->_productFactory->create()->load($cart_item['product']);
                    try {
                        //add form key to cart item data
                        $cart_item['form_key'] = $this->_formKey->getFormKey();

                        //add product to cart
                        $this->_cartModel->addProduct($product, $cart_item);
                        $messages[] = array(
                            'message' => __('Success! %1 is restored successfully.', $product->getName()),
                            'success' => true
                        );

                    } catch (\Exception $e){
                        $messages[] = array(
                            'message' => __('Error! %1 is not restored. %2', $product->getName(), $e->getMessage()),
                            'success' => false
                        );
                        $this->addLog($e->getMessage());
                    }
                }

                //save the cart
                $this->_cartModel->save();
            } catch (\Exception $e) {
                $messages[] = array(
                    'message' => __('Error! Cart is not restored.'),
                    'success' => false
                );
                $this->addLog("Could not restore flashy cart hash for id=$id cart=" . $cartHash->getCart());
            }
        }
        return $messages;
    }

    /**
     * Create new coupon
     *
     * @param $args
     * @return array
     */
    public function createCoupon( $args=array() ) {

        $this->addLog("Creating new coupon.");

        $ruleId = null;
        $couponCode = $this->generateCouponCode( 8 );

        $default = array(
            'name' => 'default',    //String
            'desc' => 'default desc',   //String
            'start' => date('Y-m-d'),   //Date
            'end' => date('Y-m-d', strtotime('+371 days')),    //Date
            'usesPerCustomer' => 1,     //Int
            'usesPerCoupon' => 1,   //Int
            'isActive' => 1,    //1\0
            'QTY' => 1,    //Int
            'websiteId' => array(1),    //Array
            'customersGroupId' => array('0','1','2','3',),  //Array
            'productsId' => null,   //Array
            'type' =>  'by_percent',    //String options - 'to_percent' 'by_percent' 'to_fixed' 'by_fixed' 'cart_fixed' 'buy_x_get_y'
            'amount' => 20,     //Float
            'includeShipping' => true,     //Bool
            'freeShipping' => 'yes',    //String
            'code' => $couponCode,
        );

        $merged = array_merge( $default, $args );

        if( isset($args['code']) ) {
            $ruleId = $this->_coupon->loadByCode($merged['code'])->getRuleId();
        }

        if ( $ruleId != null ) {
            $this->addLog("Coupon code already exists.");
            return array(
                "data" => 'Unable to create coupon, check args.',
                "success" => false
            );

        } else {
            $shoppingCartPriceRule = $this->_objectManager->create('Magento\SalesRule\Model\Rule');
            $shoppingCartPriceRule->setName($merged['name'])
                ->setDescription($merged['desc'])
                ->setFromDate($merged['start'])
                ->setToDate($merged['end'])
                ->setUsesPerCustomer($merged['usesPerCustomer'])
                ->setCustomerGroupIds($merged['customersGroupId'])
                ->setIsActive($merged['isActive'])
                ->setSimpleAction($merged['type'])
                ->setDiscountAmount($merged['amount'])
                ->setDiscountQty($merged['QTY'])
                ->setApplyToShipping($merged['includeShipping'])
                ->setWebsiteIds($merged['websiteId'])
                ->setUsesPerCoupon($merged['usesPerCoupon'])
                ->setCouponType(2)
                ->setCouponCode($merged['code']);
            $shoppingCartPriceRule->save();

            $this->addLog("Coupon created successfully. " . $merged['code']);

            return array(
                "data" => $merged['code'],
                "success" => true
            );
        }
    }

    /**
     * Generate coupon code
     *
     * @param $lenght
     * @return string
     */
    public function generateCouponCode( $length  ) {
        $couponGenerator = $this->_objectManager->get('\Magento\SalesRule\Model\Coupon\Codegenerator');

        $couponHelper = $this->_objectManager->get('\Magento\SalesRule\Helper\Coupon');
        $couponGenerator->setFormat($couponHelper::COUPON_FORMAT_ALPHANUMERIC);

        $couponGenerator->setLength( $length ); // length of coupon code upto 32
        return $couponGenerator->generateCode();
    }

    public function createURL() {
        $default = array(
            'name' => 'defaultttt',    //String
            'desc' => 'default desccccc',   //String
            'start' => date('Y-m-d'),   //Date
            'end' => date('Y-m-d', strtotime('+371 days')),    //Date
            'usesPerCustomer' => 2,     //Int
            'usesPerCoupon' => 2,   //Int
            'isActive' => 1,    //1\0
            'QTY' => 1,    //Int
            'websiteId' => array(1),    //Array
            'customersGroupId' => array('0','1','2','3',),  //Array
            'productsId' => null,   //Array
            'type' =>  'to_percent',    //String options - 'to_percent' 'by_percent' 'to_fixed' 'by_fixed' 'cart_fixed' 'buy_x_get_y'
            'amount' => 20,     //Float
            'includeShipping' => false,     //Bool
            'freeShipping' => 'no',    //String
            'code' => '1225-58955',
        );

        var_dump( http_build_query(array('args' => $default)) );
    }

    /**
     * Get exported log file
     *
     * @param $store_id
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function exportLogFile($store_id)
    {
        $fileContent = array();
        $this->addLog("Log exported.");

        if ($this->getFlashyLog()) {
            $fileContent = explode("\n", file_get_contents($this->_directorylist->getPath('var') . '\log\flashy.log'));
        }

        return array(
            "data" => $fileContent,
            "store_id" => $store_id,
            "success" => true
        );
    }

    public function addLog($m, $l=200)
    {
        if ($this->getFlashyLog()) {
            $this->_flashyLogger->log($l,$m);
        }
    }
}