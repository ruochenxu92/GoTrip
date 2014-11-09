<?php

/*
 * the map from the database stored int value to human readable string/characters
 * filter options: TODO TODO TODO
 * value:   0 => 不限
 *          1 => 更多
 *          >2 => other option
 */
return array(
    'product_discount' => array(
        'discount_code' => array(
            //code (max 5 letters) => price change
            // first calculate multipliers, then calculate addition/subtraction
            'addsubtract' => array(
                'LASHP' => -40,
            ),
            'multiplier' => array(
                'GQ' => 0.9,
            ),
            'associated_product_opt' => array(
                //this will link selectable_option with discount_code
                2 => 'LASHP',
            ),
        ),
    ),
    'filter' => array(
        //multi-select enabled filter
        'singleselect' => array(
            'duration_range',
//            'duration',
            'region',
            'startCity',
            'endCity',
        ),
        'multiselect' => array(
            'theme',
            'month',
            'includedPlace',
            'type',
            'special',
            'promo',
        ),
        'multiselect_diycombo' => array(
            'product_selected',
        ),
        'singleselect_diycombo' => array(
        ),
    ),
);

