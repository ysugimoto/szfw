<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Session Library ( use Driver )
 * 
 * @package  Seezoo-Framework
 * @category Libraries
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Session extends SZ_Driver
{
	/**
	 * Enviroment ckass instance
	 * @var Enviroment
	 */
	protected $env;
	
	public function __construct()
	{
		$this->env = Seezoo::getENV();
		
		$driverName  = $this->env->getConfig('session_store_type');
		if ( ! $driverName )
		{
			$driverName = 'php';
		}
		$this->_loadDriver('session', ucfirst($driverName) . '_session');
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * set session data
	 * 
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 */
	public function set($key, $value = '')
	{
		$this->driver->set($key, $value);
		return $this;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * remove session data
	 * 
	 * @access public
	 * @param  string $key
	 */
	public function remove($key)
	{
		foreach ( (array)$key as $index )
		{
			$this->driver->remove($index);
		}
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * set flash session data
	 * 
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 */
	public function setFlash($key, $value = '')
	{
		$this->driver->setFlash($key, $value);
		return $this;
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * keep flash session data
	 * 
	 * @access public
	 * @param  string $key
	 */
	public function keepFlash($key = null)
	{
		$this->driver->keepFlash($key);
		return $this;
	}
}