<?php

namespace Product\Db;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Cache\Db\CacheService;
use Product\Model\ProductAvailable as ProductAvailableModel;
use Product\Model\ProductPrice as ProductPriceModel;
use Product\Model\Product as ProductModel;
use Product\Util\ErrorType;
use Product\Util\ServiceUtil;

class ProductScheduleService {

    private $db_master_adapter;
    private $db_slave_adapter;
    private $product_available_model_master;
    private $product_available_model_slave;
    private $product_price_model_master;
    private $product_price_model_slave;
    private $product_model_master;
    private $product_model_slave;
    private $serviceLocator;

    public function __construct(DbAdapter $master_adapter, DbAdapter $slave_adapter, $serviceLocator) {
        $this->db_master_adapter = $master_adapter;
        $this->db_slave_adapter = $slave_adapter;
        $this->product_available_model_master = new ProductAvailableModel($this->db_master_adapter);
        $this->product_available_model_slave = new ProductAvailableModel($this->db_slave_adapter);
        $this->product_price_model_master = new ProductPriceModel($this->db_master_adapter);
        $this->product_price_model_slave = new ProductPriceModel($this->db_slave_adapter);
        $this->product_model_master = new ProductModel($this->db_master_adapter);
        $this->product_model_slave = new ProductModel($this->db_slave_adapter);
        $this->serviceLocator = $serviceLocator;
    }

    /*
     * $form_post = array(
     *  product_id  int
     *  numRoom     int
     *  adult       array([0]=>numAdult,[1]=>numAdult)
     *  child       array([0]=>numChild,[1]=>numChild)
     *  from        string 'year'-'month'-'day' can be null
     * )
     */

    public function update_price_query($form_post, CacheService $cache) {
        $product_id = $form_post['product_id'];
        $numRoom = $form_post['numRoom'];
        $adultArray = $form_post['adult'];
        $childArray = $form_post['child'];

        //TODO refine logics
        $dateStr = '';
        if (strlen($form_post['from']) > 0) {
            $dateArray = explode('-', $form_post['from']);
            if ($dateArray[0] >= date("Y") && intval($dateArray[1]) >= date("n") && intval($dateArray[2]) >= date("j")) {
                $date['year'] = $dateArray[0];
                $date['month'] = $dateArray[1];
                $date['day'] = intval($dateArray[2]);
                $dateStr = $form_post['from'];
            }
        }
        if ($dateStr == '') {
            $date['year'] = date("Y");
            $date['month'] = date("m");
            $date['day'] = date("j");
            $dateStr = $date['year'] . '-' . $date['month'] . '-' . date("d");
        }
        
        $product_price_cache = $this->query_by_product_id_and_date($product_id, $date, $cache);
        if (!$product_price_cache['result']) {
            return array('result' => false, 'reason' => $product_price_cache['reason']);
        }

        //no longer support price_identical
        $price = $product_price_cache['content']['price'][intval($date['day'])-1];

        //cumulate total number of people and price
        $totalPpl = 0;
        $totalPrice = 0;
        $unitPrice = 0;
        if ($price[0]==-1) {
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_AVAILABLE));
        }

        //loop through each room and cumulate the total price and total num of people
        for ($i = 0; $i < $numRoom; $i++) {
            $roomPpl = 0; //total number of ppl in this room
            $adult = $adultArray[$i];
            $child = $childArray[$i];
            $roomPpl = $adult + $child;
            if ($adult > 0) {
                if ($price[4] == 0) {
                    $unitPrice = $price[$roomPpl - 1]; //search the price 
                    //query product price
                    $totalPrice += $unitPrice * $roomPpl;
                } else {
                    $tempTotal = $price[$adult - 1] * $adult + $price[4] * $child;
                    $unitPrice = $tempTotal / $roomPpl;
                    $totalPrice += $tempTotal;
                }
                $totalPpl += $roomPpl;
            }
        }
        $totalPrice = round($totalPrice / 100);
        if ($totalPpl > 0) {
            $unitPrice = round($totalPrice / $totalPpl, 2);
        }
        return array('result' => true, 'totalPrice' => $totalPrice, 'unitPrice' => $unitPrice, 'priceTable' => $price);
    }

    /**
     * @param int $id	product_id
     * @param date array('year' => $year, 'month' => $month) $year:2014, $month:0-11
     * @return array('result' => true, 'content' => array('always_available' => (true, false) , 'vacancy' => .. , 'price' => .. , 'price_identity' => ..))
     * 	'vacancy' => array(1 => 3, 2 => 3, ... , 31 => 5): >0:available, 0:not enough room, -1:other value	//product which is not always available
     *  'price' => array(1 => array(400, 300, 200, 100), 2 => array(500, 400, 300, 200), ... 31 => array()): date => (price_single, price_double, price_triple, price_quadruple)
     *  'vacancy' can me omitted if always_available is true
     *  'price' can be omited, 'identical_price' => array(x,x,x,x) should take the place.
     */
    public function query_by_product_id_and_date($id, $date, CacheService $cache) {
        $config = $this->serviceLocator->get('config');
        $app_version = $config['app_version'];
        //for debug only!!!!!!!
        if ($app_version['DEBUG']) {
            $this->generate_available_date($id, $date, $cache); //This is temporary auto generation to make front end debug easier
        }

        $res = $cache->read_cache('product_available_' . $id . '_' . $date['year'] . '_' . $date['month']);
        if ($res['result'] !== true)
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_AVAILABLE_CACHE_NOT_EXIST));

        return array('result' => true, 'content' => $res['content']);
    }

    /**
     * @param int $id	product_id
     * @param date array('year' => $year, 'month' => $month) $year:2014, $month:0-11
     * save_to_cache array('result' => true, 'content' => array('always_available' => (true, false) , 'vacancy' => .. , 'price' => .. , 'price_identity' => ..))
     * 	'vacancy' => array(1 => 3, 2 => 3, ... , 31 => 5): >0:available, 0:not enough room, -1:other value	//product which is not always available
     *  'duration' => int (number of days)
     *  'depart_weekday' => same as database
     *  'price' => array(1 => array(400, 300, 200, 100, 50), 2 => array(500, 400, 300, 200), ... 31 => array()): date => (price_single, price_double, price_triple, price_quadruple, price_child)
     *  'vacancy' can me omitted if always_available is true
     *  'start_date' copy the start_date information from product table
     *  'price'
     */
    public function generate_available_date($id, $date, CacheService $cache) {
        $product_available = array();

        //TODO write some code here to get price of the date, format is shown above
        //TODO currently, price remains the same within one month, so we just generate a rather simple array

        $year = $date['year'];
        $month = $date['month'];

        $product_result = $this->product_model_slave->select(array('product_id' => $id));
        $product = $product_result->current();
        if ($product === false) {
            //error
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
        }

        if ($product['always_available'] != true) {
            $product_available['always_available'] = false;

            $sql = new Sql($this->db_slave_adapter);
            $select = $sql->select();
            $select->from('product_available')->where(array('product_id' => $id, 'year' => $year, 'month' => intval($month)))->order('date');
            $statement = $sql->prepareStatementForSqlObject($select);
            $product_available_result = $statement->execute();
            $product_available_list = array();
            foreach ($product_available_result as $availability)
                $product_available_list[$availability['date']] = $availability['available'];

            $product_available['vacancy'] = $product_available_list;
        } else {
            $product_available['always_available'] = true;
        }

        $product_available['start_date'] = $product['start_date']; //copy start date periods
        $product_available['duration'] = $product['duration'];
        $product_available['depart_weekday'] = $product['depart_weekday'];

        //first check month 0
        $price_result = $this->product_price_model_slave->select(array('product_id' => $id, 'year' => $year, 'month' => 0));
        if ($price_result === false) {
            $price_result = $this->product_price_model_slave->select(array('product_id' => $id, 'year' => $year, 'month' => intval($month)));
        }
        if ($price_result === false) {
            //log
//                    $stream = @fopen('/path/to/logfile', 'a', false);
//                    if (! $stream) {
//                        throw new Exception('Failed to open stream');
//                    }
//                    $writer = new Zend_Log_Writer_Stream($stream);
//                    $logger = new Zend_Log($writer);
//
//                    $logger->info('Informational message');
            //error
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
        }

        $num_days_in_month = cal_days_in_month(CAL_GREGORIAN, intval($month), $year);

        $price_arr = array();
        $price = array();
        foreach ($price_result as $aPrice) {
            $price_arr[] = $aPrice;
        }

        for ($d = 1; $d <= $num_days_in_month; $d++) {
            $dateStr = $year . '-' . $month . '-' . $d;
            $the_week_index = ServiceUtil::getWeeks($dateStr);
            $the_weekday_index = ServiceUtil::getWeekDay($year, intval($month), $d);
            $effective_price = array();
            foreach ($price_arr as $someWeekPrice) {
                $valid_week = $someWeekPrice['week'];
                $valid_weekday = $someWeekPrice['weekday'];
                if (empty($valid_week) || in_array($the_week_index, explode(';', $valid_week))) {
                    if (empty($valid_weekday) || in_array($the_weekday_index, explode(';', $valid_weekday))) {
                        $effective_price = $someWeekPrice;
                    }
                }
            }
            if(empty($effective_price)){
                $price_each_day = array(-1);
            }else{
                $price_each_day = array($effective_price['price_single'], $effective_price['price_double'], $effective_price['price_triple'], 
                    $effective_price['price_quadruple'], $effective_price['price_child']);
            }
            $price[] = $price_each_day;
        }

        $product_available['price'] = $price;

        $cache->save_cache('product_available_' . $id . '_' . $date['year'] . '_' . $date['month'], $product_available);

        return array('result' => true);
    }

}
