<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link	  http://github.com/zendframework/Product for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Product\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Product\Util\ControllerUtil;
use Product\Util\ErrorType;
use Product\Model\ProductList;
use Product\Model\ProductSearch;

class QueryController extends AbstractActionController {
//    public function indexAction() {
//        $this->layout()->setVariable('service_locator', $this->getServiceLocator());
//        return array();
//    }

    /**
     * taking AJAX JSON string input and return search result in JSON
     */
    public function searchAction() {
        $is_post = false;
        if ($this->getRequest()->isPost()) {
            $is_post = true;
            $data = $_POST;
        } else {
            $data = $_REQUEST;
        }
        if (!isset($data['search']))
            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_GIVEN)));

        $searchStr = $data['search'];
        $search = new ProductSearch($this->getServiceLocator(), $searchStr, $is_post);
        $result = $search->getResult();

        if (!$result['result'])
            return ControllerUtil::generateErrorViewModel($this->getResponse(), $result);

        if ($this->getRequest()->isPost()) {
            return ControllerUtil::generateAjaxViewModel($this->getResponse(), $result);
        } else {
            $view = new ViewModel();
            $view->setTerminal(true);
            $view->setTemplate('product/query/list');
            $view->setVariables(array('content' => $result['content']));
            return $view;
        }
    }

    public function getDiyResultAction() {
        if (isset($_REQUEST['product_selected']) && $_REQUEST['product_selected'] != '0') {
            //TODO: use [] instead of '-'
            $product_selected = $_REQUEST['product_selected'];
        } else {
            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_GIVEN)));
        }
        //find the matching product
        $product_service = $this->getServiceLocator()->get('product_service');
        $res = $product_service->query_by_package($product_selected);
        if ($res['result'] !== true) {
            return ControllerUtil::generateErrorViewModel($this->getResponse(), $res);
        }
        $id = $res['content']['product_id'];
        return $this->detailAction($id);
    }

    /*
     * Get the cached html of a product diy
     */

    public function diyAction() {
        $config = $this->getServiceLocator()->get('config');
        $filter_config = $config['filter'];

        $product_list = new ProductList($this->params('id'), $_REQUEST, $filter_config, ProductList::DIY_CACHE_NAME);

        $cache = $this->getServiceLocator()->get('cache');
        $app_version = $config['app_version'];
        //for debug only!!!!!!!
        if ($app_version['DEBUG']) {
            $product_service = $this->getServiceLocator()->get('product_service');
            $res = $product_service->generate_list($product_list, $cache);
        }

        $res = $cache->read_cache($product_list->getCacheName());

        if ($res['result'] !== true) {
            $product_service = $this->getServiceLocator()->get('product_service');
            $res = $product_service->generate_list($product_list, $cache);
            $res = $cache->read_cache($product_list->getCacheName());
            if ($res['result'] !== true) {
                //TODO return /error & recommend page/ we say sorry there are some problems with this, would you like to see some others.
            }
        }
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setTemplate('product/query/diy');
        $view->setVariables(array('content' => $res['content']));
        $view->setVariable('service_locator', $this->getServiceLocator());
        return $view;
    }

    /*
     * Get the cached html of a product list
     * deprecated
     */
//    public function listAction() {
//        $this->layout()->setVariable('service_locator', $this->getServiceLocator());
//        $config = $this->getServiceLocator()->get('config');
//        $filter_config = $config['filter'];
//
//        $product_list = new ProductList($this->params('id'), $_REQUEST, $filter_config);
//
//        $cache = $this->getServiceLocator()->get('cache');
//        $res = $cache->read_cache($product_list->getCacheName());
//
//        if ($res['result'] !== true) {
//            $product_service = $this->getServiceLocator()->get('product_service');
//            $res = $product_service->generate_list($product_list, $cache);
//            $res = $cache->read_cache($product_list->getCacheName());
//            if ($res['result'] !== true) {
//                //TODO return /error & recommend page/ we say sorry there are some problems with this, would you like to see some others.
//            }
//        }
//        $view = new ViewModel();
//        $view->setTerminal(true);
//        $view->setTemplate('product/query/list');
//        $view->setVariables(array('content' => $res['content']));
//        $view->setVariable('service_locator', $this->getServiceLocator());
//        return $view;
//    }

    public function detailAction($id = null) {
        if ($id == null) {
            $id = $this->params('id');
            if (empty($id))
                return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_GIVEN)));
        }
        $config = $this->getServiceLocator()->get('config');
        $app_version = $config['app_version'];
        //TODO: for debug only!!!!!!!
        if ($app_version['DEBUG']) {
            $this->regenerateAction(); //auto refresh cache
        }

        $cache = $this->getServiceLocator()->get('cache');
        $res = $cache->read_cache('product_detail_' . $id);

        if ($res['result'] !== true) {
            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PRODUCT_NOT_EXIST)));
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setTemplate('layout/layout');
        $view->setVariables(array('content' => $res['content'], 'service_locator' => $this->getServiceLocator()));
        return $view;
    }

    public function regenerateAction($id = null) {
        if ($id == null) {
            $id = $this->params('id');
            if (empty($id))
                return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_GIVEN)));
        }
        $cache = $this->getServiceLocator()->get('cache');
//        $res = $cache->read_cache('product_detail_' . $id);

        $product_service = $this->getServiceLocator()->get('product_service');
        $res = $product_service->generate_detail_page($id, $cache);

        return ControllerUtil::generateAjaxViewModel($this->getResponse(), array('result' => $res['result']));
    }

    public function scheduleAction() {
        $id = $this->params('id');
        if (empty($id))
            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_GIVEN)));

        if (isset($_REQUEST['date'])) {
            $date = urldecode($_REQUEST['date']);
            $year = intval(substr($date, 0, 4));
            $month = intval(substr($date, 4, 2));
        } else {
            if (isset($_REQUEST['year']) && isset($_REQUEST['month'])) {
                $year = intval(urldecode($_REQUEST['year']));
                $month = intval(urldecode($_REQUEST['month']));
            } else {
                return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_GIVEN)));
            }
        }
        //for debug only!!!!!!!
        //! NOT NEEDED in this action
//        $config = $this->getServiceLocator()->get('config');
//        $app_version = $config['app_version'];
//        if ($app_version['DEBUG']) {
//            $this->regenerateScheduleAction();
//        }
        //divide date to year & month, and check validaty of year and month
        //get the system current month
        $cur_year = date("Y");
        $cur_month = date("n");

        //TODO handling different time zones?
        //Do not forget to check whether it is more than 6 month later, you may add more errors to ErrorType
//        if ($month > 11 || $month < 0 || ($year - $cur_year) * 12 + $month - $cur_month < 0      //checking past price is not allowed
//                || ($year - $cur_year) * 12 + $month - $cur_month > 6)     //checking price in no more than 6 months
//            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_VALID)));

        /* Start Query */
        if ($month < 10) {
            $month = '0' . $month;
        }

        $cache = $this->getServiceLocator()->get('cache');

        $product_schedule_service = $this->getServiceLocator()->get('product_schedule_service');

        $res = $product_schedule_service->query_by_product_id_and_date($id, array('year' => $year, 'month' => $month), $cache);

        return ControllerUtil::generateAjaxViewModel($this->getResponse(), array('result' => true, 'content' => $res['content']));
    }

    public function submitQuestionAction() {
        $mail = $this->getServiceLocator()->get('mail_service');
        $mail_composer = $this->getServiceLocator()->get('mail_composer_service');

        $resp = ControllerUtil::checkCaptcha($_POST['captcha']);
        if (!$resp) {
            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::CAPTCHA_NOT_CORRECT)));
        }
        
        if (!isset($_POST['name'])) {
            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => array(ErrorType::PARAMETER_NOT_GIVEN)));
        }

        $mailmessage = $mail_composer->createMessage(array('type' => $mail_composer::CUSTOMER_PRODUCT_QUERY,
            'content' => $_POST));

        $mail->send($mailmessage);

        return ControllerUtil::generateAjaxViewModel($this->getResponse(), array('result' => true));
    }
    
    /**
     * update the product price based on the order form selection
     * @return json array object
     */
    public function updateDetailAction() {
        $product_schedule_service = $this->getServiceLocator()->get('product_schedule_service');
        $cache = $this->getServiceLocator()->get('cache');
        $order_price = $product_schedule_service->update_price_query($_POST, $cache);

        if (!$order_price['result']) {
            return ControllerUtil::generateErrorViewModel($this->getResponse(), array('reason' => $order_price['reason']));
        }

        return ControllerUtil::generateAjaxViewModel($this->getResponse(), array('result' => true, 'totalPrice' => $order_price['totalPrice'], 'unitPrice' => $order_price['unitPrice'], 'priceTable' => $order_price['priceTable']));
    }

}
