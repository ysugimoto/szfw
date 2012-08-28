<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * System Basic settings
 * 
 * @package  Seezoo-Framework
 * @category config
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

/*
 * --------------------------------------------------
 * base_url
 * 
 * set a your application root path
 * ( need slash on last character )
 * --------------------------------------------------
 */

$config['base_url'] = 'http://localhost/szfw/htdocs/';


/*
 * --------------------------------------------------
 * enable_mod_rewrite
 * 
 * set TRUE when your application enables mod_rewrite
 * --------------------------------------------------
 */

$config['enable_mod_rewirte'] = FALSE;


/*
 * --------------------------------------------------
 * Production / Development environment
 * 
 * --------------------------------------------------
 */

$config['deploy_status'] = 'development';

/*
 * --------------------------------------------------
 * enable debug profiler
 * 
 * set TRUE when your application debug or develop
 * --------------------------------------------------
 */

$config['enable_debug'] = TRUE;


/*
 * --------------------------------------------------
 * Error reporting level
 * 
 * set repoting bit
 * --------------------------------------------------
 */

$config['error_reporting'] = E_ALL;


/*
 * --------------------------------------------------
 * Your application date-timezone
 * --------------------------------------------------
 */

$config['date_timezone'] = 'Asia/Tokyo';

/*
 * --------------------------------------------------
 * Server encoding
 * 
 * set a your application server encoding
 * ( if your server cannot change )
 * --------------------------------------------------
 */

$config['server_encoding'] = 'UTF-8';

/*
 * --------------------------------------------------
 * prefix/suffix
 * 
 * prefix:
 * If you extensioned Library or Core class,
 * Set original Prefix and make Prefixed class file
 * 
 * sufiix:
 * autoload and use methods suffix
 * --------------------------------------------------
 */

$config['subclass_prefix'] = 'EX_';
$config['model_suffix']    = 'Model';
$config['helper_suffix']   = 'Helper';

$config['controller_suffix'] = 'Controller';
$config['method_prefix']     = 'act_';


/*
 * --------------------------------------------------
 * Default database connection handle
 * 
 * Handle set by default to use to connect to Database
 * --------------------------------------------------
 */
 
 $config['default_database_connection_handle'] = 'default';

 
/*
 * --------------------------------------------------
 * Autoload
 * 
 * system loaded automatically
 * in controler instanciate process
 * --------------------------------------------------
 */
$config['autoload_database'] = FALSE;
$config['autoload_library']  = array();
$config['autoload_model']    = array();
$config['autoload_helper']   = array();


/*
 * --------------------------------------------------
 * default process mode
 * 
 * default process mode definition on Seezoo::init()
 * 
 * SZ_MODE_PROC    Function process mode
 * SZ_MODE_ACTION  Single action mode
 * SZ_MODE_DEFAULT Simple process mode
 * SZ_MODE_MVC     MVC-routing mode
 * --------------------------------------------------
 */
$config['default_process'] = SZ_MODE_MVC;


/*
 * --------------------------------------------------
 * default_controller
 * 
 * If requested root path or directory,
 * use this controller at default. 
 * --------------------------------------------------
 */

$config['default_controller'] = 'welcome';


/*
 * --------------------------------------------------
 * Logging setting
 * 
 * set a save to log filepath,
 * and set the logging level on your application status.
 * 0 : deploy
 * 1 : development
 * --------------------------------------------------
 */

$config['logging_level']     = 1;
$config['logging_save_type'] = 'file';
$config['logging_error']     = FALSE;
$config['logging_save_dir']  = SZPATH . 'logs/';



/*
 * --------------------------------------------------
 * package
 * 
 * Framework routing from subpackge system.
 * Controller, helper, model, library, and view detect subpackage.
 * --------------------------------------------------
 */

$config['package'] = array();


/*
 * --------------------------------------------------
 * Default rendering engine
 * 
 * Framework use this default template engine.
 * this parameter is able to set these parameters
 * 
 * default:
 *  native PHP viewfile
 * smarty:
 *  use Smarty.
 *  it's need to set a Smarty package in engine/smarty/.
 * phptal:
 *  use PHPTAL
 *  it's need to set a PHPTAL package in engine/phptal/.
 * --------------------------------------------------
 */

$config['rendering_engine'] = 'default';


/*
 * --------------------------------------------------
 * Smarty setting
 * 
 * If you choose smarty on View rendering,
 * you have to determine some directories path,
 * and add write permission.
 * --------------------------------------------------
 */

$config['smarty_lib_path']             = COREPATH . 'engines/Smarty/';

$config['Smarty']['plugins_dir']       = COREPATH . 'engines/Smarty/plugins/';
$config['Smarty']['compile_dir']       = ETCPATH . 'caches/smarty/templates_c/';
$config['Smarty']['config_dir']        = ETCPATH . 'caches/smarty/configs/';
$config['Smarty']['cache_dir']         = ETCPATH . 'caches/smarty/cache/';
$config['Smarty']['left_delimiter']    = '<!--{';
$config['Smarty']['right_delimiter']   = '}-->';
$config['Smarty']['default_modifiers'] = array('escape:"html"');


/*
 * --------------------------------------------------
 * PHPTAL setting
 * 
 * If you choose PHPTAL on View rendering,
 * you have to determine some directories path.
 * --------------------------------------------------
 */

$config['PHPTAL_lib_path'] = COREPATH . 'engines/phptal/';


/*
 * --------------------------------------------------
 * Twig setting
 * 
 * If you choose Twig on View rendering,
 * you have to determine some directories path.
 * --------------------------------------------------
 */

$config['Twig_lib_path'] = COREPATH . 'engines/Twig/';

$config['Twig']['cache'] = ETCPATH . 'caches/twig/';


/*
 * --------------------------------------------------
 * Cookie settings
 * 
 * Framework cookie settings. 
 * --------------------------------------------------
 */

$config['cookie_domain'] = '';
$config['cookie_path']   = '/';

/*
 * --------------------------------------------------
 * Session settings
 * 
 * Framework session settings. 
 * --------------------------------------------------
 */

$config['session_store_type']      = 'file';
$config['session_auth_key']        = 'seezoo_session_key';  // session authorize key
$config['session_lifetime']        = 500;                   // session expiration time ( sec digit )
$config['session_name']            = 'sz_session';          // session name
$config['session_encryption']      = TRUE;                  // encryption session auth key
$config['session_match_ip']        = TRUE;                  // session matching ip_address
$config['session_match_useragent'] = TRUE;                  // session matching User-Agent
$config['session_update_time']     = 300;                   // session_id update timing ( sec digit )


/* ----------------- File session config ---------------------- */
$config['session_filename_prefix'] = 'sess_';
$config['session_file_store_path'] = ETCPATH . 'caches/session/';

/* ----------------- Database session config ------------------ */
$config['session_db_tablename']    = 'sz_session';

/* ----------------- Memcache session config ------------------ */
$config['session_memcache_host']     = 'localhost';
$config['session_memcache_post']     = 11211;
$config['session_memcache_pconnect'] = TRUE;

/*
 * --------------------------------------------------
 * Encription settings
 * 
 * Encription key and Encription vector string 
 * --------------------------------------------------
 */

$config['encrypt_key_string']  = 'SeezooEncryption';
$config['encrypt_init_vector'] = 'szvector';

/*
 * --------------------------------------------------
 * Zip work mode
 * 
 * you can set these paramter:
 * auto   : auto detection
 * php    : use php ZipArchive class
 * manual : manually achive 
 * --------------------------------------------------
 */

$config['zip_mode'] = 'manual';

/*
 * --------------------------------------------------
 * Picture manipulation mode
 * 
 * you can set these paramter:
 * gd          : use GD libs ( default )
 * imagemagick : use Imagemagick ( need set imagemagick lib path )
 * --------------------------------------------------
 */

$config['picture_manipulation'] = 'gd';
$config['imagemagick_lib_path'] = '/usr/bin/convert';

// End of config.php