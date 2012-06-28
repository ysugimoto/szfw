<?php if ( ! defined('SZ_EXEC') ) exit('access denied.');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Factory Instances and management
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SeezooFactory
{
	/**
	 * Configure included data stacks
	 * @var array
	 */
	private static $config    = array();
	
	
	/**
	 * classes stacks
	 * @var array
	 */
	private static $classes   = array();
	
	
	/**
	 * Libraries stacks
	 * @var array
	 */
	private static $libraries = array();
	
	
	/**
	 * Lead stacks
	 * @var array
	 */
	private static $leads     = array();
	
	
	/**
	 * Models stacks
	 * @var array
	 */
	private static $models    = array();
	
	
	/**
	 * Helpers stacks
	 * @var array
	 */
	private static $helpers   = array();
	
	
	/**
	 * Tools stacks
	 * @var array
	 */
	private static $tools     = array();
	
	
	/**
	 * Vendors stacks
	 * @var array
	 */
	private static $vendors   = array();
	
	
	/**
	 * Process instances stacks
	 * @var array
	 */
	private static $instances = array();
	
	
	/**
	 * Database instances stacks
	 * @var array
	 */
	private static $dbs       = array();
	
	
	/**
	 * process level
	 * @var int
	 */
	private static $level     = 0;
	
	
	/**
	 * Destruct stacks
	 * @var array
	 */
	private static $_destructStack = array();
	
	
	/**
	 * Stacks for debugging
	 * @var array
	 */
	private static $_debugStack    = array();
	
	
	
	/**
	 * suffix of page_link
	 * @var array
	 */
	private static $_queryStringSuffix = array();
	
	
	/**
	 * Stack of booted pakcages
	 * @var array
	 */
	private static $_bootedPackage = array();
	
	
	/**
	 * Stack of system processes
	 * @var array
	 */
	private static $_processes = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add system process
	 * @access public static
	 * @param object $proc
	 */
	public static function addProcess($proc)
	{
		return array_push(self::$_processes, $proc);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get current system process
	 * @access public static
	 * @return object
	 */
	public static function getProcess()
	{
		return end(self::$_processes);
	}

	
	// ---------------------------------------------------------------
	
	
	/**
	 * Stack exists
	 * 
	 * @access public static
	 * @param  string $type
	 * @param  string $name
	 * @return bool
	 */
	public static function exists($type, $name)
	{
		$type = str_replace('/', '_', $type);
		return ( isset(self::${$type}) && isset(self::${$type}[$name]) ) ? TRUE : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add stack
	 * 
	 * @access public static
	 * @param  string $type
	 * @param  string $name
	 * @param  mixed  $data
	 * @param  mixed  $alias
	 */
	public static function push($type, $name, $alias, $data)
	{
		$type = str_replace('/', '_', $type);
		if ( isset(self::${$type}) )
		{
			self::${$type}[$name] = array($data, ( $alias ) ? $alias : $name);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get from stack
	 * 
	 * @access public static
	 * @param  string $type
	 * @param  string $name
	 * @return mixed
	 */
	public static function get($type, $name)
	{
		return self::${$type}[$name][0];
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Mark the process level
	 * 
	 * @access public static
	 * @param  object $instance
	 * @return int    $level
	 */
	public static function sub(&$instance)
	{
		self::$instances[] = $instance;
		return ++self::$level;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * release the process
	 * 
	 * @access public static
	 * @param  int    $level
	 */
	public static function endSub($level)
	{
		$instance = array_pop(self::$instances);
		self::$_debugStack[] = $instance;
		unset($instance);
		--self::$level;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get a current process instance
	 * 
	 * @access public static
	 * @return object instance
	 */
	public static function getInstance()
	{
		return end(self::$instances);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get current process instances
	 * 
	 * @access public static
	 * @return object instance
	 */
	public static function getInstancesForDebug()
	{
		return self::$_debugStack;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get current level
	 * 
	 * @access public static
	 * @return int    $level
	 */
	public static function getLevel()
	{
		return self::$level;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Check already booted package
	 * 
	 * @access public static
	 * @param string $packageName
	 * @return bool
	 */
	public static function isBootedPackage($packageName)
	{
		if ( in_array($packageName, self::$_bootedPackage) )
		{
			return TRUE;
		}
		self::$_bootedPackage[] = $packageName;
		return FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add database stack
	 * 
	 * @access public static
	 * @param  string $group
	 * @param  object $db
	 */
	public static function pushDB($group, $db)
	{
		self::$dbs[$group] =& $db;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get database instance from stack
	 * 
	 * @access public static
	 * @param  string $group
	 * @return mixed
	 */
	public static function getDB($group)
	{
		return ( isset(self::$dbs[$group]) ) ? self::$dbs[$group] : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Queue destruct 
	 * 
	 * @access private static
	 */
	private static function onDescruct()
	{
		foreach ( self::$_destructStack as $destruct )
		{
			call_user_func($destruct);
		}
		self::$_destructStack = null;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Kill all stacks
	 * 
	 * @access public static
	 */
	public static function killAll()
	{
		self::$classes =
		self::$helpers =
		self::$instances =
		self::$libraries = 
		self::$models = null;
		
		foreach ( self::$dbs as $db )
		{
			$db->disconnect();
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	public static function getQueryStringSuffix()
	{
		return implode('&amp;', self::$_queryStringSuffix);
	}
	
	
	// ---------------------------------------------------------------
	
	
	public static function addQueryStringSuffix($key, $value, $replace = FALSE)
	{
		if ( isset(self::$_queryStringSuffix[$key]) )
		{
			if ( $replace === TRUE )
			{
				self::$_queryStringSuffix[$key] = rawurlencode($key) . '=' . rawurlencode($value);
			}
		}
		else
		{
			self::$_queryStringSuffix[$key] = rawurlencode($key) . '=' . rawurlencode($value);
		}
	}
	
	// ---------------------------------------------------------------
	
	public static function getLoadedClasses()
	{
		return self::$classes;
	}
	
	public static function getLoadedLibraries()
	{
		return self::$libraries;
	}
	
	public static function getLoadedHelpers()
	{
		return self::$helpers;
	}
	
	public static function getLoadedHelperClasses()
	{
		return self::$helpers_class;
	}
	
	public static function getLoadedModels()
	{
		return self::$models;
	}
	
	public static function getConnectedDBs()
	{
		return self::$dbs;
	}
	
}
