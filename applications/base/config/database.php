<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Database settings
 * 
 * @package  Seezoo-Framework
 * @category config
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

// default group --------------------------------------------- //

$database['default']['host']         = 'localhost';
$database['default']['port']         = 3306;
$database['default']['username']     = 'root';
$database['default']['password']     = 'dawningblue';
$database['default']['driver']       = 'mysql';
$database['default']['dbname']       = 'posts';
$database['default']['table_prefix'] = '';
$database['default']['driver_name']  = '';
$database['default']['pconnect']     = TRUE;
$database['default']['query_debug']  = TRUE;
/*
$database['default']['path']         = '/Users/sugimoto/local/sqlite2/bin/';
$database['default']['port']         = 3306;
$database['default']['username']     = 'root';
$database['default']['password']     = 'dawningblue';
$database['default']['driver']       = 'sqlite2';
$database['default']['dbname']       = 'sample.db';
$database['default']['table_prefix'] = '';
$database['default']['driver_name']  = '';
$database['default']['pconnect']     = TRUE;
$database['default']['query_debug']  = TRUE;
*/

// sample sqlite group --------------------------------------- //
$database['sqlite']['path']        = '/Users/sugimoto/local/sqlite/';
$database['sqlite']['port']        = null;
$database['sqlite']['username']    = null;
$database['sqlite']['password']    = null;
$database['sqlite']['driver']      = 'sqlite3';
$database['sqlite']['dbname']      = 'test.sqlite3';
$database['sqlite']['pconnect']    = TRUE;
$database['sqlite']['driver_name']  = '';
$database['sqlite']['query_debug'] = FALSE;
