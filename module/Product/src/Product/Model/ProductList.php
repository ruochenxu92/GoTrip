<?php

namespace Product\Model;

class ProductList {

    const LIST_CACHE_NAME = 'product_list';
    const DIY_CACHE_NAME = 'product_diy';

    protected $_filterOpts;
    private $is_diy = false;
    private $is_already_in_diy_process = false; // product_selected is set
    
    private $filter_config;
    private $cacheInfoName, //cacheName without sort/page number
            $cacheName, //used to fetched the pre-rendered html page cache
            $cacheRawInfoName, //name used to store raw cache info name without sort/page, it is $cacheInfoName without cache_name_header
            $cacheRawName, //used to store raw cache info, it is $cacheInfoName without cache_name_header
            $cacheDiyComboRawName = ''; //store raw info of the combo info

    ////filter option:
    // region, includedPlace, duration, startCity, endCity, theme, promo
    //value = 0 -> all
    //       1 -> more
    //       >2 -> other options

    public function __construct($id, $request, $filter_config, $cache_name_header = ProductList::LIST_CACHE_NAME) {
        if ($cache_name_header == ProductList::DIY_CACHE_NAME) {
            $this->is_diy = true;
        }
        if (isset($request['product_selected']) && !empty($request['product_selected'])) {
            $this->is_already_in_diy_process = true;
        }

        $this->filter_config = $filter_config;
        $filterOpts = array();

        if (isset($request['sort']) && !empty($request['sort']))
            $filterOpts['sort'] = $request['sort'];
        else
            $filterOpts['sort'] = '0-0';

        $filterOpts["page"] = $id;
        if (empty($filterOpts["page"]))
            $filterOpts["page"] = 1;

        //======================================================================
        //special processing for product_selected
        if ($this->is_diy) {
            $filter_multiselect_combo = $filter_config['multiselect_diycombo'];
            $filter_singleselect_combo = $filter_config['singleselect_diycombo'];

            foreach ($filter_singleselect_combo as $key) {
                if (isset($request[$key]) && !empty($request[$key]))
                    $filterOpts[$key] = $request[$key];
                else
                    $filterOpts[$key] = 0;
            }
            //process multi-select and remove duplicate and (sort in ascending order->no sorting at this stage)
            foreach ($filter_multiselect_combo as $key) {
                if (isset($request[$key]) && !empty($request[$key]))
                    $filterOpts[$key] = $request[$key];
                else
                    $filterOpts[$key] = 0;

                if (is_string($filterOpts[$key])) {
                    $filterOpts[$key] = array_unique(explode('-', $filterOpts[$key]));
                    if (!is_array($filterOpts[$key]))
                        $filterOpts[$key] = array($filterOpts[$key]); //make it into an array

                    //sort($filterOpts[$key]);
                }else if (!is_array($filterOpts[$key])) {
                    $filterOpts[$key] = array(0); //convert 0 into {[0]=>0} anyway
                }
            }
            $this->cacheDiyComboRawName = '_diycombo';
            foreach ($filter_singleselect_combo as $key) {
                $this->cacheDiyComboRawName .= $filterOpts[$key] . '_';
            }
            
            foreach ($filter_multiselect_combo as $key) {
                $this->cacheDiyComboRawName .= $this->concatFilterOpt($filterOpts[$key]) . '_';
            }
        }
        //======================================================================

        $filter_multiselect = $filter_config['multiselect'];
        $filter_singleselect = $filter_config['singleselect'];

        foreach ($filter_singleselect as $key) {
            if (isset($request[$key]) && !empty($request[$key]))
                $filterOpts[$key] = $request[$key];
            else
                $filterOpts[$key] = 0;
        }

        //process multi-select and remove duplicate and sort in ascending order
        foreach ($filter_multiselect as $key) {
            if (isset($request[$key]) && !empty($request[$key]))
                $filterOpts[$key] = $request[$key];
            else
                $filterOpts[$key] = 0;

            if (is_string($filterOpts[$key])) {
                $filterOpts[$key] = array_unique(explode('-', $filterOpts[$key]));
                if (!is_array($filterOpts[$key]))
                    $filterOpts[$key] = array($filterOpts[$key]); //make it into an array

                sort($filterOpts[$key]);
            }else if (!is_array($filterOpts[$key])) {
                $filterOpts[$key] = array(0); //convert 0 into {[0]=>0} anyway
            }
        }
        $this->cacheRawInfoName = '_';
        if($this->is_already_in_diy_process){
            $this->cacheRawInfoName .= $this->cacheDiyComboRawName .'_';
        }
        
        foreach ($filter_singleselect as $key) {
            $this->cacheRawInfoName .= $filterOpts[$key] . '_';
        }
        foreach ($filter_multiselect as $key) {
            $this->cacheRawInfoName .= $this->concatFilterOpt($filterOpts[$key]) . '_';
        }

        $this->cacheInfoName = $cache_name_header . $this->cacheRawInfoName;

        $this->cacheRawName = $this->cacheRawInfoName
                . 's' . $filterOpts['sort']
                . '_p' . $filterOpts['page'];

        $this->cacheName = $this->cacheInfoName
                . 's' . $filterOpts['sort']
                . '_p' . $filterOpts['page'];

        //special process to sort
        $filterOpts['sort'] = explode('-', $filterOpts['sort']);
        $this->_filterOpts = $filterOpts;
    }

    public function getIsDiy() {
        return $this->is_diy;
    }

    public function getFilterConfig() {
        return $this->filter_config;
    }

    public function getCacheInfoName() {
        return $this->cacheInfoName;
    }

    public function getCacheName() {
        return $this->cacheName;
    }

    public function getCacheRawInfoName() {
        return $this->cacheRawInfoName;
    }

    public function getCacheRawName() {
        return $this->cacheRawName;
    }

    /**
     * @param parameterName : region, startCity, ...
     * @return array() | false
     */
    public function getParameter($parameterName) {
        if (isset($this->_filterOpts[$parameterName])) {
            return $this->_filterOpts[$parameterName];
        }
        return false;
    }

    private function concatFilterOpt($filterOptArray) {
        $oneFilterStr = '';
        foreach ($filterOptArray as $each_opt) {
            $oneFilterStr .= $each_opt . '-'; //the last one will also have a '-' deliminator
        }
        return $oneFilterStr;
    }

}
