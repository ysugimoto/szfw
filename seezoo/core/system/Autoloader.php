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
	
	
	/**
	 * Load destination directories
	 * @var array
	 */
	private static $loadDir = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Autolorder init
	 * 
	 * @access public static
	 */
	public static function init()
	{
		$path = dirname(__FILE__);
		require_once($path . '/constants.php');
		require_once($path . '/common.php');
		
		spl_autoload_register(array('Autoloader', '_initLoad'));
		spl_autoload_register(array('Event', 'loadEventDispatcher'));
	}
		

	// ---------------------------------------------------------------
	
	
	/**
	 * Register load destination directory
	 * 
	 * @param public static
	 * @param string $path
	 */
	public static function register($path)
	{
		// Register autoload first time!
		if ( count(self::$loadDir) === 0 )
		{
			spl_autoload_register(array('Autoloader', 'load'));
		}
		self::$loadDir[] = trail_slash($path);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Unregister load destination directory
	 * 
	 * @access public static
	 * @param  string $path
	 */
	public static function unregister($path)
	{
		if ( FALSE !== ( $key = array_search(trail_slash($path), self::$loadDir)) )
		{
			array_splice(self::$loadDir, $key, 1);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load Class from registerd paths
	 * 
	 * @access public static ( handler )
	 * @param string $className
	 */
	public static function load($className)
	{
		foreach ( self::$loadDir as $dir )
		{
			if ( file_exists($dir . $className . '.php') )
			{
				require_once($dir . $className . '.php');
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Autoload core load handler
	 *
	 * @access public static
	 * @param  string
	 */
	public static function _initLoad($className)
	{
		if ( strpos($className, self::$coreClassPrefix) === 0 )
		{
			$dir       = 'classes/';
			$className = substr($className, strlen(self::$coreClassPrefix));
		}
		else if ( strpos($className, 'ActiveRecord') === 0 )
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