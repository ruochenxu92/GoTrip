<?php

namespace Product\Db;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Product\Model\Product as ProductModel;
use Product\Model\ProductInfo as ProductInfoModel;
use Cache\Db\CacheService;
use Product\Util\ErrorType;

class ProductService {

    const LIST_TEMPLATE = 'list_template.phtml';
    const DIY_TEMLATE = 'diy_template.phtml';
    const NUM_PROUCT_PER_PAGE = 10;

    private $db_master_adapter;
    private $db_slave_adapter;
    private $product_model_master;
    private $product_model_slave;
    private $productInfo_model_master;
    private $productInfo_model_slave;
    
    private $serviceLocator;

    public function __construct(DbAdapter $master_adapter, DbAdapter $slave_adapter, $serviceLocator) {
        $this->db_master_adapter = $master_adapter;
        $this->db_slave_adapter = $slave_adapter;
        $this->product_model_master = new ProductModel($this->db_master_adapter);
        $this->product_model_slave = new ProductModel($this->db_slave_adapter);
        $this->productInfo_model_master = new ProductInfoModel($this->db_master_adapter);
        $this->productInfo_model_slave = new ProductInfoModel($this->db_slave_adapter);
        
        $this->serviceLocator = $serviceLocator;
    }

    public function query_product_by_id($id) {
        //TODO consider using cache here?
        $product_result = $this->product_model_slave->select(array('product_id' => $id));
        if ($product_result === false)
                return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
       
        if(!is_array($id)){
            $product = $product_result->current();
        }else{
            $product = array();
            foreach($product_result as $aproduct){
            $product[$aproduct['product_id']] = $aproduct;
            }
        }
        return array('result' => true, 'content' => $product);
    }

    
    public function generate_detail_page($id, CacheService $cache) {
        $product = $this->query_product_by_id($id);
        if (!$product['result'])
            return array('result' => false, 'reason' => $product['reason']);
        $product = $product['content'];
        $product_info_result = $this->productInfo_model_slave->select(array('product_id' => $id));
        $product_info = $product_info_result->current();
        if ($product_info === false)
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_INFO_NOT_EXIST));
        
        //get recommondation products
        $recom_product_id = explode(';',$product_info['recom_product']);
        $recom_product_arr = array();
        foreach($recom_product_id as $aRecomProductId){
            $rp = $this->query_product_by_id($aRecomProductId);
            if ($rp['result']){
                $recom_product_arr[] = $rp['content'];
            }
        }
        //needs to prepare translation service
        $config = $this->serviceLocator->get('config');
        $filter_config = $config['filter'];

        ob_start();
        require __DIR__ . '/../../../view/product/query/detail_template.phtml';
        $string = ob_get_contents();
        ob_end_clean();
        $cache->save_cache('product_detail_' . $id, $string);
//        echo $string; //for debug
        $update_result = $this->product_model_master->update(array('is_cached' => 1), array('product_id' => $id));
        if ($update_result === false)
            return array('result' => false, 'reason' => array(ErrorType::NOT_ENOUGH_RIGHTS));
        return array('result' => true);
    }

}
