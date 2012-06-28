<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * View rendering with PHPTAL
 * 
 * @package  Seezoo-Framework
 * @category Drivers
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Phptal_view extends SZ_View_driver
{
	/**
	 * PHPTAL library path
	 * @var string
	 */
	protected $_phptalLibPath;
	
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_phptalLibPath  = $this->env->getConfig('PHPTAL_lib_path');
		$this->_loadPHPTAL();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * abstruct implements
	 * @see seezoo/core/drivers/view/SZ_View_driver::render()
	 */
	public function render($path, $vars, $return)
	{
		$this->_stackVars =& $vars;
		
		$viewLoaded = FALSE;
		$this->bufferStart();
		
		foreach ($this->_packages as $pkg )
		{
			if ( file_exists(PKGPATH . $pkg . 'views/' . $path . $this->_templateExtension) )
			{
				$TAL = new PHPTAL(PKGPATH . $pkg . 'views/' . $path . $this->_templateExtension);
				$viewLoaded = TRUE;
				break;
			}
		}
		
		if ( ! $viewLoaded )
		{
			if ( file_exists(EXTPATH . 'views/' . $path . '.php') )
			{
				$TAL = new PHPTAL(EXTPATH . 'views/' . $path . '.php');
			}
			else if ( file_exists(APPPATH . 'views/' . $path . $this->_templateExtension) )
			{
				$TAL = new PHPTAL(APPPATH . 'views/' . $path . $this->_templateExtension);
			}
			else
			{
				@ob_end_clean();
				throw new Exception('Unable to load requested file:' . $path . $this->_templateExtension, 500);
				return;
			}
		}
		
		// PHPTAL
		// assign value
		foreach ( $vars as $key => $val )
		{
			$TAL->{$key} = $val;
		}
		// execute
		try
		{
			echo $TAL->execute();
		}
		catch ( Exception $e )
		{
			throw $e;
		}
		
		$this->_stackVars = array();
		
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
		
		// destroy GC
		unset($TAL);
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * load PHPTAL class
	 */
	protected function _loadPHPTAL()
	{
		if ( class_exists('PHPTAL') )
		{
			return;
		}
		
		if ( ! file_exists($this->_phptalLibPath . 'PHPTAL.php') )
		{
			throw new Exception('PHPTAL Class not exists!');
			return;
		}
		require_once($this->_phptalLibPath . 'PHPTAL.php');
	}
}