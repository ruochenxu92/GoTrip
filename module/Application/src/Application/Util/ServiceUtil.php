<?php

namespace Application\Util;

class ServiceUtil {
    
    //search subarray
    public static function getSubArrayRow($arr, $key, $value){
        $theRow= array();
        foreach($arr as $aRow){
            if($aRow[$key] == $value){
                $theRow = $aRow;
                break;
            }
        }
        return $theRow;
    }
}
