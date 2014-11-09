<?php

namespace Product\Model;
use Product\Util\ServiceUtil;

/**
 * Description of ProductSearch
 * It encapsulate keyword seach functions and returns all matched product entries a
 *
 * @author Grit
 */
class ProductSearch {
    private $result = array();
    //put your code here
    public function __construct($serviceLocator, $searchStr, $is_post = false, $language_code = 'zh') {
        $this->result['result'] = false;
        $this->result['reason'] = '';

        //Convert the searchStr into index keys
        $filter_config = $serviceLocator->get('config')['filter'];
        $cache = $serviceLocator->get('cache');
        $keywardMap = $filter_config[$language_code];
//        $s = "/\/|, | & | and |/";
//        $searchArray = preg_split($s, httpspecialchar($searchStr));
        $delimiters = array(',', '，', ' ', '和', '与', '.', '。', '不限',"'");
        $searchArray = array_unique(ServiceUtil::multipleExplode($delimiters, $searchStr));
        
        //search enabled array
        $enabled_search_index_array = array('region', 'place');
        $indexArr = array();
        foreach ($searchArray as $keyword) {
            $keyword = trim($keyword);
            if (strlen($keyword) > 0) {
                foreach ($enabled_search_index_array as $enabled_arr_name) {
                    $mapArr = $keywardMap[$enabled_arr_name];
                    $key = array_search($keyword, $mapArr);
                    if (!isset($indexArr[$enabled_arr_name])) {
                        $indexArr[$enabled_arr_name] = array();
                    }
                    if ($key > 0) {
                        array_push($indexArr[$enabled_arr_name], $key);
                    }
                }
            }
        }
        //translation in order to use the ProductList model
        $indexArr['includedPlace'] = $indexArr['place'];
                
        //map the index array to a cache name
        $product_list = new ProductList(1, $indexArr,$filter_config, $cache_name_header = ProductList::LIST_CACHE_NAME);
        if($is_post){
            //this is ajax post
            $cacheName = $product_list->getCacheRawName();
        }else{
            //this is get
            $cacheName = $product_list->getCacheName();
        }
        
        $res = $cache->read_cache($cacheName);
        if ($res['result'] !== true)
        {
            $product_service = $serviceLocator->get('product_service');
            $res = $product_service->generate_list($product_list,$cache);
            $res = $cache->read_cache($cacheName);
            if ($res['result'] !== true)
            {
                    //TODO return /error & recommend page/ we say sorry there are some problems with this, would you like to see some others.
            }
        }
        $this->result = $res;
    }

    public function getResult() {
        return $this->result;
    }

}
