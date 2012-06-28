<?php if ( ! defined('SZ_EXEC') ) exit('access denied.');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Event module
 * 
 * @package  Seezoo-Framework
 * @category System
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Event
{
	/**
	 * Event handlers stacks
	 * @var array
	 */
	protected static $_handlers = array();
	
	
	/**
	 * Process flag
	 * @var bool
	 */
	protected static $isProcess = FALSE;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add listener
	 * 
	 * @access public static
	 * @param  string $type
	 * @param  mixed  $callback
	 * @param  bool   $isOnce
	 */
	public static function addListener($type, $callback, $isOnce = FALSE)
	{
		$set = array($callback, $isOnce);
		if ( isset(self::$_handlers[$type]) )
		{
			self::$_handlers[$type][] = $set;
		}
		else
		{
			self::$_handlers[$type] = array($set);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add listener from setting file
	 * 
	 * @access public static
	 * @param  string $filePath
	 */
	public static function addListenerFromFile($filePath = '')
	{
		if ( ! file_exists($filePath) )
		{
			return;
		}
		
		require($filePath);
		
		if ( ! isset($event) || ! is_array($event))
		{
			return;
		}
		
		foreach ( $event as $type => $handlers )
		{
			if ( isset($handlers[0]) && is_array($handlers[0]) )
			{
				foreach ( $handlers as $handler )
				{
					$callback = ( empty($handler['class']) )
					              ? $handler['function']
					              : array($handler['class'], $handler['function']);
					self::addListener($type, $callback, (bool)$handler['once']);
				}
			}
			else
			{
				$callback = ( empty($handlers['class']) )
				              ? $handlers['function']
				              : array($handlers['class'], $handlers['function']);
				self::addListener($type, $callback, (bool)$handlers['once']);
				
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Fire event
	 * 
	 * @access public static
	 * @param  string $type
	 * @param  mixed  $data
	 */
	public static function fire($type, $data = null)
	{
		if ( ! isset(self::$_handlers[$type]) || self::$isProcess === TRUE )
		{
			return;
		}
		
		self::$isProcess = TRUE;
		$new_handlers    = array();
		$evt             = new EventObject($type, $data);
		
		foreach ( self::$_handlers[$type] as $handler )
		{
			self::_fireEvent($handler, $evt);
			if ( $handler[1] === FALSE )
			{
				$new_handers[] = $handler;
			}
		}
		self::$_handlers[$type] = $new_handlers;
		self::$isProcess = FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Remove event handler
	 * 
	 * @access public static
	 * @param  string $type
	 * @param  mixed  $callback
	 */
	public static function removeListener($type, $callback = null)
	{
		if ( ! isset(self::$_handlers[$type]) )
		{
			return;
		}
		
		if ( ! $callback )
		{
			unset(self::$_handlers[$type]);
		}
		else
		{
			$new_handler = array();
			foreach ( self::$_handlers[$type] as $handler )
			{
				if ( $handler[1] !== $callback )
				{
					$new_handler[] = $handler;
				}
			}
			self::$_handlers[$type] = $new_handler;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Load event dispatcher class/function
	 * 
	 * @access public static
	 * @param  string $fileName
	 */
	public static function loadEventDispatcher($fileName)
	{
		if ( self::$isProcess === FALSE )
		{
			return;
		}
		
		$package  = Seezoo::$config['package'];
		$isLoaded = FALSE;
		
		foreach ( $package as $pkg )
		{
			if ( file_exists(EXTPATH . $pkg . '/events/' . $fileName . '.php') )
			{
				require_once(EXTPATH . $pkg . '/events/' . $fileName . '.php');
				$isLoaded = TRUE;
				break;
			}
		}
		
		if ( ! $isLoaded )
		{
			if( file_exists(APPPATH. 'events/' . $fileName . '.php') )
			{
				require_once(APPPATH . 'events/' . $fileName . '.php');
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Exec event fire
	 * 
	 * @access private static
	 * @param  mixed       $handler
	 * @param  EventObject $evt
	 */
	private static function _fireEvent($handler, $evt)
	{
		list($callback, $isOnce) = $handler;
		
		if ( is_array($callback) )
		{
			list($class, $method) = $callback;
			if ( ! is_object($class) )
			{
				$obj = new $class();
			}
			else
			{
				$obj = $class;
			}
			
			if ( ! method_exists($obj, $method) )
			{
				throw new BadMethodCallException(get_class($obj) . ': doesn\'t have method: ' . $method . '.');
			}
			$obj->{$method}($evt);
		}
		else
		{
			self::loadEventDispatcher($callback);
			if ( ! function_exists($callback) )
			{
				throw new BadFunctionCallException('Function ' . $callback , ' is not defined.');
			}
			$callback($evt);
		}
	}
	
	public static function onShutdown()
	{
		self::fire('shutdown');
	}
}


// ---------------------------------------------------------------


/**
 * Event Object class *
 */
class EventObject
{
	public $timestamp;
	public $type;
	public $data;
	
	public function __construct($type, $data)
	{
		$this->data      =& $data;
		$this->type      = $type;
		$this->timestamp = time();
	}
}
