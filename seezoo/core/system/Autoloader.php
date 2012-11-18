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
	 * PSR Load constant
	 */
	const LOAD_PSR = 'PSR';
	
	/**
	 * Load destination directories
	 * @var array
	 */
	private static $loadDir     = array();
	private static $loadPkgDir  = array();
	private static $loadExtDir  = array();
	private static $loadAppDir  = array();
	private static $loadCoreDir = array();
	
	
	private static $aliasClass = array(
	                                    'ActiveRecord' => SZ_PREFIX_CORE,
	                                    'Database'     => SZ_PREFIX_CORE
	                                  );
	
	
	public static $loadTargets = array(
	                                    'classes'           => 'classes',
	                                    'classes/helpers'   => 'helper',
	                                    'classes/libraries' => 'library',
	                                    'classes/models'    => 'model'
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
		
		spl_autoload_register(array('Autoloader', 'loadSystem'));
		spl_autoload_register(array('Autoloader', 'load'));

		foreach ( self::$loadTargets as $path => $loadType )
		{
			self::register(EXTPATH  . $path, $loadType, SZ_PREFIX_EXT);
			self::register(APPPATH  . $path, $loadType, SZ_PREFIX_APP);
			self::register(COREPATH . $path, $loadType, SZ_PREFIX_CORE);
			self::register(APPPATH  . $path, $loadType);
		}
		
		spl_autoload_register(array('Event', 'loadEventDispatcher'));
	}
		

	// ---------------------------------------------------------------
	
	
	/**
	 * Register load destination directory
	 * 
	 * @param public static
	 * @param string $path
	 * @param string $loadType
	 * @param string $prefix
	 */
	public static function register($path, $loadType = '', $prefix = NULL)
	{
		$path = trail_slash($path);
		switch ( $prefix )
		{
			case SZ_PREFIX_PKG:
				self::$loadPkgDir[$path]  = $loadType;
				break;
			case SZ_PREFIX_EXT:
				self::$loadExtDir[$path]  = $loadType;
				break;
			case SZ_PREFIX_APP:
				self::$loadAppDir[$path]  = $loadType;
				break;
			case SZ_PREFIX_CORE:
				self::$loadCoreDir[$path] = $loadType;
				break;
			default:
				self::$loadDir[$path]     = $loadType;
				break;
		}
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
		foreach ( array(self::$loadPkgDir, self::$loadExtDir, self::$loadAppDir, self::$loadDir) as $dir )
		{
			if ( array_key_exists($path, $dir) )
			{
				unset($dir[$path]);
				break;
			}
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
		$class  = $className;
		if ( Seezoo::hasPrefix($className) )
		{
			list($prefix, $class) = Seezoo::removePrefix($className, TRUE);
		}
		else if ( isset(self::$aliasClass[$className]) )
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
		
		foreach ( $dirs as $path => $type )
		{
			switch ( $type )
			{
				// PSR load type
				case self::LOAD_PSR:
					$className = str_replace(array('\\', '_'), '/', $class);
					if ( file_exists($path . $className . '.php') )
					{
						require_once($path . $className . '.php');
					}
					break;
				
				// Etc, Prefix-Suffixed load type
				default:
					foreach ( Seezoo::getSuffix($type) as $suffix )
					{
						$file = ( $suffix !== '' ) ? str_replace($suffix, '', $class) . $suffix : $class;
						$body =  ucfirst($file);
						if ( file_exists($path . $prefix . $body . '.php') )
						{
							require_once($path .$prefix . $body . '.php');
							break;
						}
						else if ( file_exists($path . $body . '.php') )
						{
							require_once($path . $body . '.php');
							break;
						}
					}
					break;
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
	public static function loadSystem($className)
	{
		if ( file_exists(COREPATH . 'system/' . $className . '.php') )
		{
			require_once(COREPATH . 'system/' . $className . '.php');
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