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
		$ENV        = Seezoo::getENV();
		$packages   = Seezoo::getPackage();
		$subClass   = $ENV->getConfig('subclass_prefix');
		$driverPath = 'classes/drivers/' . $driverType . '/'; 
		$driverBase = ucfirst($driverType) . '_driver';
		
		if ( $useBaseDriver )
		{
			// First, driver base class include
			if ( ! file_exists(COREPATH . $driverPath . $driverBase. '.php') )
			{
				throw new Exception('DriverBase: ' . $driverBase . ' file not exists.');
				return FALSE;
			}
			require_once(COREPATH . $driverPath . $driverBase. '.php');
		}
		
		if ( empty($driverClass) )
		{
			$Class = 'SZ_' . $driverBase;
			$this->driver = new $Class();
			return $this->driver;
		}
		
		// Mark the load class
		$Class  = '';
		if ( file_exists(COREPATH . $driverPath . $driverClass . '.php') )
		{
			require_once(COREPATH . $driverPath . $driverClass . '.php');
			$Class    = 'SZ_' . $driverClass;
		}
		else
		{
			// If coreclass not exists, load the original driver
			$subClass = '';
		}
		
		$isLoaded = FALSE;
		
		// packages override
		foreach ( $packages as $pkg )
		{
			$extPath = PKGPATH . $pkg . '/';
			if ( file_exists($extPath . $driverPath . $subClass . $driverClass . '.php' ) )
			{
				require_once($extPath . $driverPath . $subClass . $driverClass . '.php');
				$Class    = $subClass . $driverClass;
				$isLoaded = TRUE;
				break;
			}
		}
		
		// Load a driver from apps directory when package driver is not exists.
		if ( $isLoaded === FALSE )
		{
			if ( file_exists(APPPATH . $driverPath . $subClass . $driverClass . '.php') )
			{
				require_once(APPPATH . $driverPath . $subClass . $driverClass . '.php');
				$Class = $subClass . $driverClass;
			}
		}
		
		if ( $Class === '' || ! class_exists($Class) )
		{
			throw new Exception('DirverClass:' . $subClass . $driverClass . ' is not declared!');
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