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
	private static $loadPkgDir = array();
	private static $loadExtDir = array();
	private static $loadAppDir = array();
	private static $loadCoreDir = array();
	
	
	
	private static $aliasClass = array(
	                                    'ActiveRecord' => SZ_PREFIX_CORE,
	                                    'Database'     => SZ_PREFIX_CORE
	                                  );
	
	
	public static $loadTargets = array(
	                                    'classes'           => array(''),
	                                    'classes/helpers'   => array('Helper'),
	                                    'classes/libraries' => array(''),
	                                    'classes/models'    => array('Model', '_model')
	                                  );
	
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
		
		//spl_autoload_register(array('Autoloader', 'loadCoreModule'));
		
		self::register(COREPATH . 'system', array(''));
		spl_autoload_register(array('Autoloader', 'load'));
		
		$packages = Seezoo::getPackage();
		foreach ( self::$loadTargets as $path => $suffix )
		{
			foreach ( $packages as $pkg )
			{
				self::register(PKGPATH . $pkg . '/' . $path, $suffix, SZ_PREFIX_PKG);
			}
			self::register(EXTPATH .  $path, $suffix, SZ_PREFIX_EXT);
			self::register(APPPATH .  $path, $suffix, SZ_PREFIX_APP);
			self::register(COREPATH . $path, $suffix, SZ_PREFIX_CORE);
		}
		
		spl_autoload_register(array('Event', 'loadEventDispatcher'));
	}
		

	// ---------------------------------------------------------------
	
	
	/**
	 * Register load destination directory
	 * 
	 * @param public static
	 * @param string $path
	 */
	public static function register($path, $suffix = array(), $prefix = NULL)
	{
		$path = trail_slash($path);
		switch ( $prefix )
		{
			case SZ_PREFIX_PKG:
				self::$loadPkgDir[$path] = $suffix;
				break;
			case SZ_PREFIX_EXT:
				self::$loadExtDir[$path] = $suffix;
				break;
			case SZ_PREFIX_APP:
				self::$loadAppDir[$path] = $suffix;
				break;
			case SZ_PREFIX_CORE:
				self::$loadCoreDir[$path] = $suffix;
				break;
			default:
				self::$loadDir[$path] = $suffix;
				break;
		}
		// Register autoload first time!
		//if ( count(self::$loadDir) === 0 )
		//{
		//	spl_autoload_register(array('Autoloader', 'load'));
		//}
		//self::$loadDir[] = trail_slash($path);
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
		$path = trail_slash($path);
		foreach ( array(self::$loadPkgDir,self::$loadExtDir,self::$loadAppDir,self::$loadDir) as $dir )
		{
			if ( array_key_exists($path, $dir) )
			{
				unset($dir[$path]);
				break;
			}
			//if ( FALSE !== ( $key = array_search(trail_slash($path), $dir)) )
			//{
			//	array_splice($dir, $key, 1);
			//	break;
			//}
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
		$prefix = '';
		if ( FALSE !== ($point = strpos($className, '_')) )
		{
			$prefix = substr($className, 0, ++$point);
			$class  = substr($className, $point);
		}
		
		if ( isset(self::$aliasClass[$className]) )
		{
			$prefix = self::$aliasClass[$className];
			$class  = $className;
		}
		
		switch ( $prefix )
		{
			case SZ_PREFIX_PKG:
				$dirs = self::$loadPkgDir;
				break;
			case SZ_PREFIX_EXT:
				$dirs = self::$loadExtDir;
				break;
			case SZ_PREFIX_APP:
				$dirs = self::$loadAppDir;
				break;
			case SZ_PREFIX_CORE:
				$dirs = self::$loadCoreDir;
				break;
			default:
				$dirs  = self::$loadDir;
				$class = $className;
				break;
		}
		
		foreach ( $dirs as $path => $suffixes )
		{
			foreach ( $suffixes as $suffix )
			{
				$file = ( $suffix !== '' ) ? str_replace($suffix, '', $class) : $class;
				if ( file_exists($path . ucfirst($file) . $suffix . '.php') )
				{
					require_once($path . ucfirst($file) . $suffix . '.php');
					break;
				}
				else if ( file_exists($path . lcfirst($file) . $suffix . '.php') )
				{
					require_once($path . lcfirst($file) . $suffix . '.php');
					break;
				}
				
			}
		}
		//foreach ( self::$loadDir as $dir )
		//{
		//	if ( file_exists($dir . $className . '.php') )
		//	{
		//		require_once($dir . $className . '.php');
		//	}
		//}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Autoload core load handler
	 *
	 * @access public static
	 * @param  string
	 */
	public static function loadCoreModule($className)
	{
		if ( strpos($className, self::$coreClassPrefix) === 0 )
		{
			$dir       = 'classes/';
			$className = substr($className, strlen(self::$coreClassPrefix));
		}
		else if ( isset(self::$aliasClass[$className]) )
		{
			$dir = self::$aliasClass[$className];
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