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
	protected function _loadDriver($driverType, $driverClass, $instantiate = TRUE, $useBaseDriver = TRUE)
	{
		$ENV        = Seezoo::getENV();
		$packages   = $ENV->getConfig('package');
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
		
		if ( ! file_exists(COREPATH . $driverPath . $driverClass . '.php') )
		{
			throw new Exception('Driver: ' . $driverClass . ' file not exists.');
			return FALSE;
		}
		
		require_once(COREPATH . $driverPath . $driverClass . '.php');
		$Class = 'SZ_' . $driverClass;
		
		// packages override
		foreach ( $packages as $pkg )
		{
			$extPath = APPPATH . rtrim($pkg, '/') . '/';
			if ( file_exists($extPath . $driverPath . $subClass . $driverClass . '.php' ) )
			{
				require_once($extPath . $this->_driverPath . $subClass . $driverClass . '.php');
				if ( ! class_exists($subClass . $driverClass) )
				{
					throw new Exception('class:' . $subClass . $driverClass . ' is not declared!');
				}
				$Class = $subClass . $driverClass;
				break;
			}
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