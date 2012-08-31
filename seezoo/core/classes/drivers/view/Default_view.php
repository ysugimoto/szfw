<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * View rendering with Default PHP file
 * 
 * @package  Seezoo-Framework
 * @category Drivers
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Default_view extends SZ_View_driver
{
	public function __construct()
	{
		parent::__construct();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * abstruct implements
	 * @see seezoo/core/drivers/view/SZ_View_driver::render()
	 */
	public function render($viewFile, $vars, $return)
	{
		$this->_stackVars =& $vars;
		
		foreach ( $vars as $key => $val )
		{
			$$key = $val;
		}
		
		$this->bufferStart();
		require($viewFile);
		
		if ( $return === TRUE )
		{
			return $this->getBufferEnd();
		}
		
		if ( ob_get_level() > $this->_initBufLevel + 1 )
		{
			@ob_end_flush();
		}
		else
		{
			$this->getBufferEnd(TRUE);
		}
	}
}