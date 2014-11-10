<?php

namespace Application\Db;

use Cache\Db\CacheService;

class IndexService {

    private $serviceLocator;

    public function __construct($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function read() {
        ob_start();
        require __DIR__ . '/../../../view/application/index/index_template.phtml';
        $string = ob_get_contents();
        ob_end_clean();
        return array('result' => true, 'content' => $string);
    }

}
