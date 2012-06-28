<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Helper management class (at View)
 * 
 * @package  Seezoo-Framework
 * @category Classes
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Helpers
{
	
	/**
	 * Stack loaded helers
	 * @var array
	 */
	protected $_helpers = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Overload method
	 */
	public function __get($name)
	{
		$name = ucfirst($name);
		if ( isset($this->_helpers[$name]) )
		{
			return $this->_helpers[$name];
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Overload method
	 */
	public function __set($name, $helperObject)
	{
		$this->{$name} = $helperObject;
		$this->_helpers[$name] = $helperObject;
	}
}
