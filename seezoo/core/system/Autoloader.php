<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * AutoLoader class
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Autoloader
{
	/**
	 * Core classname prefix
	 * @var string
	 */
	private static $coreClassPrefix = 'SZ_';
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Autolorder register
	 * 
	 * @access public static
	 */
	public static function register()
	{
		$path = realpath(dirname(__FILE__));
		require_once($path . '/constants.php');
		require_once($path . '/common.php');
		
		spl_autoload_register(array('Autoloader', 'load'));
		spl_autoload_register(array('Event', 'loadEventDispatcher'));
	}
		

	// ---------------------------------------------------------------
	
	
	/**
	 * Autoload register handler
	 *
	 * @access public static
	 * @param string
	 */
	public static function load($className)
	{
		if ( strpos($className, self::$coreClassPrefix) === 0 )
		{
			$dir       = 'classes/';
			$className = substr($className, strlen(self::$coreClassPrefix));
		}
		else if ( $className === 'ActiveRecord' )
		{
			$dir = 'classes/';
		}
		else
		{
			$dir = 'system/';
		}
		
		if ( file_exists(COREPATH . $dir . $className . '.php') )
		{
			require_once(COREPATH . $dir . $className . '.php');
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Autoload register handler for testcase
	 *
	 * @access public static
	 * @param string
	 */
	public static function loadTestModule($name)
	{
		$module = preg_replace('/^SZ_/', '', $name);
		if ( file_exists(COREPATH . 'test/' . $module . '.php') )
		{
			require_once(COREPATH . 'test/' . $module . '.php');
		}
	}
}