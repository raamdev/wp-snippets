<?php
/**
 * WP PHP RV.
 *
 * @author @jaswsinc
 * @copyright WP Sharks™
 */
// PHP v5.2 compatible.

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
$GLOBALS['wp_php_rv'] = array(
    'os' => '', //os-required//

    'min'        => '7.0.4', //php-required-version//
    'bits'       => 64, //php-required-bits//
    'functions'  => array(), //php-required-functions//
    'extensions' => array(), //php-required-extensions//

    'wp' => array(
        'min' => '4.6', //wp-required-version//
    ),
); // The following keys are for back compat. only.
$GLOBALS['wp_php_rv']['rv'] = $GLOBALS['wp_php_rv']['min'];
$GLOBALS['wp_php_rv']['re'] = $GLOBALS['wp_php_rv']['extensions'];
