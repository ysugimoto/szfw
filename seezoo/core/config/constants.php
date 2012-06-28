<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * System use constants
 * 
 * @package  Seezoo-Framework
 * @category config
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

define('SZ_MODE_MVC',      'mvc');
define('SZ_MODE_ACTION',   'action');
define('SZ_MODE_PROC',     'proc');
define('SZ_MODE_CLI',      'cli');
define('SZ_MODE_DEFAULT',  'default');
define('SZ_MODE_TESTCASE', 'test');

define('SZ_TMPL_DEFAULT', 'default');
define('SZ_TMPL_SMARTY',  'smarty');
define('SZ_TMPL_PHPTAL',  'phptal');
define('SZ_TMPL_TWIG',    'twig');

define('SZ_LOG_LEVEL_DEVELOPMENT', 1);
define('SZ_LOG_LEVEL_DEPLOY', 0);

define('SZ_ERROR_CODE_GENERAL', 500);
define('SZ_ERROR_CODE_DATABASE', 501);


// compatible PHP version constants -------------------- //

/**
 * -------------------------------------------
 * File upload error constants
 * -------------------------------------------
 */
if ( ! defined('UPLOAD_ERR_NO_TMP_DIR') ) define('UPLOAD_ERR_NO_TMP_DIR', 6); // PHP 5.0.3+ 
if ( ! defined('UPLOAD_ERR_CANT_WRITE') ) define('UPLOAD_ERR_CANT_WRITE', 7); // PHP 5.1.0+
if ( ! defined('UPLOAD_ERR_EXTENSION')  ) define('UPLOAD_ERR_EXTENSION',  8); // PHP 5.2.0+

/**
 * -------------------------------------------
 * ERROR constants
 * -------------------------------------------
 */
if ( ! defined('E_DEPRECATED')      ) define('E_DEPRECATED',       8192); // for less than PHP 5.3.0
if ( ! defined('E_USER_DEPRECATED') ) define('E_USER_DEPRECATED', 16384); // for less than PHP 5.3.0

