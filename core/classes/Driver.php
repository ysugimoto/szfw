<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Selectable backend as driver ( base class )
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Driver
{
	protected $driver;
	
	/**
	 * load a driver
	 * 
	 * @access protected
	 * @param  string $driverType
	 * @param  string $driverClass
	 * @param  bool   $instanticate
	 * @throws Exception
	 */
	protected function _loadDriver(
	                               $driverType,
	                               $driverClass,
	                               $instantiate = TRUE,
	                               $useBaseDriver = TRUE)
	{
		$driverPath = 'classes/drivers/' . $driverType . '/'; 
		$driverBase = ucfirst($driverType) . '_driver';
		$loadFile   = $driverPath . $driverBase. '.php';
		
		if ( $useBaseDriver )
		{
			// First, driver base class include
			if ( ! file_exists(SZPATH . 'core/' . $loadFile) )
			{
				throw new Exception('DriverBase: ' . $driverBase . ' file not exists.');
				return FALSE;
			}
			require_once(SZPATH . 'core/' . $loadFile);
		}
		
		if ( empty($driverClass) )
		{
			$Class = SZ_PREFIX_CORE . $driverBase;
			$this->driver = new $Class();
			return $this->driver;
		}
		
		// Mark the load class
		$Class  = '';
		if ( file_exists(SZPATH . 'core/' . $driverPath . $driverClass . '.php') )
		{
			require_once(SZPATH . 'core/' . $driverPath . $driverClass . '.php');
			$Class = SZ_PREFIX_CORE . $driverClass;
		}
		
		foreach ( Seezoo::getApplication() as $app )
		{
			if ( file_exists($app->path . $driverPath . $app->prefix . $driverClass . '.php') )
			{
				require_once($app->path . $driverPath . $app->prefix . $driverClass . '.php');
				$Class = ( class_exists($app->prefix . $driverClass, FALSE) )
				           ? $app->prefix . $driverClass
				           : $driverClass;
				break;
			}
			if ( file_exists($app->path . $driverPath . $driverClass . '.php') )
			{
				require_once($app->path . $driverPath . $driverClass . '.php');
				$Class = ( class_exists($app->prefix . $driverClass, FALSE) )
				           ? $app->prefix . $driverClass
				           : $driverClass;
				break;
			}
		}
		
		if ( $Class === '' || ! class_exists($Class, FALSE) )
		{
			throw new Exception('DriverClass:' . $Class . ' is not declared!');
		}
		
		$this->driver = ( $instantiate === TRUE ) ? new $Class() : $Class;
		return $this->driver;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Overload method
	 * calls like subclass member function
	 */
	public function __call($name, $arguments)
	{
		if ( method_exists($this, $name) )
		{
			return call_user_func_array(array($this, $name), $arguments);
		}
		else if ( is_object($this->driver)
		          && is_callable(array($this->driver, $name)) )
		{
			return call_user_func_array(array($this->driver, $name), $arguments);
		}
		throw new BadMethodCallException('Undefined method called ' . get_class($this) . '::' . $name . '.');
	}
}