<?php

namespace Application\Db;

use Cache\Db\CacheService;

class IndexService {

    private $serviceLocator;

    public function __construct($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function index_cache_generate($config,$cache) {
        $product_service = $this->serviceLocator->get('product_service');
        $index_config = $config['index_page'];
        $product = $product_service->query_product_by_id($index_config['related_product']);
        if(!$product['result']){
            return $product;
        }
        $product = $product['content'];
        ob_start();
        require __DIR__ . '/../../../view/application/index/index_template.phtml';
        $string = ob_get_contents();
        ob_end_clean();
        $cache->save_cache('index', $string);
        return array('result' => true);
    }

}
