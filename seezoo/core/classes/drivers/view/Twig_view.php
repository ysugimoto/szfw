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

class SZ_Twig_view extends SZ_View_driver
{
	/**
	 * PHPTAL library path
	 * @var string
	 */
	protected $_libpath;
	protected $options = array();
	
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_libpath  = $this->env->getConfig('Twig_lib_path');
		$this->options   = (array)$this->env->getConfig('Twig');
		$this->_loadTwigAutoloader();
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
				$loader = new Twig_Loader_Filesystem(PKGPATH . $pkg . 'views/');
				$viewLoaded = $path . $this->_templateExtension;
				break;
			}
		}
		
		if ( ! $viewLoaded )
		{
			if ( file_exists(EXTPATH . 'views/' . $path . $this->_templateExtension) )
			{
				$loader = new Twig_Loader_Filesystem(EXTPATH . 'views/');
				
			}
			if ( file_exists(APPPATH . 'views/' . $path . $this->_templateExtension) )
			{
				$loader = new Twig_Loader_Filesystem(APPPATH . 'views/');
			}
			else
			{
				@ob_end_clean();
				throw new Exception('Unable to load requested file:' . $path . $this->_templateExtension, 500);
				return;
			}
		}
		
		$twigEnv = new Twig_Environment($loader, $this->options);
		$twig    = $twigEnv->loadTemplate($path . $this->_templateExtension);
		
		// TODO : implement Twig extension enables.
		
		// Twig execute!
		try
		{
			echo $twig->render($vars);
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
		unset($loader);
		unset($twigEnv);
		unset($twig);
	}
	
	
	// --------------------------------------------------
	
	
	/**
	 * load Twig class
	 * 
	 * @access protected
	 */
	protected function _loadTwigAutoloader()
	{
		if ( class_exists('Twig_Autoloader') )
		{
			return;
		}
		
		if ( ! file_exists($this->_libpath . 'Autoloader.php') )
		{
			throw new Exception('Twig Autoloader Class not exists!');
			return;
		}
		require_once($this->_libpath . 'Autoloader.php');
		Twig_Autoloader::register();
	}
}