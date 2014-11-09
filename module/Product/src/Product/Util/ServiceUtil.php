<?php

namespace Product\Util;

class ServiceUtil {
    
    //return 1 is monday and 0 is sunday
    public static function getWeekDay($year, $month, $day){
        return date("w", mktime(0, 0, 0, $month, $day, $year)); 
    }
    
    /**
     * Returns the amount of weeks into the month a date is
     * @param $date a YYYY-MM-DD formatted date
     * @param $rollover The day on which the week rolls over
     * echo getWeeks("2011-06-11", "sunday"); //outputs 2, for the second week of the month
     * the first Sunday(if not first day of the month) belong to the second week
     */
    public static function getWeeks($date, $rollover='sunday')
    {
        $cut = substr($date, 0, 8);
        $daylen = 86400;

        $timestamp = strtotime($date);
        $first = strtotime($cut . "00");
        $elapsed = ($timestamp - $first) / $daylen;

        $i = 1;
        $weeks = 1;

        for($i; $i<=$elapsed; $i++)
        {
            $dayfind = $cut . (strlen($i) < 2 ? '0' . $i : $i);
            $daytimestamp = strtotime($dayfind);

            $day = strtolower(date("l", $daytimestamp));

            if($day == strtolower($rollover))  $weeks ++;
        }
        return $weeks;
    }
    
    /*
     * return the Url of the product image
     */
    public static function generateProductImgUrl($product_id, $img_name, $useCdn = true){
        if($useCdn){
            return 'http://cdn.xiyouus.com/img/product/'.$product_id.'/'.$img_name.'.jpg';
        }else{
            return '/img/product/'.$product_id.'/'.$img_name.'.jpg';
        }
    }
    
    /**
     * 
     * @param int $cur_stop
     * @return int next stop
     */
    public static function generateDIYStopSign($cur_stop){
        echo '<div class="diy_stop">';
        echo "第" .$cur_stop. '站:';
        echo '</div>';
        return ++$cur_stop;
    }
    
    /**
     * Called in diy_template to generate html filter section
     * @param type $filter_config
     * @param array $display_filter_order
     * @param type $product_list
     * @param type $remaining_opts
     */
    public static function generateDIYFilter($filter_config, $display_filter_order, $product_list, $remaining_opts, $displayZeroOpt = true,$displaySelected = true, $to_echo_when_empty = '') {
        $filter_tranlation = $filter_config['zh'];
        $filter_label_tranlation = $filter_tranlation['filter_label'];
        $not_empty = false;
        $string_to_echo = '';
        foreach ($display_filter_order as $key) {
            $selectedOption = $product_list->getParameter($key);
            if (!is_array($selectedOption)) {
                $selectedOption = array($selectedOption);
            }

            $string_to_echo .= '<div class="youxi clearfix">';
            $string_to_echo .=  '<div class="xi pull-left">' . htmlspecialchars($filter_label_tranlation[$key]);
            $string_to_echo .=  ' :</div>';
            $string_to_echo .=  '<ul class="shi pull-right">';

            if ($key === 'includedPlace' || $key === 'startCity' || $key === 'endCity') {
                $filter_opt_translation = $filter_tranlation['place'];
            } else {
                $filter_opt_translation = $filter_tranlation[$key];
            }

            $remaining_filter = $remaining_opts[$key];
            if (!is_array($remaining_filter)) {
                $remaining_filter = array($remaining_filter);
            }
            sort($remaining_filter);
            foreach ($remaining_filter as $one_filter_opt) {
                if($displaySelected ||(!$displaySelected && !in_array($one_filter_opt, $selectedOption))){
                    if($displayZeroOpt || (!$displayZeroOpt && $one_filter_opt!=0)){
                        $not_empty = true;
                        $onClickFunction = 'filter_select';
                        $string_to_echo .=  "<li";
                        if (in_array($one_filter_opt, $selectedOption)) {
                            //height light here
                            $string_to_echo .=  ' class="buxian"';
                            $onClickFunction = 'filter_remove';
                        }

                        $string_to_echo .=  '><a class="'.$key.'" onclick="'. $onClickFunction. '(' . "'" . $key . "'" . ',' . "'" . $one_filter_opt . "'" . ')">'
                        . $filter_opt_translation[$one_filter_opt]
                        . "</a></li>\n";
                    }
                }
            }

            $string_to_echo .=  '</ul></div>';
        }
        if($not_empty)
            echo $string_to_echo;
        else
            echo $to_echo_when_empty;
    }

    /*     * ************************************************************
     *
     * 	使用特定function对数组中所有元素做处理
     * 	@param	string	&$array		要处理的字符串
     * 	@param	string	$function	要执行的函数
     * 	@return boolean	$apply_to_keys_also		是否也应用到key上
     * 	@access public
     *
     * *********************************************************** */

    public static function arrayRecursive(&$array, $function, $apply_to_keys_also = false) {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                ServiceUtil::arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }

    /* *************************************************************
     *
     * 	将数组转换为JSON字符串（兼容中文）
     * 	@param	array	$array		要转换的数组
     * 	@return string		转换得到的json字符串
     * 	@access public
     *
     * *********************************************************** */

    public static function JSON($array) {
        ServiceUtil::arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }

    public static function multipleExplode($delimiters = array(), $string = '') {

        $mainDelim = $delimiters[count($delimiters) - 1]; // dernier

        array_pop($delimiters);

        foreach ($delimiters as $delimiter) {

            $string = str_replace($delimiter, $mainDelim, $string);
        }

        $result = explode($mainDelim, $string);
        return $result;
    }

}
