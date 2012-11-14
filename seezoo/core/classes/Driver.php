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

class SZ_Driver extends SeezooBase
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
			if ( ! file_exists(COREPATH . $loadFile) )
			{
				throw new Exception('DriverBase: ' . $driverBase . ' file not exists.');
				return FALSE;
			}
			require_once(COREPATH . $loadFile);
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
			$subClass = get_config('subclass_prefix');
		}
		else
		{
			// If coreclass not exists, load the original driver
			$subClass = '';
		}
		
		$isLoaded = FALSE;
		$loadFile = $driverPath . $subClass . $driverClass . '.php';
		
		// packages override
		foreach ( Seezoo::getPackage() as $pkg )
		{
			$extPath = PKGPATH . $pkg . '/';
			if ( file_exists($extPath . $loadFile) )
			{
				require_once($extPath . $loadFile);
				$Class    = $subClass . $driverClass;
				$isLoaded = TRUE;
				break;
			}
		}
		
		// Load a driver from extension/application directory when package driver is not exists.
		if ( $isLoaded === FALSE )
		{
			foreach ( array(EXTPATH, APPPATH) as $paths )
			{
				if ( file_exists($paths . $loadFile) )
				{
					require_once($paths . $loadFile);
					$Class = $subClass . $driverClass;
					break;
				}
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