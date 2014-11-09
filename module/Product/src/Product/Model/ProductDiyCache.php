<?php

namespace Product\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Cache\Db\CacheService;
use Product\Util\ErrorType;
use Product\Model\ProductList;

/**
 * Description of ProductDiyCache
 *
 * @author Grit
 */
class ProductDiyCache {

    const MAX_RECUR_STEP = 3;

    private $cache;
    private $db_slave_adapter;
    private $product_list_model;
    private $config;

    public function __construct(CacheService $cache, DbAdapter $slave_adapter, ProductList $product_list, $config) {
        $this->cache = $cache;
        $this->db_slave_adapter = $slave_adapter;
        $this->product_list_model = $product_list;
        $this->config = $config;
    }

    /*
     * $theme: value of the theme
     * $remaining_duration: max duration for this stop
     * $startCity: start city of this stop
     * $endCity: end city of the entire DIY, NOT necessarily the endCity of this stop
     * 
     * return
     *      array('result'=> true, 'content'=> array('product'=>array(products rows), 
     *                          'theme_next_available'=>array(themes), //available themes after this stop
     *              ))
     */

    public function getThemeAvailableOption($theme, $remaining_duration, $startCity, $endCity, $recursion_step = 0) {
        if ($remaining_duration <= 0 || $recursion_step > $this::MAX_RECUR_STEP) {
            //recursion stops
            return array('result' => false, 'reason' => ErrorType::PARAMETER_NOT_VALID);
        }

        $product_search_result = array();
        $product_theme_search_result = array();
        $available_theme_next_stage = array();
        $region = $this->product_list_model->getParameter('region');
        $month = $this->product_list_model->getParameter('month');

        //first check cache
        $cache_name = "diycombo_" . $theme . '_'
                . $remaining_duration . '_'
                . $startCity . '_'
                . $endCity . '_'
                . $region . '-';

        foreach ($month as $aMonth) {
            $cache_name .= $aMonth . '-';
        }

        $app_version = $this->config['app_version'];
        //for debug only!!!!!!!
        if (!$app_version['DEBUG']) {
            $res = $this->cache->read_cache($cache_name);
            if ($res['result']) {
                return $res;
            }
        }

        //otherwise generate cache
        $sql = new Sql($this->db_slave_adapter);
        $select = $sql->select();
        $select->from('product');

        if ($region > 0) {
            $select->where(array('region' => $region));
        }
        $select->where(array('depart_weekday' => '0'));
        $select->where(array('package_of_product' => '0'));

        //theme is now a multiselect as well (use fine filter)
//        if ($theme > 0) {
//            $select->where(array('theme' => $theme));
//        }

        $select->where('duration <= ' . $remaining_duration);

        //TODO implement OR logic for array formed startCity
        if ($startCity != 0) {
            $select->where(array('startCity' => $startCity));
        }

        //execute to get rough filtered data
        $select->order(array('duration ASC'));
        $statement = $sql->prepareStatementForSqlObject($select);
        $product_result = $statement->execute();
        if ($product_result == false || count($product_result) == 0) {
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
        }

        //TODO inplement config based filtering
        //filtering according to the month
        $product_result_fine = array();
        if ($month[0] > 0) {
            foreach ($product_result as $product) {
                $pass_check = true;
                $product_theme = $product['theme'];
                $theme_array = explode(';', $product_theme);
                if ($theme > 0 && !in_array($theme, $theme_array)) {
                    $pass_check = false;
                }

                $product_endCity = $product['endCity'];
                $endCity_array = explode(';', $product_endCity);
                if ($remaining_duration <= 2 && $endCity != 0 && !in_array($endCity, $endCity_array)) {
                    $pass_check = false;
                };
                $product_month = $product['month'];
                $month_array = explode(';', $product_month);
                foreach ($month as $aMonth) {
                    if (!in_array($aMonth, $month_array)) {
                        $pass_check = false;
                        break;
                    }
                }
                if ($pass_check) {
                    
                    $product_result_fine[] = $product;
                }
            }
        } else {
            $product_result_fine = $product_result;
        }

        $product_result = $product_result_fine;
        if ($product_result == false || count($product_result) == 0) {
            return array('result' => false, 'reason' => array(ErrorType::PRODUCT_NOT_EXIST));
        }
        foreach ($product_result as $product) {
            $product_already_added = false;
            $remaining_duration_nextLevel = $this->subtractRemainingDuration($remaining_duration, $product['duration']);
            // implement endCity to support array
            $product_endCity_array = explode(";", $product['endCity']);
            $startCity_nextLevel_array = $product_endCity_array;
            $product_id = $product['product_id'];
            $available_theme_next_stage[$product_id] = array();

            //adds to result if endCity matches final destination
            if ($endCity == 0 || in_array($endCity, $product_endCity_array)) {
                $product_search_result[] = $product;
                $product_already_added = true;
                //merge the themes
                $product_theme_search_result = array_merge($product_theme_search_result, explode(";", $product['theme']));
            }

            //for product that has endCity other than final destination
            //recursion
            //theme is set as 不限
            if ($remaining_duration_nextLevel > 0) {
                foreach ($startCity_nextLevel_array as $startCity_nextLevel) {
                    $recur_result = $this->getThemeAvailableOption(0, $remaining_duration_nextLevel, $startCity_nextLevel, $endCity, ++$recursion_step);
                    if ($recur_result['result']) {
                        if (!$product_already_added) {
                            //newly available product
                            $product_search_result[] = $product;
                            $product_theme_search_result = array_merge($product_theme_search_result, explode(";", $product['theme']));
                        }
                        $recur_content = $recur_result['content'];
                        //process next stage available product theme
                        if (isset($recur_content['product_theme'])) {
                            $available_theme_next_stage[$product_id] = array_merge($available_theme_next_stage[$product_id], $recur_content['product_theme']);
                        }
                        if (isset($recur_content['theme_next_available'])) {
                            $available_theme_next_stage[$product_id] = array_merge($available_theme_next_stage[$product_id], $recur_content['theme_next_available']);
                        }
                    }
                }
            }
        }

        //make sure all the arrays are unique (this is just a safty measure for some arrays)
        $product_search_result = array_unique($product_search_result, SORT_REGULAR);
        $available_theme_next_stage = array_unique($available_theme_next_stage, SORT_REGULAR);
        $product_theme_search_result = array_unique($product_theme_search_result, SORT_REGULAR);

        $result = array('result' => true, 'content' => array('product' => $product_search_result, 'theme_next_available' => $available_theme_next_stage, 'product_theme' => $product_theme_search_result));
        $this->cache->save_cache($cache_name, $result);

        return $result;
    }

    private function subtractRemainingDuration($to_subtract_from, $to_subtract) {
        if ($to_subtract_from >= 100) {
            return $to_subtract_from;
        }
        return $to_subtract_from - $to_subtract;
    }

}
