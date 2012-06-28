<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * View rendering with Smarty
 * 
 * @package  Seezoo-Framework
 * @category Drivers
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class SZ_Smarty_view extends SZ_View_driver
{
	/**
	 * Smartry library path
	 * @var string
	 */
	protected $_smartyLibPath;
	
	
	/**
	 * Smarty setting from config
	 * @var array
	 */
	protected $_smartySettings;
	
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_smartyLibPath  = $this->env->getConfig('smarty_lib_path');
		$this->_smartySettings = $this->env->getConfig('Smarty');
		
		$this->_loadSmarty();
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * abstruct implements
	 * @see seezoo/core/drivers/view/SZ_View_driver::render()
	 */
	public function render($path, $vars, $return)
	{
		$this->_stackVars =& $vars;
		
		$Smarty = new Smarty();
		foreach ( $this->_smartySettings as $property => $setting )
		{
			$Smarty->{$property} = $setting;
		}
		
		foreach ( $vars as $key => $val )
		{
			$Smarty->assign($key, $val);
		}
		
		$viewLoaded = FALSE;
		$this->bufferStart();
		
		// Does packaged viewfile exists?
		foreach ($this->_packages as $pkg )
		{
			if ( file_exists(PKGPATH . $pkg . 'views/' . $path . $this->_templateExtension) )
			{
				$Smarty->template_dir = PKGPATH . $pkg . 'views/';
				$Smarty->display($path . $this->_templateExtension);
				$viewLoaded = TRUE;
				break;
			}
		}
		
		if ( ! $viewLoaded )
		{
			if ( file_exists(EXTPATH . 'views/' . $path . $this->_templateExtension) )
			{
				$Smarty->template_dir = APPPATH . 'views/';
				$Smarty->display($path . $this->_templateExtension);
			}
			else if ( file_exists(APPPATH . 'views/' . $path . $this->_templateExtension) )
			{
				$Smarty->template_dir = APPPATH . 'views/';
				$Smarty->display($path . $this->_templateExtension);
			}
			else
			{
				@ob_end_clean();
				throw new Exception('Unable to load requested file:' . $path . $this->_templateExtension);
				return;
			}
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
		unset($Smarty);
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * load Smarty class
	 */
	protected function _loadSmarty()
	{
		if ( class_exists('Smarty') )
		{
			return;
		}
		
		if ( ! file_exists($this->_smartyLibPath . 'Smarty.class.php') )
		{
			throw new Exception('Smarty Class not exists!');
			return;
		}
		require_once($this->_smartyLibPath . 'Smarty.class.php');
	}
}