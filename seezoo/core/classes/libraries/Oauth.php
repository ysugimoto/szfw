<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Oauth library
 * 
 * @package  Seezoo-Framework
 * @category Libraries
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
class SZ_Oauth extends SZ_Driver
{
	/**
	 * Support drivers
	 */
	protected $drivers = array(
		'facebook' => FALSE,
		'google'   => FALSE,
		'mixi'     => FALSE,
		'twitter'  => FALSE,
		'dropbox'  => FALSE
	);
	
	
	public function __construct($serviceName = null)
	{
		if ( $serviceName )
		{
			$this->service($serviceName);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Select use service
	 * 
	 * @access public
	 * @param  string $serviceName
	 * @param  array  $conf
	 */
	public function service($serviceName, $conf = array())
	{
		$serviceName = strtolower($serviceName);
		if ( ! isset($this->drivers[$serviceName]) )
		{
			throw new Exception('Service ' . $serviceName . ' does not support!');
		}
		
		if ( ! $this->drivers[$serviceName] )
		{
			$this->_loadDriver('oauth', ucfirst($serviceName) . '_oauth');
			$this->drivers[$serviceName] =& $this->driver;
		}
		else
		{
			$this->driver = $this->drivers[$serviceName];
		}
		
		$this->driver->configure($conf);
	}
}