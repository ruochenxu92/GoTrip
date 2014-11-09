<?php

namespace Product\Db;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Product\Model\ProductList;
use Product\Model\ProductDIYCache;
use Product\Model\Product as ProductModel;
use Product\Model\ProductInfo as ProductInfoModel;
use Cache\Db\CacheService;
use Product\Util\ErrorType;
use Product\Util\ServiceUtil;

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

    public function query_by_package($package_str){
        $product_result = $this->product_model_slave->select(array('package_of_product' => $package_str));
        $product = $product_result->current();
        if ($product === false)
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
        return array('result' => true, 'content' => $product);
    }
    
    /*
     * Generate caches for product list/search/diy page
     */
    public function generate_list(ProductList $product_list, CacheService $cache) {
        $diy_combo = $this->generate_diy_combo_option($product_list, $cache);
//        var_dump($diy_combo); //debug output

        $sql = new Sql($this->db_slave_adapter);
        $select = $sql->select();
        $select->from('product');
        $filter_config = $product_list->getFilterConfig();
        $filter_single_key_list = $filter_config['singleselect']; //single select array

        foreach ($filter_single_key_list as $key) {
            $filter = $product_list->getParameter($key);
            if ($filter >= 1) { //0: all, have no effect
                $select->where(array($key => $filter));
            }
        }
        $select->order('popularity');
        $statement = $sql->prepareStatementForSqlObject($select);
        $product_result = $statement->execute();

        if ($product_result == false || count($product_result) == 0) {
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
        }

        //process the multi-select
        $filter_multi_key_list = $filter_config['multiselect']; //multi select array
        $product_list_arr = array();
        foreach ($product_result as $product) {//loop through each products after single select filters
            $qualified_result = true; //flag
            foreach ($filter_multi_key_list as $key) {
                //check each multi-select filter
                $filter_opts = $product_list->getParameter($key); //get the filter options selected
                $product[$key] = explode(';', $product[$key]); //split the result string by ';'
                if ($filter_opts[0] >= 1) { //0: all, 1: more. they have no effect
                    foreach ($filter_opts as $one_filter_opt) {
                        if (!in_array($one_filter_opt, $product[$key])) {
                            //this option is not contained in this result
                            $qualified_result = false;
                            break;
                        }
                    }
                    if (!$qualified_result) {
                        break;
                    }
                }
            }
            if ($qualified_result) {
                $product_list_arr[] = $product;
            }
        }

        if (count($product_list_arr) == 0) {
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
        }

        /* Calculate the remaining number of other options */
        $remaining_opts = array();
        foreach (array_merge($filter_single_key_list, $filter_multi_key_list) as $key) {   //loop through single-select and multi-select key
            //walk through the product_list to calculate the num of products in one option.
            $remaining_opts[$key] = array();
            $remaining_opts[$key][] = 0; //0: all, is always an option
            $user_select = $product_list->getParameter($key);

            //for single select key we only check the product if selection is 0:all
            if (in_array($key, $filter_multi_key_list) || $user_select[0] == 0) {
                foreach ($product_list_arr as $product) {
                    $available_filter = $product[$key];
                    if (is_array($available_filter)) {
                        foreach ($available_filter as $one_filter_opt) {
                            if (!in_array($one_filter_opt, $remaining_opts[$key])) {
                                array_push($remaining_opts[$key], $one_filter_opt);
                            }
                        }
                    } else {
                        if (!in_array($available_filter, $remaining_opts[$key])) {
                            array_push($remaining_opts[$key], $available_filter);
                        }
                    }
                }
            } elseif (in_array($key, $filter_single_key_list)) {
                //the selected option should remain in the filter
                array_push($remaining_opts[$key], $user_select);
            }
        }

        /* Sorting */
        // generate all sorting orientations for all pages
        $sort_map = $filter_config['sort_map'];
        $cacheRawBaseName = $product_list->getCacheRawInfoName();
        //generate contents for all pages
        $num_product = count($product_list_arr);
        $num_page = ceil(((float) $num_product) / ProductService::NUM_PROUCT_PER_PAGE);

        for ($sort_map_key = 0; $sort_map_key < count($sort_map); $sort_map_key++) {
            for ($sort_order = 0; $sort_order <= 1; $sort_order++) {
                $cacheName = "s" . $sort_map_key . "-" . $sort_order;
                usort($product_list_arr, $this->getSortFunction($sort_map[$sort_map_key], $sort_order));

                $cacheName .= "_p";
                $product_cnt = 0;
                for ($p = 1; $p <= $num_page; $p++) {
                    $page_product_content = array();
                    for ($i = 0; $i < ProductService::NUM_PROUCT_PER_PAGE; $i++) {
                        if ($product_cnt >= $num_product) {
                            break; //we have looped through all product on the last page
                        }
                        array_push($page_product_content, $product_list_arr[$product_cnt]);
                        $product_cnt++;
                    }

                    //stores raw data in array format including
                    //  $remaining_opts
                    //  $page_product_content
                    //  $p
                    //  $num_page
                    //($filter_config) is also needed but it is not stored in the cache
                    // using JSON function to make sure chinese characters are displayed correctly
//                    $page_cache_raw = ServiceUtil::JSON(array('remaining_opts' => $remaining_opts,
//                                'page_product_content' => $page_product_content,
//                                'p' => $p,
//                                'num_page' => $num_page));
//
//                    $cache->save_cache($cacheRawBaseName . $cacheName . $p, $page_cache_raw);
//
                    //create rendered page cache
//                    ob_start();
//                    require __DIR__ . '/../../../view/product/query/' . ProductService::LIST_TEMPLATE;
//                    $string = ob_get_contents();
//                    ob_end_clean();
//                    $cache->save_cache(ProductList::LIST_CACHE_NAME . $cacheRawBaseName . $cacheName . $p, $string);

                    ob_start();
                    require __DIR__ . '/../../../view/product/query/' . ProductService::DIY_TEMLATE;
                    $string = ob_get_contents();
                    ob_end_clean();
                    $cache->save_cache(ProductList::DIY_CACHE_NAME . $cacheRawBaseName . $cacheName . $p, $string);
                }
            }
        }
        return array('result' => true);
    }

    private function generate_diy_combo_option(ProductList $product_list, CacheService $cache) {
        if (!$product_list->getIsDiy())
            return array('result' => false, 'reason' => ErrorType::REQUEST_IS_NOT_DIY);

        //==========
        /*
         * get already selected products (must be from the first to the last)
         * in order
         */
        $product_selected = $product_list->getParameter('product_selected');
        if (is_array($product_list->getParameter('duration_range')))
            $duration_total = max($product_list->getParameter('duration_range'));
        else
            $duration_total = $product_list->getParameter('duration_range');

        if ($duration_total == 0) {
            $duration_total = 100;
        }

        $endCity = $product_list->getParameter('endCity');
        $startCity = $product_list->getParameter('startCity');

        $product_selected_arr = array();
        $duration_selected = array();
        $startCity_selected = array();
        $endCity_selected = array();
        $duration_remain = array();
        $total_duration = 0;
        $total_low_price = 0;
        if (count($product_selected) > 0 && $product_selected[0] != 0) {
            $num_product = count($product_selected); //number of product selected
            foreach ($product_selected as $product_selected_id) {
                $res = $this->query_product_by_id($product_selected_id);
                if (!$res['result']) {
                    return $res;
                }
                $product_selected_arr[] = $res['content'];
                $duration_selected[] = $res['content']['duration'];
                $startCity_selected[] = $res['content']['startCity'];
                $endCity_selected[] = $res['content']['endCity'];
                $total_duration += $res['content']['duration'];
                $total_low_price += $res['content']['low_price'];
            }

            $duration_remain_total = $duration_total;
            for ($i = 0; $i < $num_product; $i++) {
                if ($duration_total == 0 || $duration_total == 100) {
                    //不限 or 10+ 天
                    $duration_remain[$i] = 100;
                } else {
                    $duration_remain[$i] = $duration_remain_total - $duration_selected[$i];
                    $duration_remain_total -= $duration_selected[$i];
                }
            }
            if ($duration_remain[$num_product - 1] <= 0) {
                return array('result' => false, 'reason' => ErrorType::PARAMETER_OUT_OF_RANGE);
            }
        } else {
            $num_product = 0;
            $duration_remain[] = $duration_total;
            $startCity_selected[] = $product_list->getParameter('startCity');
        }

        //==========
        /*
         * 1. Query for available tours for each theme
         * 2. Query for remaining available themes
         */
        $theme_selected = $product_list->getParameter('theme');
        $numTheme = count($theme_selected);
        if ($numTheme > $num_product + 1 || $numTheme < $num_product) {
            return array('result' => false, 'reason' => ErrorType::PARAMETER_NOT_VALID);
        }
        
        $product_diy_model = new \Product\Model\ProductDiyCache($cache, $this->db_slave_adapter, $product_list,$config = $this->serviceLocator->get('config'));
        $combo_array = array();    
        $diy_available_theme = array();
        $isFirstRound = ($theme_selected[0] == 0);
//        for ($i = 0; $i < count($theme_selected); $i++) {
//            if ($i > 0 && isset($endCity_selected[$i])) {
//                $startCity = $endCity_selected[$i];
//            }
//            $res = $product_diy_model->getThemeAvailableOption($theme_selected[$i], $duration_remain[$i], $startCity, $endCity);
//            if ($res['result']) {
//                $combo_array[] = $res['content'];
//            }
//        }
        
        //only query the not yet selected
        for ($i = $num_product; $i < count($theme_selected); $i++) {
            if ($i > 0 && isset($endCity_selected[$i])) {
                $startCity = $endCity_selected[$i];
            }
            $res = $product_diy_model->getThemeAvailableOption($theme_selected[$i], $duration_remain[$i], $startCity, $endCity);
            if ($res['result']) {
                $product_themes = $res['content']['product_theme'];
                $diy_available_theme = array_merge($diy_available_theme, $product_themes);
                $diy_available_theme = array_unique($diy_available_theme, SORT_REGULAR);
                $combo_array[] = $res['content'];
            }
        }
        return array('result' => !$isFirstRound, 'content' => array('selected_theme'=>$theme_selected, 
                        'is_first_round' => $isFirstRound,
                        'theme_available' =>$diy_available_theme,
                        'selected_product' => $product_selected_arr, 
                        'diy_combo' => $combo_array, 
                        'total_duration'=>$total_duration,
                        'total_price'=>$total_low_price));
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

    /**
     * 
     * @param type $num_to_gen: number of page to generate
     *  -1 means all
     * TODO generate corresponding schedule cache
     */
    public function generate_multiple_datail_page($num_to_gen, CacheService $cache, $only_not_cached = true) {
        if ($only_not_cached)
            $product_result = $this->product_model_slave->select(array('is_cached' => 0));
        else
            $product_result = $this->product_model_slave->select();

        //it is possible that no page is not cached, therefore, return true
        if ($product_result === false)
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));

        if (count($product_result) == 0)
            return array('result' => true, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));

        //result contains more than 0 entry
        $id_to_cache_arr = array();
        $product_to_cache_arr = array();
        $cnt = 0;
        foreach ($product_result as $product) {
            $id = $product['product_id'];
            $product_to_cache_arr[$id] = $product;
            array_push($id_to_cache_arr, $id);
            if ($num_to_gen > 0 && $cnt++ < $num_to_gen)
                break;
        }

        $product_info_result = $this->productInfo_model_slave->select(array('product_id' => $id_to_cache_arr));
        foreach ($product_info_result as $product_info) {
            $id = $product_info['product_id'];
            $product = $product_to_cache_arr[$id];

            ob_start();
            require __DIR__ . '/../../../view/product/query/detail_template.phtml';
            $string = ob_get_contents();
            ob_end_clean();
            $cache->save_cache('product_detail_' . $id, $string);

            $update_result = $this->product_model_master->update(array('is_cached' => 1), array('product_id' => $id));
            if ($update_result === false)
                return array('result' => false, 'reason' => array(ErrorType::NOT_ENOUGH_RIGHTS));
        }

        return array('result' => true);
    }

    private function getSortFunction($key, $sort_order) {
        switch ($sort_order) {
            case 1: //ascending
                return function ($a, $b) use ($key) {
                    return intval($a[$key]) - intval($b[$key]);
                };
            case 0: //descending
                return function ($a, $b) use ($key) {
                    return intval($b[$key]) - intval($a[$key]);
                };
        }
    }

}
